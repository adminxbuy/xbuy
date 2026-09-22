<?php

namespace App\Actions\Escrow;

use App\Models\Escrow;
use App\Models\Notification;
use App\Services\RazorpayService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Atomically performs a partial (split) release of escrow.
 *
 * Vulnerability 2 fix: enforces that seller_amount + buyer_amount == amount_held
 * using integer-paisa arithmetic to avoid PHP floating-point precision errors
 * (e.g. 0.1 + 0.2 !== 0.3 in IEEE 754).
 *
 * Usage:
 *   app(PartialReleaseEscrowAction::class)->execute($escrowId, $sellerAmount, $buyerAmount, auth()->user()->name);
 */
class PartialReleaseEscrowAction
{
    public function __construct(
        protected RazorpayService $razorpayService
    ) {}

    /**
     * @param  float  $sellerAmount  Amount (in INR) to transfer to seller
     * @param  float  $buyerAmount   Amount (in INR) to refund to buyer
     * @throws ValidationException   if the split does not exactly equal held amount, or gateway fails.
     */
    public function execute(int $escrowId, float $sellerAmount, float $buyerAmount, string $releasedBy): Escrow
    {
        return DB::transaction(function () use ($escrowId, $sellerAmount, $buyerAmount, $releasedBy) {
            $escrow = Escrow::where('id', $escrowId)->lockForUpdate()->firstOrFail();

            if (!in_array($escrow->status, ['held', 'disputed'])) {
                throw ValidationException::withMessages([
                    'escrow' => "Cannot partially release escrow in status '{$escrow->status}'.",
                ]);
            }

            // Integer-paisa precision check (Vulnerability 2 fix)
            $totalSplitPaisa = (int) round(($sellerAmount + $buyerAmount) * 100);
            $totalHeldPaisa  = (int) round($escrow->amount_held * 100);
            if ($totalSplitPaisa !== $totalHeldPaisa) {
                throw ValidationException::withMessages([
                    'split' => 'Sum of buyer refund and seller payout must exactly equal the held escrow amount.',
                ]);
            }

            // Process refund to buyer
            if ($buyerAmount > 0) {
                $paymentId = $escrow->order->razorpay_payment_id;
                if (!empty($paymentId)) {
                    $refundRes = $this->razorpayService::refundToBuyer(
                        $paymentId,
                        $buyerAmount,
                        "Partial Refund for Order #{$escrow->order->order_number}"
                    );
                    if (!$refundRes['success']) {
                        throw ValidationException::withMessages([
                            'refund' => "Razorpay Refund failed: {$refundRes['message']}",
                        ]);
                    }
                }
            }

            // Process transfer to seller
            $transferId  = null;
            $payoutStatus = 'success';
            $payoutError  = null;

            if ($sellerAmount > 0) {
                $accountId = $escrow->order->seller->razorpay_account_id;
                if (!empty($accountId)) {
                    $transferRes = $this->razorpayService::transferToSeller(
                        $accountId,
                        $sellerAmount,
                        $escrow->order->order_number
                    );
                    if ($transferRes['success']) {
                        $transferId = $transferRes['transfer_id'];
                    } else {
                        $payoutStatus = 'failed';
                        $payoutError  = $transferRes['message'];
                    }
                } else {
                    $payoutStatus = 'failed';
                    $payoutError  = 'Seller Razorpay account ID is not configured.';
                }
            }

            $escrow->update([
                'status'               => $payoutStatus === 'failed' ? 'held' : 'partially_released',
                'payout_status'        => $payoutStatus,
                'payout_error_message' => $payoutError,
                'razorpay_payout_id'   => $transferId,
                'seller_amount'        => $sellerAmount,
                'released_at'          => $payoutStatus === 'failed' ? null : now(),
                'released_by'          => $releasedBy,
                'commission_amount'    => 0,
            ]);

            if ($payoutStatus !== 'failed' && $escrow->order) {
                $escrow->order()->update([
                    'order_status' => 'completed',
                    'completed_at' => now(),
                ]);
            }

            $notes = "Partial split resolution: Seller payout ₹{$sellerAmount}, Buyer refund ₹{$buyerAmount}.";

            if ($payoutStatus !== 'failed') {
                Notification::sendSystemMail($escrow->order->buyer, 'dispute_resolved', [
                    'buyer_name'       => $escrow->order->buyer->name,
                    'order_number'     => $escrow->order->order_number,
                    'resolution_notes' => $notes,
                    'buyer_payout'     => $buyerAmount,
                    'seller_payout'    => $sellerAmount,
                ]);

                Notification::sendSystemMail($escrow->order->seller->user, 'dispute_resolved', [
                    'seller_name'      => $escrow->order->seller->user->name,
                    'order_number'     => $escrow->order->order_number,
                    'resolution_notes' => $notes,
                    'buyer_payout'     => $buyerAmount,
                    'seller_payout'    => $sellerAmount,
                ]);
            } else {
                Notification::sendSystemMail($escrow->order->seller->user, 'payout_failed', [
                    'seller_name'          => $escrow->order->seller->user->name,
                    'order_number'         => $escrow->order->order_number,
                    'seller_payout_amount' => $sellerAmount,
                    'payout_error_message' => $payoutError,
                ]);

                throw ValidationException::withMessages([
                    'payout' => "Seller payout failed: {$payoutError} (Buyer portion refunded successfully)",
                ]);
            }

            return $escrow;
        });
    }
}
