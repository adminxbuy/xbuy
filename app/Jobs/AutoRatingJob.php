<?php

namespace App\Jobs;

use App\Models\Order;
use App\Models\Rating;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class AutoRatingJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('Running AutoRatingJob...');

        // Find orders completed > 7 days ago that don't have ratings
        $sevenDaysAgo = Carbon::now()->subDays(7);
        $orders = Order::where('order_status', 'completed')
            ->where('completed_at', '<=', $sevenDaysAgo)
            ->whereDoesntHave('rating')
            ->get();

        foreach ($orders as $order) {
            try {
                Rating::create([
                    'order_id' => $order->id,
                    'seller_id' => $order->seller_id,
                    'buyer_id' => $order->buyer_id,
                    'communication_rating' => 5,
                    'shipping_rating' => 5,
                    'description_rating' => 5,
                    'weighted_total' => 5.00,
                    'comment' => 'System Auto-Generated Rating after 7 days.',
                ]);
                Log::info("Auto-rated order #{$order->order_number}");
            } catch (\Exception $e) {
                Log::error("Failed auto-rating order #{$order->id}: " . $e->getMessage());
            }
        }
    }
}
