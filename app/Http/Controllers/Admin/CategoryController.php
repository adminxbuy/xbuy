<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    /**
     * Display a listing of the categories.
     */
    public function index()
    {
        $categories = Category::parentOnly()->with('children')->orderBy('sort_order')->get();
        $trashedCategories = Category::onlyTrashed()->with('parent')->orderBy('deleted_at', 'desc')->get();
        return view('admin.categories', compact('categories', 'trashedCategories'));
    }

    /**
     * Store a newly created category in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:categories,id',
            'icon' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = 'cat_' . time() . '_' . rand(100, 999) . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('website_assets/images'), $filename);
            $imagePath = '/website_assets/images/' . $filename;
        } elseif ($request->has('selected_image_path') && $request->input('selected_image_path') !== '') {
            $imagePath = $request->input('selected_image_path');
            if ($imagePath === 'remove') {
                $imagePath = null;
            }
        }

        Category::create([
            'name' => $request->input('name'),
            'parent_id' => $request->input('parent_id'),
            'icon' => $request->input('icon'),
            'description' => $request->input('description'),
            'image' => $imagePath,
            'meta_title' => $request->input('meta_title'),
            'meta_description' => $request->input('meta_description'),
            'is_active' => true,
            'sort_order' => Category::where('parent_id', $request->input('parent_id'))->count(),
        ]);

        return back()->with('success', 'Category created successfully.');
    }

    /**
     * Update the specified category in storage.
     */
    public function update(Request $request, $id)
    {
        $category = Category::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:categories,id|different:id',
            'icon' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
        ]);

        $slug = Str::slug($request->input('name'));
        $originalSlug = $slug;
        $count = 1;
        while (Category::where('slug', $slug)->where('id', '!=', $id)->exists()) {
            $slug = $originalSlug . '-' . $count;
            $count++;
        }

        $data = [
            'name' => $request->input('name'),
            'parent_id' => $request->input('parent_id'),
            'icon' => $request->input('icon'),
            'description' => $request->input('description'),
            'meta_title' => $request->input('meta_title'),
            'meta_description' => $request->input('meta_description'),
            'slug' => $slug,
        ];

        if ($request->hasFile('image')) {
            if ($category->image && file_exists(public_path($category->image))) {
                @unlink(public_path($category->image));
            }
            $file = $request->file('image');
            $filename = 'cat_' . time() . '_' . rand(100, 999) . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('website_assets/images'), $filename);
            $data['image'] = '/website_assets/images/' . $filename;
        } elseif ($request->has('selected_image_path') && $request->input('selected_image_path') !== '') {
            $imagePath = $request->input('selected_image_path');
            if ($imagePath === 'remove') {
                $imagePath = null;
            }
            $data['image'] = $imagePath;
        }

        $category->update($data);

        return back()->with('success', 'Category updated successfully.');
    }

    /**
     * Remove the specified category from storage.
     */
    public function destroy($id)
    {
        $category = Category::findOrFail($id);

        // If it's a parent, cascade delete children categories (soft delete)
        if ($category->parent_id === null) {
            $category->children()->delete();
        }

        $category->delete();

        return back()->with('success', 'Category moved to trash.');
    }

    /**
     * Toggle the active status of a category.
     */
    public function toggleActive($id)
    {
        $category = Category::findOrFail($id);
        $category->update(['is_active' => !$category->is_active]);

        return response()->json([
            'success' => true,
            'message' => 'Category status updated successfully.',
            'is_active' => $category->is_active
        ]);
    }

    /**
     * Reorder categories.
     */
    public function reorder(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:categories,id'
        ]);

        foreach ($request->input('ids') as $index => $id) {
            Category::where('id', $id)->update(['sort_order' => $index]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Categories reordered successfully.'
        ]);
    }
}
