<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Services\InvoiceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use Stripe\Stripe;
use Stripe\PaymentIntent;

class OrderController extends Controller
{
    protected $invoiceService;

    public function __construct(InvoiceService $invoiceService)
    {
        $this->invoiceService = $invoiceService;
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'address' => 'required|string|max:500',
            'city' => 'required|string|max:255',
            'postal_code' => 'required|string|max:20',
            'payment_method' => 'required|in:card',
            'stripeToken' => 'required_if:payment_method,card'
        ]);

        $cart = Session::get('cart', []);
        if (empty($cart)) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty!');
        }

        try {
            DB::beginTransaction();

            // Create order
            $order = new Order();
            $order->user_id = auth()->id();
            $order->order_number = $order->generateOrderNumber();
            $order->total_amount = 0; // Will be updated after adding items
            $order->status = 'pending';
            $order->payment_status = 'pending';
            $order->payment_method = $request->payment_method;
            $order->shipping_name = $request->name;
            $order->shipping_email = $request->email;
            $order->shipping_phone = $request->phone;
            $order->shipping_address = $request->address;
            $order->shipping_city = $request->city;
            $order->shipping_postal_code = $request->postal_code;
            $order->save();

            // Add order items
            $total = 0;
            foreach ($cart as $productId => $quantity) {
                $product = Product::findOrFail($productId);
                
                $orderItem = new OrderItem([
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'price' => $product->price,
                    'quantity' => $quantity
                ]);
                
                $order->items()->save($orderItem);
                $total += $product->price * $quantity;
            }

            // Update order total
            $order->total_amount = $total;
            $order->save();

            // Process payment
            if ($request->payment_method === 'card') {
                Stripe::setApiKey(config('services.stripe.secret'));
                
                $paymentIntent = PaymentIntent::create([
                    'amount' => $total * 100, // Amount in cents
                    'currency' => 'usd',
                    'payment_method_types' => ['card'],
                    'metadata' => [
                        'order_id' => $order->id,
                        'order_number' => $order->order_number
                    ]
                ]);

                $order->transaction_id = $paymentIntent->id;
                $order->save();
            }

            // Clear cart
            Session::forget('cart');

            // Send order confirmation email
            Mail::to($order->shipping_email)->queue(new OrderConfirmation($order));

            DB::commit();

            return redirect()->route('orders.show', $order)
                           ->with('success', 'Order placed successfully! Order number: ' . $order->order_number);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'There was an error processing your order. Please try again.');
        }
    }

    public function show(Order $order)
    {
        if (auth()->id() !== $order->user_id) {
            abort(403);
        }

        return view('orders.show', compact('order'));
    }

    public function index()
    {
        $orders = auth()->user()->orders()->latest()->paginate(10);
        return view('orders.index', compact('orders'));
    }

    public function cancel(Order $order)
    {
        if (auth()->id() !== $order->user_id || !in_array($order->status, ['pending', 'processing'])) {
            abort(403);
        }

        $order->updateStatus('cancelled');
        return back()->with('success', 'Order cancelled successfully.');
    }

    public function downloadInvoice(Order $order)
    {
        $this->authorize('view', $order);
        return $this->invoiceService->downloadInvoice($order);
    }

    public function viewInvoice(Order $order)
    {
        $this->authorize('view', $order);
        return $this->invoiceService->streamInvoice($order);
    }
}
