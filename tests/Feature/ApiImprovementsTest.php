<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Brand;
use App\Models\Listing;
use App\Models\ListingSpec;
use App\Models\SpecTemplate;
use App\Models\User;
use App\Models\SellerProfile;
use App\Models\Rating;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiImprovementsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed base settings to prevent missing settings error
        \App\Models\SiteSetting::create([
            'key' => 'commission_rates',
            'value' => json_encode(['gpu' => 5.0]),
            'type' => 'json',
            'group' => 'rates',
        ]);
        
        \App\Models\SiteSetting::create([
            'key' => 'testing_windows',
            'value' => json_encode(['gpu' => 3]),
            'type' => 'json',
            'group' => 'policy',
        ]);
    }

    public function test_can_create_listing_with_ids_and_specs()
    {
        $user = User::factory()->create(['role' => 'seller', 'phone' => '8888888888']);
        $seller = SellerProfile::create([
            'user_id' => $user->id,
            'shop_name' => 'Test Shop',
            'shop_slug' => 'test-shop',
            'shop_city' => 'Delhi',
            'shop_state' => 'Delhi',
            'shop_pincode' => '110001',
            'kyc_status' => 'approved',
            'badge_level' => 'basic',
            'status' => 'active',
        ]);
        $user->role = 'seller';
        $user->save();

        $category = Category::create(['name' => 'GPU', 'slug' => 'gpu', 'is_active' => true]);
        $subcategory = Category::create(['name' => 'NVIDIA GeForce', 'slug' => 'nvidia-geforce', 'parent_id' => $category->id, 'is_active' => true]);
        $brand = Brand::create(['name' => 'ASUS', 'slug' => 'asus', 'is_active' => true]);

        SpecTemplate::create([
            'category_id' => $category->id,
            'spec_key' => 'vram_gb',
            'spec_label' => 'VRAM',
            'spec_type' => 'number',
            'spec_unit' => 'GB',
            'is_required' => true,
            'is_highlighted' => true
        ]);

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/seller/listings', [
            'title' => 'ASUS ROG RTX 4070',
            'description' => 'Superb GPU',
            'category_id' => $category->id,
            'subcategory_id' => $subcategory->id,
            'brand_id' => $brand->id,
            'grade' => 'A',
            'price' => 50000,
            'original_price' => 60000,
            'serial_number' => 'SN_ASUS_123',
            'manufacturer_warranty_status' => 'none',
            'document_status' => 'full',
            'shipping_type' => 'prepaid',
            'shipping_charges' => 150,
            'pickup_city' => 'Mumbai',
            'pickup_state' => 'Maharashtra',
            'pickup_pincode' => '400001',
            'images' => ['https://via.placeholder.com/150'],
            'specs' => [
                'vram_gb' => 12
            ]
        ]);

        $response->assertStatus(201);
        $response->assertJsonPath('success', true);

        $this->assertDatabaseHas('listings', [
            'title' => 'ASUS ROG RTX 4070',
            'category_id' => $category->id,
            'subcategory_id' => $subcategory->id,
            'brand_id' => $brand->id,
            'category' => 'gpu',
            'brand' => 'ASUS',
        ]);

        $this->assertDatabaseHas('listing_specs', [
            'spec_key' => 'vram_gb',
            'spec_value' => '12',
        ]);
    }

    public function test_listing_list_includes_objects_and_dynamic_specs()
    {
        $sellerUser = User::factory()->create(['role' => 'seller', 'phone' => '8888888888']);
        $profile = SellerProfile::create([
            'user_id' => $sellerUser->id,
            'shop_name' => 'Shop',
            'shop_slug' => 'shop',
            'shop_city' => 'Delhi',
            'shop_state' => 'Delhi',
            'shop_pincode' => '110001',
            'kyc_status' => 'approved',
            'badge_level' => 'basic'
        ]);

        $category = Category::create(['name' => 'GPU', 'slug' => 'gpu', 'is_active' => true]);
        $subcategory = Category::create(['name' => 'NVIDIA', 'slug' => 'nvidia', 'parent_id' => $category->id, 'is_active' => true]);
        $brand = Brand::create(['name' => 'ASUS', 'slug' => 'asus', 'is_active' => true]);

        $listing = Listing::create([
            'seller_id' => $profile->id,
            'category_id' => $category->id,
            'subcategory_id' => $subcategory->id,
            'brand_id' => $brand->id,
            'title' => 'RTX 3080',
            'slug' => 'rtx-3080',
            'description' => 'Desc',
            'category' => 'gpu',
            'brand' => 'ASUS',
            'grade' => 'A',
            'price' => 40000,
            'serial_number' => 'SN9999',
            'listing_status' => 'active',
            'pickup_city' => 'Delhi',
            'pickup_state' => 'Delhi',
            'pickup_pincode' => '110001',
        ]);

        ListingSpec::create([
            'listing_id' => $listing->id,
            'spec_key' => 'vram_gb',
            'spec_label' => 'VRAM',
            'spec_value' => '10',
            'spec_unit' => 'GB',
            'is_highlighted' => true
        ]);

        ListingSpec::create([
            'listing_id' => $listing->id,
            'spec_key' => 'interface',
            'spec_label' => 'Interface',
            'spec_value' => 'PCIe 4.0',
            'is_highlighted' => false
        ]);

        $response = $this->getJson('/api/listings');

        $response->assertStatus(200);
        $response->assertJsonPath('data.data.0.category.name', 'GPU');
        $response->assertJsonPath('data.data.0.subcategory.name', 'NVIDIA');
        $response->assertJsonPath('data.data.0.brand.name', 'ASUS');
        $response->assertJsonPath('data.data.0.highlighted_specs.0.spec_value', '10');
        $response->assertJsonPath('data.data.0.all_specs.0.spec_key', 'vram_gb');
        $response->assertJsonPath('data.data.0.compatible_with', 'Compatible: PCIe 4.0');
    }

    public function test_listing_detail_includes_templates_and_compatibility()
    {
        $sellerUser = User::factory()->create(['role' => 'seller', 'phone' => '8888888888']);
        $profile = SellerProfile::create([
            'user_id' => $sellerUser->id,
            'shop_name' => 'Shop',
            'shop_slug' => 'shop',
            'shop_city' => 'Delhi',
            'shop_state' => 'Delhi',
            'shop_pincode' => '110001',
            'kyc_status' => 'approved',
            'badge_level' => 'basic'
        ]);

        $category = Category::create(['name' => 'GPU', 'slug' => 'gpu', 'is_active' => true]);
        $listing = Listing::create([
            'seller_id' => $profile->id,
            'category_id' => $category->id,
            'title' => 'RTX 3080',
            'slug' => 'rtx-3080',
            'description' => 'Desc',
            'category' => 'gpu',
            'grade' => 'A',
            'price' => 40000,
            'serial_number' => 'SN9999',
            'listing_status' => 'active',
            'pickup_city' => 'Delhi',
            'pickup_state' => 'Delhi',
            'pickup_pincode' => '110001',
        ]);

        SpecTemplate::create([
            'category_id' => $category->id,
            'spec_key' => 'vram_gb',
            'spec_label' => 'VRAM',
            'spec_type' => 'number',
            'spec_unit' => 'GB',
            'is_required' => true,
            'is_highlighted' => true
        ]);

        $response = $this->getJson("/api/listings/{$listing->slug}");

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'spec_template',
                'compatible_with'
            ]
        ]);
        $response->assertJsonPath('data.compatible_with', 'Compatible: PCIe 3.0/4.0');
    }

    public function test_search_and_filter_api()
    {
        $sellerUser = User::factory()->create(['role' => 'seller', 'phone' => '8888888888']);
        $profile = SellerProfile::create([
            'user_id' => $sellerUser->id,
            'shop_name' => 'Shop',
            'shop_slug' => 'shop',
            'shop_city' => 'Delhi',
            'shop_state' => 'Delhi',
            'shop_pincode' => '110001',
            'kyc_status' => 'approved',
            'badge_level' => 'basic'
        ]);

        $category = Category::create(['name' => 'GPU', 'slug' => 'gpu', 'is_active' => true]);
        $brand = Brand::create(['name' => 'NVIDIA', 'slug' => 'nvidia', 'is_active' => true]);

        $listing = Listing::create([
            'seller_id' => $profile->id,
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'title' => 'RTX 3080',
            'slug' => 'rtx-3080',
            'description' => 'Desc',
            'category' => 'gpu',
            'brand' => 'NVIDIA',
            'grade' => 'A',
            'price' => 40000,
            'serial_number' => 'SN9999',
            'listing_status' => 'active',
            'pickup_city' => 'Delhi',
            'pickup_state' => 'Delhi',
            'pickup_pincode' => '110001',
        ]);

        ListingSpec::create([
            'listing_id' => $listing->id,
            'spec_key' => 'vram_gb',
            'spec_label' => 'VRAM',
            'spec_value' => '10',
            'spec_unit' => 'GB',
            'is_highlighted' => true
        ]);

        // Filter by brand slug
        $response = $this->getJson('/api/listings?brand_slug=nvidia');
        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data.data');

        // Filter by min price
        $response2 = $this->getJson('/api/listings?min_price=50000');
        $response2->assertStatus(200);
        $response2->assertJsonCount(0, 'data.data');

        // Filter by spec value
        $response3 = $this->getJson('/api/listings?specs[vram_gb]=10');
        $response3->assertStatus(200);
        $response3->assertJsonCount(1, 'data.data');

        // Filter by spec range
        $response4 = $this->getJson('/api/listings?specs[vram_gb][min]=8');
        $response4->assertStatus(200);
        $response4->assertJsonCount(1, 'data.data');
    }

    public function test_automatic_listing_count_increment_decrement()
    {
        $sellerUser = User::factory()->create(['role' => 'seller', 'phone' => '8888888888']);
        $profile = SellerProfile::create([
            'user_id' => $sellerUser->id,
            'shop_name' => 'Shop',
            'shop_slug' => 'shop',
            'shop_city' => 'Delhi',
            'shop_state' => 'Delhi',
            'shop_pincode' => '110001',
            'kyc_status' => 'approved',
            'badge_level' => 'basic'
        ]);

        $category = Category::create(['name' => 'GPU', 'slug' => 'gpu', 'is_active' => true]);
        $brand = Brand::create(['name' => 'NVIDIA', 'slug' => 'nvidia', 'is_active' => true]);

        $listing = Listing::create([
            'seller_id' => $profile->id,
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'title' => 'RTX 3080',
            'slug' => 'rtx-3080',
            'description' => 'Desc',
            'category' => 'gpu',
            'brand' => 'NVIDIA',
            'grade' => 'A',
            'price' => 40000,
            'serial_number' => 'SN9999',
            'listing_status' => 'pending_approval',
            'pickup_city' => 'Delhi',
            'pickup_state' => 'Delhi',
            'pickup_pincode' => '110001',
        ]);

        $this->assertEquals(0, Category::find($category->id)->listing_count);
        $this->assertEquals(0, Brand::find($brand->id)->listing_count);

        // Approve (make active)
        $listing->update(['listing_status' => 'active']);

        $this->assertEquals(1, Category::find($category->id)->listing_count);
        $this->assertEquals(1, Brand::find($brand->id)->listing_count);

        // Make sold
        $listing->update(['listing_status' => 'sold']);

        $this->assertEquals(0, Category::find($category->id)->listing_count);
        $this->assertEquals(0, Brand::find($brand->id)->listing_count);
    }
}
