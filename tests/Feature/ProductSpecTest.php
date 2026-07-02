<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Listing;
use App\Models\ListingSpec;
use App\Models\SpecTemplate;
use App\Models\User;
use App\Models\SellerProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductSpecTest extends TestCase
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

    public function test_admin_can_load_spec_templates_page()
    {
        $admin = User::factory()->create(['role' => 'admin', 'phone' => '9999999999']);
        $category = Category::create(['name' => 'GPU', 'slug' => 'gpu', 'is_active' => true]);
        SpecTemplate::create([
            'category_id' => $category->id,
            'spec_key' => 'vram_gb',
            'spec_label' => 'VRAM',
            'spec_type' => 'number',
            'spec_unit' => 'GB',
            'is_required' => true,
            'is_highlighted' => true
        ]);

        $response = $this->actingAs($admin)->get('/admin/spec-templates');

        $response->assertStatus(200);
        $response->assertSee('vram_gb');
        $response->assertSee('VRAM');
    }

    public function test_admin_can_create_spec_template()
    {
        $admin = User::factory()->create(['role' => 'admin', 'phone' => '9999999999']);
        $category = Category::create(['name' => 'GPU', 'slug' => 'gpu', 'is_active' => true]);

        $response = $this->actingAs($admin)->post('/admin/spec-templates', [
            'category_id' => $category->id,
            'spec_key' => 'vram_gb',
            'spec_label' => 'VRAM',
            'spec_type' => 'number',
            'spec_unit' => 'GB',
            'is_required' => 1,
            'is_highlighted' => 1
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('spec_templates', [
            'category_id' => $category->id,
            'spec_key' => 'vram_gb',
            'spec_type' => 'number',
            'is_required' => true,
            'is_highlighted' => true
        ]);
    }

    public function test_admin_can_update_spec_template()
    {
        $admin = User::factory()->create(['role' => 'admin', 'phone' => '9999999999']);
        $category = Category::create(['name' => 'GPU', 'slug' => 'gpu', 'is_active' => true]);
        $template = SpecTemplate::create([
            'category_id' => $category->id,
            'spec_key' => 'vram_gb',
            'spec_label' => 'VRAM',
            'spec_type' => 'number',
            'spec_unit' => 'GB'
        ]);

        $response = $this->actingAs($admin)->put("/admin/spec-templates/{$template->id}", [
            'spec_label' => 'VRAM Capacity',
            'spec_type' => 'select',
            'options' => '8GB,12GB,16GB',
            'is_required' => 1,
            'is_highlighted' => 1
        ]);

        $response->assertRedirect();
        
        $updated = $template->fresh();
        $this->assertEquals('VRAM Capacity', $updated->spec_label);
        $this->assertEquals('select', $updated->spec_type);
        $this->assertEquals(['8GB', '12GB', '16GB'], $updated->options);
        $this->assertTrue($updated->is_required);
        $this->assertTrue($updated->is_highlighted);
    }

    public function test_seller_creation_validates_required_specs()
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
        SpecTemplate::create([
            'category_id' => $category->id,
            'spec_key' => 'vram_gb',
            'spec_label' => 'VRAM',
            'spec_type' => 'number',
            'spec_unit' => 'GB',
            'is_required' => true,
            'is_highlighted' => true
        ]);

        // Creating without required spec 'vram_gb' should fail
        $response = $this->actingAs($user, 'sanctum')->postJson('/api/seller/listings', [
            'title' => 'NVIDIA RTX 4070',
            'description' => 'Brand new card',
            'category' => 'gpu',
            'grade' => 'A',
            'price' => 50000,
            'original_price' => 60000,
            'serial_number' => 'SN12345',
            'brand' => 'NVIDIA',
            'model_name' => 'RTX 4070',
            'manufacturer_warranty_status' => 'none',
            'document_status' => 'full',
            'shipping_type' => 'prepaid',
            'shipping_charges' => 150,
            'pickup_city' => 'Mumbai',
            'pickup_state' => 'Maharashtra',
            'pickup_pincode' => '400001',
            'images' => ['https://via.placeholder.com/150'],
            'specs' => [
                // 'vram_gb' missing
            ]
        ]);

        $response->assertStatus(422);
        $response->assertJsonPath('success', false);
        $response->assertJsonFragment(['message' => "The spec field 'VRAM' is required."]);

        // Creating with required spec 'vram_gb' should succeed
        $response2 = $this->actingAs($user, 'sanctum')->postJson('/api/seller/listings', [
            'title' => 'NVIDIA RTX 4070',
            'description' => 'Brand new card',
            'category' => 'gpu',
            'grade' => 'A',
            'price' => 50000,
            'original_price' => 60000,
            'serial_number' => 'SN12345',
            'brand' => 'NVIDIA',
            'model_name' => 'RTX 4070',
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

        $response2->assertStatus(201);
        $response2->assertJsonPath('success', true);
        $this->assertDatabaseHas('listing_specs', [
            'spec_key' => 'vram_gb',
            'spec_value' => '12',
            'spec_unit' => 'GB',
            'is_highlighted' => true
        ]);
    }

    public function test_listing_detail_api_returns_grouped_specs()
    {
        $seller = User::factory()->create(['role' => 'seller', 'phone' => '8888888888']);
        $profile = SellerProfile::create([
            'user_id' => $seller->id,
            'shop_name' => 'Shop',
            'shop_slug' => 'shop',
            'shop_city' => 'Delhi',
            'shop_state' => 'Delhi',
            'shop_pincode' => '110001',
            'kyc_status' => 'approved',
            'badge_level' => 'basic',
            'status' => 'active',
        ]);
        $seller->role = 'seller';
        $seller->save();

        $listing = Listing::create([
            'seller_id' => $profile->id,
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
            'spec_key' => 'memory_type',
            'spec_label' => 'Memory Type',
            'spec_value' => 'GDDR6X',
            'is_highlighted' => false
        ]);

        $response = $this->getJson("/api/listings/{$listing->slug}");

        $response->assertStatus(200);
        $response->assertJsonPath('data.highlighted_specs.0.spec_key', 'vram_gb');
        $response->assertJsonPath('data.other_specs.0.spec_key', 'memory_type');
    }
}
