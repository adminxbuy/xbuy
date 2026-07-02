<?php

namespace Tests\Feature;

use App\Models\Listing;
use App\Models\SellerProfile;
use App\Models\User;
use App\Models\Wishlist;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class WishlistTest extends TestCase
{
    use RefreshDatabase;

    private $buyer;
    private $seller;
    private $listing;

    protected function setUp(): void
    {
        parent::setUp();

        $this->buyer = User::factory()->create(['phone' => '1234567890']);
        
        $sellerUser = User::factory()->create(['phone' => '9876543210']);
        $this->seller = SellerProfile::create([
            'user_id' => $sellerUser->id,
            'shop_name' => 'Test Shop',
            'shop_slug' => 'test-shop',
            'shop_city' => 'Delhi',
            'shop_state' => 'Delhi',
            'shop_pincode' => '110001',
        ]);

        $this->listing = Listing::create([
            'seller_id' => $this->seller->id,
            'title' => 'GPU Test',
            'slug' => 'gpu-test',
            'category' => 'gpu',
            'grade' => 'A',
            'price' => 1000,
            'serial_number' => 'SN123',
            'pickup_city' => 'Delhi',
            'pickup_state' => 'Delhi',
            'pickup_pincode' => '110001',
            'listing_status' => 'active',
        ]);
    }

    public function test_get_wishlist_returns_paginated_list()
    {
        Wishlist::create([
            'user_id' => $this->buyer->id,
            'listing_id' => $this->listing->id,
            'price_at_wishlist' => $this->listing->price,
        ]);

        Sanctum::actingAs($this->buyer);

        $response = $this->getJson('/api/buyer/wishlist');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'data' => [
                    '*' => [
                        'id',
                        'user_id',
                        'listing_id',
                        'listing',
                    ]
                ]
            ]
        ]);
        $this->assertCount(1, $response->json('data.data'));
    }

    public function test_add_to_wishlist_adds_item_and_increments_count()
    {
        Sanctum::actingAs($this->buyer);

        $response = $this->postJson("/api/buyer/wishlist/{$this->listing->id}");

        $response->assertStatus(200);
        $this->assertDatabaseHas('wishlists', [
            'user_id' => $this->buyer->id,
            'listing_id' => $this->listing->id,
        ]);

        $this->listing->refresh();
        $this->assertEquals(1, $this->listing->wishlist_count);
    }

    public function test_delete_from_wishlist_removes_item_and_decrements_count()
    {
        Wishlist::create([
            'user_id' => $this->buyer->id,
            'listing_id' => $this->listing->id,
            'price_at_wishlist' => $this->listing->price,
        ]);
        $this->listing->update(['wishlist_count' => 1]);

        Sanctum::actingAs($this->buyer);

        $response = $this->deleteJson("/api/buyer/wishlist/{$this->listing->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('wishlists', [
            'user_id' => $this->buyer->id,
            'listing_id' => $this->listing->id,
        ]);

        $this->listing->refresh();
        $this->assertEquals(0, $this->listing->wishlist_count);
    }
}
