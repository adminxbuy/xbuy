<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $settings = [
            // Google OAuth
            [
                'key' => 'google_login_enabled',
                'value' => '0',
                'type' => 'boolean',
                'group' => 'social',
                'description' => 'Enable or disable Google login on the web client.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'google_client_id',
                'value' => '',
                'type' => 'string',
                'group' => 'social',
                'description' => 'Google Client ID credentials for OAuth.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'google_client_secret',
                'value' => '',
                'type' => 'string',
                'group' => 'social',
                'description' => 'Google Client Secret credentials for OAuth.',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Facebook OAuth
            [
                'key' => 'facebook_login_enabled',
                'value' => '0',
                'type' => 'boolean',
                'group' => 'social',
                'description' => 'Enable or disable Facebook login on the web client.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'facebook_client_id',
                'value' => '',
                'type' => 'string',
                'group' => 'social',
                'description' => 'Facebook Client ID credentials for OAuth.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'facebook_client_secret',
                'value' => '',
                'type' => 'string',
                'group' => 'social',
                'description' => 'Facebook Client Secret credentials for OAuth.',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Apple OAuth
            [
                'key' => 'apple_login_enabled',
                'value' => '0',
                'type' => 'boolean',
                'group' => 'social',
                'description' => 'Enable or disable Apple login on the web client.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'apple_client_id',
                'value' => '',
                'type' => 'string',
                'group' => 'social',
                'description' => 'Apple Client ID credentials for OAuth.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'apple_client_secret',
                'value' => '',
                'type' => 'string',
                'group' => 'social',
                'description' => 'Apple Client Secret credentials for OAuth.',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Dynamic Policy Slugs
            [
                'key' => 'terms_page_slug',
                'value' => 'terms-of-service',
                'type' => 'string',
                'group' => 'social',
                'description' => 'The slug of the Terms of Service policy page.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'privacy_page_slug',
                'value' => 'privacy-policy',
                'type' => 'string',
                'group' => 'social',
                'description' => 'The slug of the Privacy Policy page.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($settings as $setting) {
            DB::table('site_settings')->updateOrInsert(['key' => $setting['key']], $setting);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $keys = [
            'google_login_enabled',
            'google_client_id',
            'google_client_secret',
            'facebook_login_enabled',
            'facebook_client_id',
            'facebook_client_secret',
            'apple_login_enabled',
            'apple_client_id',
            'apple_client_secret',
            'terms_page_slug',
            'privacy_page_slug',
        ];

        DB::table('site_settings')->whereIn('key', $keys)->delete();
    }
};
