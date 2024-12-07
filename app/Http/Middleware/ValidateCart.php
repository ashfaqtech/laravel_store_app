<?php

namespace App\Http\Middleware;

use App\Models\Product;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class ValidateCart
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $cart = Session::get('cart', []);
        $invalidItems = [];
        $updatedCart = [];

        if (!empty($cart)) {
            foreach ($cart as $productId => $quantity) {
                $product = Product::find($productId);
                
                // Check if product exists and is active
                if (!$product || !$product->active) {
                    $invalidItems[] = $productId;
                    continue;
                }

                // Check if quantity is valid
                if ($quantity <= 0) {
                    $invalidItems[] = $productId;
                    continue;
                }

                // Add valid item to updated cart
                $updatedCart[$productId] = $quantity;
            }

            // Update cart if there were invalid items
            if (count($invalidItems) > 0) {
                Session::put('cart', $updatedCart);
                Session::flash('cart_updated', 'Some items in your cart were removed because they are no longer available.');
            }
        }

        return $next($request);
    }
}
