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

        // 5. Create only 1 Dummy Listing
        $listings = [];

        // Create a specific, searchable ASUS RX 6600 listing for testing
        $asusrx6600 = Listing::create([
            'seller_id' => $sellerProfiles[0]->id,
            'title' => 'ASUS Dual Radeon RX 6600 8GB GDDR6 Graphics Card',
            'slug' => 'asus-dual-radeon-rx-6600-8gb',
            'description' => 'Refurbished ASUS Dual Radeon RX 6600 8GB Graphics Card. Features AMD RDNA 2 architecture, 8GB GDDR6 memory, dual Axial-tech fan design, and a protective backplate. Ideal for high-framerate 1080p gaming and budget-friendly streaming builds. Tested, cleaned, and fully operational in excellent condition.',
            'category' => 'gpu',
            'grade' => 'A',
            'serial_number' => 'SN-ASUSRX6600-DUAL',
            'brand' => 'ASUS',
            'model_name' => 'ASUS Dual RX 6600',
            'price' => 18500,
            'original_price' => 26000,
            'manufacturer_warranty_status' => 'expired',
            'document_status' => 'none',
            'listing_status' => 'active',
            'shipping_type' => 'prepaid',
            'shipping_charges' => 120.00,
            'pickup_city' => 'Mumbai',
            'pickup_state' => 'Maharashtra',
            'pickup_pincode' => '400001',
            'views_count' => 145,
        ]);

        ListingImage::create([
            'listing_id' => $asusrx6600->id,
            'image_url' => '/website_assets/images/image_2026-08-15_13-42-28_1786781939.png',
            'is_primary' => true,
            'sort_order' => 0,
        ]);

        ListingImage::create([
            'listing_id' => $asusrx6600->id,
            'image_url' => '/website_assets/images/image_2026-08-15_13-42-28__2__1786781939.png',
            'is_primary' => false,
            'sort_order' => 1,
        ]);

        ListingImage::create([
            'listing_id' => $asusrx6600->id,
            'image_url' => '/website_assets/images/image_2026-08-15_13-42-28__3__1786781939.png',
            'is_primary' => false,
            'sort_order' => 2,
        ]);

        ListingImage::create([
            'listing_id' => $asusrx6600->id,
            'image_url' => '/website_assets/images/image_2026-08-15_13-42-28__4__1786781939.png',
            'is_primary' => false,
            'sort_order' => 3,
        ]);

        $listings[] = $asusrx6600;

        // Seed 4 additional GPU listings for Similar Products section
        $dummyGpus = [
            [
                'title' => 'MSI GeForce RTX 3050 VENTUS 2X 8G OC',
                'slug' => 'msi-geforce-rtx-3050-ventus-2x-8g',
                'description' => 'MSI GeForce RTX 3050 Ventus 2x graphics card with dual fan cooling, 8GB GDDR6 VRAM, Ray Tracing support. Perfect for modern games.',
                'price' => 23409,
                'original_price' => 33999,
                'primary_img' => 'https://images.unsplash.com/photo-1591488320449-011701bb6704?auto=format&fit=crop&q=80&w=400',
            ],
            [
                'title' => 'AMD Sapphire Pulse Radeon RX 6600 8GB',
                'slug' => 'amd-sapphire-pulse-radeon-rx-6600-8gb',
                'description' => 'Sapphire Pulse Radeon RX 6600 graphics card, 8GB memory, high performance cooling, RDNA 2 architecture.',
                'price' => 23129,
                'original_price' => 25839,
                'primary_img' => 'https://images.unsplash.com/photo-1607604276583-eef5d076aa5f?auto=format&fit=crop&q=80&w=400',
            ],
            [
                'title' => 'ASUS Dual NVIDIA Geforce RTX 3060 12GB',
                'slug' => 'asus-dual-nvidia-geforce-rtx-3060-12gb',
                'description' => 'ASUS Dual RTX 3060 with 12GB high-speed GDDR6 VRAM. Great for streaming, content creation and 1080p ultra gaming.',
                'price' => 26500,
                'original_price' => 35000,
                'primary_img' => 'https://images.unsplash.com/photo-1587202372775-e229f172b9d7?auto=format&fit=crop&q=80&w=400',
            ],
            [
                'title' => 'GIGABYTE GeForce RTX 3060 Ti Gaming OC 8G',
                'slug' => 'gigabyte-geforce-rtx-3060-ti-gaming-oc-8g',
                'description' => 'Gigabyte GeForce RTX 3060 Ti with Windforce triple fans, 8GB memory. Premium performance model.',
                'price' => 34999,
                'original_price' => 85500,
                'primary_img' => 'https://images.unsplash.com/photo-1591488320449-011701bb6704?auto=format&fit=crop&q=80&w=400',
            ]
        ];

        foreach ($dummyGpus as $index => $dg) {
            $dl = Listing::create([
                'seller_id' => $sellerProfiles[($index + 1) % count($sellerProfiles)]->id,
                'title' => $dg['title'],
                'slug' => $dg['slug'],
                'description' => $dg['description'],
                'category' => 'gpu',
                'grade' => 'A',
                'serial_number' => 'SN-DGPU-' . $index,
                'brand' => 'ASUS',
                'model_name' => $dg['title'],
                'price' => $dg['price'],
                'original_price' => $dg['original_price'],
                'manufacturer_warranty_status' => 'expired',
                'document_status' => 'none',
                'listing_status' => 'active',
                'shipping_type' => 'prepaid',
                'shipping_charges' => 120.00,
                'pickup_city' => 'Bangalore',
                'pickup_state' => 'Karnataka',
                'pickup_pincode' => '560001',
                'views_count' => rand(50, 200),
            ]);

            ListingImage::create([
                'listing_id' => $dl->id,
                'image_url' => $dg['primary_img'],
                'is_primary' => true,
                'sort_order' => 0,
            ]);

            $listings[] = $dl;
        }


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
