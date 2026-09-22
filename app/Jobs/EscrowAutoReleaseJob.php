<?php

namespace App\Jobs;

use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EscrowAutoReleaseJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('Running EscrowAutoReleaseJob...');

        // Find orders in testing_period that have exceeded the testing window.
        // We only fetch IDs here; each order is re-fetched inside its own transaction
        // with a pessimistic lock to prevent race conditions with concurrent buyer disputes.
        $expiredOrderIds = Order::where('order_status', 'testing_period')
            ->where('testing_window_ends_at', '<=', Carbon::now())
            ->pluck('id');

        foreach ($expiredOrderIds as $orderId) {
            try {
                DB::transaction(function () use ($orderId) {
                    // Vulnerability 3 fix: lock the row before acting on it.
                    // If a buyer just raised a dispute and changed order_status to 'disputed',
                    // this lock acquisition will see the updated status and skip processing.
                    $lockedOrder = Order::where('id', $orderId)
                        ->lockForUpdate()
                        ->with(['escrow', 'buyer', 'seller.user'])
                        ->first();

                    if (!$lockedOrder) {
                        return; // Order deleted — skip silently
                    }

                    if ($lockedOrder->order_status !== 'testing_period') {
                        Log::info("EscrowAutoReleaseJob: Skipping order #{$lockedOrder->order_number} — status has changed to '{$lockedOrder->order_status}' (likely a concurrent dispute or cancellation).");
                        return;
                    }

                    $lockedOrder->update([
                        'order_status' => 'completed',
                        'completed_at' => Carbon::now(),
                    ]);

                    if ($lockedOrder->escrow) {
                        $lockedOrder->escrow->update([
                            'status'      => 'released',
                            'released_at' => Carbon::now(),
                        ]);
                    }

                    // Increment completed orders count for stats
                    $lockedOrder->buyer->increment('completed_orders_count');
                    $lockedOrder->seller->user->increment('completed_orders_count');

                    // Recalculate buyer badge
                    \App\Jobs\BuyerBadgeJob::dispatch($lockedOrder->buyer_id);

                    // Recalculate seller badge
                    \App\Jobs\BadgeCalculationJob::dispatch($lockedOrder->seller_id);

                    Log::info("Order #{$lockedOrder->order_number} has been auto-completed and escrow released.");
                });
            } catch (\Exception $e) {
                Log::error("Failed to auto-release escrow for order #{$orderId}: " . $e->getMessage());
            }
        }
    }
}
