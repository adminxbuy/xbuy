<?php

namespace App\Services;

use App\Models\SiteSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RazorpayService
{
    /**
     * Get Razorpay credentials.
     */
    protected static function getCredentials(): array
    {
        return [
            'key_id' => SiteSetting::getVal('razorpay_key_id'),
            'key_secret' => SiteSetting::getVal('razorpay_key_secret'),
            'enabled' => SiteSetting::getVal('razorpay_route_enabled', false),
        ];
    }

    /**
     * Transfer funds to a seller's linked account via Razorpay Route.
     */
    public static function transferToSeller(string $sellerAccountId, float $amount, string $orderNumber): array
    {
        $creds = self::getCredentials();
        
        // Log the action
        Log::info("RazorpayService: Initiating transfer of ₹{$amount} to seller account {$sellerAccountId} for Order #{$orderNumber}");

        // If credentials are empty or route is not enabled, run in mock simulation mode
        if (empty($creds['key_id']) || empty($creds['key_secret']) || !$creds['enabled']) {
            Log::warning("RazorpayService: Credentials missing or Route disabled. Running in MOCK SIMULATION mode.");
            return [
                'success' => true,
                'transfer_id' => 'fake_trsf_' . bin2hex(random_bytes(6)),
                'message' => 'Simulated transfer successful (Mock Mode)',
            ];
        }

        try {
            // Razorpay API expects amount in paise (multiply by 100)
            $amountInPaise = (int) round($amount * 100);

            $response = Http::withBasicAuth($creds['key_id'], $creds['key_secret'])
                ->post('https://api.razorpay.com/v1/transfers', [
                    'account' => $sellerAccountId,
                    'amount' => $amountInPaise,
                    'currency' => 'INR',
                    'notes' => [
                        'order_number' => $orderNumber,
                        'source' => 'X-Buy Escrow Payout'
                    ]
                ]);

            if ($response->successful()) {
                $data = $response->json();
                Log::info("RazorpayService: Transfer successful. Razorpay Transfer ID: " . ($data['id'] ?? 'N/A'));
                return [
                    'success' => true,
                    'transfer_id' => $data['id'],
                    'message' => 'Transfer processed successfully',
                ];
            }

            $errorMsg = $response->json()['error']['description'] ?? 'Razorpay API returned HTTP ' . $response->status();
            Log::error("RazorpayService: Transfer failed: " . $errorMsg);
            return [
                'success' => false,
                'message' => $errorMsg,
            ];

        } catch (\Exception $e) {
            Log::error("RazorpayService: Exception during transfer: " . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Refund funds back to the buyer via Razorpay Refund API.
     */
    public static function refundToBuyer(string $paymentId, float $amount, string $reason = 'Dispute resolution refund'): array
    {
        $creds = self::getCredentials();
        
        Log::info("RazorpayService: Initiating refund of ₹{$amount} for Payment ID {$paymentId}. Reason: {$reason}");

        if (empty($creds['key_id']) || empty($creds['key_secret'])) {
            Log::warning("RazorpayService: Credentials missing. Running in MOCK SIMULATION mode.");
            return [
                'success' => true,
                'refund_id' => 'fake_rfnd_' . bin2hex(random_bytes(6)),
                'message' => 'Simulated refund successful (Mock Mode)',
            ];
        }

        try {
            $amountInPaise = (int) round($amount * 100);

            $response = Http::withBasicAuth($creds['key_id'], $creds['key_secret'])
                ->post("https://api.razorpay.com/v1/payments/{$paymentId}/refund", [
                    'amount' => $amountInPaise,
                    'notes' => [
                        'reason' => $reason,
                        'source' => 'X-Buy Escrow Refund'
                    ]
                ]);

            if ($response->successful()) {
                $data = $response->json();
                Log::info("RazorpayService: Refund successful. Razorpay Refund ID: " . ($data['id'] ?? 'N/A'));
                return [
                    'success' => true,
                    'refund_id' => $data['id'],
                    'message' => 'Refund processed successfully',
                ];
            }

            $errorMsg = $response->json()['error']['description'] ?? 'Razorpay API returned HTTP ' . $response->status();
            Log::error("RazorpayService: Refund failed: " . $errorMsg);
            return [
                'success' => false,
                'message' => $errorMsg,
            ];

        } catch (\Exception $e) {
            Log::error("RazorpayService: Exception during refund: " . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }
}
