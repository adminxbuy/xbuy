<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Dispute;
use App\Models\DisputeResponse;
use App\Models\Listing;
use App\Models\ListingImage;
use App\Models\Order;
use App\Models\SellerMetrics;
use App\Models\Shipment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class SellerController extends Controller
{
    /**
     * Helper to verify if the user is a seller.
     */
    private function verifySeller(Request $request)
    {
        $user = $request->user();
        if (!$user->is_seller && !$user->is_admin) {
            return false;
        }
        return $user->sellerProfile;
    }

    /**
     * Seller dashboard statistics.
     */
    public function dashboard(Request $request): JsonResponse
    {
        $seller = $this->verifySeller($request);
        if (!$seller) {
            return $this->errorResponse('Access denied. Seller profile not found.', 403);
        }

        $metrics = SellerMetrics::firstOrCreate(['seller_id' => $seller->id]);

        $activeListingsCount = Listing::where('seller_id', $seller->id)
            ->where('listing_status', 'active')
            ->count();

        $orderStats = Order::where('seller_id', $seller->id)
            ->selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN order_status = 'pending_payment' THEN 1 ELSE 0 END) as pending_payment,
                SUM(CASE WHEN order_status = 'payment_received' THEN 1 ELSE 0 END) as payment_received,
                SUM(CASE WHEN order_status = 'confirmed' THEN 1 ELSE 0 END) as confirmed,
                SUM(CASE WHEN order_status = 'delivered' THEN 1 ELSE 0 END) as delivered,
                SUM(CASE WHEN order_status = 'completed' THEN 1 ELSE 0 END) as completed,
                SUM(CASE WHEN order_status = 'disputed' THEN 1 ELSE 0 END) as disputed,
                SUM(CASE WHEN order_status = 'completed' THEN seller_payout_amount ELSE 0 END) as earnings
            ")
            ->first();

        return $this->successResponse([
            'metrics' => $metrics,
            'listings' => [
                'active_count' => $activeListingsCount,
            ],
            'orders' => [
                'total' => $orderStats->total ?? 0,
                'pending_payment' => $orderStats->pending_payment ?? 0,
                'payment_received' => $orderStats->payment_received ?? 0,
                'confirmed' => $orderStats->confirmed ?? 0,
                'delivered' => $orderStats->delivered ?? 0,
                'completed' => $orderStats->completed ?? 0,
                'disputed' => $orderStats->disputed ?? 0,
                'earnings' => round($orderStats->earnings ?? 0, 2),
            ]
        ], 'Dashboard metrics retrieved successfully');
    }

    /**
     * Get seller listings.
     */
    public function getListings(Request $request): JsonResponse
    {
        $seller = $this->verifySeller($request);
        if (!$seller) {
            return $this->errorResponse('Access denied.', 403);
        }

        $listings = Listing::with('images')
            ->where('seller_id', $seller->id)
            ->orderBy('created_at', 'desc')
            ->paginate($request->input('per_page', 15));

        return $this->successResponse($listings, 'Seller listings retrieved successfully');
    }

    /**
     * Create listing.
     */
    public function createListing(Request $request): JsonResponse
    {
        $seller = $this->verifySeller($request);
        if (!$seller) {
            return $this->errorResponse('Access denied.', 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'category' => 'required_without:category_id|nullable|in:gpu,cpu,motherboard,ram,storage,psu,cabinet,peripheral,full_build,cooling,networking,cables,other',
            'category_id' => 'required_without:category|nullable|exists:categories,id',
            'subcategory_id' => 'nullable|exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
            'grade' => 'required|in:A,B,C',
            'price' => 'required|numeric|min:1',
            'original_price' => 'nullable|numeric|min:1',
            'serial_number' => 'required|string|max:255',
            'brand' => 'nullable|string|max:255',
            'model_name' => 'nullable|string|max:255',
            'condition_notes' => 'nullable|string',
            'manufacturer_warranty_status' => 'required|in:active,expired,none',
            'manufacturer_warranty_months' => 'required_if:manufacturer_warranty_status,active|nullable|integer|min:0',
            'seller_warranty_months' => 'nullable|integer|min:0',
            'document_status' => 'required|in:full,partial,none',
            'shipping_type' => 'required|in:prepaid,cod',
            'shipping_charges' => 'required|numeric|min:0',
            'pickup_city' => 'required|string|max:255',
            'pickup_state' => 'required|string|max:255',
            'pickup_pincode' => 'required|string|max:10',
            'trusted_buyers_only' => 'nullable|boolean',
            'images' => 'required|array|min:1',
            'images.*' => 'required|url',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation error', 422, $validator->errors());
        }

        // Check if serial_number already exists in active listings
        $activeExists = Listing::where('serial_number', $request->input('serial_number'))
            ->where('listing_status', 'active')
            ->exists();
        if ($activeExists) {
            return $this->errorResponse('Serial number already listed', 422);
        }

        // Limit seller warranty months if not fulfilled badge seller
        $sellerWarranty = $request->input('seller_warranty_months');
        if ($seller->badge_level !== 'fulfilled' && $sellerWarranty > 0) {
            return $this->errorResponse('Only Fulfilled badge sellers can offer seller warranty.', 400);
        }

        $categoryId = $request->input('category_id');
        $subcategoryId = $request->input('subcategory_id');
        $brandId = $request->input('brand_id');

        if ($categoryId) {
            $categoryModel = \App\Models\Category::find($categoryId);
        } else {
            $categorySlug = $request->input('category');
            $categoryModel = \App\Models\Category::where('slug', $categorySlug)->first();
        }

        $brandName = $request->input('brand');
        if ($brandId) {
            $brandModel = \App\Models\Brand::find($brandId);
            if ($brandModel) {
                $brandName = $brandModel->name;
            }
        }

        // Specs validation
        $specs = $request->input('specs', []);
        $templates = collect();
        if ($categoryModel) {
            $templates = \App\Models\SpecTemplate::where('category_id', $categoryModel->id)->get();
            foreach ($templates as $temp) {
                if ($temp->is_required) {
                    if (!isset($specs[$temp->spec_key]) || $specs[$temp->spec_key] === '' || $specs[$temp->spec_key] === null) {
                        return $this->errorResponse("The spec field '{$temp->spec_label}' is required.", 422);
                    }
                }
            }
        }

        $slug = Str::slug($request->input('title')) . '-' . Str::lower(Str::random(6));

        $listing = DB::transaction(function () use ($request, $seller, $slug, $templates, $specs, $categoryModel, $subcategoryId, $brandId, $brandName) {
            $listing = Listing::create([
                'seller_id' => $seller->id,
                'category_id' => $categoryModel ? $categoryModel->id : null,
                'subcategory_id' => $subcategoryId,
                'brand_id' => $brandId,
                'title' => $request->input('title'),
                'slug' => $slug,
                'description' => $request->input('description'),
                'category' => $categoryModel ? $categoryModel->slug : $request->input('category'),
                'grade' => $request->input('grade'),
                'price' => $request->input('price'),
                'original_price' => $request->input('original_price'),
                'serial_number' => $request->input('serial_number'),
                'brand' => $brandName,
                'model_name' => $request->input('model_name'),
                'condition_notes' => $request->input('condition_notes'),
                'manufacturer_warranty_status' => $request->input('manufacturer_warranty_status'),
                'manufacturer_warranty_months' => $request->input('manufacturer_warranty_months'),
                'seller_warranty_months' => $request->input('seller_warranty_months', 0),
                'document_status' => $request->input('document_status'),
                'shipping_type' => $request->input('shipping_type', 'prepaid'),
                'shipping_charges' => $request->input('shipping_charges', 0),
                'pickup_city' => $request->input('pickup_city'),
                'pickup_state' => $request->input('pickup_state'),
                'pickup_pincode' => $request->input('pickup_pincode'),
                'trusted_buyers_only' => $request->input('trusted_buyers_only', false),
                'listing_status' => 'pending_approval',
            ]);

            // Save Images
            $images = $request->input('images');
            foreach ($images as $index => $imageUrl) {
                ListingImage::create([
                    'listing_id' => $listing->id,
                    'image_url' => $imageUrl,
                    'is_primary' => $index === 0,
                    'sort_order' => $index,
                ]);
            }

            // Save Specs
            foreach ($templates as $temp) {
                if (isset($specs[$temp->spec_key]) && $specs[$temp->spec_key] !== '' && $specs[$temp->spec_key] !== null) {
                    \App\Models\ListingSpec::create([
                        'listing_id' => $listing->id,
                        'spec_key' => $temp->spec_key,
                        'spec_label' => $temp->spec_label,
                        'spec_value' => (string) $specs[$temp->spec_key],
                        'spec_unit' => $temp->spec_unit,
                        'is_highlighted' => $temp->is_highlighted,
                        'sort_order' => $temp->sort_order,
                    ]);
                }
            }
            return $listing;
        });

        // Notify admin of new pending listing
        \App\Models\Notification::notifyAdmins(
            'New Listing Pending Approval',
            "Seller '{$seller->shop_name}' has submitted a new listing: '{$listing->title}' which requires administrative approval.",
            'system',
            'listing',
            $listing->id
        );

        // Queue listing image optimization
        \App\Jobs\OptimizeListingImagesJob::dispatch($listing->id);

        return $this->successResponse($listing->load(['images', 'specs']), 'Listing created successfully and is pending admin approval', 201);
    }

    /**
     * Update listing.
     */
    public function updateListing(int $id, Request $request): JsonResponse
    {
        $seller = $this->verifySeller($request);
        if (!$seller) {
            return $this->errorResponse('Access denied.', 403);
        }

        $listing = Listing::where('seller_id', $seller->id)->find($id);
        if (!$listing) {
            return $this->errorResponse('Listing not found.', 404);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'category' => 'sometimes|required|in:gpu,cpu,motherboard,ram,storage,psu,cabinet,peripheral,full_build,cooling,networking,cables,other',
            'category_id' => 'sometimes|required|exists:categories,id',
            'subcategory_id' => 'nullable|exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
            'grade' => 'sometimes|required|in:A,B,C',
            'price' => 'sometimes|required|numeric|min:1',
            'original_price' => 'nullable|numeric|min:1',
            'serial_number' => 'sometimes|required|string|max:255',
            'brand' => 'nullable|string|max:255',
            'model_name' => 'nullable|string|max:255',
            'condition_notes' => 'nullable|string',
            'manufacturer_warranty_status' => 'sometimes|required|in:active,expired,none',
            'manufacturer_warranty_months' => 'required_if:manufacturer_warranty_status,active|nullable|integer|min:0',
            'seller_warranty_months' => 'nullable|integer|min:0',
            'document_status' => 'sometimes|required|in:full,partial,none',
            'shipping_type' => 'sometimes|required|in:prepaid,cod',
            'shipping_charges' => 'sometimes|required|numeric|min:0',
            'pickup_city' => 'sometimes|required|string|max:255',
            'pickup_state' => 'sometimes|required|string|max:255',
            'pickup_pincode' => 'sometimes|required|string|max:10',
            'trusted_buyers_only' => 'nullable|boolean',
            'images' => 'sometimes|array',
            'images.*' => 'required|url',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation error', 422, $validator->errors());
        }

        // Check if serial_number already exists in active listings
        if ($request->has('serial_number')) {
            $activeExists = Listing::where('serial_number', $request->input('serial_number'))
                ->where('listing_status', 'active')
                ->where('id', '!=', $id)
                ->exists();
            if ($activeExists) {
                return $this->errorResponse('Serial number already listed', 422);
            }
        }

        // Limit seller warranty months if not fulfilled badge seller
        $sellerWarranty = $request->input('seller_warranty_months', $listing->seller_warranty_months);
        if ($seller->badge_level !== 'fulfilled' && $sellerWarranty > 0) {
            return $this->errorResponse('Only Fulfilled badge sellers can offer seller warranty.', 400);
        }

        // Specs validation
        $hasSpecs = $request->has('specs');
        $specs = $request->input('specs', []);
        
        $categorySlug = $request->input('category');
        $categoryId = $request->input('category_id');
        $subcategoryId = $request->input('subcategory_id');
        $brandId = $request->input('brand_id');

        if ($categoryId) {
            $categoryModel = \App\Models\Category::find($categoryId);
        } elseif ($categorySlug) {
            $categoryModel = \App\Models\Category::where('slug', $categorySlug)->first();
        } else {
            $categoryModel = $listing->category_id ? \App\Models\Category::find($listing->category_id) : \App\Models\Category::where('slug', $listing->category)->first();
        }

        $templates = collect();
        if ($hasSpecs && $categoryModel) {
            $templates = \App\Models\SpecTemplate::where('category_id', $categoryModel->id)->get();
            foreach ($templates as $temp) {
                if ($temp->is_required) {
                    if (!isset($specs[$temp->spec_key]) || $specs[$temp->spec_key] === '' || $specs[$temp->spec_key] === null) {
                        return $this->errorResponse("The spec field '{$temp->spec_label}' is required.", 422);
                    }
                }
            }
        }

        DB::transaction(function () use ($request, $listing, $hasSpecs, $templates, $specs, $categoryModel, $subcategoryId, $brandId) {
            $data = $request->except(['images', 'specs']);
            // Reset to pending_approval if vital fields change
            if ($request->has('price') || $request->has('title') || $request->has('serial_number')) {
                $data['listing_status'] = 'pending_approval';
            }
            if ($categoryModel) {
                $data['category_id'] = $categoryModel->id;
                $data['category'] = $categoryModel->slug;
            }
            if ($request->has('subcategory_id')) {
                $data['subcategory_id'] = $subcategoryId;
            }
            if ($request->has('brand_id')) {
                $data['brand_id'] = $brandId;
                $brandModel = \App\Models\Brand::find($brandId);
                if ($brandModel) {
                    $data['brand'] = $brandModel->name;
                }
            }
            $listing->update($data);

            if ($request->has('images')) {
                // Delete old images
                $listing->images()->delete();
                // Add new ones
                foreach ($request->input('images') as $index => $imageUrl) {
                    ListingImage::create([
                        'listing_id' => $listing->id,
                        'image_url' => $imageUrl,
                        'is_primary' => $index === 0,
                        'sort_order' => $index,
                    ]);
                }
            }

            if ($hasSpecs) {
                $listing->specs()->delete();
                foreach ($templates as $temp) {
                    if (isset($specs[$temp->spec_key]) && $specs[$temp->spec_key] !== '' && $specs[$temp->spec_key] !== null) {
                        \App\Models\ListingSpec::create([
                            'listing_id' => $listing->id,
                            'spec_key' => $temp->spec_key,
                            'spec_label' => $temp->spec_label,
                            'spec_value' => (string) $specs[$temp->spec_key],
                            'spec_unit' => $temp->spec_unit,
                            'is_highlighted' => $temp->is_highlighted,
                            'sort_order' => $temp->sort_order,
                        ]);
                    }
                }
            }
        });

        // Queue listing image optimization
        \App\Jobs\OptimizeListingImagesJob::dispatch($listing->id);

        return $this->successResponse($listing->fresh()->load(['images', 'specs']), 'Listing updated successfully. May require admin approval.');
    }

    /**
     * Delete listing.
     */
    public function deleteListing(int $id, Request $request): JsonResponse
    {
        $seller = $this->verifySeller($request);
        if (!$seller) {
            return $this->errorResponse('Access denied.', 403);
        }

        $listing = Listing::where('seller_id', $seller->id)->find($id);
        if (!$listing) {
            return $this->errorResponse('Listing not found.', 404);
        }

        $listing->delete();

        return $this->successResponse(null, 'Listing deleted successfully');
    }

    /**
     * Get seller sales orders.
     */
    public function getOrders(Request $request): JsonResponse
    {
        $seller = $this->verifySeller($request);
        if (!$seller) {
            return $this->errorResponse('Access denied.', 403);
        }

        $orders = Order::with(['listing.images', 'buyer'])
            ->where('seller_id', $seller->id)
            ->orderBy('created_at', 'desc')
            ->paginate($request->input('per_page', 10));

        return $this->successResponse($orders, 'Sales orders retrieved successfully');
    }

    /**
     * Get single sale order details.
     */
    public function getOrder(int $id, Request $request): JsonResponse
    {
        $seller = $this->verifySeller($request);
        if (!$seller) {
            return $this->errorResponse('Access denied.', 403);
        }

        $order = Order::with(['listing.images', 'buyer', 'escrow', 'shipment', 'dispute.responses.user'])
            ->where('seller_id', $seller->id)
            ->where('id', $id)
            ->first();

        if (!$order) {
            return $this->errorResponse('Order not found', 404);
        }

        return $this->successResponse($order, 'Order details retrieved successfully');
    }

    /**
     * Confirm order.
     */
    public function confirmOrder(int $id, Request $request): JsonResponse
    {
        $seller = $this->verifySeller($request);
        if (!$seller) {
            return $this->errorResponse('Access denied.', 403);
        }

        $order = Order::where('seller_id', $seller->id)
            ->where('id', $id)
            ->where('order_status', 'payment_received')
            ->first();

        if (!$order) {
            return $this->errorResponse('Order not found or cannot be confirmed', 404);
        }

        // Mock Shiprocket generation details
        $shiprocketOrderId = 'SR-' . mt_rand(100000, 999999);
        $awbNumber = 'AWB' . mt_rand(1000000000, 9999999999);

        DB::transaction(function () use ($order, $shiprocketOrderId, $awbNumber) {
            $order->update([
                'order_status' => 'confirmed',
            ]);

            if ($order->shipment) {
                $order->shipment->update([
                    'shiprocket_order_id' => $shiprocketOrderId,
                    'awb_number' => $awbNumber,
                    'status' => 'label_generated',
                    'label_generated_at' => now(),
                ]);
            }
        });

        return $this->successResponse($order->fresh(['shipment']), 'Order confirmed. Shipping label generated successfully.');
    }

    /**
     * Get disputes raised against this seller.
     */
    public function getDisputes(Request $request): JsonResponse
    {
        $seller = $this->verifySeller($request);
        if (!$seller) {
            return $this->errorResponse('Access denied.', 403);
        }

        $disputes = Dispute::with(['order.listing.images', 'buyer'])
            ->where('seller_id', $seller->id)
            ->orderBy('created_at', 'desc')
            ->paginate($request->input('per_page', 10));

        return $this->successResponse($disputes, 'Disputes retrieved successfully');
    }

    /**
     * Respond to a dispute.
     */
    public function respondToDispute(int $id, Request $request): JsonResponse
    {
        $seller = $this->verifySeller($request);
        if (!$seller) {
            return $this->errorResponse('Access denied.', 403);
        }

        $dispute = Dispute::where('seller_id', $seller->id)
            ->where('id', $id)
            ->first();

        if (!$dispute) {
            return $this->errorResponse('Dispute not found', 404);
        }

        if ($dispute->status !== 'open') {
            return $this->errorResponse('This dispute has already been responded to or resolved.', 400);
        }

        $validator = Validator::make($request->all(), [
            'response_text' => 'required|string',
            'evidence_images' => 'nullable|array',
            'evidence_images.*' => 'string', // URLs or file paths
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation error', 422, $validator->errors());
        }

        DB::transaction(function () use ($dispute, $request) {
            DisputeResponse::create([
                'dispute_id' => $dispute->id,
                'responder_id' => $request->user()->id,
                'responder_type' => 'seller',
                'response_text' => $request->input('response_text'),
                'evidence_images' => $request->input('evidence_images', []),
            ]);

            $dispute->update([
                'status' => 'seller_responded',
            ]);
        });

        return $this->successResponse($dispute->fresh(['responses.user']), 'Response submitted successfully. Under investigation.');
    }

    /**
     * Get seller's sold archive.
     */
    public function getSoldArchive(Request $request): JsonResponse
    {
        $seller = $this->verifySeller($request);
        if (!$seller) {
            return $this->errorResponse('Access denied.', 403);
        }

        $archive = \App\Models\SoldArchive::where('seller_id', $seller->id)
            ->orderBy('sold_at', 'desc')
            ->paginate($request->input('per_page', 15));

        return $this->successResponse($archive, 'Sold archive retrieved successfully');
    }
}
