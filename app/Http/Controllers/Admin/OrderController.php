<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Exports\OrdersExport;
use App\Mail\OrderStatusUpdated;
use PDF;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $orders = Order::with(['user', 'items'])
                      ->latest()
                      ->paginate(20);
                      
        $totalOrders = Order::count();
        $pendingOrders = Order::where('status', 'pending')->count();
        $completedOrders = Order::where('status', 'completed')->count();
        $cancelledOrders = Order::where('status', 'cancelled')->count();
        
        $totalRevenue = Order::where('payment_status', 'paid')
                            ->sum('total_amount');

        return view('admin.orders.index', compact(
            'orders',
            'totalOrders',
            'pendingOrders',
            'completedOrders',
            'cancelledOrders',
            'totalRevenue'
        ));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function show(Order $order)
    {
        $order->load(['user', 'items.product']);
        return view('admin.orders.show', compact('order'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|in:pending,processing,completed,cancelled',
            'payment_status' => 'required|in:pending,paid,failed,refunded',
            'notes' => 'nullable|string|max:500'
        ]);

        $oldStatus = $order->status;
        $newStatus = $request->status;

        $order->update([
            'status' => $newStatus,
            'payment_status' => $request->payment_status,
            'notes' => $request->notes
        ]);

        // Send status update email to customer
        if ($oldStatus !== $newStatus) {
            Mail::to($order->shipping_email)
                ->queue(new OrderStatusUpdated($order));
        }

        return redirect()
            ->route('admin.orders.show', $order)
            ->with('success', 'Order updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function destroy(Order $order)
    {
        if ($order->status === 'completed') {
            return back()->with('error', 'Cannot delete completed orders');
        }

        $order->items()->delete();
        $order->delete();

        return redirect()->route('admin.orders.index')
            ->with('success', 'Order deleted successfully');
    }

    public function export(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'status' => 'nullable|in:all,pending,processing,completed,cancelled'
        ]);

        $orders = Order::with(['user', 'items'])
                      ->whereBetween('created_at', [
                          $request->start_date,
                          $request->end_date
                      ]);

        if ($request->status && $request->status !== 'all') {
            $orders->where('status', $request->status);
        }

        $orders = $orders->get();

        return Excel::download(
            new OrdersExport($orders),
            'orders_' . now()->format('Y-m-d') . '.xlsx'
        );
    }

    public function printInvoice(Order $order)
    {
        $order->load(['user', 'items.product']);
        $pdf = PDF::loadView('admin.orders.invoice', compact('order'));
        
        return $pdf->download('invoice_' . $order->order_number . '.pdf');
    }

    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:update_status,delete',
            'status' => 'required_if:action,update_status|in:pending,processing,completed,cancelled',
            'orders' => 'required|array',
            'orders.*' => 'exists:orders,id'
        ]);

        if ($request->action === 'update_status') {
            Order::whereIn('id', $request->orders)
                 ->update(['status' => $request->status]);

            $message = count($request->orders) . ' orders updated successfully';
        } else {
            Order::whereIn('id', $request->orders)->delete();
            $message = count($request->orders) . ' orders deleted successfully';
        }

        return back()->with('success', $message);
    }

    public function filter(Request $request)
    {
        $orders = Order::with(['user', 'items'])
                      ->when($request->status, function($query, $status) {
                          return $query->where('status', $status);
                      })
                      ->when($request->payment_status, function($query, $status) {
                          return $query->where('payment_status', $status);
                      })
                      ->when($request->search, function($query, $search) {
                          return $query->where(function($q) use ($search) {
                              $q->where('order_number', 'like', "%{$search}%")
                                ->orWhere('shipping_email', 'like', "%{$search}%")
                                ->orWhere('shipping_name', 'like', "%{$search}%")
                                ->orWhereHas('user', function($q) use ($search) {
                                    $q->where('name', 'like', "%{$search}%")
                                      ->orWhere('email', 'like', "%{$search}%");
                                });
                          });
                      })
                      ->when($request->date_range, function($query) use ($request) {
                          $dates = explode(' - ', $request->date_range);
                          return $query->whereBetween('created_at', [
                              $dates[0],
                              $dates[1]
                          ]);
                      })
                      ->latest()
                      ->paginate(20);

        if ($request->ajax()) {
            return view('admin.orders.partials.orders-table', compact('orders'))->render();
        }

        return view('admin.orders.index', compact('orders'));
    }

    public function dashboard()
    {
        $dailyOrders = Order::selectRaw('DATE(created_at) as date, COUNT(*) as count, SUM(total_amount) as revenue')
                           ->groupBy('date')
                           ->orderBy('date', 'DESC')
                           ->limit(30)
                           ->get();

        $topProducts = OrderItem::selectRaw('product_name, SUM(quantity) as total_quantity, SUM(subtotal) as total_revenue')
                               ->groupBy('product_name')
                               ->orderByDesc('total_quantity')
                               ->limit(10)
                               ->get();

        $recentOrders = Order::with('user')
                            ->latest()
                            ->limit(5)
                            ->get();

        return view('admin.orders.dashboard', compact(
            'dailyOrders',
            'topProducts',
            'recentOrders'
        ));
    }
}
