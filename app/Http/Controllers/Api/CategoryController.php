<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Listing;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Get all active parent categories with children and listing count.
     */
    public function index(): JsonResponse
    {
        $categories = Category::active()
            ->parentOnly()
            ->with(['children' => function ($q) {
                $q->active();
            }])
            ->orderBy('sort_order')
            ->get();

        return $this->successResponse($categories, 'Categories retrieved successfully');
    }

    /**
     * Get a single category with subcategories and paginated listings.
     */
    public function show(string $slug): JsonResponse
    {
        $category = Category::active()->where('slug', $slug)->first();

        if (!$category) {
            return $this->errorResponse('Category not found', 404);
        }

        $category->load(['children' => function ($q) {
            $q->active();
        }]);

        $query = Listing::active()->with(['seller', 'images']);

        if ($category->parent_id === null) {
            $subIds = $category->children->pluck('id');
            $query->where(function ($q) use ($category, $subIds) {
                $q->where('category_id', $category->id)
                  ->orWhereIn('subcategory_id', $subIds);
            });
        } else {
            $query->where('subcategory_id', $category->id);
        }

        $listings = $query->orderBy('created_at', 'desc')->paginate(20);

        // Get spec templates for this category
        $specTemplates = \App\Models\SpecTemplate::where('category_id', $category->id)->orderBy('sort_order')->get();

        return $this->successResponse([
            'category' => $category,
            'spec_templates' => $specTemplates,
            'listings' => $listings
        ], 'Category details retrieved successfully');
    }

    /**
     * Get listings in a category filtered and paginated.
     */
    public function listings(Request $request, string $slug): JsonResponse
    {
        $category = Category::active()->where('slug', $slug)->first();

        if (!$category) {
            return $this->errorResponse('Category not found', 404);
        }

        $query = Listing::active()->with(['seller', 'images']);

        if ($category->parent_id === null) {
            $subIds = $category->children()->active()->pluck('id');
            $query->where(function ($q) use ($category, $subIds) {
                $q->where('category_id', $category->id)
                  ->orWhereIn('subcategory_id', $subIds);
            });
        } else {
            $query->where('subcategory_id', $category->id);
        }

        // Filter by Grade
        if ($request->has('grade') && !empty($request->input('grade'))) {
            $query->where('grade', $request->input('grade'));
        }

        // Filter by Price range
        if ($request->has('price_min') && is_numeric($request->input('price_min'))) {
            $query->where('price', '>=', $request->input('price_min'));
        }
        if ($request->has('price_max') && is_numeric($request->input('price_max'))) {
            $query->where('price', '<=', $request->input('price_max'));
        }

        // Filter by Badge (Seller profile badge level)
        if ($request->has('badge') && !empty($request->input('badge'))) {
            $query->whereHas('seller', function ($q) use ($request) {
                $q->where('badge_level', $request->input('badge'));
            });
        }

        // Filter by Warranty status
        if ($request->has('warranty') && !empty($request->input('warranty'))) {
            $warranty = $request->input('warranty');
            if ($warranty === 'manufacturer') {
                $query->where('manufacturer_warranty_status', 'active');
            } elseif ($warranty === 'seller') {
                $query->whereNotNull('seller_warranty_months')->where('seller_warranty_months', '>', 0);
            } elseif ($warranty === 'none') {
                $query->where('manufacturer_warranty_status', '!=', 'active')
                      ->where(function ($q) {
                          $q->whereNull('seller_warranty_months')
                            ->orWhere('seller_warranty_months', 0);
                      });
            }
        }

        $listings = $query->orderBy('created_at', 'desc')->paginate(20);

        return $this->successResponse($listings, 'Category listings retrieved successfully');
    }
}
