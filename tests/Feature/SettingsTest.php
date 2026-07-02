<?php

namespace Tests\Feature;

use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_load_and_update_site_settings()
    {
        // Pre-seed some settings
        SiteSetting::create([
            'key' => 'commission_rates',
            'value' => json_encode(['gpu' => 5.0]),
            'type' => 'json',
            'group' => 'rates',
        ]);

        SiteSetting::create([
            'key' => 'testing_windows',
            'value' => json_encode(['gpu' => 3]),
            'type' => 'json',
            'group' => 'policy',
        ]);

        $admin = User::factory()->create([
            'phone' => '9999999999',
            'role' => 'admin',
        ]);

        $response = $this->actingAs($admin)
            ->get('/admin/settings');

        $response->assertStatus(200);
        $response->assertSee('General Policy & Rates', false);

        // Update settings
        $updateResponse = $this->actingAs($admin)
            ->post('/admin/settings', [
                'commission_rates' => json_encode(['gpu' => 6.0]),
                'testing_windows' => json_encode(['gpu' => 4]),
            ]);

        $updateResponse->assertRedirect();
        $this->assertEquals(json_encode(['gpu' => 6.0]), SiteSetting::where('key', 'commission_rates')->value('value'));
        $this->assertEquals(json_encode(['gpu' => 4]), SiteSetting::where('key', 'testing_windows')->value('value'));
    }

    public function test_admin_can_update_logo_and_favicon_from_asset_library_or_remove()
    {
        $admin = User::factory()->create([
            'phone' => '9999999998',
            'role' => 'admin',
        ]);

        // 1. Select logo and favicon from asset library
        $response = $this->actingAs($admin)
            ->post('/admin/settings', [
                'selected_logo_path' => '/website_assets/images/logo.png',
                'selected_favicon_path' => '/website_assets/images/favicon.ico',
            ]);

        $response->assertRedirect();
        $this->assertEquals('/website_assets/images/logo.png', SiteSetting::where('key', 'website_logo')->value('value'));
        $this->assertEquals('/website_assets/images/favicon.ico', SiteSetting::where('key', 'website_favicon')->value('value'));

        // 2. Remove logo and favicon
        $response = $this->actingAs($admin)
            ->post('/admin/settings', [
                'selected_logo_path' => 'remove',
                'selected_favicon_path' => 'remove',
            ]);

        $response->assertRedirect();
        $this->assertEquals('', SiteSetting::where('key', 'website_logo')->value('value'));
        $this->assertEquals('', SiteSetting::where('key', 'website_favicon')->value('value'));
    }
}
