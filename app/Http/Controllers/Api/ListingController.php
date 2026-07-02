<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Listing;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ListingController extends Controller
{
    /**
     * Display a listing of active listings.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Listing::active()->with(['seller.metrics', 'seller.ratings', 'images', 'category', 'subcategory', 'brand', 'specs']);

        // Search filter
        if ($request->has('search') && !empty($request->input('search'))) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('brand', 'like', "%{$search}%")
                  ->orWhere('model_name', 'like', "%{$search}%");
            });
        }

        // Category filter
        if ($request->has('category') && !empty($request->input('category'))) {
            $query->where('category', $request->input('category'));
        }

        // Subcategory filter
        if ($request->has('subcategory_id') && !empty($request->input('subcategory_id'))) {
            $query->where('subcategory_id', $request->input('subcategory_id'));
        }

        // Brand filter
        if ($request->has('brand') && !empty($request->input('brand'))) {
            $query->where('brand', $request->input('brand'));
        }
        if ($request->has('brand_id') && !empty($request->input('brand_id'))) {
            $query->where('brand_id', $request->input('brand_id'));
        }
        if ($request->has('brand_slug') && !empty($request->input('brand_slug'))) {
            $query->whereHas('brand', function ($q) use ($request) {
                $q->where('slug', $request->input('brand_slug'));
            });
        }

        // Grade filter
        if ($request->has('grade') && !empty($request->input('grade'))) {
            $query->where('grade', $request->input('grade'));
        }

        // Price range filters
        $minPrice = $request->input('min_price', $request->input('price_min'));
        if ($minPrice !== null && is_numeric($minPrice)) {
            $query->where('price', '>=', $minPrice);
        }
        $maxPrice = $request->input('max_price', $request->input('price_max'));
        if ($maxPrice !== null && is_numeric($maxPrice)) {
            $query->where('price', '<=', $maxPrice);
        }

        // Seller filter
        if ($request->has('seller_id') && is_numeric($request->input('seller_id'))) {
            $query->where('seller_id', $request->input('seller_id'));
        }

        // Spec filters (?specs[vram_gb]=12, ?specs[socket]=AM4, ?specs[capacity_gb][min]=500)
        if ($request->has('specs') && is_array($request->input('specs'))) {
            foreach ($request->input('specs') as $key => $val) {
                if (is_array($val)) {
                    if (isset($val['min']) && $val['min'] !== '') {
                        $query->whereHas('specs', function ($q) use ($key, $val) {
                            $q->where('spec_key', $key)->whereRaw('CAST(spec_value AS NUMERIC) >= ?', [(float)$val['min']]);
                        });
                    }
                    if (isset($val['max']) && $val['max'] !== '') {
                        $query->whereHas('specs', function ($q) use ($key, $val) {
                            $q->where('spec_key', $key)->whereRaw('CAST(spec_value AS NUMERIC) <= ?', [(float)$val['max']]);
                        });
                    }
                } else {
                    if ($val !== '' && $val !== null) {
                        $query->whereHas('specs', function ($q) use ($key, $val) {
                            $q->where('spec_key', $key)->where('spec_value', $val);
                        });
                    }
                }
            }
        }

        // Sorting
        $sort = $request->input('sort');
        if ($sort === 'price_asc') {
            $query->orderBy('price', 'asc');
        } elseif ($sort === 'price_desc') {
            $query->orderBy('price', 'desc');
        } elseif ($sort === 'newest') {
            $query->orderBy('created_at', 'desc');
        } elseif ($sort === 'most_viewed') {
            $query->orderBy('views_count', 'desc');
        } elseif ($sort === 'rating') {
            $query->select('listings.*')
                ->addSelect([
                    'seller_rating' => \App\Models\Rating::selectRaw('AVG(weighted_total)')
                        ->whereColumn('seller_id', 'listings.seller_id')
                ])
                ->orderByDesc('seller_rating');
        } else {
            $sortBy = $request->input('sort_by', 'created_at');
            $sortOrder = $request->input('sort_order', 'desc');
            if (in_array($sortBy, ['price', 'created_at', 'views_count'])) {
                $query->orderBy($sortBy, $sortOrder === 'asc' ? 'asc' : 'desc');
            } else {
                $query->orderBy('created_at', 'desc');
            }
        }

        $perPage = $request->input('per_page', 15);
        $listings = $query->paginate($perPage);

        return $this->successResponse($listings, 'Listings retrieved successfully');
    }

    public function show(string $slug): JsonResponse
    {
        $listing = Listing::with(['seller.user', 'seller.metrics', 'seller.ratings', 'images', 'specs', 'category', 'subcategory', 'brand'])
            ->where('slug', $slug)
            ->where('listing_status', 'active')
            ->first();

        if (!$listing) {
            return $this->errorResponse('Listing not found', 404);
        }

        // Increment view count
        $listing->increment('views_count');

        // Group specs by highlighted vs other
        $highlightedSpecs = $listing->specs->where('is_highlighted', true)->values();
        $otherSpecs = $listing->specs->where('is_highlighted', false)->values();

        // Get spec template for this listing's category
        $categoryModel = $listing->category_id ? \App\Models\Category::find($listing->category_id) : \App\Models\Category::where('slug', $listing->category)->first();
        $specTemplates = $categoryModel ? \App\Models\SpecTemplate::where('category_id', $categoryModel->id)->orderBy('sort_order')->get() : [];

        return $this->successResponse(array_merge($listing->toArray(), [
            'highlighted_specs' => $highlightedSpecs,
            'other_specs' => $otherSpecs,
            'spec_templates' => $specTemplates,
            'spec_template' => $specTemplates
        ]), 'Listing retrieved successfully');
    }

    /**
     * Get sitemap listings (active slugs + updated_at).
     */
    public function sitemap(): JsonResponse
    {
        $listings = Listing::active()
            ->select(['slug', 'updated_at'])
            ->get();

        return response()->json([
            'success' => true,
            'data' => $listings,
        ]);
    }

    /**
     * Get paginated seller ratings by shop slug.
     */
    public function sellerRatingsByShop(string $shopSlug, Request $request): JsonResponse
    {
        $seller = \App\Models\SellerProfile::where('shop_slug', $shopSlug)->first();

        if (!$seller) {
            return $this->errorResponse('Seller profile not found', 404);
        }

        $ratings = \App\Models\Rating::where('seller_id', $seller->id)
            ->with('buyer')
            ->orderBy('created_at', 'desc')
            ->paginate($request->input('per_page', 15));

        $transformed = collect($ratings->items())->map(function ($rating) {
            return [
                'id' => $rating->id,
                'buyer_first_name' => $rating->buyer ? explode(' ', $rating->buyer->name)[0] : 'Anonymous',
                'rating_breakdown' => [
                    'item_accuracy' => (float)$rating->item_accuracy,
                    'packaging' => (float)$rating->packaging,
                    'shipping_speed' => (float)$rating->shipping_speed,
                    'communication' => (float)$rating->communication,
                    'weighted_total' => (float)$rating->weighted_total,
                ],
                'review_text' => $rating->review_text,
                'rating_type' => $rating->rating_type,
                'created_at' => $rating->created_at,
            ];
        });

        $ratings->setCollection($transformed);

        return $this->successResponse($ratings, 'Seller ratings retrieved successfully');
    }

    /**
     * Get paginated seller ratings from listing context.
     */
    public function sellerRatingsByListing(string $slug, Request $request): JsonResponse
    {
        $listing = Listing::where('slug', $slug)->first();

        if (!$listing) {
            return $this->errorResponse('Listing not found', 404);
        }

        $seller = \App\Models\SellerProfile::find($listing->seller_id);

        if (!$seller) {
            return $this->errorResponse('Seller profile not found for this listing', 404);
        }

        return $this->sellerRatingsByShop($seller->shop_slug, $request);
    }
}
