<?php

namespace Tests\Feature;

use App\Models\Listing;
use App\Models\Order;
use App\Models\Rating;
use App\Models\SellerMetrics;
use App\Models\SellerProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SellerRatingsApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_retrieve_ratings_by_shop_slug_and_listing_slug()
    {
        // 1. Create entities
        $sellerUser = User::factory()->create(['phone' => '9999999999']);
        $seller = SellerProfile::create([
            'user_id' => $sellerUser->id,
            'shop_name' => 'Tech Shop',
            'shop_slug' => 'tech-shop',
            'shop_city' => 'Delhi',
            'shop_state' => 'Delhi',
            'shop_pincode' => '110001',
            'kyc_status' => 'approved',
            'status' => 'active',
        ]);

        $buyer = User::factory()->create(['name' => 'John Doe', 'phone' => '8888888888']);

        $listing = Listing::create([
            'seller_id' => $seller->id,
            'title' => 'Gaming GPU',
            'slug' => 'gaming-gpu',
            'description' => 'Fabulous card',
            'price' => 25000,
            'listing_status' => 'active',
            'category' => 'gpu',
            'grade' => 'A',
            'brand' => 'Nvidia',
            'model_name' => 'RTX 3070',
            'serial_number' => 'SN-GPU-RTX3070-1234',
            'pickup_city' => 'Delhi',
            'pickup_state' => 'Delhi',
            'pickup_pincode' => '110001',
        ]);

        $order = Order::create([
            'order_number' => 'ORD12345',
            'listing_id' => $listing->id,
            'buyer_id' => $buyer->id,
            'seller_id' => $seller->id,
            'product_amount' => 25000,
            'shipping_amount' => 100,
            'total_amount' => 25100,
            'commission_percent' => 5,
            'commission_amount' => 1250,
            'seller_payout_amount' => 23750,
            'order_status' => 'completed',
            'delivery_address' => ['city' => 'Mumbai'],
        ]);

        // 2. Create rating and metrics
        $rating = Rating::create([
            'order_id' => $order->id,
            'seller_id' => $seller->id,
            'buyer_id' => $buyer->id,
            'rating_type' => 'manual',
            'item_accuracy' => 5.0,
            'packaging' => 4.0,
            'shipping_speed' => 5.0,
            'communication' => 4.0,
            'weighted_total' => 4.5,
            'review_text' => 'Fast shipping, item as described!',
        ]);

        SellerMetrics::create([
            'seller_id' => $seller->id,
            'star_rating' => 4.50,
            'total_ratings' => 1,
        ]);

        // 3. Test GET /api/v1/sellers/{shop_slug}/ratings
        $response = $this->getJson("/api/v1/sellers/tech-shop/ratings");
        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $response->assertJsonStructure([
            'data' => [
                'data' => [
                    '*' => [
                        'id',
                        'buyer_first_name',
                        'rating_breakdown' => [
                            'item_accuracy',
                            'packaging',
                            'shipping_speed',
                            'communication',
                            'weighted_total',
                        ],
                        'review_text',
                        'rating_type',
                        'created_at',
                    ]
                ]
            ]
        ]);
        $this->assertEquals('John', $response->json('data.data.0.buyer_first_name'));
        $this->assertEquals('Fast shipping, item as described!', $response->json('data.data.0.review_text'));

        // 4. Test GET /api/v1/listings/{slug}/seller-ratings
        $listingResponse = $this->getJson("/api/v1/listings/gaming-gpu/seller-ratings");
        $listingResponse->assertStatus(200);
        $listingResponse->assertJsonPath('success', true);
        $this->assertEquals('John', $listingResponse->json('data.data.0.buyer_first_name'));

        // 5. Test rating summary breakdown in listing show API
        $showResponse = $this->getJson("/api/listings/gaming-gpu");
        $showResponse->assertStatus(200);
        $showResponse->assertJsonPath('success', true);
        
        // Assert rating attributes exist under seller relation
        $showResponse->assertJsonPath('data.seller.star_rating', 4.5);
        $showResponse->assertJsonPath('data.seller.total_ratings', 1);
        $showResponse->assertJsonPath('data.seller.rating_breakdown.5', 1); // 4.5 rounds to 5
        $showResponse->assertJsonPath('data.seller.rating_breakdown.4', 0);
    }
}
