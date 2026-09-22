<?php

namespace App\Actions\Dispute;

use App\Models\Dispute;
use App\Models\DisputeResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Reopens a resolved dispute back to 'under_review'.
 *
 * Vulnerability 7 fix: guards against reopening after escrow funds have already
 * been released or refunded, which would create unresolvable accounting debt.
 *
 * Usage:
 *   app(ReopenDisputeAction::class)->execute($disputeId, auth()->id());
 */
class ReopenDisputeAction
{
    /**
     * @throws ValidationException if the dispute cannot be reopened.
     */
    public function execute(int $disputeId, int $adminId): Dispute
    {
        return DB::transaction(function () use ($disputeId, $adminId) {
            $dispute = Dispute::with(['order.escrow', 'buyer', 'seller.user'])->findOrFail($disputeId);

            if ($dispute->status !== 'resolved') {
                throw ValidationException::withMessages([
                    'dispute' => 'Only resolved disputes can be reopened.',
                ]);
            }

            // Vulnerability 7 fix: block reopening if escrow funds have already been finalized.
            if ($dispute->order && $dispute->order->escrow &&
                in_array($dispute->order->escrow->status, ['released', 'refunded'])) {
                throw ValidationException::withMessages([
                    'escrow' => 'Cannot reopen dispute: associated escrow funds have already been finalized.',
                ]);
            }

            $order = $dispute->order;

            // Decrement completed order counts if they were previously incremented
            if (in_array($dispute->admin_decision, ['seller_favor', 'partial'])) {
                $dispute->buyer->decrement('completed_orders_count');
                $dispute->seller->user->decrement('completed_orders_count');
            }

            // Decrement fraud disputes count if fault was seller/shipping
            if (in_array($dispute->fault_assigned_to, ['seller', 'shipping'])) {
                $dispute->buyer->decrement('fraud_disputes_count');
            }

            // Set order status back to disputed
            $order->update(['order_status' => 'disputed']);

            // Set escrow status back to disputed
            if ($order->escrow) {
                $order->escrow->update([
                    'status'      => 'disputed',
                    'released_at' => null,
                ]);
            }

            // Set dispute status back to under_review, preserving old resolution fields for history
            $dispute->update([
                'status'      => 'under_review',
                'resolved_at' => null,
            ]);

            // Log a system response for audit trail
            DisputeResponse::create([
                'dispute_id'   => $dispute->id,
                'responder_id' => $adminId,
                'responder_type' => 'admin',
                'message'      => 'Dispute reopened by admin for further review.',
            ]);

            return $dispute;
        });
    }
}
