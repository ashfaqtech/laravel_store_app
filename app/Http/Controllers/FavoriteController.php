<?php

namespace App\Http\Controllers;

use App\Models\Favorite;
use App\Models\Product;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $favorites = auth()->user()->favorites()->with('product')->latest()->paginate(12);
        return view('favorites.index', compact('favorites'));
    }

    public function store(Request $request, Product $product)
    {
        $favorite = auth()->user()->favorites()->where('product_id', $product->id)->first();
        
        if ($favorite) {
            return response()->json([
                'message' => 'Product is already in your wishlist'
            ], 409);
        }

        auth()->user()->favorites()->create([
            'product_id' => $product->id
        ]);

        return response()->json([
            'message' => 'Product added to wishlist'
        ]);
    }

    public function destroy(Product $product)
    {
        auth()->user()->favorites()->where('product_id', $product->id)->delete();

        if (request()->wantsJson()) {
            return response()->json([
                'message' => 'Product removed from wishlist'
            ]);
        }

        return back()->with('success', 'Product removed from wishlist');
    }
}
