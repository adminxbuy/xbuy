<?php

namespace Tests\Feature;

use App\Models\Listing;
use App\Models\SellerProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class BuyerOrderRestrictionTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_buyer_is_blocked_from_trusted_buyers_only_listing()
    {
        $sellerUser = User::factory()->create(['phone' => '9876543210']);
        $seller = SellerProfile::create([
            'user_id' => $sellerUser->id,
            'shop_name' => 'Test Shop',
            'shop_slug' => 'test-shop',
            'shop_city' => 'Delhi',
            'shop_state' => 'Delhi',
            'shop_pincode' => '110001',
        ]);

        $buyer = User::factory()->create([
            'phone' => '1234567890',
            'buyer_badge' => 'new_buyer',
        ]);

        $listing = Listing::create([
            'seller_id' => $seller->id,
            'title' => 'Test GPU',
            'slug' => 'test-gpu',
            'category' => 'gpu',
            'grade' => 'A',
            'price' => 1000,
            'serial_number' => 'SN12345',
            'pickup_city' => 'Delhi',
            'pickup_state' => 'Delhi',
            'pickup_pincode' => '110001',
            'listing_status' => 'active',
            'trusted_buyers_only' => true,
        ]);

        Sanctum::actingAs($buyer);

        $response = $this->postJson('/api/buyer/orders', [
            'listing_id' => $listing->id,
            'delivery_address' => [
                'name' => 'John Doe',
                'phone' => '9999999999',
                'street' => '123 Test St',
                'city' => 'Delhi',
                'state' => 'Delhi',
                'pincode' => '110001',
            ],
        ]);

        $response->assertStatus(400);
        $response->assertJson([
            'success' => false,
            'message' => 'This seller requires Verified Buyer status. Complete 5+ purchases to unlock.',
        ]);
    }

    public function test_verified_buyer_can_purchase_trusted_buyers_only_listing()
    {
        $sellerUser = User::factory()->create(['phone' => '9876543210']);
        $seller = SellerProfile::create([
            'user_id' => $sellerUser->id,
            'shop_name' => 'Test Shop',
            'shop_slug' => 'test-shop',
            'shop_city' => 'Delhi',
            'shop_state' => 'Delhi',
            'shop_pincode' => '110001',
        ]);

        $buyer = User::factory()->create([
            'phone' => '1234567890',
            'buyer_badge' => 'verified_buyer',
        ]);

        $listing = Listing::create([
            'seller_id' => $seller->id,
            'title' => 'Test GPU 2',
            'slug' => 'test-gpu-2',
            'category' => 'gpu',
            'grade' => 'A',
            'price' => 1000,
            'serial_number' => 'SN12346',
            'pickup_city' => 'Delhi',
            'pickup_state' => 'Delhi',
            'pickup_pincode' => '110001',
            'listing_status' => 'active',
            'trusted_buyers_only' => true,
        ]);

        Sanctum::actingAs($buyer);

        $response = $this->postJson('/api/buyer/orders', [
            'listing_id' => $listing->id,
            'delivery_address' => [
                'name' => 'John Doe',
                'phone' => '9999999999',
                'street' => '123 Test St',
                'city' => 'Delhi',
                'state' => 'Delhi',
                'pincode' => '110001',
            ],
        ]);

        $response->assertStatus(201);
    }
}
