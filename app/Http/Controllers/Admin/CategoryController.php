<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

class CategoryController extends Controller
{
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
        ]);

        $data = $request->all();
        $data['slug'] = Str::slug($request->name);

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $filename = time() . '.' . $image->getClientOriginalExtension();
            
            // Save the original image
            $image->storeAs('public/categories', $filename);
            
            // Create and save thumbnail
            $thumbnail = Image::make($image)
                ->resize(300, 300, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
            $thumbnail->save(storage_path('app/public/categories/thumbnails/' . $filename));
            
            $data['image'] = $filename;
        }

        Category::create($data);
        return redirect()->route('admin.categories.index')->with('success', 'Category created successfully');
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
        ]);

        $data = $request->all();
        $data['slug'] = Str::slug($request->name);

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $filename = time() . '.' . $image->getClientOriginalExtension();
            
            // Save the original image
            $image->storeAs('public/categories', $filename);
            
            // Create and save thumbnail
            $thumbnail = Image::make($image)
                ->resize(300, 300, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
            $thumbnail->save(storage_path('app/public/categories/thumbnails/' . $filename));
            
            // Delete old image if exists
            if ($category->image) {
                @unlink(storage_path('app/public/categories/' . $category->image));
                @unlink(storage_path('app/public/categories/thumbnails/' . $category->image));
            }
            
            $data['image'] = $filename;
        }

        $category->update($data);
        return redirect()->route('admin.categories.index')->with('success', 'Category updated successfully');
    }

    public function destroy(Category $category)
    {
        // Check if category has products
        if ($category->products()->count() > 0) {
            return back()->with('error', 'Cannot delete category with associated products');
        }

        // Delete category images
        if ($category->image) {
            @unlink(storage_path('app/public/categories/' . $category->image));
            @unlink(storage_path('app/public/categories/thumbnails/' . $category->image));
        }

        $category->delete();
        return redirect()->route('admin.categories.index')->with('success', 'Category deleted successfully');
    }
}
