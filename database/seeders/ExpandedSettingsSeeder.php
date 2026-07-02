<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SiteSetting;

class ExpandedSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            // General
            [
                'key' => 'platform_name',
                'value' => 'X-Buy.in',
                'type' => 'string',
                'group' => 'general',
                'description' => 'The public name of the marketplace platform.'
            ],
            [
                'key' => 'platform_tagline',
                'value' => "India's Safest PC Parts Marketplace",
                'type' => 'string',
                'group' => 'general',
                'description' => 'Tagline used across page headers and metadata.'
            ],
            [
                'key' => 'support_email',
                'value' => 'support@x-buy.in',
                'type' => 'string',
                'group' => 'general',
                'description' => 'Support email address shown to users.'
            ],
            [
                'key' => 'contact_phone',
                'value' => '',
                'type' => 'string',
                'group' => 'general',
                'description' => 'Customer support contact phone number.'
            ],
            [
                'key' => 'platform_active',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'general',
                'description' => 'Toggle online operations of the platform.'
            ],
            [
                'key' => 'maintenance_message',
                'value' => "We'll be back soon!",
                'type' => 'string',
                'group' => 'general',
                'description' => 'Message displayed to users when platform_active is false.'
            ],
            [
                'key' => 'auto_release_escrow_days',
                'value' => '3',
                'type' => 'integer',
                'group' => 'general',
                'description' => 'Auto escrow release window (days) if delivered but not completed.'
            ],

            // Commission
            [
                'key' => 'default_commission',
                'value' => '4',
                'type' => 'integer',
                'group' => 'commission',
                'description' => 'Default commission percentage when not category specified.'
            ],

            // Testing
            [
                'key' => 'default_testing_days',
                'value' => '2',
                'type' => 'integer',
                'group' => 'testing',
                'description' => 'Default listing testing window in days.'
            ],

            // Listings
            [
                'key' => 'max_images_per_listing',
                'value' => '8',
                'type' => 'integer',
                'group' => 'listings',
                'description' => 'Maximum allowed uploaded product photos.'
            ],
            [
                'key' => 'min_images_per_listing',
                'value' => '3',
                'type' => 'integer',
                'group' => 'listings',
                'description' => 'Minimum required photos for product validation.'
            ],
            [
                'key' => 'max_listing_price',
                'value' => '500000',
                'type' => 'integer',
                'group' => 'listings',
                'description' => 'Maximum possible price for standard listing.'
            ],
            [
                'key' => 'min_listing_price',
                'value' => '100',
                'type' => 'integer',
                'group' => 'listings',
                'description' => 'Minimum allowed listing price.'
            ],
            [
                'key' => 'listing_expiry_days',
                'value' => '90',
                'type' => 'integer',
                'group' => 'listings',
                'description' => 'Duration in days before listing automatically expires.'
            ],
            [
                'key' => 'auto_approve_fulfilled_sellers',
                'value' => '0',
                'type' => 'boolean',
                'group' => 'listings',
                'description' => 'Bypass admin review queues for Fulfilled sellers.'
            ],
            [
                'key' => 'require_serial_number',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'listings',
                'description' => 'Mandate hardware serial number declarations.'
            ],
            [
                'key' => 'require_grade',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'listings',
                'description' => 'Mandate product grading criteria (A, B, C).'
            ],

            // Orders
            [
                'key' => 'order_id_prefix',
                'value' => 'XBUY-ORD',
                'type' => 'string',
                'group' => 'orders',
                'description' => 'Prefix slug generated for customer orders.'
            ],
            [
                'key' => 'max_order_value',
                'value' => '500000',
                'type' => 'integer',
                'group' => 'orders',
                'description' => 'Cap limit on transaction order value.'
            ],
            [
                'key' => 'shipping_charge_default',
                'value' => '150',
                'type' => 'integer',
                'group' => 'orders',
                'description' => 'Fallback default shipping fees charge.'
            ],
            [
                'key' => 'free_shipping_above',
                'value' => '20000',
                'type' => 'integer',
                'group' => 'orders',
                'description' => 'Threshold order value for free shipping waiver.'
            ],
            [
                'key' => 'cash_on_delivery',
                'value' => '0',
                'type' => 'boolean',
                'group' => 'orders',
                'description' => 'Enable or disable Cash on Delivery checkout path.'
            ],

            // Seller
            [
                'key' => 'kyc_required_before_listing',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'seller',
                'description' => 'Restrict listing creations to KYC-verified sellers.'
            ],
            [
                'key' => 'min_images_required',
                'value' => '3',
                'type' => 'integer',
                'group' => 'seller',
                'description' => 'Verify minimum image files uploaded.'
            ],
            [
                'key' => 'auto_badge_calculation',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'seller',
                'description' => 'Toggle automatic seller badge level updates.'
            ],
            [
                'key' => 'seller_response_deadline_hours',
                'value' => '48',
                'type' => 'integer',
                'group' => 'seller',
                'description' => 'Time allowed for seller dispute response before resolution.'
            ],
            [
                'key' => 'fulfilled_min_orders',
                'value' => '10',
                'type' => 'integer',
                'group' => 'seller',
                'description' => 'Minimum successful orders required for Fulfilled badge.'
            ],
            [
                'key' => 'fulfilled_min_rating',
                'value' => '4.0',
                'type' => 'string',
                'group' => 'seller',
                'description' => 'Minimum average seller rating required for Fulfilled badge.'
            ],
            [
                'key' => 'fulfilled_min_cooperation_score',
                'value' => '7',
                'type' => 'integer',
                'group' => 'seller',
                'description' => 'Minimum seller cooperation score required for Fulfilled badge.'
            ],
            [
                'key' => 'fulfilled_response_time_hours',
                'value' => '3',
                'type' => 'integer',
                'group' => 'seller',
                'description' => 'Required average inquiry response duration limit.'
            ],

            // Buyer
            [
                'key' => 'trusted_buyer_min_orders',
                'value' => '10',
                'type' => 'integer',
                'group' => 'buyer',
                'description' => 'Minimum completed orders for Trusted Buyer badge.'
            ],
            [
                'key' => 'verified_buyer_min_orders',
                'value' => '5',
                'type' => 'integer',
                'group' => 'buyer',
                'description' => 'Minimum completed orders for Verified Buyer badge.'
            ],
            [
                'key' => 'buyer_badge_visible_to_seller',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'buyer',
                'description' => 'Expose buyer trust levels to product sellers.'
            ],
            [
                'key' => 'buyer_badge_visible_to_buyer',
                'value' => '0',
                'type' => 'boolean',
                'group' => 'buyer',
                'description' => 'Show badge level on buyer portal interfaces.'
            ],

            // Dispute
            [
                'key' => 'dispute_window_after_delivery_days',
                'value' => '3',
                'type' => 'integer',
                'group' => 'dispute',
                'description' => 'Grace window in days after delivery to open disputes.'
            ],
            [
                'key' => 'escalation_after_hours',
                'value' => '48',
                'type' => 'integer',
                'group' => 'dispute',
                'description' => 'Threshold duration before dispute auto escalates to admin.'
            ],
            [
                'key' => 'auto_escalate',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'dispute',
                'description' => 'Auto escalate open cases when seller deadline expires.'
            ],
            [
                'key' => 'dispute_evidence_max_images',
                'value' => '5',
                'type' => 'integer',
                'group' => 'dispute',
                'description' => 'Max allowed image attachments uploaded for evidence.'
            ],
            [
                'key' => 'dispute_evidence_max_size_mb',
                'value' => '5',
                'type' => 'integer',
                'group' => 'dispute',
                'description' => 'Size limit (MB) of upload attachments.'
            ],

            // Notifications
            [
                'key' => 'send_order_email',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'notifications',
                'description' => 'Send transaction emails on orders state change.'
            ],
            [
                'key' => 'send_dispute_email',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'notifications',
                'description' => 'Send notification alerts when disputes are opened/responded.'
            ],
            [
                'key' => 'send_badge_email',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'notifications',
                'description' => 'Alert user via email on trust level level-ups.'
            ],
            [
                'key' => 'send_payment_email',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'notifications',
                'description' => 'Mail confirmation invoices on checkout payments.'
            ],
            [
                'key' => 'send_weekly_seller_summary',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'notifications',
                'description' => 'Dispatch metrics digest newsletter to sellers weekly.'
            ],
            [
                'key' => 'admin_email_on_new_dispute',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'notifications',
                'description' => 'Alert administrators instantly on dispute filings.'
            ],
            [
                'key' => 'admin_email_on_new_seller',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'notifications',
                'description' => 'Alert administrators when a user registers a seller shop.'
            ],
            [
                'key' => 'admin_email_on_high_dispute_rate',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'notifications',
                'description' => 'Trigger high priority warning on seller dispute spikes.'
            ],

            // SEO
            [
                'key' => 'meta_title_suffix',
                'value' => '| X-Buy.in',
                'type' => 'string',
                'group' => 'seo',
                'description' => 'Suffix appended to default browser metadata titles.'
            ],
            [
                'key' => 'meta_description_default',
                'value' => 'Buy and sell verified refurbished PC parts safely on X-Buy.in — escrow protected.',
                'type' => 'string',
                'group' => 'seo',
                'description' => 'Default SEO fallback meta descriptions.'
            ],
            [
                'key' => 'og_image_default',
                'value' => '/og-default.jpg',
                'type' => 'string',
                'group' => 'seo',
                'description' => 'Default open-graph preview card display image.'
            ],
            [
                'key' => 'google_analytics_id',
                'value' => '',
                'type' => 'string',
                'group' => 'seo',
                'description' => 'Google Analytics (GA4) Tracking ID.'
            ],
            [
                'key' => 'google_search_console_key',
                'value' => '',
                'type' => 'string',
                'group' => 'seo',
                'description' => 'Google Webmaster Tools Verification Key.'
            ],

            // Social
            [
                'key' => 'facebook_url',
                'value' => '',
                'type' => 'string',
                'group' => 'social',
                'description' => 'Official Facebook page url link.'
            ],
            [
                'key' => 'instagram_url',
                'value' => '',
                'type' => 'string',
                'group' => 'social',
                'description' => 'Instagram profile link.'
            ],
            [
                'key' => 'twitter_url',
                'value' => '',
                'type' => 'string',
                'group' => 'social',
                'description' => 'Twitter/X feed account link.'
            ],
            [
                'key' => 'youtube_url',
                'value' => '',
                'type' => 'string',
                'group' => 'social',
                'description' => 'Official YouTube Channel link.'
            ],
            [
                'key' => 'whatsapp_number',
                'value' => '',
                'type' => 'string',
                'group' => 'social',
                'description' => 'Business WhatsApp channel number.'
            ],
            [
                'key' => 'playstore_url',
                'value' => '',
                'type' => 'string',
                'group' => 'social',
                'description' => 'Google Play Store link for your Android app.'
            ],

            // Razorpay
            [
                'key' => 'razorpay_mode',
                'value' => 'test',
                'type' => 'string',
                'group' => 'razorpay',
                'description' => 'Razorpay Gateway mode: test or live.'
            ],
            [
                'key' => 'razorpay_key_id',
                'value' => '',
                'type' => 'string',
                'group' => 'razorpay',
                'description' => 'Razorpay API Key ID credentials.'
            ],
            [
                'key' => 'razorpay_key_secret',
                'value' => '',
                'type' => 'string',
                'group' => 'razorpay',
                'description' => 'Razorpay API Key Secret credentials.'
            ],
            [
                'key' => 'razorpay_webhook_secret',
                'value' => '',
                'type' => 'string',
                'group' => 'razorpay',
                'description' => 'Razorpay Webhook verification secret.'
            ],

            // Shiprocket
            [
                'key' => 'shiprocket_active',
                'value' => '0',
                'type' => 'boolean',
                'group' => 'shiprocket',
                'description' => 'Enable or disable Shiprocket auto delivery flow.'
            ],
            [
                'key' => 'shiprocket_email',
                'value' => '',
                'type' => 'string',
                'group' => 'shiprocket',
                'description' => 'Shiprocket account email.'
            ],
            [
                'key' => 'shiprocket_password',
                'value' => '',
                'type' => 'string',
                'group' => 'shiprocket',
                'description' => 'Shiprocket account password.'
            ],
            [
                'key' => 'shiprocket_channel_id',
                'value' => '',
                'type' => 'string',
                'group' => 'shiprocket',
                'description' => 'Shiprocket integration channel ID.'
            ],
            [
                'key' => 'default_courier',
                'value' => 'auto',
                'type' => 'string',
                'group' => 'shiprocket',
                'description' => 'Preferred shipping vendor service.'
            ],
        ];

        foreach ($settings as $item) {
            SiteSetting::updateOrCreate(
                ['key' => $item['key']],
                [
                    'value' => $item['value'],
                    'type' => $item['type'],
                    'group' => $item['group'],
                    'description' => $item['description'],
                ]
            );
        }
    }
}
