<?php

namespace App\Actions\Escrow;

use App\Models\Escrow;
use App\Models\Notification;
use App\Services\RazorpayService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Atomically refunds an escrow to the buyer.
 *
 * Acquires a pessimistic row-level lock (lockForUpdate) inside the transaction
 * before reading the status, preventing a concurrent release+refund double-disbursement.
 *
 * Usage:
 *   app(RefundEscrowAction::class)->execute($escrowId, auth()->user()->name);
 */
class RefundEscrowAction
{
    public function __construct(
        protected RazorpayService $razorpayService
    ) {}

    /**
     * @throws ValidationException if escrow is not in a refundable state or gateway fails.
     */
    public function execute(int $escrowId, string $releasedBy): Escrow
    {
        return DB::transaction(function () use ($escrowId, $releasedBy) {
            $escrow = Escrow::where('id', $escrowId)->lockForUpdate()->firstOrFail();

            if (!in_array($escrow->status, ['held', 'disputed'])) {
                throw ValidationException::withMessages([
                    'escrow' => "Cannot refund escrow in status '{$escrow->status}'.",
                ]);
            }

            $amount    = $escrow->amount_held;
            $paymentId = $escrow->order->razorpay_payment_id;

            // Trigger Razorpay Refund
            if (!empty($paymentId)) {
                $response = $this->razorpayService::refundToBuyer(
                    $paymentId,
                    $amount,
                    "Admin Escrow Refund for Order #{$escrow->order->order_number}"
                );
                if (!$response['success']) {
                    throw ValidationException::withMessages([
                        'refund' => "Razorpay Refund failed: {$response['message']}",
                    ]);
                }
            }

            $escrow->update([
                'status'            => 'refunded',
                'payout_status'     => 'none',
                'released_at'       => now(),
                'released_by'       => $releasedBy,
                'seller_amount'     => 0,
                'commission_amount' => 0,
            ]);

            $escrow->order()->update([
                'order_status'        => 'refunded',
                'cancellation_reason' => 'Manually refunded by admin',
            ]);

            Notification::sendSystemMail($escrow->order->buyer, 'dispute_resolved', [
                'buyer_name'       => $escrow->order->buyer->name,
                'order_number'     => $escrow->order->order_number,
                'resolution_notes' => 'Full refund issued to buyer by administrator.',
                'buyer_payout'     => $amount,
                'seller_payout'    => 0,
            ]);

            Notification::sendSystemMail($escrow->order->seller->user, 'dispute_resolved', [
                'seller_name'      => $escrow->order->seller->user->name,
                'order_number'     => $escrow->order->order_number,
                'resolution_notes' => 'Full refund issued to buyer by administrator.',
                'buyer_payout'     => $amount,
                'seller_payout'    => 0,
            ]);

            return $escrow;
        });
    }
}
