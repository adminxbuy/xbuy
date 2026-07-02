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

        // Find orders in testing_period that have exceeded the testing window ends at
        $expiredOrders = Order::where('order_status', 'testing_period')
            ->where('testing_window_ends_at', '<=', Carbon::now())
            ->with(['escrow', 'buyer', 'seller.user'])
            ->get();

        foreach ($expiredOrders as $order) {
            try {
                DB::transaction(function () use ($order) {
                    $order->update([
                        'order_status' => 'completed',
                        'completed_at' => Carbon::now(),
                    ]);

                    if ($order->escrow) {
                        $order->escrow->update([
                            'status' => 'released',
                            'released_at' => Carbon::now(),
                        ]);
                    }

                    // Increment completed orders count for stats
                    $order->buyer->increment('completed_orders_count');
                    $order->seller->user->increment('completed_orders_count');

                    // Recalculate buyer badge
                    \App\Jobs\BuyerBadgeJob::dispatch($order->buyer_id);

                    // Recalculate seller badge
                    \App\Jobs\BadgeCalculationJob::dispatch($order->seller_id);
                });

                Log::info("Order #{$order->order_number} has been auto-completed and escrow released.");
            } catch (\Exception $e) {
                Log::error("Failed to auto-release escrow for order #{$order->id}: " . $e->getMessage());
            }
        }
    }
}
