<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BrandController extends Controller
{
    /**
     * Display a listing of the brands.
     */
    public function index()
    {
        $brands = Brand::with('categories')->orderBy('sort_order')->get();
        $categories = Category::parentOnly()->get();
        return view('admin.brands', compact('brands', 'categories'));
    }

    /**
     * Store a newly created brand in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'category_ids' => 'nullable|array',
            'category_ids.*' => 'exists:categories,id',
        ]);

        $logoPath = null;
        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $filename = 'brand_' . time() . '_' . rand(100, 999) . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('website_assets/images'), $filename);
            $logoPath = '/website_assets/images/' . $filename;
        } elseif ($request->has('selected_logo_path') && $request->input('selected_logo_path') !== '') {
            $logoPath = $request->input('selected_logo_path');
            if ($logoPath === 'remove') {
                $logoPath = null;
            }
        }

        $brand = Brand::create([
            'name' => $request->input('name'),
            'logo' => $logoPath,
            'is_active' => true,
            'sort_order' => Brand::count(),
        ]);

        if ($request->has('category_ids')) {
            $brand->categories()->sync($request->input('category_ids'));
        }

        return back()->with('success', 'Brand created successfully.');
    }

    /**
     * Update the specified brand in storage.
     */
    public function update(Request $request, $id)
    {
        $brand = Brand::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'category_ids' => 'nullable|array',
            'category_ids.*' => 'exists:categories,id',
        ]);

        $data = [
            'name' => $request->input('name'),
            'slug' => Str::slug($request->input('name')),
        ];

        if ($request->hasFile('logo')) {
            if ($brand->logo && file_exists(public_path($brand->logo))) {
                @unlink(public_path($brand->logo));
            }
            $file = $request->file('logo');
            $filename = 'brand_' . time() . '_' . rand(100, 999) . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('website_assets/images'), $filename);
            $data['logo'] = '/website_assets/images/' . $filename;
        } elseif ($request->has('selected_logo_path') && $request->input('selected_logo_path') !== '') {
            $logoPath = $request->input('selected_logo_path');
            if ($logoPath === 'remove') {
                $logoPath = null;
            }
            $data['logo'] = $logoPath;
        }

        $brand->update($data);

        if ($request->has('category_ids')) {
            $brand->categories()->sync($request->input('category_ids'));
        } else {
            $brand->categories()->detach();
        }

        return back()->with('success', 'Brand updated successfully.');
    }

    /**
     * Remove the specified brand from storage.
     */
    public function destroy($id)
    {
        $brand = Brand::findOrFail($id);

        if ($brand->listings()->count() > 0) {
            return back()->with('error', 'Cannot delete brand because it contains listings.');
        }

        if ($brand->logo && file_exists(public_path($brand->logo))) {
            @unlink(public_path($brand->logo));
        }

        $brand->delete();

        return back()->with('success', 'Brand deleted successfully.');
    }

    /**
     * Toggle the active status of a brand.
     */
    public function toggleActive($id)
    {
        $brand = Brand::findOrFail($id);
        $brand->update(['is_active' => !$brand->is_active]);

        return response()->json([
            'success' => true,
            'message' => 'Brand status updated successfully.',
            'is_active' => $brand->is_active
        ]);
    }
}
