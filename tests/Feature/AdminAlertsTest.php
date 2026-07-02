<?php

namespace Tests\Feature;

use App\Models\AdminAlert;
use App\Models\Escrow;
use App\Models\Listing;
use App\Models\Order;
use App\Models\SellerMetrics;
use App\Models\SellerProfile;
use App\Models\User;
use App\Jobs\SellerMetricsJob;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class AdminAlertsTest extends TestCase
{
    use RefreshDatabase;

    public function test_same_serial_number_listed_twice_triggers_alert()
    {
        $sellerUser = User::factory()->create(['phone' => '9999999999']);
        $seller = SellerProfile::create([
            'user_id' => $sellerUser->id,
            'shop_name' => 'Shop A',
            'shop_slug' => 'shop-a',
            'shop_city' => 'Delhi',
            'shop_state' => 'Delhi',
            'shop_pincode' => '110001',
        ]);

        // Listing 1
        $listing1 = Listing::create([
            'seller_id' => $seller->id,
            'title' => 'First GPU',
            'slug' => 'first-gpu',
            'category' => 'gpu',
            'grade' => 'A',
            'price' => 10000,
            'serial_number' => 'DUPLICATE123',
            'pickup_city' => 'Delhi',
            'pickup_state' => 'Delhi',
            'pickup_pincode' => '110001',
            'listing_status' => 'active',
        ]);

        // Listing 2 (Duplicate serial)
        $listing2 = Listing::create([
            'seller_id' => $seller->id,
            'title' => 'Second GPU',
            'slug' => 'second-gpu',
            'category' => 'gpu',
            'grade' => 'A',
            'price' => 10000,
            'serial_number' => 'DUPLICATE123',
            'pickup_city' => 'Delhi',
            'pickup_state' => 'Delhi',
            'pickup_pincode' => '110001',
            'listing_status' => 'active',
        ]);

        $this->assertDatabaseHas('admin_alerts', [
            'type' => 'suspicious_activity',
            'reference_type' => 'Listing',
            'reference_id' => $listing2->id,
        ]);
    }

    public function test_multiple_accounts_same_ip_triggers_alert()
    {
        // Setup mock verification to pass for user 1
        \App\Models\OtpVerification::create([
            'email' => 'user1@example.com',
            'type' => 'register',
            'otp' => '123456',
            'expires_at' => now()->addMinutes(10),
        ]);

        // First user verify
        $response1 = $this->postJson('/api/auth/otp/verify', [
            'email' => 'user1@example.com',
            'otp' => '123456',
            'type' => 'register',
            'name' => 'User One',
            'phone' => '1111111111',
        ], ['REMOTE_ADDR' => '192.168.1.50']); // mock IP

        $response1->assertStatus(200);

        // Setup mock verification to pass for user 2
        \App\Models\OtpVerification::create([
            'email' => 'user2@example.com',
            'type' => 'register',
            'otp' => '654321',
            'expires_at' => now()->addMinutes(10),
        ]);

        // Second user verify from same IP
        $response2 = $this->postJson('/api/auth/otp/verify', [
            'email' => 'user2@example.com',
            'otp' => '654321',
            'type' => 'register',
            'name' => 'User Two',
            'phone' => '2222222222',
        ], ['REMOTE_ADDR' => '192.168.1.50']); // same mock IP

        $response2->assertStatus(200);

        $this->assertDatabaseHas('admin_alerts', [
            'type' => 'suspicious_activity',
            'title' => 'Multiple Accounts Same IP Alert',
        ]);
    }

    public function test_seller_dispute_rate_above_threshold_triggers_alert()
    {
        $sellerUser = User::factory()->create(['phone' => '9999999999']);
        $seller = SellerProfile::create([
            'user_id' => $sellerUser->id,
            'shop_name' => 'Disputed Shop',
            'shop_slug' => 'disputed-shop',
            'shop_city' => 'Delhi',
            'shop_state' => 'Delhi',
            'shop_pincode' => '110001',
        ]);

        $buyer = User::factory()->create(['phone' => '8888888888']);
        $listing = Listing::create([
            'seller_id' => $seller->id,
            'title' => 'Product',
            'slug' => 'product',
            'category' => 'gpu',
            'grade' => 'A',
            'price' => 5000,
            'serial_number' => 'SN1',
            'pickup_city' => 'Delhi',
            'pickup_state' => 'Delhi',
            'pickup_pincode' => '110001',
            'listing_status' => 'active',
        ]);

        // Create disputed order
        Order::create([
            'order_number' => 'ORD1',
            'listing_id' => $listing->id,
            'buyer_id' => $buyer->id,
            'seller_id' => $seller->id,
            'product_amount' => 5000,
            'shipping_amount' => 100,
            'total_amount' => 5100,
            'commission_percent' => 5,
            'commission_amount' => 250,
            'seller_payout_amount' => 4750,
            'order_status' => 'disputed',
            'delivery_address' => ['city' => 'Delhi'],
        ]);

        // Run Metrics Job
        (new SellerMetricsJob())->handle();

        $this->assertDatabaseHas('admin_alerts', [
            'type' => 'high_dispute_rate',
            'reference_type' => 'SellerProfile',
            'reference_id' => $seller->id,
        ]);
    }

    public function test_overdue_escrow_and_failed_payout_trigger_alerts()
    {
        $sellerUser = User::factory()->create(['phone' => '9999999999']);
        $seller = SellerProfile::create([
            'user_id' => $sellerUser->id,
            'shop_name' => 'Seller Shop',
            'shop_slug' => 'seller-shop',
            'shop_city' => 'Delhi',
            'shop_state' => 'Delhi',
            'shop_pincode' => '110001',
            // no razorpay ID to force failed payout
        ]);

        $buyer = User::factory()->create(['phone' => '8888888888']);
        $listing = Listing::create([
            'seller_id' => $seller->id,
            'title' => 'Product 2',
            'slug' => 'product-2',
            'category' => 'gpu',
            'grade' => 'A',
            'price' => 5000,
            'serial_number' => 'SN2',
            'pickup_city' => 'Delhi',
            'pickup_state' => 'Delhi',
            'pickup_pincode' => '110001',
            'listing_status' => 'active',
        ]);

        $order = Order::create([
            'order_number' => 'ORD2',
            'listing_id' => $listing->id,
            'buyer_id' => $buyer->id,
            'seller_id' => $seller->id,
            'product_amount' => 5000,
            'shipping_amount' => 100,
            'total_amount' => 5100,
            'commission_percent' => 5,
            'commission_amount' => 250,
            'seller_payout_amount' => 4750,
            'order_status' => 'testing_period',
            'delivery_address' => ['city' => 'Delhi'],
        ]);

        $escrow = Escrow::create([
            'order_id' => $order->id,
            'amount_held' => 5000,
            'seller_amount' => 4750,
            'commission_amount' => 250,
            'status' => 'held',
            'warranty_days' => 2,
            'release_scheduled_at' => now()->subDays(2), // overdue by > 1 day
        ]);

        // Run auto-release command
        Artisan::call('escrow:auto-release');

        // Overdue Escrow Alert
        $this->assertDatabaseHas('admin_alerts', [
            'type' => 'overdue_escrow',
            'reference_type' => 'Escrow',
            'reference_id' => $escrow->id,
        ]);

        // Failed Payout Alert (due to missing account ID)
        $this->assertDatabaseHas('admin_alerts', [
            'type' => 'failed_payout',
            'reference_type' => 'Order',
            'reference_id' => $order->id,
        ]);
    }

    public function test_queue_job_failed_3_times_triggers_alert()
    {
        $job = \Mockery::mock(\Illuminate\Contracts\Queue\Job::class);
        $job->shouldReceive('attempts')->andReturn(1);
        $job->shouldReceive('resolveName')->andReturn('TestJob');
        
        $exception = new \Exception('Failed simulation');

        // Fire failing event directly
        event(new \Illuminate\Queue\Events\JobFailed('connection', $job, $exception));
        // Job attempts = 1, should not raise alert yet
        $this->assertDatabaseMissing('admin_alerts', ['type' => 'system_error']);

        // Mock job with 3 attempts
        $job3 = \Mockery::mock(\Illuminate\Contracts\Queue\Job::class);
        $job3->shouldReceive('attempts')->andReturn(3);
        $job3->shouldReceive('resolveName')->andReturn('TestJob');

        event(new \Illuminate\Queue\Events\JobFailed('connection', $job3, $exception));
        
        $this->assertDatabaseHas('admin_alerts', [
            'type' => 'system_error',
            'title' => 'Queue Job Failed Multiple Times',
        ]);
    }

    public function test_admin_can_manage_alerts_via_dashboard()
    {
        $admin = User::factory()->create(['role' => 'admin', 'phone' => '9999999999']);
        
        $alert = AdminAlert::create([
            'type' => 'system_error',
            'title' => 'Error Title',
            'message' => 'Error body description',
            'severity' => 'critical',
            'is_read' => false,
        ]);

        // List
        $response = $this->actingAs($admin)->get('/admin/alerts');
        $response->assertStatus(200);
        $response->assertSee('Error Title');

        // Mark read
        $readResponse = $this->actingAs($admin)->post("/admin/alerts/{$alert->id}/read");
        $readResponse->assertStatus(302);
        $this->assertTrue($alert->fresh()->is_read);

        // Clear all
        $clearResponse = $this->actingAs($admin)->post("/admin/alerts/clear-all");
        $clearResponse->assertStatus(302);
        $this->assertSoftDeleted($alert);
    }
}
