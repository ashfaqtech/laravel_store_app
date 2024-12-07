<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Storage;

class CategoryController extends Controller
{
    protected $messages = [
        'name.required' => 'The category name is required.',
        'name.max' => 'The category name cannot be longer than :max characters.',
        'name.unique' => 'This category name is already taken. Please choose a different name.',
        'parent_id.exists' => 'The selected parent category is invalid.',
        'image.image' => 'The file must be an image.',
        'image.mimes' => 'The image must be a file of type: jpeg, png, jpg.',
        'image.max' => 'The image size cannot be larger than 2MB.'
    ];

    public function index()
    {
        $categories = Category::withCount('products')->paginate(10);
        return view('admin.categories.index', compact('categories'));
    }

    public function create()
    {
        $categories = Category::all(); // For parent category selection
        return view('admin.categories.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|max:255|unique:categories',
            'description' => 'nullable',
            'parent_id' => 'nullable|exists:categories,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
        ], $this->messages);

        try {
            $data = $request->all();
            $slug = Str::slug($request->name);
            
            // Check if slug exists and append number if it does
            $count = 1;
            $originalSlug = $slug;
            while (Category::where('slug', $slug)->exists()) {
                $slug = $originalSlug . '-' . $count;
                $count++;
            }
            $data['slug'] = $slug;

            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $filename = time() . '.' . $image->getClientOriginalExtension();

                // Ensure directories exist
                Storage::makeDirectory('public/categories');
                Storage::makeDirectory('public/categories/thumbnails');
                
                // Save the original image
                $image->storeAs('public/categories', $filename);
                
                try {
                    // Create and save thumbnail
                    $thumbnailPath = storage_path('app/public/categories/thumbnails/' . $filename);
                    Image::make($image->getRealPath())
                        ->resize(300, 300, function ($constraint) {
                            $constraint->aspectRatio();
                            $constraint->upsize();
                        })
                        ->save($thumbnailPath);
                } catch (\Exception $e) {
                    // If thumbnail creation fails, delete the original image
                    Storage::delete('public/categories/' . $filename);
                    throw new \Exception('Failed to create image thumbnail. Please try again.');
                }
                
                $data['image'] = $filename;
            }

            Category::create($data);
            return redirect()->route('admin.categories.index')
                ->with('success', 'Category created successfully');

        } catch (\Exception $e) {
            return back()->withInput()
                ->withErrors(['error' => $e->getMessage() ?: 'There was a problem creating the category. Please try again.']);
        }
    }

    public function edit(Category $category)
    {
        $categories = Category::where('id', '!=', $category->id)->get(); // Exclude current category
        return view('admin.categories.edit', compact('category', 'categories'));
    }

    public function update(Request $request, Category $category)
    {
        $request->validate([
            'name' => 'required|max:255|unique:categories,name,' . $category->id,
            'description' => 'nullable',
            'parent_id' => 'nullable|exists:categories,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
        ], $this->messages);

        try {
            $data = $request->all();
            $slug = Str::slug($request->name);
            
            // Check if slug exists (excluding current category) and append number if it does
            $count = 1;
            $originalSlug = $slug;
            while (Category::where('slug', $slug)->where('id', '!=', $category->id)->exists()) {
                $slug = $originalSlug . '-' . $count;
                $count++;
            }
            $data['slug'] = $slug;

            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $filename = time() . '.' . $image->getClientOriginalExtension();

                // Ensure directories exist
                Storage::makeDirectory('public/categories');
                Storage::makeDirectory('public/categories/thumbnails');
                
                // Save the original image
                $image->storeAs('public/categories', $filename);
                
                try {
                    // Create and save thumbnail
                    $thumbnailPath = storage_path('app/public/categories/thumbnails/' . $filename);
                    Image::make($image->getRealPath())
                        ->resize(300, 300, function ($constraint) {
                            $constraint->aspectRatio();
                            $constraint->upsize();
                        })
                        ->save($thumbnailPath);

                    // Delete old images if they exist
                    if ($category->image) {
                        Storage::delete([
                            'public/categories/' . $category->image,
                            'public/categories/thumbnails/' . $category->image
                        ]);
                    }
                    
                    $data['image'] = $filename;
                } catch (\Exception $e) {
                    // If thumbnail creation fails, delete the original image
                    Storage::delete('public/categories/' . $filename);
                    throw new \Exception('Failed to create image thumbnail. Please try again.');
                }
            }

            $category->update($data);
            return redirect()->route('admin.categories.index')
                ->with('success', 'Category updated successfully');

        } catch (\Exception $e) {
            return back()->withInput()
                ->withErrors(['error' => $e->getMessage() ?: 'There was a problem updating the category. Please try again.']);
        }
    }

    public function destroy(Category $category)
    {
        try {
            // Check if category has products
            if ($category->products()->count() > 0) {
                return back()->withErrors(['error' => 'Cannot delete category because it has associated products.']);
            }

            // Delete category images if they exist
            if ($category->image) {
                Storage::delete([
                    'public/categories/' . $category->image,
                    'public/categories/thumbnails/' . $category->image
                ]);
            }

            $category->delete();
            return redirect()->route('admin.categories.index')
                ->with('success', 'Category deleted successfully');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'There was a problem deleting the category. Please try again.']);
        }
    }
}
