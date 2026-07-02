<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BrandController extends Controller
{
    /**
     * Get all active brands, optionally filtered by category.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Brand::active()->orderBy('sort_order');

        if ($request->has('category') && !empty($request->input('category'))) {
            $categorySlug = $request->input('category');
            $category = Category::where('slug', $categorySlug)->first();
            
            if (!$category) {
                return $this->errorResponse('Category not found', 404);
            }

            // Resolve to parent category if it is a subcategory
            $categoryId = $category->parent_id ?: $category->id;

            $query->whereHas('categories', function ($q) use ($categoryId) {
                $q->where('categories.id', $categoryId);
            });
        }

        $brands = $query->get();

        return $this->successResponse($brands, 'Brands retrieved successfully');
    }
}
