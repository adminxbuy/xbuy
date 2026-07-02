<?php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class BuyerBadgeJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $buyerId;

    /**
     * Create a new job instance.
     */
    public function __construct(int $buyerId)
    {
        $this->buyerId = $buyerId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $buyer = User::find($this->buyerId);
        if (!$buyer) {
            return;
        }

        $completedOrders = $buyer->completed_orders_count;
        $fraudDisputes = $buyer->fraud_disputes_count;

        $badge = 'new_buyer';
        if ($completedOrders >= 10 && $fraudDisputes === 0) {
            $badge = 'trusted_buyer';
        } elseif ($completedOrders >= 5 && $fraudDisputes === 0) {
            $badge = 'verified_buyer';
        }

        if ($buyer->buyer_badge !== $badge) {
            $buyer->update(['buyer_badge' => $badge]);
            Log::info("Buyer #{$buyer->id} badge updated to {$badge}");
        }
    }
}
