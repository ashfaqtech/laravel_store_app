<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class CartController extends Controller
{
    public function index()
    {
        $cart = Session::get('cart', []);
        $total = 0;
        $products = [];

        if (!empty($cart)) {
            $productIds = array_keys($cart);
            $products = Product::whereIn('id', $productIds)->get();
            
            foreach ($products as $product) {
                $quantity = $cart[$product->id];
                $total += $product->price * $quantity;
                $product->quantity = $quantity;
            }
        }

        return view('cart', compact('products', 'total'));
    }

    public function add(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1'
        ]);

        $product = Product::findOrFail($request->product_id);
        $cart = Session::get('cart', []);

        // Check if product exists in cart, if yes add quantity
        if (isset($cart[$request->product_id])) {
            $cart[$request->product_id] += $request->quantity;
        } else {
            $cart[$request->product_id] = $request->quantity;
        }

        Session::put('cart', $cart);

        return redirect()->back()->with('success', 'Product added to cart successfully!');
    }

    public function update(Request $request)
    {
        $request->validate([
            'quantities' => 'required|array',
            'quantities.*' => 'required|integer|min:0'
        ]);

        $cart = Session::get('cart', []);
        $updated = false;

        foreach ($request->quantities as $productId => $quantity) {
            if ($quantity > 0) {
                $cart[$productId] = $quantity;
                $updated = true;
            } else {
                unset($cart[$productId]);
                $updated = true;
            }
        }

        if ($updated) {
            Session::put('cart', $cart);
            return redirect()->route('cart.index')->with('success', 'Cart updated successfully!');
        }

        return redirect()->route('cart.index');
    }

    public function remove($productId)
    {
        $cart = Session::get('cart', []);

        if (isset($cart[$productId])) {
            unset($cart[$productId]);
            Session::put('cart', $cart);
            return redirect()->route('cart.index')->with('success', 'Product removed from cart!');
        }

        return redirect()->route('cart.index');
    }

    public function clear()
    {
        Session::forget('cart');
        return redirect()->route('cart.index')->with('success', 'Cart cleared successfully!');
    }

    public function checkout()
    {
        $cart = Session::get('cart', []);
        
        if (empty($cart)) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty!');
        }

        $total = 0;
        $products = Product::whereIn('id', array_keys($cart))->get();
        
        foreach ($products as $product) {
            $quantity = $cart[$product->id];
            $total += $product->price * $quantity;
            $product->quantity = $quantity;
        }

        return view('checkout', compact('products', 'total'));
    }
}
