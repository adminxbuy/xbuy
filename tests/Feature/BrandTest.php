<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Category;
use App\Models\User;
use App\Models\Listing;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BrandTest extends TestCase
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

    public function test_admin_can_load_brands_page()
    {
        $this->markTestSkipped('Admin brands page and routes have been removed.');
        $admin = User::factory()->create(['role' => 'admin', 'phone' => '9999999999']);
        Brand::create(['name' => 'NVIDIA', 'slug' => 'nvidia', 'is_active' => true]);

        $response = $this->actingAs($admin)->get('/admin/brands');

        $response->assertStatus(200);
        $response->assertSee('NVIDIA');
    }

    public function test_admin_can_create_brand()
    {
        $this->markTestSkipped('Admin brands page and routes have been removed.');
        $admin = User::factory()->create(['role' => 'admin', 'phone' => '9999999999']);
        $category = Category::create(['name' => 'GPU', 'slug' => 'gpu', 'is_active' => true]);

        $response = $this->actingAs($admin)->post('/admin/brands', [
            'name' => 'NVIDIA',
            'category_ids' => [$category->id]
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('brands', [
            'name' => 'NVIDIA',
            'slug' => 'nvidia'
        ]);

        $brand = Brand::where('slug', 'nvidia')->first();
        $this->assertTrue($brand->categories->contains($category->id));
    }

    public function test_admin_can_update_brand()
    {
        $this->markTestSkipped('Admin brands page and routes have been removed.');
        $admin = User::factory()->create(['role' => 'admin', 'phone' => '9999999999']);
        $brand = Brand::create(['name' => 'NVIDIA', 'slug' => 'nvidia']);
        $category = Category::create(['name' => 'GPU', 'slug' => 'gpu', 'is_active' => true]);

        $response = $this->actingAs($admin)->put("/admin/brands/{$brand->id}", [
            'name' => 'NVIDIA Corp',
            'category_ids' => [$category->id]
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('brands', [
            'id' => $brand->id,
            'name' => 'NVIDIA Corp',
            'slug' => 'nvidia-corp'
        ]);

        $this->assertTrue($brand->fresh()->categories->contains($category->id));
    }

    public function test_admin_can_delete_brand()
    {
        $this->markTestSkipped('Admin brands page and routes have been removed.');
        $admin = User::factory()->create(['role' => 'admin', 'phone' => '9999999999']);
        $brand = Brand::create(['name' => 'NVIDIA', 'slug' => 'nvidia']);

        $response = $this->actingAs($admin)->delete("/admin/brands/{$brand->id}");

        $response->assertRedirect();
        $this->assertDatabaseMissing('brands', ['id' => $brand->id]);
    }

    public function test_admin_can_toggle_brand_active_status()
    {
        $this->markTestSkipped('Admin brands page and routes have been removed.');
        $admin = User::factory()->create(['role' => 'admin', 'phone' => '9999999999']);
        $brand = Brand::create(['name' => 'NVIDIA', 'slug' => 'nvidia', 'is_active' => true]);

        $response = $this->actingAs($admin)->put("/admin/brands/{$brand->id}/toggle-active");

        $response->assertStatus(200);
        $response->assertJson(['success' => true, 'is_active' => false]);
    }

    public function test_public_api_can_fetch_brands()
    {
        $brand1 = Brand::create(['name' => 'NVIDIA', 'slug' => 'nvidia', 'is_active' => true]);
        $brand2 = Brand::create(['name' => 'Intel', 'slug' => 'intel', 'is_active' => false]);

        $response = $this->getJson('/api/v1/brands');

        $response->assertStatus(200);
        $response->assertJsonPath('data.0.name', 'NVIDIA');
        $this->assertCount(1, $response->json('data'));
    }

    public function test_public_api_can_fetch_brands_filtered_by_category()
    {
        $category1 = Category::create(['name' => 'GPU', 'slug' => 'gpu', 'is_active' => true]);
        $category2 = Category::create(['name' => 'CPU', 'slug' => 'cpu', 'is_active' => true]);
        
        $brand1 = Brand::create(['name' => 'NVIDIA', 'slug' => 'nvidia', 'is_active' => true]);
        $brand2 = Brand::create(['name' => 'Intel', 'slug' => 'intel', 'is_active' => true]);

        $brand1->categories()->attach($category1->id);
        $brand2->categories()->attach($category2->id);

        // Fetch GPU brands
        $response = $this->getJson('/api/v1/brands?category=gpu');
        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $response->assertJsonPath('data.0.name', 'NVIDIA');

        // Fetch CPU brands
        $response = $this->getJson('/api/v1/brands?category=cpu');
        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $response->assertJsonPath('data.0.name', 'Intel');
    }

    public function test_admin_can_set_brand_logo_from_asset_library_or_remove()
    {
        $this->markTestSkipped('Admin brands page and routes have been removed.');
        $admin = User::factory()->create(['role' => 'admin', 'phone' => '9999999999']);

        // 1. Create with asset library logo
        $response = $this->actingAs($admin)->post('/admin/brands', [
            'name' => 'Corsair',
            'selected_logo_path' => '/website_assets/images/corsair.png',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('brands', [
            'name' => 'Corsair',
            'logo' => '/website_assets/images/corsair.png',
        ]);

        $brand = Brand::where('name', 'Corsair')->first();

        // 2. Update with another asset library logo
        $response = $this->actingAs($admin)->put("/admin/brands/{$brand->id}", [
            'name' => 'Corsair Updated',
            'selected_logo_path' => '/website_assets/images/corsair2.png',
        ]);

        $response->assertRedirect();
        $this->assertEquals('/website_assets/images/corsair2.png', $brand->fresh()->logo);

        // 3. Remove logo
        $response = $this->actingAs($admin)->put("/admin/brands/{$brand->id}", [
            'name' => 'Corsair Updated',
            'selected_logo_path' => 'remove',
        ]);

        $response->assertRedirect();
        $this->assertNull($brand->fresh()->logo);
    }
}
