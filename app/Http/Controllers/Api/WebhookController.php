<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Escrow;
use App\Models\Notification;
use App\Models\Order;
use App\Models\Shipment;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    /**
     * Handle Razorpay Webhook.
     */
    public function razorpay(Request $request): JsonResponse
    {
        Log::info('Razorpay Webhook received', $request->all());

        // In production: Verify signature using $request->header('X-Razorpay-Signature')
        // For development/mocking, we read the event payload:
        $event = $request->input('event');
        $payload = $request->input('payload');

        if ($event === 'order.paid' || $event === 'payment.captured') {
            $orderPayload = $payload['order']['entity'] ?? $payload['payment']['entity'] ?? null;
            if ($orderPayload) {
                $razorpayOrderId = $orderPayload['order_id'] ?? $orderPayload['id'] ?? null;
                $razorpayPaymentId = $orderPayload['payment_id'] ?? $orderPayload['id'] ?? null;

                $order = Order::where('razorpay_order_id', $razorpayOrderId)
                    ->where('order_status', 'pending_payment')
                    ->first();

                if ($order) {
                    DB::transaction(function () use ($order, $razorpayPaymentId) {
                        // Update listing status
                        $order->listing->update([
                            'listing_status' => 'sold',
                            'sold_at' => Carbon::now(),
                        ]);

                        // Update order status
                        $order->update([
                            'order_status' => 'payment_received',
                            'razorpay_payment_id' => $razorpayPaymentId,
                        ]);

                        // Create Escrow
                        Escrow::updateOrCreate(
                            ['order_id' => $order->id],
                            [
                                'amount_held' => $order->product_amount,
                                'seller_amount' => $order->seller_payout_amount,
                                'commission_amount' => $order->commission_amount,
                                'status' => 'held',
                                'payout_status' => 'pending',
                                'warranty_days' => $order->testing_window_days,
                            ]
                        );

                        // Create Shipment
                        Shipment::updateOrCreate(
                            ['order_id' => $order->id],
                            [
                                'status' => 'pending',
                            ]
                        );

                        // Send dynamic emails
                        Notification::sendSystemMail($order->buyer, 'payment_success', [
                            'buyer_name' => $order->buyer->name,
                            'order_number' => $order->order_number,
                            'order_amount' => $order->total_amount,
                            'product_title' => $order->listing->title,
                            'seller_shop_name' => $order->seller->shop_name,
                        ]);

                        Notification::sendSystemMail($order->seller->user, 'escrow_hold', [
                            'seller_name' => $order->seller->user->name,
                            'buyer_name' => $order->buyer->name,
                            'order_number' => $order->order_number,
                            'product_title' => $order->listing->title,
                            'seller_payout_amount' => $order->seller_payout_amount,
                            'testing_days' => $order->testing_window_days,
                        ]);
                    });

                    Log::info("Order #{$order->order_number} successfully paid via Razorpay webhook");
                }
            }
        }

        return response()->json(['success' => true, 'message' => 'Webhook handled']);
    }

    /**
     * Handle Shiprocket Webhook.
     */
    public function shiprocket(Request $request): JsonResponse
    {
        Log::info('Shiprocket Webhook received', $request->all());

        // In production: Validate auth token or IP whitelist
        $awbNumber = $request->input('awb');
        $shiprocketStatus = strtolower($request->input('current_status'));
        $shiprocketOrderId = $request->input('order_id');

        $shipment = Shipment::where('awb_number', $awbNumber)
            ->orWhere('shiprocket_order_id', $shiprocketOrderId)
            ->first();

        if (!$shipment) {
            return response()->json(['success' => false, 'message' => 'Shipment not found'], 404);
        }

        $order = $shipment->order;

        DB::transaction(function () use ($shipment, $order, $shiprocketStatus) {
            // Map Shiprocket status to local shipment status
            $localStatus = 'pending';
            $orderStatus = $order->order_status;

            switch ($shiprocketStatus) {
                case 'pickup scheduled':
                case 'ready to ship':
                    $localStatus = 'label_generated';
                    $orderStatus = 'label_generated';
                    break;

                case 'picked up':
                case 'in transit':
                case 'reached at destination':
                    $localStatus = 'in_transit';
                    $orderStatus = 'in_transit';
                    break;

                case 'out for delivery':
                    $localStatus = 'out_for_delivery';
                    $orderStatus = 'out_for_delivery';
                    break;

                case 'delivered':
                    $localStatus = 'delivered';
                    $orderStatus = 'testing_period';
                    $shipment->delivered_at = Carbon::now();
                    
                    // Set delivered details on order
                    $order->delivered_at = Carbon::now();
                    $order->testing_window_ends_at = Carbon::now()->addDays($order->testing_window_days);
                    break;

                case 'cancelled':
                    $localStatus = 'cancelled';
                    $orderStatus = 'cancelled';
                    break;
            }

            // Update shipment
            $shipment->update([
                'status' => $localStatus,
            ]);

            // Update order status if not already completed/disputed/cancelled
            if (!in_array($order->order_status, ['completed', 'disputed', 'cancelled', 'refunded'])) {
                $order->update([
                    'order_status' => $orderStatus,
                ]);
            }
        });

        return response()->json(['success' => true, 'message' => 'Webhook tracking updated']);
    }
}
