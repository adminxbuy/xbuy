<?php

namespace App\Jobs;

use App\Models\SellerMetrics;
use App\Models\SellerProfile;
use App\Models\Order;
use App\Models\Rating;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SellerMetricsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('Running SellerMetricsJob...');

        $sellers = SellerProfile::all();

        foreach ($sellers as $seller) {
            try {
                $totalOrders = Order::where('seller_id', $seller->id)->count();
                $completedOrders = Order::where('seller_id', $seller->id)->where('order_status', 'completed')->count();
                $cancelledOrders = Order::where('seller_id', $seller->id)->where('order_status', 'cancelled')->count();
                $disputedOrders = Order::where('seller_id', $seller->id)->where('order_status', 'disputed')->count();

                $disputeRatePercent = $totalOrders > 0 ? round(($disputedOrders / $totalOrders) * 100, 2) : 0;

                // Calculate ratings stats
                $ratings = Rating::where('seller_id', $seller->id)->get();
                $totalRatings = $ratings->count();
                $avgStarRating = $totalRatings > 0 ? round($ratings->avg('weighted_total'), 2) : 0;

                // Cooperation score logic (starts at 10.00, drops on cancellations/disputes)
                $cooperationScore = 10.00;
                if ($totalOrders > 0) {
                    $penalty = ($cancelledOrders * 1.5) + ($disputedOrders * 2.0);
                    $cooperationScore = max(1.00, round(10.00 - $penalty, 2));
                }

                SellerMetrics::updateOrCreate(
                    ['seller_id' => $seller->id],
                    [
                        'total_orders' => $totalOrders,
                        'completed_orders' => $completedOrders,
                        'cancelled_orders' => $cancelledOrders,
                        'disputed_orders' => $disputedOrders,
                        'dispute_rate_percent' => $disputeRatePercent,
                        'star_rating' => $avgStarRating,
                        'total_ratings' => $totalRatings,
                        'cooperation_score' => $cooperationScore,
                        'last_calculated_at' => Carbon::now(),
                    ]
                );

                if ($disputeRatePercent > 20) {
                    \App\Models\AdminAlert::firstOrCreate(
                        [
                            'type' => 'high_dispute_rate',
                            'reference_type' => 'SellerProfile',
                            'reference_id' => $seller->id,
                            'is_read' => false,
                        ],
                        [
                            'title' => 'High Dispute Rate Alert',
                            'message' => "Seller {$seller->shop_name} has a dispute rate of {$disputeRatePercent}%, exceeding the 20% threshold.",
                            'severity' => 'high',
                        ]
                    );
                }

                // Update badge levels dynamically:
                // fulfilled: completed > 50, rating > 4.5, cooperation > 9.0
                // verified: completed > 10, rating > 4.0
                $newBadge = 'basic';
                if ($completedOrders >= 50 && $avgStarRating >= 4.5 && $cooperationScore >= 9.0) {
                    $newBadge = 'fulfilled';
                } elseif ($completedOrders >= 10 && $avgStarRating >= 4.0) {
                    $newBadge = 'verified';
                }

                if ($seller->badge_level !== $newBadge) {
                    $seller->update(['badge_level' => $newBadge]);
                    Log::info("Seller #{$seller->id} badge updated to {$newBadge}");
                }

            } catch (\Exception $e) {
                Log::error("Failed to calculate metrics for seller #{$seller->id}: " . $e->getMessage());
            }
        }

        // Global Amendment Trigger Check
        try {
            $month = now()->month;
            $year  = now()->year;
            $ordersThisMonth = Order::where('order_status', 'completed')
                ->whereMonth('completed_at', $month)
                ->whereYear('completed_at', $year)
                ->count();

            $amendmentTrigger = (int) \App\Models\SiteSetting::getVal('staff_amendment_trigger_orders', 100);

            if ($ordersThisMonth > $amendmentTrigger) {
                // 1. Create admin alert
                \App\Models\AdminAlert::firstOrCreate(
                    [
                        'type' => 'amendment_trigger_reached',
                        'is_read' => false,
                    ],
                    [
                        'title' => 'Amendment Trigger Reached',
                        'message' => "{$amendmentTrigger} orders completed this month. Staff compensation review required.",
                        'severity' => 'high',
                    ]
                );

                // 2. Email both superadmins
                $superadmins = \App\Models\User::where('is_admin', true)
                    ->where('admin_role', 'super_admin')
                    ->get();

                foreach ($superadmins as $admin) {
                    try {
                        \Illuminate\Support\Facades\Mail::raw(
                            "Amendment trigger reached — please review staff compensation. {$ordersThisMonth} orders completed this month.",
                            function ($message) use ($admin) {
                                $message->to($admin->email)
                                        ->subject('Amendment Trigger Reached — Please Review Staff Compensation');
                            }
                        );
                    } catch (\Exception $mailEx) {
                        Log::error("Mail failed for superadmin {$admin->email}: " . $mailEx->getMessage());
                    }
                }

                Log::info("Amendment trigger reached: {$ordersThisMonth} orders completed this month.");
            }
        } catch (\Exception $e) {
            Log::error("Failed to execute global amendment trigger check: " . $e->getMessage());
        }
    }
}
