<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmailLog;
use App\Models\User;
use App\Models\Order;
use App\Models\SupportTicket;
use App\Mail\SystemNotificationMail;
use App\Mail\SellerOnboardingMail;
use App\Mail\SupportTicketRaised;
use App\Mail\DynamicCampaignMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class MailLogController extends Controller
{
    /**
     * List all sent/failed/pending emails.
     */
    public function index(Request $request)
    {
        $query = EmailLog::query();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('to_email', 'like', "%{$search}%")
                  ->orWhere('subject', 'like', "%{$search}%")
                  ->orWhere('template_name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $logs = $query->orderBy('created_at', 'desc')->paginate($request->input('per_page', 20));

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'data' => $logs
            ]);
        }

        return view('admin.mails.logs', compact('logs'));
    }

    /**
     * Show details of a specific log.
     */
    public function show(int $id, Request $request)
    {
        $log = EmailLog::with(['user', 'order'])->findOrFail($id);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'data' => $log
            ]);
        }

        return view('admin.mails.log_show', compact('log'));
    }

    /**
     * Resend a failed or existing email.
     */
    public function resend(int $id)
    {
        $log = EmailLog::findOrFail($id);

        try {
            $mailable = null;
            $toEmail = $log->to_email;

            // Reconstruct the mailable based on the template name and log details
            if (str_starts_with($log->template_name, 'system_notification_')) {
                $templateKey = str_replace('system_notification_', '', $log->template_name);
                
                $tokens = [];
                if ($log->user_id) {
                    $user = User::find($log->user_id);
                    if ($user) {
                        $tokens['name'] = $user->name;
                        $tokens['shop_name'] = $user->sellerProfile?->shop_name ?? $user->name;
                    }
                }
                if ($log->order_id) {
                    $order = Order::find($log->order_id);
                    if ($order) {
                        $tokens['order_number'] = $order->order_number;
                        $tokens['amount'] = $order->total_amount;
                        $tokens['resolution'] = 'Escrow release/payout processed';
                        $tokens['error_message'] = 'Bank transfer issue';
                    }
                }
                
                // Add a dummy OTP for auth OTP template if it's resending auth_otp
                if ($templateKey === 'auth_otp') {
                    $tokens['otp'] = rand(100000, 999999);
                }

                $mailable = new SystemNotificationMail($templateKey, $tokens);

            } elseif (str_starts_with($log->template_name, 'seller_onboarding_day_')) {
                $day = (int) str_replace('seller_onboarding_day_', '', $log->template_name);
                $shopName = 'Seller';
                
                if ($log->user_id) {
                    $user = User::find($log->user_id);
                    if ($user && $user->sellerProfile) {
                        $shopName = $user->sellerProfile->shop_name;
                    }
                }
                $mailable = new SellerOnboardingMail($day, $shopName, $log->user_id);

            } elseif ($log->template_name === 'support_ticket_raised') {
                // Find a ticket for this user or order
                $ticket = SupportTicket::where('user_id', $log->user_id)->orderBy('created_at', 'desc')->first();
                if ($ticket) {
                    $mailable = new SupportTicketRaised($ticket, $ticket->user?->name ?? 'User', $ticket->user?->email ?? $toEmail);
                }

            } elseif ($log->template_name === 'dynamic_campaign') {
                $unsubscribeUrl = route('subscribers.unsubscribe', ['token' => 'user']);
                $mailable = new DynamicCampaignMail($log->subject, '<p>Resent campaign message. Full body history is saved in logs.</p>', $unsubscribeUrl);
            }

            if ($mailable) {
                Mail::to($toEmail)->send($mailable);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Email resent successfully'
                ]);
            }

            // Fallback: send as raw HTML if mailable cannot be reconstructed
            Mail::html('<p>This is a resent message from X-Buy Admin. Original subject: ' . e($log->subject) . '</p>', function ($message) use ($log, $toEmail) {
                $message->to($toEmail)->subject('Resend: ' . $log->subject);
            });

            return response()->json([
                'success' => true,
                'message' => 'Email resent via fallback mailer'
            ]);

        } catch (\Exception $e) {
            Log::error("Failed resending mail log #{$id}: " . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to resend email: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get email templates and preview layouts.
     */
    public function templates(Request $request)
    {
        $templates = [
            [
                'id' => 'auth_otp',
                'name' => 'OTP Verification Email',
                'subject' => \App\Models\SiteSetting::getVal('mail_template_auth_otp_subject', 'Your OTP for X-Buy is {otp}'),
                'body' => \App\Models\SiteSetting::getVal('mail_template_auth_otp_body', '<p>Your OTP code is <strong>{otp}</strong>. It is valid for 5 minutes.</p>'),
            ],
            [
                'id' => 'seller_kyc_approved',
                'name' => 'Seller KYC Approved',
                'subject' => \App\Models\SiteSetting::getVal('mail_template_seller_kyc_approved_subject', 'Your seller KYC has been approved!'),
                'body' => \App\Models\SiteSetting::getVal('mail_template_seller_kyc_approved_body', '<p>Welcome {shop_name}, your KYC has been approved.</p>'),
            ],
            [
                'id' => 'payment_success',
                'name' => 'Order Payment Successful',
                'subject' => \App\Models\SiteSetting::getVal('mail_template_payment_success_subject', 'Payment Received for Order #{order_number}'),
                'body' => \App\Models\SiteSetting::getVal('mail_template_payment_success_body', '<p>Payment received for order #{order_number}.</p>'),
            ],
            [
                'id' => 'escrow_hold',
                'name' => 'Escrow Hold Notification',
                'subject' => \App\Models\SiteSetting::getVal('mail_template_escrow_hold_subject', 'Funds held in Escrow for Order #{order_number}'),
                'body' => \App\Models\SiteSetting::getVal('mail_template_escrow_hold_body', '<p>Funds for order #{order_number} are now in escrow.</p>'),
            ],
            [
                'id' => 'escrow_released',
                'name' => 'Escrow Funds Released',
                'subject' => \App\Models\SiteSetting::getVal('mail_template_escrow_released_subject', 'Payout Released for Order #{order_number}'),
                'body' => \App\Models\SiteSetting::getVal('mail_template_escrow_released_body', '<p>Payout released for order #{order_number}.</p>'),
            ],
            [
                'id' => 'dispute_resolved',
                'name' => 'Order Dispute Resolved',
                'subject' => \App\Models\SiteSetting::getVal('mail_template_dispute_resolved_subject', 'Dispute Resolved for Order #{order_number}'),
                'body' => \App\Models\SiteSetting::getVal('mail_template_dispute_resolved_body', '<p>Dispute has been resolved for order #{order_number}. Decision: {resolution}.</p>'),
            ],
            [
                'id' => 'payout_failed',
                'name' => 'Seller Payout Failed',
                'subject' => \App\Models\SiteSetting::getVal('mail_template_payout_failed_subject', 'Payout Failed for Order #{order_number}'),
                'body' => \App\Models\SiteSetting::getVal('mail_template_payout_failed_body', '<p>Payout failed for order #{order_number}. Error: {error_message}</p>'),
            ],
            [
                'id' => 'badge_updated',
                'name' => 'Seller Badge Level Updated',
                'subject' => \App\Models\SiteSetting::getVal('mail_template_badge_updated_subject', 'Your Seller Badge Level Updated to {badge_level}'),
                'body' => \App\Models\SiteSetting::getVal('mail_template_badge_updated_body', '<p>Congratulations! Your seller badge has been updated to {badge_level}.</p>'),
            ],
            [
                'id' => 'support_ticket',
                'name' => 'Support Ticket Update',
                'subject' => \App\Models\SiteSetting::getVal('mail_template_support_ticket_subject', 'Support Ticket #{ticket_id} Update: {subject}'),
                'body' => \App\Models\SiteSetting::getVal('mail_template_support_ticket_body', '<p>Ticket #{ticket_id} updated. Message: {message}</p>'),
            ],
            [
                'id' => 'seller_onboarding_day_0',
                'name' => 'Seller Onboarding - Day 0 (Welcome)',
                'subject' => 'Welcome to X-Buy! Start Selling PC Components',
                'body' => "<h2>Welcome to X-Buy!</h2><p>Your seller profile has been approved and is now active. You are now part of India's safest PC parts marketplace with built-in Escrow Protection.</p>",
            ],
            [
                'id' => 'seller_onboarding_day_1',
                'name' => 'Seller Onboarding - Day 1 (How to Create Listing)',
                'subject' => 'How to Create Your First Listing on X-Buy',
                'body' => "<h2>Create Your First Listing</h2><p>Ready to turn your computer components into cash? Creating a listing on X-Buy is extremely simple...</p>",
            ],
            [
                'id' => 'seller_onboarding_day_3',
                'name' => 'Seller Onboarding - Day 3 (Tips for Better Photos)',
                'subject' => 'Pro-Tips: Take Better Photos & Grade Components Correctly',
                'body' => "<h2>Boost Your Sales with Better Listings</h2><p>Here are a few quick tips to help sell your components up to 3x faster...</p>",
            ],
            [
                'id' => 'seller_onboarding_day_7',
                'name' => 'Seller Onboarding - Day 7 (No Listing Reminder)',
                'subject' => 'Need Help Listing Your PC Components on X-Buy?',
                'body' => "<h2>Need a hand getting started?</h2><p>We noticed you haven't created your first listing on X-Buy yet. Is there anything holding you back?</p>",
            ]
        ];

        // If template preview is requested
        if ($request->filled('preview_id')) {
            $previewId = $request->input('preview_id');
            $found = collect($templates)->firstWhere('id', $previewId);
            
            if ($found) {
                $header = \App\Models\SiteSetting::getVal('mail_template_header', '<html><body style="font-family: sans-serif; padding: 20px; background: #f9f9f9;"><div style="max-width: 600px; margin: 0 auto; background: #fff; border: 1px solid #ddd; padding: 20px; border-radius: 8px;">');
                $footer = \App\Models\SiteSetting::getVal('mail_template_footer', '</div></body></html>');
                
                return response($header . $found['body'] . $footer)
                    ->header('Content-Type', 'text/html');
            }
        }

        return response()->json([
            'success' => true,
            'data' => $templates
        ]);
    }
}
