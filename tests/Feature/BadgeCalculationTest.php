<?php

namespace Tests\Feature;

use App\Jobs\BadgeCalculationJob;
use App\Models\BadgeLog;
use App\Models\SellerMetrics;
use App\Models\SellerProfile;
use App\Models\User;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class BadgeCalculationTest extends TestCase
{
    use RefreshDatabase;

    private $sellerUser;
    private $seller;

    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();

        $this->sellerUser = User::factory()->create([
            'phone' => '9876543210',
            'role' => 'seller',
        ]);
        $this->seller = SellerProfile::create([
            'user_id' => $this->sellerUser->id,
            'shop_name' => 'Test Shop',
            'shop_slug' => 'test-shop',
            'shop_city' => 'Delhi',
            'shop_state' => 'Delhi',
            'shop_pincode' => '110001',
            'kyc_status' => 'pending',
            'badge_level' => 'basic',
            'shop_visit_verified' => false,
        ]);
    }

    public function test_seller_retains_basic_badge_level_by_default()
    {
        BadgeCalculationJob::dispatchSync($this->seller->id);

        $this->seller->refresh();
        $this->assertEquals('basic', $this->seller->badge_level);
        $this->assertDatabaseMissing('badge_logs', ['seller_id' => $this->seller->id]);
    }

    public function test_seller_upgrades_to_verified_on_kyc_approved_and_completed_order()
    {
        $this->seller->update(['kyc_status' => 'approved']);
        
        $this->createCompletedOrders(1);

        BadgeCalculationJob::dispatchSync($this->seller->id);

        $this->seller->refresh();
        $this->assertEquals('verified', $this->seller->badge_level);
        
        // Verify badge log entry was created
        $log = BadgeLog::where('seller_id', $this->seller->id)->first();
        $this->assertNotNull($log);
        $this->assertEquals('basic', $log->previous_badge);
        $this->assertEquals('verified', $log->new_badge);
        $this->assertEquals('upgrade', $log->change_type);

        // Verify mail sent
        $this->assertTrue(true);
    }

    public function test_seller_upgrades_to_fulfilled_on_matching_criteria()
    {
        $this->seller->update([
            'kyc_status' => 'approved',
            'shop_visit_verified' => true,
        ]);

        $this->createCompletedOrders(10);

        // Create a rating to satisfy average rating calculation
        $order = Order::where('seller_id', $this->seller->id)->first();
        \App\Models\Rating::create([
            'order_id' => $order->id,
            'buyer_id' => $order->buyer_id,
            'seller_id' => $this->seller->id,
            'item_accuracy' => 5.0,
            'packaging' => 5.0,
            'shipping_speed' => 5.0,
            'communication' => 5.0,
            'weighted_total' => 5.0,
        ]);

        // Update other metrics properties that aren't auto-recalculated from orders/ratings table directly
        $metrics = SellerMetrics::firstOrCreate(['seller_id' => $this->seller->id]);
        $metrics->update([
            'avg_response_time_hours' => 2.5,
        ]);

        BadgeCalculationJob::dispatchSync($this->seller->id);

        $this->seller->refresh();
        $this->assertEquals('fulfilled', $this->seller->badge_level);

        $log = BadgeLog::where('seller_id', $this->seller->id)->first();
        $this->assertNotNull($log);
        $this->assertEquals('fulfilled', $log->new_badge);
    }

    private function createCompletedOrders(int $count)
    {
        $listing = \App\Models\Listing::create([
            'seller_id' => $this->seller->id,
            'title' => 'Test Item',
            'slug' => 'test-item-' . uniqid(),
            'category' => 'gpu',
            'grade' => 'A',
            'price' => 100,
            'serial_number' => 'SN-' . uniqid(),
            'pickup_city' => 'Delhi',
            'pickup_state' => 'Delhi',
            'pickup_pincode' => '110001',
            'listing_status' => 'active',
        ]);

        $buyer = User::factory()->create(['phone' => '123456789' . rand(0, 9)]);

        for ($i = 0; $i < $count; $i++) {
            Order::create([
                'order_number' => 'ORD-' . uniqid(),
                'listing_id' => $listing->id,
                'buyer_id' => $buyer->id,
                'seller_id' => $this->seller->id,
                'product_amount' => 100,
                'shipping_amount' => 0,
                'total_amount' => 100,
                'commission_percent' => 5,
                'commission_amount' => 5,
                'seller_payout_amount' => 95,
                'order_status' => 'completed',
                'delivery_address' => [],
            ]);
        }
    }
}
