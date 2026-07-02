<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use App\Models\Listing;
use App\Models\SellerProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryTest extends TestCase
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

    public function test_admin_can_load_categories_page()
    {
        $admin = User::factory()->create(['role' => 'admin', 'phone' => '9999999999']);
        Category::create(['name' => 'GPU', 'slug' => 'gpu', 'is_active' => true]);

        $response = $this->actingAs($admin)->get('/admin/categories');

        $response->assertStatus(200);
        $response->assertSee('GPU');
    }

    public function test_admin_can_create_category()
    {
        $admin = User::factory()->create(['role' => 'admin', 'phone' => '9999999999']);

        $response = $this->actingAs($admin)->post('/admin/categories', [
            'name' => 'Motherboard',
            'icon' => 'layers',
            'description' => 'Motherboard category description'
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('categories', [
            'name' => 'Motherboard',
            'slug' => 'motherboard',
            'icon' => 'layers'
        ]);
    }

    public function test_admin_can_update_category()
    {
        $admin = User::factory()->create(['role' => 'admin', 'phone' => '9999999999']);
        $category = Category::create(['name' => 'GPU', 'slug' => 'gpu']);

        $response = $this->actingAs($admin)->put("/admin/categories/{$category->id}", [
            'name' => 'Graphics Cards',
            'description' => 'New Description'
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'Graphics Cards',
            'slug' => 'graphics-cards'
        ]);
    }

    public function test_admin_cannot_delete_category_with_listings()
    {
        $admin = User::factory()->create(['role' => 'admin', 'phone' => '9999999999']);
        $category = Category::create(['name' => 'GPU', 'slug' => 'gpu', 'listing_count' => 1]);

        $response = $this->actingAs($admin)->delete("/admin/categories/{$category->id}");

        $response->assertRedirect();
        $this->assertDatabaseHas('categories', ['id' => $category->id]);
    }

    public function test_admin_can_delete_empty_category()
    {
        $admin = User::factory()->create(['role' => 'admin', 'phone' => '9999999999']);
        $category = Category::create(['name' => 'GPU', 'slug' => 'gpu', 'listing_count' => 0]);

        $response = $this->actingAs($admin)->delete("/admin/categories/{$category->id}");

        $response->assertRedirect();
        $this->assertSoftDeleted('categories', ['id' => $category->id]);
    }

    public function test_admin_can_toggle_category_active_status()
    {
        $admin = User::factory()->create(['role' => 'admin', 'phone' => '9999999999']);
        $category = Category::create(['name' => 'GPU', 'slug' => 'gpu', 'is_active' => true]);

        $response = $this->actingAs($admin)->put("/admin/categories/{$category->id}/toggle-active");

        $response->assertStatus(200);
        $response->assertJson(['success' => true, 'is_active' => false]);
    }

    public function test_admin_can_reorder_categories()
    {
        $admin = User::factory()->create(['role' => 'admin', 'phone' => '9999999999']);
        $cat1 = Category::create(['name' => 'GPU', 'slug' => 'gpu', 'sort_order' => 0]);
        $cat2 = Category::create(['name' => 'CPU', 'slug' => 'cpu', 'sort_order' => 1]);

        $response = $this->actingAs($admin)->put("/admin/categories/reorder", [
            'ids' => [$cat2->id, $cat1->id]
        ]);

        $response->assertStatus(200);
        $this->assertEquals(0, $cat2->fresh()->sort_order);
        $this->assertEquals(1, $cat1->fresh()->sort_order);
    }

    public function test_public_api_can_fetch_categories()
    {
        $parent = Category::create(['name' => 'GPU', 'slug' => 'gpu', 'is_active' => true]);
        Category::create(['name' => 'NVIDIA', 'slug' => 'nvidia', 'parent_id' => $parent->id, 'is_active' => true]);
        Category::create(['name' => 'AMD', 'slug' => 'amd', 'parent_id' => $parent->id, 'is_active' => false]);

        $response = $this->getJson('/api/v1/categories');

        $response->assertStatus(200);
        $response->assertJsonPath('data.0.name', 'GPU');
        $this->assertCount(1, $response->json('data.0.children'));
        $response->assertJsonPath('data.0.children.0.name', 'NVIDIA');
    }

    public function test_public_api_can_fetch_category_details()
    {
        $parent = Category::create(['name' => 'GPU', 'slug' => 'gpu', 'is_active' => true]);
        
        $response = $this->getJson("/api/v1/categories/{$parent->slug}");

        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'data' => ['category', 'listings']]);
    }

    public function test_admin_can_set_category_image_from_asset_library_or_remove()
    {
        $admin = User::factory()->create(['role' => 'admin', 'phone' => '9999999999']);

        // 1. Create with asset library image
        $response = $this->actingAs($admin)->post('/admin/categories', [
            'name' => 'Monitors',
            'selected_image_path' => '/website_assets/images/monitor.png',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('categories', [
            'name' => 'Monitors',
            'image' => '/website_assets/images/monitor.png',
        ]);

        $category = Category::where('name', 'Monitors')->first();

        // 2. Update with another asset library image
        $response = $this->actingAs($admin)->put("/admin/categories/{$category->id}", [
            'name' => 'Monitors Updated',
            'selected_image_path' => '/website_assets/images/monitor2.png',
        ]);

        $response->assertRedirect();
        $this->assertEquals('/website_assets/images/monitor2.png', $category->fresh()->image);

        // 3. Remove image
        $response = $this->actingAs($admin)->put("/admin/categories/{$category->id}", [
            'name' => 'Monitors Updated',
            'selected_image_path' => 'remove',
        ]);

        $response->assertRedirect();
        $this->assertNull($category->fresh()->image);
    }
}
