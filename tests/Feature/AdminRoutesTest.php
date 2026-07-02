<?php

namespace Tests\Feature;

use App\Models\SellerProfile;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminRoutesTest extends TestCase
{
    use RefreshDatabase;

    private $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'phone' => '9999999999',
            'role' => 'admin',
        ]);
    }

    public function test_analytics_endpoints()
    {
        $this->actingAs($this->admin);

        $this->getJson('/admin/analytics/gmv')
            ->assertStatus(200)
            ->assertJsonStructure(['success', 'data' => ['total_gmv', 'monthly']]);

        $this->getJson('/admin/analytics/top-sellers')
            ->assertStatus(200)
            ->assertJsonStructure(['success', 'data']);

        $this->getJson('/admin/analytics/top-categories')
            ->assertStatus(200)
            ->assertJsonStructure(['success', 'data']);

        $this->getJson('/admin/analytics/dispute-rate')
            ->assertStatus(200)
            ->assertJsonStructure(['success', 'data' => ['total_orders', 'disputed_orders', 'dispute_rate_percent']]);
    }

    public function test_chart_endpoints()
    {
        $this->actingAs($this->admin);

        $this->getJson('/admin/dashboard/charts/orders')
            ->assertStatus(200)
            ->assertJsonStructure(['success', 'data']);

        $this->getJson('/admin/dashboard/charts/revenue')
            ->assertStatus(200)
            ->assertJsonStructure(['success', 'data']);

        $this->getJson('/admin/dashboard/charts/categories')
            ->assertStatus(200)
            ->assertJsonStructure(['success', 'data']);
    }

    public function test_visit_verify_endpoint()
    {
        $sellerUser = User::factory()->create(['phone' => '9876543210']);
        $seller = SellerProfile::create([
            'user_id' => $sellerUser->id,
            'shop_name' => 'Test Shop',
            'shop_slug' => 'test-shop',
            'shop_city' => 'Delhi',
            'shop_state' => 'Delhi',
            'shop_pincode' => '110001',
            'shop_visit_verified' => false,
        ]);

        $this->actingAs($this->admin)
            ->post("/admin/sellers/{$seller->id}/visit-verify")
            ->assertRedirect();

        $seller->refresh();
        $this->assertTrue($seller->shop_visit_verified);
        $this->assertNotNull($seller->shop_visit_date);
    }

    public function test_seller_status_manual_update()
    {
        $sellerUser = User::factory()->create(['phone' => '9876543210']);
        $seller = SellerProfile::create([
            'user_id' => $sellerUser->id,
            'shop_name' => 'Test Shop',
            'shop_slug' => 'test-shop',
            'shop_city' => 'Delhi',
            'shop_state' => 'Delhi',
            'shop_pincode' => '110001',
            'status' => 'pending',
        ]);

        $this->actingAs($this->admin)
            ->post("/admin/sellers/{$seller->id}/status", [
                'status' => 'suspended',
            ])
            ->assertRedirect();

        $seller->refresh();
        $this->assertEquals('suspended', $seller->status);
    }

    public function test_seller_badge_manual_update()
    {
        $sellerUser = User::factory()->create(['phone' => '9876543210']);
        $seller = SellerProfile::create([
            'user_id' => $sellerUser->id,
            'shop_name' => 'Test Shop',
            'shop_slug' => 'test-shop',
            'shop_city' => 'Delhi',
            'shop_state' => 'Delhi',
            'shop_pincode' => '110001',
            'badge_level' => 'basic',
        ]);

        $this->actingAs($this->admin)
            ->post("/admin/sellers/{$seller->id}/badge", [
                'badge_level' => 'fulfilled',
            ])
            ->assertRedirect();

        $seller->refresh();
        $this->assertEquals('fulfilled', $seller->badge_level);
    }

    public function test_smtp_endpoints()
    {
        SiteSetting::create([
            'key' => 'smtp_host',
            'value' => 'smtp.mailtrap.io',
            'type' => 'string',
            'group' => 'smtp',
        ]);

        $this->actingAs($this->admin)
            ->put('/admin/settings/smtp', [
                'smtp_host' => 'smtp.newhost.com',
            ])
            ->assertRedirect();

        $this->assertEquals('smtp.newhost.com', SiteSetting::where('key', 'smtp_host')->value('value'));
    }
}
