<?php

namespace Tests\Feature;

use App\Jobs\BuyerBadgeJob;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BuyerBadgeJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_buyer_badge_new_buyer_by_default()
    {
        $buyer = User::factory()->create([
            'phone' => '1234567890',
            'completed_orders_count' => 3,
            'fraud_disputes_count' => 0,
            'buyer_badge' => 'new_buyer',
        ]);

        BuyerBadgeJob::dispatchSync($buyer->id);

        $buyer->refresh();
        $this->assertEquals('new_buyer', $buyer->buyer_badge);
    }

    public function test_buyer_badge_verified_buyer_at_5_orders_with_zero_disputes()
    {
        $buyer = User::factory()->create([
            'phone' => '1234567891',
            'completed_orders_count' => 5,
            'fraud_disputes_count' => 0,
            'buyer_badge' => 'new_buyer',
        ]);

        BuyerBadgeJob::dispatchSync($buyer->id);

        $buyer->refresh();
        $this->assertEquals('verified_buyer', $buyer->buyer_badge);
    }

    public function test_buyer_badge_trusted_buyer_at_10_orders_with_zero_disputes()
    {
        $buyer = User::factory()->create([
            'phone' => '1234567892',
            'completed_orders_count' => 10,
            'fraud_disputes_count' => 0,
            'buyer_badge' => 'new_buyer',
        ]);

        BuyerBadgeJob::dispatchSync($buyer->id);

        $buyer->refresh();
        $this->assertEquals('trusted_buyer', $buyer->buyer_badge);
    }

    public function test_buyer_badge_demoted_to_new_buyer_on_dispute()
    {
        $buyer = User::factory()->create([
            'phone' => '1234567893',
            'completed_orders_count' => 10,
            'fraud_disputes_count' => 1,
            'buyer_badge' => 'trusted_buyer',
        ]);

        BuyerBadgeJob::dispatchSync($buyer->id);

        $buyer->refresh();
        $this->assertEquals('new_buyer', $buyer->buyer_badge);
    }
}
