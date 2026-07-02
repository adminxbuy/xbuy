<?php

namespace Tests\Feature;

use App\Models\Listing;
use App\Models\SellerProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListingSeoSitemapTest extends TestCase
{
    use RefreshDatabase;

    private $sellerUser;
    private $seller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sellerUser = User::factory()->create(['phone' => '9876543210']);
        $this->seller = SellerProfile::create([
            'user_id' => $this->sellerUser->id,
            'shop_name' => 'Test Shop',
            'shop_slug' => 'test-shop',
            'shop_city' => 'Delhi',
            'shop_state' => 'Delhi',
            'shop_pincode' => '110001',
        ]);
    }

    public function test_listing_detail_api_includes_seo_fields()
    {
        $listing = Listing::create([
            'seller_id' => $this->seller->id,
            'title' => 'Awesome RTX 3080',
            'slug' => 'awesome-rtx-3080',
            'category' => 'gpu',
            'grade' => 'A',
            'price' => 45000,
            'serial_number' => 'SN98765',
            'pickup_city' => 'Delhi',
            'pickup_state' => 'Delhi',
            'pickup_pincode' => '110001',
            'listing_status' => 'active',
            'brand' => 'ASUS',
            'model_name' => 'RTX 3080 TUF',
        ]);

        $response = $this->getJson("/api/listings/{$listing->slug}");

        $response->assertStatus(200);
        $response->assertJsonPath('data.seo_title', "Awesome RTX 3080 - Grade A | X-Buy.in");
        $response->assertJsonStructure([
            'data' => [
                'seo_title',
                'seo_description',
                'seo_keywords',
                'og_image',
                'schema_markup',
            ]
        ]);

        $schema = $response->json('data.schema_markup');
        $this->assertEquals('Product', $schema['@type']);
        $this->assertEquals('Awesome RTX 3080', $schema['name']);
        $this->assertEquals(45000, $schema['offers']['price']);
    }

    public function test_listings_sitemap_returns_active_listings_only()
    {
        Listing::create([
            'seller_id' => $this->seller->id,
            'title' => 'Active Item',
            'slug' => 'active-item',
            'category' => 'gpu',
            'grade' => 'A',
            'price' => 1000,
            'serial_number' => 'SN111',
            'pickup_city' => 'Delhi',
            'pickup_state' => 'Delhi',
            'pickup_pincode' => '110001',
            'listing_status' => 'active',
        ]);

        Listing::create([
            'seller_id' => $this->seller->id,
            'title' => 'Draft Item',
            'slug' => 'draft-item',
            'category' => 'gpu',
            'grade' => 'B',
            'price' => 2000,
            'serial_number' => 'SN222',
            'pickup_city' => 'Delhi',
            'pickup_state' => 'Delhi',
            'pickup_pincode' => '110001',
            'listing_status' => 'draft',
        ]);

        $response = $this->getJson('/api/v1/sitemap/listings');

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
        $response->assertJsonFragment([
            'slug' => 'active-item',
        ]);
        $response->assertJsonMissing([
            'slug' => 'draft-item',
        ]);
        $response->assertJsonStructure([
            'data' => [
                '*' => ['slug', 'updated_at']
            ]
        ]);
    }
}
