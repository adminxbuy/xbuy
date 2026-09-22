<?php

namespace App\Actions\Escrow;

use App\Models\Escrow;
use App\Models\Notification;
use App\Services\RazorpayService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Atomically releases an escrow record to the seller.
 *
 * Acquires a pessimistic row-level lock (lockForUpdate) inside the transaction
 * so that concurrent release + refund admin actions cannot both succeed.
 *
 * Usage:
 *   app(ReleaseEscrowAction::class)->execute($escrowId, auth()->user()->name);
 */
class ReleaseEscrowAction
{
    public function __construct(
        protected RazorpayService $razorpayService
    ) {}

    /**
     * @throws ValidationException if escrow is not in a releasable state or gateway fails.
     */
    public function execute(int $escrowId, string $releasedBy): Escrow
    {
        return DB::transaction(function () use ($escrowId, $releasedBy) {
            $escrow = Escrow::where('id', $escrowId)->lockForUpdate()->firstOrFail();

            if (!in_array($escrow->status, ['held', 'disputed'])) {
                throw ValidationException::withMessages([
                    'escrow' => "Cannot release escrow in status '{$escrow->status}'.",
                ]);
            }

            // Execute Razorpay Payout transfer if route is enabled
            if (config('services.razorpay.route_enabled')) {
                $payout = $this->razorpayService->payoutToSeller(
                    $escrow->order->seller,
                    $escrow->seller_amount,
                    $escrow->order->order_number
                );
                if (!$payout['success']) {
                    $escrow->update([
                        'payout_status'        => 'failed',
                        'payout_error_message' => $payout['error'],
                    ]);
                    throw ValidationException::withMessages([
                        'payout' => "Gateway transfer failed: {$payout['error']}",
                    ]);
                }
            }

            $escrow->update([
                'status'        => 'released',
                'payout_status' => 'success',
                'released_at'   => now(),
                'released_by'   => $releasedBy,
            ]);

            $escrow->order()->update([
                'order_status' => 'completed',
                'completed_at' => now(),
            ]);

            // Dispatch seller notification
            Notification::sendSystemMail($escrow->order->seller->user, 'escrow_released', [
                'seller_name'          => $escrow->order->seller->user->name,
                'order_number'         => $escrow->order->order_number,
                'seller_payout_amount' => $escrow->seller_amount,
            ]);

            return $escrow;
        });
    }
}
