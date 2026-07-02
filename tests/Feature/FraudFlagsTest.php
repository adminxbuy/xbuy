<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Dispute;
use App\Models\FraudFlag;
use App\Models\Listing;
use App\Models\Order;
use App\Models\SellerProfile;
use App\Models\SoldArchive;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class FraudFlagsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed site settings
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

    public function test_duplicate_serial_triggers_fraud_flag()
    {
        $sellerUser = User::factory()->create(['role' => 'seller', 'phone' => '9999999999']);
        $seller = SellerProfile::create([
            'user_id' => $sellerUser->id,
            'shop_name' => 'Shop A',
            'shop_slug' => 'shop-a',
            'shop_city' => 'Delhi',
            'shop_state' => 'Delhi',
            'shop_pincode' => '110001',
            'status' => 'active',
        ]);
        $sellerUser->role = 'seller';
        $sellerUser->save();

        // Create an active listing
        Listing::create([
            'seller_id' => $seller->id,
            'title' => 'First GPU',
            'slug' => 'first-gpu',
            'category' => 'gpu',
            'grade' => 'A',
            'price' => 10000,
            'serial_number' => 'SERIAL123',
            'pickup_city' => 'Delhi',
            'pickup_state' => 'Delhi',
            'pickup_pincode' => '110001',
            'listing_status' => 'active',
        ]);

        // Create duplicate serial listing
        $listing2 = Listing::create([
            'seller_id' => $seller->id,
            'title' => 'Second GPU',
            'slug' => 'second-gpu',
            'category' => 'gpu',
            'grade' => 'A',
            'price' => 10000,
            'serial_number' => 'SERIAL123',
            'pickup_city' => 'Delhi',
            'pickup_state' => 'Delhi',
            'pickup_pincode' => '110001',
            'listing_status' => 'active',
        ]);

        $this->assertDatabaseHas('fraud_flags', [
            'flag_type' => 'duplicate_serial',
            'flagged_listing_id' => $listing2->id,
            'flagged_user_id' => $sellerUser->id,
        ]);
    }

    public function test_same_ip_multiple_sellers_triggers_fraud_flag()
    {
        // 3 seller profiles on same IP registration verify
        $ip = '192.168.200.5';

        // Seller 1
        $user1 = User::factory()->create(['phone' => '1111111111']);
        SellerProfile::create([
            'user_id' => $user1->id,
            'shop_name' => 'Shop 1',
            'shop_slug' => 'shop-1',
            'shop_city' => 'Delhi',
            'shop_state' => 'Delhi',
            'shop_pincode' => '110001',
            'status' => 'active',
        ]);
        $user1->role = 'seller';
        $user1->save();

        // Verification 1
        \App\Models\OtpVerification::create(['email' => $user1->email, 'type' => 'login', 'otp' => '123456', 'expires_at' => now()->addMinutes(10)]);
        $this->postJson('/api/auth/otp/verify', [
            'email' => $user1->email,
            'otp' => '123456',
            'type' => 'login'
        ], ['REMOTE_ADDR' => $ip]);

        // Seller 2
        $user2 = User::factory()->create(['phone' => '2222222222']);
        SellerProfile::create([
            'user_id' => $user2->id,
            'shop_name' => 'Shop 2',
            'shop_slug' => 'shop-2',
            'shop_city' => 'Delhi',
            'shop_state' => 'Delhi',
            'shop_pincode' => '110001',
            'status' => 'active',
        ]);
        $user2->role = 'seller';
        $user2->save();

        // Verification 2
        \App\Models\OtpVerification::create(['email' => $user2->email, 'type' => 'login', 'otp' => '654321', 'expires_at' => now()->addMinutes(10)]);
        $this->postJson('/api/auth/otp/verify', [
            'email' => $user2->email,
            'otp' => '654321',
            'type' => 'login'
        ], ['REMOTE_ADDR' => $ip]);

        // Seller 3
        $user3 = User::factory()->create(['phone' => '3333333333']);
        SellerProfile::create([
            'user_id' => $user3->id,
            'shop_name' => 'Shop 3',
            'shop_slug' => 'shop-3',
            'shop_city' => 'Delhi',
            'shop_state' => 'Delhi',
            'shop_pincode' => '110001',
            'status' => 'active',
        ]);
        $user3->role = 'seller';
        $user3->save();

        // Verification 3
        \App\Models\OtpVerification::create(['email' => $user3->email, 'type' => 'login', 'otp' => '111222', 'expires_at' => now()->addMinutes(10)]);
        $this->postJson('/api/auth/otp/verify', [
            'email' => $user3->email,
            'otp' => '111222',
            'type' => 'login'
        ], ['REMOTE_ADDR' => $ip]);

        $this->assertDatabaseHas('fraud_flags', [
            'flag_type' => 'multiple_accounts_same_ip',
            'flagged_user_id' => $user3->id
        ]);
    }

    public function test_same_bank_multiple_sellers_triggers_fraud_flag()
    {
        $user1 = User::factory()->create(['phone' => '1111111111']);
        $user2 = User::factory()->create(['phone' => '2222222222']);

        $profile1 = SellerProfile::create([
            'user_id' => $user1->id,
            'shop_name' => 'Shop 1',
            'shop_slug' => 'shop-1',
            'shop_city' => 'Delhi',
            'shop_state' => 'Delhi',
            'shop_pincode' => '110001',
            'bank_account_number' => '1234567890',
            'status' => 'active',
        ]);

        $profile2 = SellerProfile::create([
            'user_id' => $user2->id,
            'shop_name' => 'Shop 2',
            'shop_slug' => 'shop-2',
            'shop_city' => 'Delhi',
            'shop_state' => 'Delhi',
            'shop_pincode' => '110001',
            'bank_account_number' => '1234567890', // duplicate bank details
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('fraud_flags', [
            'flag_type' => 'same_bank_multiple_sellers',
            'flagged_user_id' => $user2->id
        ]);
    }

    public function test_suspicious_buyer_pattern_triggers_fraud_flag()
    {
        $buyer = User::factory()->create(['phone' => '9999999999', 'role' => 'buyer']);
        $sellerUser = User::factory()->create(['phone' => '8888888888', 'role' => 'seller']);
        $seller = SellerProfile::create([
            'user_id' => $sellerUser->id,
            'shop_name' => 'Shop A',
            'shop_slug' => 'shop-a',
            'shop_city' => 'Delhi',
            'shop_state' => 'Delhi',
            'shop_pincode' => '110001',
            'status' => 'active',
        ]);

        // Pre-create 3 orders
        $orders = [];
        for ($i = 0; $i < 3; $i++) {
            $listing = Listing::create([
                'seller_id' => $seller->id,
                'title' => "GPU {$i}",
                'slug' => "gpu-{$i}",
                'category' => 'gpu',
                'grade' => 'A',
                'price' => 1000,
                'serial_number' => "SN{$i}",
                'pickup_city' => 'Delhi',
                'pickup_state' => 'Delhi',
                'pickup_pincode' => '110001',
                'listing_status' => 'active',
            ]);

            $orders[$i] = Order::create([
                'order_number' => "ORD-{$i}",
                'listing_id' => $listing->id,
                'buyer_id' => $buyer->id,
                'seller_id' => $seller->id,
                'product_amount' => 1000,
                'shipping_amount' => 50,
                'total_amount' => 1050,
                'commission_percent' => 5,
                'commission_amount' => 50,
                'seller_payout_amount' => 950,
                'order_status' => 'testing_period',
                'delivery_address' => ['city' => 'Delhi'],
            ]);
        }

        Sanctum::actingAs($buyer);

        // Raise 3 disputes
        for ($i = 0; $i < 3; $i++) {
            $this->postJson("/api/buyer/orders/{$orders[$i]->id}/dispute", [
                'dispute_type' => 'item_not_working',
                'description' => 'Dispute'
            ]);
        }

        $this->assertDatabaseHas('fraud_flags', [
            'flag_type' => 'suspicious_buyer_pattern',
            'flagged_user_id' => $buyer->id
        ]);
    }

    public function test_rapid_listings_triggers_fraud_flag()
    {
        $sellerUser = User::factory()->create(['phone' => '8888888888', 'role' => 'seller']);
        $seller = SellerProfile::create([
            'user_id' => $sellerUser->id,
            'shop_name' => 'Shop A',
            'shop_slug' => 'shop-a',
            'shop_city' => 'Delhi',
            'shop_state' => 'Delhi',
            'shop_pincode' => '110001',
            'status' => 'active',
        ]);
        $sellerUser->role = 'seller';
        $sellerUser->save();

        // Create 10 listings rapidly
        for ($i = 0; $i < 10; $i++) {
            Listing::create([
                'seller_id' => $seller->id,
                'title' => "Fast GPU {$i}",
                'slug' => "fast-gpu-{$i}",
                'category' => 'gpu',
                'grade' => 'A',
                'price' => 5000,
                'serial_number' => "RAPIDSN{$i}",
                'pickup_city' => 'Delhi',
                'pickup_state' => 'Delhi',
                'pickup_pincode' => '110001',
                'listing_status' => 'active',
            ]);
        }

        $this->assertDatabaseHas('fraud_flags', [
            'flag_type' => 'rapid_listings',
            'flagged_user_id' => $sellerUser->id
        ]);
    }

    public function test_admin_can_manage_fraud_flags()
    {
        $admin = User::factory()->create(['role' => 'admin', 'phone' => '8888888888']);
        $user = User::factory()->create(['phone' => '9999999999']);

        $flag = FraudFlag::create([
            'flag_type' => 'suspicious_buyer_pattern',
            'flagged_user_id' => $user->id,
            'details' => ['reason' => 'Too many disputes'],
            'status' => 'pending'
        ]);

        Sanctum::actingAs($admin);

        // Get fraud flags
        $response = $this->getJson('/api/admin/fraud-flags');
        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $response->assertJsonCount(1, 'data.data');

        // Review flag
        $responseReview = $this->putJson("/api/admin/fraud-flags/{$flag->id}/review");
        $responseReview->assertStatus(200);
        $this->assertEquals('reviewed', $flag->fresh()->status);
        $this->assertEquals($admin->id, $flag->fresh()->reviewed_by);

        // Dismiss flag
        $responseDismiss = $this->putJson("/api/admin/fraud-flags/{$flag->id}/dismiss");
        $responseDismiss->assertStatus(200);
        $this->assertEquals('dismissed', $flag->fresh()->status);
        $this->assertEquals($admin->id, $flag->fresh()->reviewed_by);
    }
}
