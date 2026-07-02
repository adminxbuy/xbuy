<?php

namespace App\Jobs;

use App\Models\BadgeLog;
use App\Models\Order;
use App\Models\Rating;
use App\Models\SellerMetrics;
use App\Models\SellerProfile;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class BadgeCalculationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $sellerId;

    /**
     * Create a new job instance.
     */
    public function __construct(int $sellerId)
    {
        $this->sellerId = $sellerId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $seller = SellerProfile::with('user')->find($this->sellerId);
        if (!$seller) {
            return;
        }

        // Recalculate metrics first to ensure accuracy
        $totalOrders = Order::where('seller_id', $seller->id)->count();
        $completedOrders = Order::where('seller_id', $seller->id)->where('order_status', 'completed')->count();
        $cancelledOrders = Order::where('seller_id', $seller->id)->where('order_status', 'cancelled')->count();
        $disputedOrders = Order::where('seller_id', $seller->id)->where('order_status', 'disputed')->count();

        $disputeRatePercent = $totalOrders > 0 ? round(($disputedOrders / $totalOrders) * 100, 2) : 0;

        $ratings = Rating::where('seller_id', $seller->id)->get();
        $totalRatings = $ratings->count();
        $avgStarRating = $totalRatings > 0 ? round($ratings->avg('weighted_total'), 2) : 0;

        $cooperationScore = 10.00;
        if ($totalOrders > 0) {
            $penalty = ($cancelledOrders * 1.5) + ($disputedOrders * 2.0);
            $cooperationScore = max(1.00, round(10.00 - $penalty, 2));
        }

        // Fetch or create metrics record
        $metrics = SellerMetrics::firstOrCreate(['seller_id' => $seller->id]);
        
        $metrics->update([
            'total_orders' => $totalOrders,
            'completed_orders' => $completedOrders,
            'cancelled_orders' => $cancelledOrders,
            'disputed_orders' => $disputedOrders,
            'dispute_rate_percent' => $disputeRatePercent,
            'star_rating' => $avgStarRating,
            'total_ratings' => $totalRatings,
            'cooperation_score' => $cooperationScore,
            'last_calculated_at' => Carbon::now(),
        ]);

        $previousBadge = $seller->badge_level;
        $newBadge = 'basic';

        $avgResponseTime = $metrics->avg_response_time_hours ?? 0;

        // Check Seller badge criteria:
        // basic: always default
        // verified: kyc_status=approved AND completed_orders >= 1
        // fulfilled: completed_orders >= 10 AND avg_response_time < 3hrs AND cooperation_score >= 7 AND star_rating >= 4.0 AND shop_visit_verified = true
        if (
            $completedOrders >= 10 && 
            $avgResponseTime < 3 && 
            $cooperationScore >= 7 && 
            $avgStarRating >= 4.0 && 
            $seller->shop_visit_verified
        ) {
            $newBadge = 'fulfilled';
        } elseif ($seller->kyc_status === 'approved' && $completedOrders >= 1) {
            $newBadge = 'verified';
        }

        if ($previousBadge !== $newBadge) {
            $seller->update(['badge_level' => $newBadge]);

            // Log change to badge_logs
            $isUpgrade = $this->isUpgrade($previousBadge, $newBadge);
            BadgeLog::create([
                'seller_id' => $seller->id,
                'previous_badge' => $previousBadge,
                'new_badge' => $newBadge,
                'change_type' => $isUpgrade ? 'upgrade' : 'downgrade',
                'changed_by' => 'system',
                'reason' => "System auto-recalculated badge status based on performance metrics.",
            ]);

            Log::info("Seller #{$seller->id} badge level updated from {$previousBadge} to {$newBadge}");

            // Send email to seller on change
            try {
                if ($seller->user) {
                    \App\Models\Notification::sendSystemMail($seller->user, 'badge_updated', [
                        'previous_badge' => $previousBadge,
                        'new_badge' => $newBadge,
                    ]);
                } else {
                    Log::warning("Seller #{$seller->id} user has no email, skip sending badge email.");
                }
            } catch (\Exception $e) {
                Log::error("Failed to send badge update email to seller #{$seller->id}: " . $e->getMessage());
            }
        }
    }

    private function isUpgrade(string $old, string $new): bool
    {
        $weights = ['basic' => 1, 'verified' => 2, 'fulfilled' => 3];
        return ($weights[$new] ?? 0) > ($weights[$old] ?? 0);
    }
}
