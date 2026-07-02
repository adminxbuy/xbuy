<?php

namespace Tests\Feature;

use App\Models\Listing;
use App\Models\SellerProfile;
use App\Models\SoldArchive;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ListingSerialNumberTest extends TestCase
{
    use RefreshDatabase;

    private $sellerUser;
    private $seller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sellerUser = User::factory()->create([
            'phone' => '9876543210',
            'role' => 'seller',
        ]);
        $this->seller = SellerProfile::create([
            'user_id' => $this->sellerUser->id,
            'shop_name' => 'Test Shop',
            'shop_slug' => 'test-shop',
            'shop_city' => 'Delhi',
            'shop_state' => 'Delhi',
            'shop_pincode' => '110001',
            'status' => 'active',
        ]);
        $this->sellerUser->role = 'seller';
        $this->sellerUser->save();
    }

    public function test_cannot_create_listing_with_already_active_serial_number()
    {
        // Pre-create an active listing with SN123
        Listing::create([
            'seller_id' => $this->seller->id,
            'title' => 'Active GPU',
            'slug' => 'active-gpu',
            'category' => 'gpu',
            'grade' => 'A',
            'price' => 1000,
            'serial_number' => 'SN123',
            'pickup_city' => 'Delhi',
            'pickup_state' => 'Delhi',
            'pickup_pincode' => '110001',
            'listing_status' => 'active',
        ]);

        Sanctum::actingAs($this->sellerUser);

        // Try to create another one with SN123
        $response = $this->postJson('/api/seller/listings', [
            'title' => 'Another GPU',
            'description' => 'Test GPU description',
            'category' => 'gpu',
            'grade' => 'B',
            'price' => 1200,
            'serial_number' => 'SN123',
            'manufacturer_warranty_status' => 'none',
            'document_status' => 'none',
            'shipping_type' => 'prepaid',
            'shipping_charges' => 50,
            'pickup_city' => 'Delhi',
            'pickup_state' => 'Delhi',
            'pickup_pincode' => '110001',
            'images' => ['https://example.com/image.jpg'],
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
            'message' => 'Serial number already listed',
        ]);
    }

    public function test_can_create_listing_with_archived_serial_number_and_is_flagged()
    {
        // Pre-create a record in sold_archive with SN999
        SoldArchive::create([
            'listing_id' => 99,
            'seller_id' => $this->seller->id,
            'product_title' => 'Old GPU',
            'category' => 'gpu',
            'grade' => 'A',
            'serial_number' => 'SN999',
            'sale_price' => 900,
            'sold_at' => now(),
            'listing_snapshot' => [],
        ]);

        Sanctum::actingAs($this->sellerUser);

        // Try to create listing with SN999
        $response = $this->postJson('/api/seller/listings', [
            'title' => 'New listing with old SN',
            'description' => 'Test GPU description',
            'category' => 'gpu',
            'grade' => 'A',
            'price' => 1500,
            'serial_number' => 'SN999',
            'manufacturer_warranty_status' => 'none',
            'document_status' => 'none',
            'shipping_type' => 'prepaid',
            'shipping_charges' => 50,
            'pickup_city' => 'Delhi',
            'pickup_state' => 'Delhi',
            'pickup_pincode' => '110001',
            'images' => ['https://example.com/image.jpg'],
        ]);

        $response->assertStatus(201);
        $listing = Listing::find($response->json('data.id'));
        $this->assertTrue($listing->is_previously_sold);
    }
}
