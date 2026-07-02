<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPageManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed settings
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

    public function test_non_admin_cannot_access_page_management()
    {
        $user = User::factory()->create(['role' => 'buyer', 'phone' => '9999999991']);
        
        $response = $this->actingAs($user)->get('/admin/pages');
        $response->assertStatus(302); // Redirect to login or home
    }

    public function test_admin_can_view_pages_list()
    {
        $admin = User::factory()->create(['role' => 'admin', 'phone' => '9999999992']);
        $page = Page::create([
            'title' => 'Refund Policy',
            'slug' => 'refund-policy',
            'content' => '<p>Refund info</p>',
            'is_protected' => true,
        ]);

        $response = $this->actingAs($admin)->get('/admin/pages');
        $response->assertStatus(200);
        $response->assertSee('Refund Policy');
        $response->assertSee('refund-policy');
    }

    public function test_admin_can_create_custom_page()
    {
        $admin = User::factory()->create(['role' => 'admin', 'phone' => '9999999993']);

        $response = $this->actingAs($admin)->post('/admin/pages', [
            'title' => 'Custom Policy',
            'slug' => 'custom-policy',
            'content' => '<h1>Custom Title</h1><p>Body content</p>',
            'meta_title' => 'Custom SEO Title',
            'meta_description' => 'Custom SEO Desc',
            'is_active' => '1',
        ]);

        $response->assertRedirect('/admin/pages');
        $this->assertDatabaseHas('pages', [
            'title' => 'Custom Policy',
            'slug' => 'custom-policy',
            'is_protected' => false,
            'is_active' => true,
            'last_edited_by' => $admin->id,
        ]);
    }

    public function test_admin_can_update_page()
    {
        $admin = User::factory()->create(['role' => 'admin', 'phone' => '9999999994']);
        $page = Page::create([
            'title' => 'Old Title',
            'slug' => 'old-slug',
            'content' => 'Old content',
            'is_protected' => false,
        ]);

        $response = $this->actingAs($admin)->put("/admin/pages/{$page->id}", [
            'title' => 'Updated Title',
            'slug' => 'updated-slug',
            'content' => 'New content',
            'meta_title' => 'New SEO',
            'meta_description' => 'New SEO Desc',
            'is_active' => '1',
        ]);

        $response->assertRedirect('/admin/pages');
        $this->assertDatabaseHas('pages', [
            'id' => $page->id,
            'title' => 'Updated Title',
            'slug' => 'updated-slug',
            'content' => 'New content',
            'last_edited_by' => $admin->id,
        ]);
    }

    public function test_admin_cannot_change_protected_page_slug()
    {
        $admin = User::factory()->create(['role' => 'admin', 'phone' => '9999999995']);
        $page = Page::create([
            'title' => 'Privacy Policy',
            'slug' => 'privacy-policy',
            'content' => 'Content',
            'is_protected' => true,
        ]);

        $response = $this->actingAs($admin)->put("/admin/pages/{$page->id}", [
            'title' => 'Updated Privacy Policy',
            'slug' => 'some-other-slug-here',
            'content' => 'Updated Content',
            'is_active' => '1',
        ]);

        $response->assertRedirect('/admin/pages');
        $this->assertDatabaseHas('pages', [
            'id' => $page->id,
            'title' => 'Updated Privacy Policy',
            'slug' => 'privacy-policy', // remains unchanged
        ]);
    }

    public function test_admin_can_delete_custom_page()
    {
        $admin = User::factory()->create(['role' => 'admin', 'phone' => '9999999996']);
        $page = Page::create([
            'title' => 'Custom Page',
            'slug' => 'custom-page',
            'content' => 'content',
            'is_protected' => false,
        ]);

        $response = $this->actingAs($admin)->delete("/admin/pages/{$page->id}");
        $response->assertRedirect('/admin/pages');
        $this->assertSoftDeleted($page);
    }

    public function test_admin_cannot_delete_protected_page()
    {
        $admin = User::factory()->create(['role' => 'admin', 'phone' => '9999999997']);
        $page = Page::create([
            'title' => 'Privacy Policy',
            'slug' => 'privacy-policy',
            'content' => 'content',
            'is_protected' => true,
        ]);

        $response = $this->actingAs($admin)->delete("/admin/pages/{$page->id}");
        $response->assertRedirect('/admin/pages');
        $response->assertSessionHas('error', 'Protected core pages cannot be deleted.');
        $this->assertDatabaseHas('pages', ['id' => $page->id, 'deleted_at' => null]);
    }

    public function test_admin_can_restore_deleted_page()
    {
        $admin = User::factory()->create(['role' => 'admin', 'phone' => '9999999998']);
        $page = Page::create([
            'title' => 'Deleted Page',
            'slug' => 'deleted-page',
            'content' => 'content',
            'is_protected' => false,
        ]);
        $page->delete();

        $this->assertSoftDeleted($page);

        $response = $this->actingAs($admin)->post("/admin/pages/{$page->id}/restore");
        $response->assertRedirect('/admin/pages');
        $this->assertDatabaseHas('pages', ['id' => $page->id, 'deleted_at' => null]);
    }

    public function test_admin_can_force_delete_page()
    {
        $admin = User::factory()->create(['role' => 'admin', 'phone' => '9999999999']);
        $page = Page::create([
            'title' => 'Deleted Page',
            'slug' => 'deleted-page',
            'content' => 'content',
            'is_protected' => false,
        ]);
        $page->delete();

        $response = $this->actingAs($admin)->delete("/admin/pages/{$page->id}/force");
        $response->assertRedirect('/admin/pages');
        $this->assertDatabaseMissing('pages', ['id' => $page->id]);
    }

    public function test_category_cascade_deletes_pages_and_restores()
    {
        $admin = User::factory()->create(['role' => 'admin', 'phone' => '9999999900']);
        $category = \App\Models\PageCategory::create(['name' => 'Support']);
        $page = Page::create([
            'category_id' => $category->id,
            'title' => 'Help Page',
            'slug' => 'help-page',
            'content' => 'content',
            'is_protected' => false,
        ]);

        // Delete category
        $response = $this->actingAs($admin)->delete("/admin/page-categories/{$category->id}");
        $response->assertRedirect('/admin/pages');
        $this->assertSoftDeleted($category);
        $this->assertSoftDeleted($page);

        // Restore category
        $response = $this->actingAs($admin)->post("/admin/page-categories/{$category->id}/restore");
        $response->assertRedirect('/admin/pages');
        $this->assertDatabaseHas('page_categories', ['id' => $category->id, 'deleted_at' => null]);
        $this->assertDatabaseHas('pages', ['id' => $page->id, 'deleted_at' => null]);
    }

    public function test_auto_prune_deletes_older_than_30_days()
    {
        $admin = User::factory()->create(['role' => 'admin', 'phone' => '9999999901']);
        $page = Page::create([
            'title' => 'Old Trashed Page',
            'slug' => 'old-trashed-page',
            'content' => 'content',
            'is_protected' => false,
        ]);
        
        // Directly set deleted_at to 31 days ago
        $page->deleted_at = now()->subDays(31);
        $page->save();

        $response = $this->actingAs($admin)->get('/admin/pages');
        $response->assertStatus(200);

        $this->assertDatabaseMissing('pages', ['id' => $page->id]);
    }


    public function test_public_pages_api_endpoints()
    {
        $page1 = Page::create([
            'title' => 'Privacy Policy',
            'slug' => 'privacy-policy',
            'content' => '<p>Privacy info</p>',
            'is_active' => true,
        ]);

        $page2 = Page::create([
            'title' => 'Draft Policy',
            'slug' => 'draft-policy',
            'content' => '<p>Draft</p>',
            'is_active' => false,
        ]);

        // Test Index API
        $response = $this->getJson('/api/v1/pages');
        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.slug', 'privacy-policy');

        // Test Show API (Active)
        $response = $this->getJson('/api/v1/pages/privacy-policy');
        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('data.content', '<p>Privacy info</p>');

        // Test Show API (Draft)
        $response = $this->getJson('/api/v1/pages/draft-policy');
        $response->assertStatus(404);

        // Test Sitemap API
        $response = $this->getJson('/api/v1/sitemap/pages');
        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $response->assertJsonCount(1, 'data');
    }

    public function test_public_page_web_view()
    {
        $page = Page::create([
            'title' => 'Terms of Service',
            'slug' => 'terms-of-service',
            'content' => '<h1>Terms of Service</h1><p>Our terms content</p>',
            'is_active' => true,
        ]);

        $response = $this->get("/v1/pages/{$page->slug}");
        $response->assertStatus(200);
        $response->assertSee('Terms of Service');
        $response->assertSee('Our terms content');
        $response->assertSee('Log in'); // verifies presence of the homepage layout header elements
    }

    public function test_admin_can_reorder_pages()
    {
        $admin = User::factory()->create(['role' => 'admin', 'phone' => '9999999902']);
        $category = \App\Models\PageCategory::create(['name' => 'Support']);
        $page = Page::create([
            'title' => 'Page 1',
            'slug' => 'page-1',
            'content' => 'content',
            'is_protected' => false,
        ]);

        $response = $this->actingAs($admin)->put("/admin/pages/reorder", [
            'ids' => [$page->id],
            'page_id' => $page->id,
            'category_id' => $category->id,
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('pages', [
            'id' => $page->id,
            'category_id' => $category->id,
        ]);
    }
}


