<?php

namespace App\Console\Commands;

use App\Models\Escrow;
use App\Models\Notification;
use App\Services\RazorpayService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AutoReleaseEscrow extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'escrow:auto-release';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scan and automatically release held escrows after the testing window ends';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("Scanning for overdue escrows...");

        // Raise AdminAlert for escrow overdue by > 1 day
        $overdueAlertEscrows = Escrow::where('status', 'held')
            ->whereNotNull('release_scheduled_at')
            ->where('release_scheduled_at', '<=', Carbon::now()->subDay())
            ->get();

        foreach ($overdueAlertEscrows as $escrow) {
            \App\Models\AdminAlert::firstOrCreate(
                [
                    'type' => 'overdue_escrow',
                    'reference_type' => 'Escrow',
                    'reference_id' => $escrow->id,
                    'is_read' => false,
                ],
                [
                    'title' => 'Overdue Escrow Alert',
                    'message' => "Escrow #{$escrow->id} has been overdue for auto-release by more than 1 day (scheduled release: " . $escrow->release_scheduled_at->toDateTimeString() . ").",
                    'severity' => 'high',
                ]
            );
        }
        
        $overdueEscrows = Escrow::where('status', 'held')
            ->whereNotNull('release_scheduled_at')
            ->where('release_scheduled_at', '<=', Carbon::now())
            ->with(['order.seller.user', 'order.buyer', 'order.listing'])
            ->get();

        $this->info("Found " . $overdueEscrows->count() . " overdue escrows.");

        foreach ($overdueEscrows as $escrow) {
            $this->info("Processing Escrow #{$escrow->id} (Order #{$escrow->order->order_number})");
            self::processRelease($escrow);
        }

        $this->info("Scan completed.");
    }

    /**
     * Process release logic for a single escrow record.
     */
    public static function processRelease(Escrow $escrow): bool
    {
        $order = $escrow->order;
        $seller = $order->seller;
        $sellerUser = $seller->user;

        // Check if seller has a Razorpay account ID
        $accountId = $seller->razorpay_account_id;

        if (empty($accountId)) {
            $errorMsg = "Seller has not configured a Razorpay Linked Account ID.";
            Log::error("Escrow Auto-Release Error (Order #{$order->order_number}): " . $errorMsg);
            
            DB::transaction(function () use ($escrow, $errorMsg) {
                $escrow->update([
                    'payout_status' => 'failed',
                    'payout_error_message' => $errorMsg,
                ]);
            });

            // Raise Admin Alert
            \App\Models\AdminAlert::firstOrCreate(
                [
                    'type' => 'failed_payout',
                    'reference_type' => 'Order',
                    'reference_id' => $order->id,
                    'is_read' => false,
                ],
                [
                    'title' => 'Escrow Payout Failed',
                    'message' => "Payout of ₹{$escrow->seller_amount} for Order #{$order->order_number} failed: {$errorMsg}",
                    'severity' => 'critical',
                ]
            );

            // Notify admin
            Notification::notifyAdmins(
                'Escrow Payout Failed',
                "Payout of ₹{$escrow->seller_amount} for Order #{$order->order_number} failed: {$errorMsg}",
                'payment',
                'order',
                $order->id
            );

            // Notify seller via mail
            Notification::sendSystemMail($sellerUser, 'payout_failed', [
                'seller_name' => $sellerUser->name,
                'order_number' => $order->order_number,
                'seller_payout_amount' => $escrow->seller_amount,
                'payout_error_message' => $errorMsg,
            ]);

            return false;
        }

        // Trigger Razorpay transfer
        $response = RazorpayService::transferToSeller($accountId, $escrow->seller_amount, $order->order_number);

        if ($response['success']) {
            DB::transaction(function () use ($escrow, $order, $response) {
                $escrow->update([
                    'status' => 'released',
                    'payout_status' => 'success',
                    'razorpay_payout_id' => $response['transfer_id'],
                    'payout_error_message' => null,
                    'released_at' => Carbon::now(),
                    'released_by' => 'System Auto-Release'
                ]);

                $order->update([
                    'order_status' => 'completed',
                    'completed_at' => Carbon::now()
                ]);

                // Increment counts for user profiles
                $order->buyer->increment('completed_orders_count');
                $order->seller->user->increment('completed_orders_count');
            });

            // Notify seller via mail
            Notification::sendSystemMail($sellerUser, 'escrow_released', [
                'seller_name' => $sellerUser->name,
                'order_number' => $order->order_number,
                'seller_payout_amount' => $escrow->seller_amount,
            ]);

            // Recalculate Badges
            \App\Jobs\BuyerBadgeJob::dispatch($order->buyer_id);
            \App\Jobs\BadgeCalculationJob::dispatch($order->seller_id);

            Log::info("Escrow #{$escrow->id} auto-released successfully.");
            return true;
        } else {
            $errorMsg = $response['message'] ?? 'Unknown Razorpay error';
            Log::error("Escrow Auto-Release failed on Razorpay Route (Order #{$order->order_number}): " . $errorMsg);

            DB::transaction(function () use ($escrow, $errorMsg) {
                $escrow->update([
                    'payout_status' => 'failed',
                    'payout_error_message' => $errorMsg,
                ]);
            });

            // Raise Admin Alert
            \App\Models\AdminAlert::firstOrCreate(
                [
                    'type' => 'failed_payout',
                    'reference_type' => 'Order',
                    'reference_id' => $order->id,
                    'is_read' => false,
                ],
                [
                    'title' => 'Escrow Payout Failed',
                    'message' => "Razorpay Transfer failed for Order #{$order->order_number}: {$errorMsg}",
                    'severity' => 'critical',
                ]
            );

            // Notify admin
            Notification::notifyAdmins(
                'Escrow Payout Failed',
                "Razorpay Transfer failed for Order #{$order->order_number}: {$errorMsg}",
                'payment',
                'order',
                $order->id
            );

            // Notify seller via mail
            Notification::sendSystemMail($sellerUser, 'payout_failed', [
                'seller_name' => $sellerUser->name,
                'order_number' => $order->order_number,
                'seller_payout_amount' => $escrow->seller_amount,
                'payout_error_message' => $errorMsg,
            ]);

            return false;
        }
    }
}
