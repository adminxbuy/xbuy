<?php

namespace Database\Seeders;

use App\Models\Dispute;
use App\Models\Escrow;
use App\Models\Listing;
use App\Models\ListingImage;
use App\Models\Order;
use App\Models\Rating;
use App\Models\SellerMetrics;
use App\Models\SellerProfile;
use App\Models\SiteSetting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        \Illuminate\Support\Facades\Schema::disableForeignKeyConstraints();
        \App\Models\User::truncate();
        \App\Models\SellerProfile::truncate();
        \App\Models\SellerMetrics::truncate();
        \App\Models\Listing::truncate();
        \App\Models\ListingImage::truncate();
        \App\Models\Order::truncate();
        \App\Models\Escrow::truncate();
        \App\Models\Dispute::truncate();
        \Illuminate\Support\Facades\Schema::enableForeignKeyConstraints();

        // 1. Seed Site Settings
        $settings = [
            [
                'key' => 'commission_rates',
                'value' => json_encode([
                    'gpu' => 5.0, 'cpu' => 4.0, 'motherboard' => 4.0, 'ram' => 3.0, 
                    'storage' => 3.0, 'psu' => 4.0, 'cabinet' => 4.0, 'peripheral' => 5.0,
                    'full_build' => 6.0, 'cooling' => 4.0, 'networking' => 4.0, 'cables' => 3.0, 'other' => 5.0
                ]),
                'type' => 'json',
                'group' => 'rates',
                'description' => 'Platform commission rates percentage by product category'
            ],
            [
                'key' => 'testing_windows',
                'value' => json_encode([
                    'gpu' => 3, 'cpu' => 2, 'motherboard' => 3, 'ram' => 2,
                    'storage' => 2, 'psu' => 2, 'cabinet' => 1, 'peripheral' => 2,
                    'full_build' => 4, 'cooling' => 2, 'networking' => 2, 'cables' => 1, 'other' => 2
                ]),
                'type' => 'json',
                'group' => 'policy',
                'description' => 'Buyer testing period windows in days by category'
            ],
            [
                'key' => 'auto_release_escrow_days',
                'value' => '3',
                'type' => 'integer',
                'group' => 'policy',
                'description' => 'Fallback auto escrow release window (days) if delivered but not completed'
            ],
            [
                'key' => 'platform_active',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'general',
                'description' => 'Global marketplace operations state toggle'
            ],
            // SMTP Settings
            [
                'key' => 'smtp_host',
                'value' => '127.0.0.1',
                'type' => 'string',
                'group' => 'smtp',
                'description' => 'SMTP Mail Server Host address'
            ],
            [
                'key' => 'smtp_port',
                'value' => '1025',
                'type' => 'integer',
                'group' => 'smtp',
                'description' => 'SMTP Mail Server Port number'
            ],
            [
                'key' => 'smtp_username',
                'value' => '',
                'type' => 'string',
                'group' => 'smtp',
                'description' => 'SMTP Mail Server Username'
            ],
            [
                'key' => 'smtp_password',
                'value' => '',
                'type' => 'string',
                'group' => 'smtp',
                'description' => 'SMTP Mail Server Password'
            ],
            [
                'key' => 'smtp_from_email',
                'value' => 'no-reply@xbuy.in',
                'type' => 'string',
                'group' => 'smtp',
                'description' => 'SMTP Sender Email address'
            ],
            [
                'key' => 'smtp_from_name',
                'value' => 'X-Buy Escrow Marketplace',
                'type' => 'string',
                'group' => 'smtp',
                'description' => 'SMTP Sender display name'
            ],
            [
                'key' => 'smtp_encryption',
                'value' => 'None',
                'type' => 'string',
                'group' => 'smtp',
                'description' => 'SMTP Connection Encryption (TLS/SSL/None)'
            ],
            [
                'key' => 'currency_selector',
                'value' => 'INR',
                'type' => 'string',
                'group' => 'general',
                'description' => 'Platform display currency (INR or USD)'
            ],
            [
                'key' => 'timezone_selector',
                'value' => 'Asia/Kolkata',
                'type' => 'string',
                'group' => 'general',
                'description' => 'Platform timezone setting'
            ],
            [
                'key' => 'ticket_admin_email',
                'value' => 'contact@x-buy.in',
                'type' => 'string',
                'group' => 'tickets',
                'description' => 'Admin email to receive support tickets'
            ],
            [
                'key' => 'ticket_email_notifications_enabled',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'tickets',
                'description' => 'Send email alerts to admin when a new ticket is created'
            ],
            [
                'key' => 'ticket_whatsapp_notifications_enabled',
                'value' => '0',
                'type' => 'boolean',
                'group' => 'tickets',
                'description' => 'Trigger WhatsApp alert when a new ticket is created'
            ],
            [
                'key' => 'ticket_whatsapp_number',
                'value' => '',
                'type' => 'string',
                'group' => 'tickets',
                'description' => 'WhatsApp number (with country code) for support ticket alerts'
            ],
            // Default Brand & Logo Settings
            [
                'key' => 'platform_name',
                'value' => 'X-Buy',
                'type' => 'string',
                'group' => 'general',
                'description' => 'Name of the platform'
            ],
            [
                'key' => 'support_email',
                'value' => 'support@xbuy.in',
                'type' => 'string',
                'group' => 'general',
                'description' => 'Customer support email address'
            ],
            [
                'key' => 'website_logo',
                'value' => '/website_assets/images/logo_1780233010.png',
                'type' => 'string',
                'group' => 'general',
                'description' => 'Platform logo image file'
            ],
            [
                'key' => 'website_favicon',
                'value' => '/website_assets/images/favicon_1780233088.png',
                'type' => 'string',
                'group' => 'general',
                'description' => 'Platform favicon file'
            ],
            [
                'key' => 'homepage_banner_image',
                'value' => '',
                'type' => 'string',
                'group' => 'general',
                'description' => 'Homepage promotional banner image'
            ],
            [
                'key' => 'homepage_banner_url',
                'value' => '/listings',
                'type' => 'string',
                'group' => 'general',
                'description' => 'Homepage promotional banner button redirect URL'
            ],
            [
                'key' => 'admin_2fa_enabled',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'general',
                'description' => 'Require Two-Factor Authentication (2FA) for all administrative staff login'
            ]
        ];

        foreach ($settings as $setting) {
            SiteSetting::updateOrCreate(['key' => $setting['key']], $setting);
        }

        // 2. Create 1 Admin User
        $admin = User::updateOrCreate(
            ['email' => env('ADMIN_EMAIL', 'x-buy@admin.in')],
            [
                'name' => 'Marketplace Administrator',
                'phone' => '9999999999',
                'password' => Hash::make(env('ADMIN_PASSWORD', '9Uqb{]S0h[2Za+^"{q6`')),
                'role' => 'admin',
                'status' => 'active',
                'is_email_verified' => true,
                'is_phone_verified' => true,
            ]
        );

        // 3. Create 5 Sellers with Profiles
        $sellerNames = ['Gadget Zone', 'PC Hardware Hub', 'Silicon Bazaar', 'Tech Traders', 'Omega Rig Store'];
        $categories = ['gpu', 'cpu', 'motherboard', 'ram', 'storage', 'psu', 'cabinet', 'peripheral'];
        $brands = ['NVIDIA', 'AMD', 'Intel', 'ASUS', 'MSI', 'Gigabyte', 'Corsair', 'Kingston'];

        $sellerProfiles = [];

        foreach ($sellerNames as $index => $name) {
            $user = User::create([
                'name' => "Seller " . ($index + 1),
                'email' => "seller" . ($index + 1) . "@xbuy.in",
                'phone' => "987654321" . $index,
                'password' => Hash::make('password123'),
                'role' => 'seller',
                'status' => 'active',
                'is_email_verified' => true,
                'is_phone_verified' => true,
            ]);

            $profile = SellerProfile::create([
                'user_id' => $user->id,
                'shop_name' => $name,
                'shop_slug' => Str::slug($name),
                'shop_city' => 'Bangalore',
                'shop_state' => 'Karnataka',
                'shop_pincode' => '560001',
                'badge_level' => $index == 0 ? 'fulfilled' : ($index == 1 ? 'verified' : 'basic'),
                'kyc_status' => 'approved',
                'aadhaar_number' => '12345678901' . $index,
                'pan_number' => 'ABCDE1234' . chr(65 + $index),
                'bank_account_number' => '10002000300' . $index,
                'bank_ifsc' => 'BARB0INDBAN',
                'upi_id' => 'seller' . ($index + 1) . '@okaxis',
                'status' => 'active',
            ]);

            SellerMetrics::create([
                'seller_id' => $profile->id,
                'total_orders' => 0,
                'completed_orders' => 0,
                'star_rating' => 4.5,
                'total_ratings' => 1,
                'cooperation_score' => 9.5,
            ]);

            $sellerProfiles[] = $profile;
        }

        // 4. Create 5 Buyer Users
        $buyers = [];
        for ($i = 1; $i <= 5; $i++) {
            $buyers[] = User::create([
                'name' => "Buyer Customer {$i}",
                'email' => "buyer{$i}@xbuy.in",
                'phone' => "912345678{$i}",
                'password' => Hash::make('password123'),
                'role' => 'buyer',
                'status' => 'active',
                'is_email_verified' => true,
                'is_phone_verified' => true,
            ]);
        }

        // 5. Create 20 Listings (mixed active/sold/pending)
        $listings = [];
        for ($i = 1; $i <= 20; $i++) {
            $cat = $categories[array_rand($categories)];
            $brand = $brands[array_rand($brands)];
            $grade = ['A', 'B', 'C'][array_rand(['A', 'B', 'C'])];
            $price = rand(15, 120) * 500;
            $seller = $sellerProfiles[array_rand($sellerProfiles)];

            $status = 'active';
            if ($i == 1 || $i == 2) {
                $status = 'pending_approval';
            } elseif ($i >= 15) {
                $status = 'sold';
            }

            $listing = Listing::create([
                'seller_id' => $seller->id,
                'title' => "Refurbished {$brand} " . strtoupper($cat) . " Grade {$grade}",
                'slug' => Str::slug("Refurbished {$brand} " . strtoupper($cat) . " Grade {$grade}") . '-' . Str::lower(Str::random(5)),
                'description' => "Fully tested and clean component in working condition. Ideal for gaming and workstation builds. Includes serial code verification.",
                'category' => $cat,
                'grade' => $grade,
                'serial_number' => 'SN-' . strtoupper(Str::random(12)),
                'brand' => $brand,
                'model_name' => 'Model-' . rand(100, 999),
                'price' => $price,
                'original_price' => $price * 1.4,
                'manufacturer_warranty_status' => rand(0, 1) ? 'expired' : 'none',
                'document_status' => 'full',
                'listing_status' => $status,
                'shipping_type' => 'prepaid',
                'shipping_charges' => 150.00,
                'pickup_city' => 'Bangalore',
                'pickup_state' => 'Karnataka',
                'pickup_pincode' => '560001',
                'views_count' => rand(10, 150),
            ]);

            // Add Listing image
            ListingImage::create([
                'listing_id' => $listing->id,
                'image_url' => "https://images.unsplash.com/photo-1591488320449-011701bb6704?auto=format&fit=crop&q=80&w=400",
                'is_primary' => true,
                'sort_order' => 0,
            ]);

            $listings[] = $listing;
        }

        // Create a specific, searchable RTX 4090 listing for testing
        $rtx4090 = Listing::create([
            'seller_id' => $sellerProfiles[0]->id,
            'title' => 'NVIDIA GeForce RTX 4090 Founders Edition Graphics Card',
            'slug' => 'nvidia-geforce-rtx-4090-founders-edition',
            'description' => 'Flagship NVIDIA GeForce RTX 4090 Founders Edition GPU. 24GB GDDR6X VRAM. Tested and fully operational in pristine condition.',
            'category' => 'gpu',
            'grade' => 'A',
            'serial_number' => 'SN-RTX4090FE-9999',
            'brand' => 'NVIDIA',
            'model_name' => 'RTX 4090 FE',
            'price' => 150000,
            'original_price' => 180000,
            'manufacturer_warranty_status' => 'active',
            'document_status' => 'full',
            'listing_status' => 'active',
            'shipping_type' => 'prepaid',
            'shipping_charges' => 250.00,
            'pickup_city' => 'Bangalore',
            'pickup_state' => 'Karnataka',
            'pickup_pincode' => '560001',
            'views_count' => 150,
        ]);

        ListingImage::create([
            'listing_id' => $rtx4090->id,
            'image_url' => 'https://images.unsplash.com/photo-1591488320449-011701bb6704?auto=format&fit=crop&q=80&w=400',
            'is_primary' => true,
            'sort_order' => 0,
        ]);

        $listings[] = $rtx4090;


        // 6. Create 10 Orders with associated Escrow records
        $orderStatuses = ['payment_received', 'confirmed', 'delivered', 'testing_period', 'completed', 'disputed'];
        $soldListings = array_filter($listings, fn($l) => $l->listing_status === 'sold');
        
        $orderIndex = 1;
        foreach ($soldListings as $listing) {
            $buyer = $buyers[array_rand($buyers)];
            $status = $orderStatuses[($orderIndex - 1) % count($orderStatuses)];

            $productAmount = $listing->price;
            $shippingAmount = $listing->shipping_charges;
            $totalAmount = $productAmount + $shippingAmount;
            $commissionPercent = 5.00;
            $commissionAmount = round(($productAmount * $commissionPercent) / 100, 2);
            $sellerPayoutAmount = $productAmount - $commissionAmount;

            $order = Order::create([
                'order_number' => 'XBUY-ORD-' . strtoupper(Str::random(10)),
                'listing_id' => $listing->id,
                'buyer_id' => $buyer->id,
                'seller_id' => $listing->seller_id,
                'product_amount' => $productAmount,
                'shipping_amount' => $shippingAmount,
                'total_amount' => $totalAmount,
                'commission_percent' => $commissionPercent,
                'commission_amount' => $commissionAmount,
                'seller_payout_amount' => $sellerPayoutAmount,
                'razorpay_order_id' => 'order_' . Str::random(14),
                'razorpay_payment_id' => 'pay_' . Str::random(14),
                'order_status' => $status,
                'delivery_address' => [
                    'name' => $buyer->name,
                    'phone' => $buyer->phone,
                    'street' => '123 Main St, Tech Block',
                    'city' => 'Bangalore',
                    'state' => 'Karnataka',
                    'pincode' => '560001',
                ],
                'testing_window_days' => 2,
                'delivered_at' => $status === 'testing_period' || $status === 'completed' || $status === 'disputed' ? Carbon::now()->subDays(1) : null,
                'testing_window_ends_at' => $status === 'testing_period' || $status === 'disputed' ? Carbon::now()->addDays(1) : ($status === 'completed' ? Carbon::now()->subDays(1) : null),
                'completed_at' => $status === 'completed' ? Carbon::now() : null,
            ]);

            // Add Escrow
            Escrow::create([
                'order_id' => $order->id,
                'amount_held' => $order->product_amount,
                'seller_amount' => $order->seller_payout_amount,
                'commission_amount' => $order->commission_amount,
                'status' => $status === 'completed' ? 'released' : 'held',
                'warranty_days' => $order->testing_window_days,
                'released_at' => $status === 'completed' ? Carbon::now() : null,
            ]);

            // 7. Create 2 Disputes out of the disputed orders
            if ($status === 'disputed') {
                Dispute::create([
                    'order_id' => $order->id,
                    'buyer_id' => $order->buyer_id,
                    'seller_id' => $order->seller_id,
                    'dispute_type' => 'item_not_working',
                    'description' => 'The GPU has graphical lines on the screen during boot. Under load, it crashes immediately. Checked using Furmark benchmarks.',
                    'evidence_images' => ['https://images.unsplash.com/photo-1591488320449-011701bb6704?auto=format&fit=crop&q=80&w=400'],
                    'status' => 'open',
                ]);
            }

            $orderIndex++;
        }

        $this->call([
            CategorySeeder::class,
            BrandSeeder::class,
            SpecTemplateSeeder::class,
            PageSeeder::class,
            ExpandedSettingsSeeder::class,
            MailAndEscrowSettingsSeeder::class,
        ]);

        // 8. Seed some sample Alerts/Notifications for Admin
        if ($admin) {
            \App\Models\Notification::create([
                'user_id' => $admin->id,
                'title' => 'New Dispute Filed',
                'message' => 'Buyer Customer 1 has raised a dispute on Order #XBUY-ORD-H82K9P1S due to: Item Not Working.',
                'type' => 'dispute',
                'reference_type' => 'dispute',
                'reference_id' => 1,
                'is_read' => false,
                'created_at' => Carbon::now()->subMinutes(12),
            ]);
            \App\Models\Notification::create([
                'user_id' => $admin->id,
                'title' => 'Pending KYC Verification',
                'message' => 'Seller Omega Rig Store has submitted KYC documents for Aadhaar & PAN verification.',
                'type' => 'badge',
                'reference_type' => 'seller',
                'reference_id' => 5,
                'is_read' => false,
                'created_at' => Carbon::now()->subHours(2),
            ]);
            \App\Models\Notification::create([
                'user_id' => $admin->id,
                'title' => 'Listing Pending Approval',
                'message' => 'New listing "Refurbished NVIDIA GPU Grade A" has been created and requires review.',
                'type' => 'system',
                'reference_type' => 'listing',
                'reference_id' => 1,
                'is_read' => true,
                'read_at' => Carbon::now()->subHour(),
                'created_at' => Carbon::now()->subHours(4),
            ]);
            \App\Models\Notification::create([
                'user_id' => $admin->id,
                'title' => 'High Dispute Rate Warning',
                'message' => 'Seller PC Hardware Hub has exceeded the 3% dispute rate threshold (currently 5.2%).',
                'type' => 'system',
                'reference_type' => 'seller',
                'reference_id' => 2,
                'is_read' => false,
                'created_at' => Carbon::now()->subDays(1),
            ]);
            \App\Models\Notification::create([
                'user_id' => $admin->id,
                'title' => 'Escrow Auto-Release Warning',
                'message' => 'Order #XBUY-ORD-B82L1A9Z is scheduled for auto-escrow release in 12 hours.',
                'type' => 'payment',
                'reference_type' => 'order',
                'reference_id' => 3,
                'is_read' => true,
                'read_at' => Carbon::now()->subDays(2),
                'created_at' => Carbon::now()->subDays(3),
            ]);
        }

        // 9. Seed some sample Ratings
        $completedOrders = Order::whereIn('order_status', ['completed', 'delivered'])->get();
        foreach ($completedOrders as $index => $order) {
            Rating::updateOrCreate(
                ['order_id' => $order->id],
                [
                    'seller_id' => $order->seller_id,
                    'buyer_id' => $order->buyer_id,
                    'rating_type' => $index % 3 === 0 ? 'manual' : 'auto',
                    'item_accuracy' => $index % 2 === 0 ? 5.00 : 4.00,
                    'packaging' => $index % 3 === 0 ? 4.50 : 5.00,
                    'shipping_speed' => $index % 4 === 0 ? 3.50 : 4.50,
                    'communication' => 4.50,
                    'weighted_total' => round((($index % 2 === 0 ? 5.00 : 4.00) + ($index % 3 === 0 ? 4.50 : 5.00) + ($index % 4 === 0 ? 3.50 : 4.50) + 4.50) / 4, 2),
                    'review_text' => $index % 2 === 0 ? 'Excellent product! Packaging was secure and shipping was fast. Recommended.' : 'Good communication and decent shipping times. Product is as described.',
                    'is_public' => true,
                ]
            );
        }

        // Recalculate metrics for the seeded ratings
        (new \App\Jobs\SellerMetricsJob())->handle();
    }
}
