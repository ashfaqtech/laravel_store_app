<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // Get monthly orders count
        $orderCount = Order::whereMonth('created_at', Carbon::now()->month)->count();

        // Get total products count
        $productCount = Product::count();

        // Get total users count
        $userCount = User::count();

        // Calculate monthly revenue
        $revenue = Order::whereMonth('created_at', Carbon::now()->month)
                       ->where('status', 'completed')
                       ->sum('total_amount');

        // Get recent orders
        $recentOrders = Order::with('user')
                            ->latest()
                            ->take(10)
                            ->get();

        return view('admin.dashboard', compact(
            'orderCount',
            'productCount',
            'userCount',
            'revenue',
            'recentOrders'
        ));
    }
}
