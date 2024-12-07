<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    protected $messages = [
        'name.required' => 'The product name is required.',
        'name.max' => 'The product name cannot be longer than :max characters.',
        'price.required' => 'The price is required.',
        'price.numeric' => 'The price must be a number.',
        'price.min' => 'The price must be greater than zero.',
        'category_id.required' => 'Please select a category.',
        'category_id.exists' => 'The selected category is invalid.',
        'image.image' => 'The file must be an image.',
        'image.mimes' => 'The image must be a file of type: jpeg, png, jpg.',
        'image.max' => 'The image size cannot be larger than 2MB.'
    ];

    public function index()
    {
        $products = Product::with('category')->latest()->paginate(10);
        return view('admin.products.index', compact('products'));
    }

    public function create()
    {
        $categories = Category::all();
        return view('admin.products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|max:255',
            'description' => 'nullable',
            'price' => 'required|numeric|min:0',
            'category_id' => 'required|exists:categories,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
        ], $this->messages);

        try {
            $data = $request->all();
            $data['slug'] = $this->generateUniqueSlug($request->name);

            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $filename = time() . '.' . $image->getClientOriginalExtension();

                // Ensure directories exist
                Storage::makeDirectory('public/products');
                Storage::makeDirectory('public/products/thumbnails');
                
                // Save the original image
                $image->storeAs('public/products', $filename);
                
                try {
                    // Create and save thumbnail
                    $thumbnailPath = storage_path('app/public/products/thumbnails/' . $filename);
                    Image::make($image->getRealPath())
                        ->resize(300, 300, function ($constraint) {
                            $constraint->aspectRatio();
                            $constraint->upsize();
                        })
                        ->save($thumbnailPath);
                } catch (\Exception $e) {
                    // If thumbnail creation fails, delete the original image
                    Storage::delete('public/products/' . $filename);
                    throw new \Exception('Failed to create image thumbnail. Please try again.');
                }
                
                $data['image'] = $filename;
            }

            Product::create($data);
            return redirect()->route('admin.products.index')
                ->with('success', 'Product created successfully');

        } catch (\Exception $e) {
            return back()->withInput()
                ->withErrors(['error' => $e->getMessage() ?: 'There was a problem creating the product. Please try again.']);
        }
    }

    public function edit(Product $product)
    {
        $categories = Category::all();
        return view('admin.products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product)
    {
        $request->validate([
            'name' => 'required|max:255',
            'description' => 'nullable',
            'price' => 'required|numeric|min:0',
            'category_id' => 'required|exists:categories,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
        ], $this->messages);

        try {
            $data = $request->all();
            
            if ($request->name !== $product->name) {
                $data['slug'] = $this->generateUniqueSlug($request->name, $product->id);
            }

            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $filename = time() . '.' . $image->getClientOriginalExtension();

                // Ensure directories exist
                Storage::makeDirectory('public/products');
                Storage::makeDirectory('public/products/thumbnails');
                
                // Save the original image
                $image->storeAs('public/products', $filename);
                
                try {
                    // Create and save thumbnail
                    $thumbnailPath = storage_path('app/public/products/thumbnails/' . $filename);
                    Image::make($image->getRealPath())
                        ->resize(300, 300, function ($constraint) {
                            $constraint->aspectRatio();
                            $constraint->upsize();
                        })
                        ->save($thumbnailPath);

                    // Delete old images if they exist
                    if ($product->image) {
                        Storage::delete([
                            'public/products/' . $product->image,
                            'public/products/thumbnails/' . $product->image
                        ]);
                    }
                    
                    $data['image'] = $filename;
                } catch (\Exception $e) {
                    // If thumbnail creation fails, delete the original image
                    Storage::delete('public/products/' . $filename);
                    throw new \Exception('Failed to create image thumbnail. Please try again.');
                }
            }

            $product->update($data);
            return redirect()->route('admin.products.index')
                ->with('success', 'Product updated successfully');

        } catch (\Exception $e) {
            return back()->withInput()
                ->withErrors(['error' => $e->getMessage() ?: 'There was a problem updating the product. Please try again.']);
        }
    }

    public function destroy(Product $product)
    {
        try {
            // Delete product images if they exist
            if ($product->image) {
                Storage::delete([
                    'public/products/' . $product->image,
                    'public/products/thumbnails/' . $product->image
                ]);
            }

            $product->delete();
            return redirect()->route('admin.products.index')
                ->with('success', 'Product deleted successfully');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'There was a problem deleting the product. Please try again.']);
        }
    }

    protected function generateUniqueSlug($name, $ignoreId = null)
    {
        $slug = Str::slug($name);
        $count = 1;
        $originalSlug = $slug;
        
        while (true) {
            $query = Product::where('slug', $slug);
            if ($ignoreId) {
                $query->where('id', '!=', $ignoreId);
            }
            
            if (!$query->exists()) {
                break;
            }
            
            $slug = $originalSlug . '-' . $count;
            $count++;
        }
        
        return $slug;
    }
}
