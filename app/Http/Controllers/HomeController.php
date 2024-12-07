<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Slider;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $sliders = Slider::where('active', true)->get();
        $featuredProducts = Product::where('featured', true)
            ->with('category')
            ->take(8)
            ->get();
        $categories = Category::whereNull('parent_id')
            ->with(['children', 'products'])
            ->take(6)
            ->get();

        return view('home', compact('sliders', 'featuredProducts', 'categories'));
    }

    /**
     * Show the shop page.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function shop(Request $request)
    {
        $query = Product::query()->with('category');

        // Category filter
        if ($request->filled('category')) {
            $categoryIds = explode(',', $request->category);
            $query->whereIn('category_id', $categoryIds);
        }

        // Price filter
        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }
        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        // Sort
        switch ($request->sort) {
            case 'price_low':
                $query->orderBy('price', 'asc');
                break;
            case 'price_high':
                $query->orderBy('price', 'desc');
                break;
            case 'newest':
                $query->latest();
                break;
            default:
                $query->orderBy('name', 'asc');
        }

        // Search
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
        }

        $products = $query->paginate(12)->withQueryString();
        $categories = Category::whereNull('parent_id')->with('children')->get();

        return view('shop', compact('products', 'categories'));
    }

    /**
     * Show the product details page.
     *
     * @param  string  $slug
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function product($slug)
    {
        $product = Product::where('slug', $slug)
            ->with(['category', 'reviews.user'])
            ->firstOrFail();
        
        $relatedProducts = Product::where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->take(4)
            ->get();

        return view('product', compact('product', 'relatedProducts'));
    }

    /**
     * Show the category page.
     *
     * @param  string  $slug
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function category($slug)
    {
        $category = Category::where('slug', $slug)
            ->with(['products', 'children.products'])
            ->firstOrFail();

        $products = $category->products()
            ->paginate(12);

        return view('category', compact('category', 'products'));
    }
}
