<?php

namespace Database\Seeders;

use App\Models\SiteSetting;
use Illuminate\Database\Seeder;

class MailAndEscrowSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            // Razorpay Escrow Settings
            [
                'key' => 'razorpay_route_enabled',
                'value' => '0', // Disabled by default, user can turn it on
                'type' => 'boolean',
                'group' => 'razorpay',
                'description' => 'Enable automated Razorpay Route split payouts and refunds.'
            ],
            [
                'key' => 'razorpay_escrow_hold_days',
                'value' => '3',
                'type' => 'integer',
                'group' => 'razorpay',
                'description' => 'Default hold duration in days for the escrow before auto-release.'
            ],
            
            // Mail Layouts
            [
                'key' => 'mail_template_header',
                'value' => '<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: \'Helvetica Neue\', Helvetica, Arial, sans-serif; background-color: #f4f4f7; color: #51545e; margin: 0; padding: 0; width: 100% !important; }
        .email-wrapper { width: 100%; background-color: #f4f4f7; margin: 0; padding: 20px 0; }
        .email-content { max-width: 570px; margin: 0 auto; background-color: #ffffff; border: 1px solid #e8e8f1; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05); }
        .email-header { background-color: #fdd835; padding: 30px; text-align: center; }
        .email-logo { font-size: 24px; font-weight: bold; color: #000000; text-decoration: none; }
        .email-body { padding: 35px; line-height: 1.6; }
        .email-footer { text-align: center; padding: 30px; font-size: 12px; color: #a8a8b3; }
        .button { display: inline-block; background-color: #000000; color: #ffffff; padding: 12px 24px; border-radius: 8px; font-weight: bold; text-decoration: none; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <div class="email-content">
            <div class="email-header">
                <a href="http://127.0.0.1:8000" class="email-logo">X-BUY ESCROW</a>
            </div>
            <div class="email-body">',
                'type' => 'string',
                'group' => 'mail_templates',
                'description' => 'Shared HTML header template for all system emails.'
            ],
            [
                'key' => 'mail_template_footer',
                'value' => '</div>
            <div class="email-footer">
                <p>&copy; 2026 X-Buy Escrow Marketplace. All rights reserved.</p>
                <p>You received this email because of activity on your account. If this was not you, please contact support.</p>
            </div>
        </div>
    </div>
</body>
</html>',
                'type' => 'string',
                'group' => 'mail_templates',
                'description' => 'Shared HTML footer template for all system emails.'
            ],

            // 1. Payment Success
            [
                'key' => 'mail_template_payment_success_subject',
                'value' => 'Payment Confirmed: Order #{order_number} is locked in Escrow!',
                'type' => 'string',
                'group' => 'mail_templates',
                'description' => 'Subject for the Payment Success email sent to buyers.'
            ],
            [
                'key' => 'mail_template_payment_success_body',
                'value' => '<h3>Hi {buyer_name},</h3>
<p>Your payment of <strong>₹{order_amount}</strong> for <strong>{product_title}</strong> has been successfully received and is safely locked in our Escrow wallet.</p>
<p>The seller (<strong>{seller_shop_name}</strong>) is preparing your shipment. Once shipped, you will receive tracking updates.</p>
<p>Thank you for shopping with X-Buy!</p>',
                'type' => 'string',
                'group' => 'mail_templates',
                'description' => 'Body content for the Payment Success email.'
            ],

            // 2. Escrow Hold (Seller)
            [
                'key' => 'mail_template_escrow_hold_subject',
                'value' => 'New Sale: Order #{order_number} is locked in Escrow!',
                'type' => 'string',
                'group' => 'mail_templates',
                'description' => 'Subject for the Escrow Hold email sent to sellers.'
            ],
            [
                'key' => 'mail_template_escrow_hold_body',
                'value' => '<h3>Hi {seller_name},</h3>
<p>Congratulations on your sale! Buyer <strong>{buyer_name}</strong> has paid for <strong>{product_title}</strong>.</p>
<p>The amount of <strong>₹{seller_payout_amount}</strong> (after commission) is held in Escrow and will be released to your account after the delivery and testing period (<strong>{testing_days} days</strong>).</p>
<p>Please confirm and ship the order via your dashboard to proceed.</p>',
                'type' => 'string',
                'group' => 'mail_templates',
                'description' => 'Body content for the Escrow Hold email.'
            ],

            // 3. Escrow Released (Seller)
            [
                'key' => 'mail_template_escrow_released_subject',
                'value' => 'Payout Released: ₹{seller_payout_amount} transferred for Order #{order_number}',
                'type' => 'string',
                'group' => 'mail_templates',
                'description' => 'Subject for the Escrow Payout Released email.'
            ],
            [
                'key' => 'mail_template_escrow_released_body',
                'value' => '<h3>Hi {seller_name},</h3>
<p>Great news! The testing window for Order <strong>#{order_number}</strong> has completed successfully with no disputes.</p>
<p>We have released the funds. An amount of <strong>₹{seller_payout_amount}</strong> has been transferred to your Razorpay Linked Account / Bank details.</p>
<p>Keep selling on X-Buy!</p>',
                'type' => 'string',
                'group' => 'mail_templates',
                'description' => 'Body content for the Escrow Payout Released email.'
            ],

            // 4. Payout Failed
            [
                'key' => 'mail_template_payout_failed_subject',
                'value' => 'Payout Failed: Action Required for Order #{order_number}',
                'type' => 'string',
                'group' => 'mail_templates',
                'description' => 'Subject for the Payout Failed email.'
            ],
            [
                'key' => 'mail_template_payout_failed_body',
                'value' => '<h3>Hi {seller_name},</h3>
<p>We attempted to release your payout of <strong>₹{seller_payout_amount}</strong> for Order <strong>#{order_number}</strong>, but the transaction failed.</p>
<p><strong>Reason:</strong> {payout_error_message}</p>
<p>Please log in to your dashboard and verify/update your Razorpay Linked Account or Bank details. Once updated, admin will re-trigger the payout.</p>',
                'type' => 'string',
                'group' => 'mail_templates',
                'description' => 'Body content for the Payout Failed email.'
            ],

            // 5. Dispute Raised
            [
                'key' => 'mail_template_dispute_raised_subject',
                'value' => 'Dispute Raised: Order #{order_number} has been put on Hold',
                'type' => 'string',
                'group' => 'mail_templates',
                'description' => 'Subject for the Dispute Raised email.'
            ],
            [
                'key' => 'mail_template_dispute_raised_body',
                'value' => '<h3>Attention,</h3>
<p>A dispute has been raised by the buyer (<strong>{buyer_name}</strong>) for Order <strong>#{order_number}</strong> ({product_title}).</p>
<p><strong>Reason:</strong> {dispute_reason}</p>
<p><strong>Description:</strong> {dispute_description}</p>
<p>The escrow funds will remain locked until the dispute is resolved by the administrator.</p>',
                'type' => 'string',
                'group' => 'mail_templates',
                'description' => 'Body content for the Dispute Raised email.'
            ],

            // 6. Dispute Resolved
            [
                'key' => 'mail_template_dispute_resolved_subject',
                'value' => 'Dispute Resolved: Order #{order_number} Resolution Payout',
                'type' => 'string',
                'group' => 'mail_templates',
                'description' => 'Subject for the Dispute Resolved email.'
            ],
            [
                'key' => 'mail_template_dispute_resolved_body',
                'value' => '<h3>Hello,</h3>
<p>The dispute for Order <strong>#{order_number}</strong> has been resolved by our administrator.</p>
<p><strong>Resolution Details:</strong> {resolution_notes}</p>
<p><strong>Payout Splits:</strong></p>
<ul>
    <li>Buyer Refund: ₹{buyer_payout}</li>
    <li>Seller Payout: ₹{seller_payout}</li>
</ul>
<p>Refunds / Transfers are being processed automatically via Razorpay.</p>',
                'type' => 'string',
                'group' => 'mail_templates',
                'description' => 'Body content for the Dispute Resolved email.'
            ],
            // 7. Auth OTP
            [
                'key' => 'mail_template_auth_otp_subject',
                'value' => 'Your X-Buy verification code',
                'type' => 'string',
                'group' => 'mail_templates',
                'description' => 'Subject for the OTP login verification email.'
            ],
            [
                'key' => 'mail_template_auth_otp_body',
                'value' => '<h3>Verification Code</h3>
<p>Your X-Buy verification code is: <strong>{otp}</strong></p>
<p>This code is valid for 10 minutes. If you did not request this, please ignore this email.</p>',
                'type' => 'string',
                'group' => 'mail_templates',
                'description' => 'Body content for the OTP login verification email.'
            ],
            // 8. Badge Updated
            [
                'key' => 'mail_template_badge_updated_subject',
                'value' => 'Seller Badge Status Updated!',
                'type' => 'string',
                'group' => 'mail_templates',
                'description' => 'Subject for the seller badge update email.'
            ],
            [
                'key' => 'mail_template_badge_updated_body',
                'value' => '<h3>Badge Recalculated</h3>
<p>Hello,</p>
<p>Your seller badge on X-Buy has been updated from <strong>{previous_badge}</strong> to <strong>{new_badge}</strong> due to performance metrics recalculation.</p>
<p>Check your dashboard for details.</p>',
                'type' => 'string',
                'group' => 'mail_templates',
                'description' => 'Body content for the seller badge update email.'
            ],
            // 9. Dispute Reopened
            [
                'key' => 'mail_template_dispute_reopened_subject',
                'value' => 'Dispute Reopened: Order #{order_number}',
                'type' => 'string',
                'group' => 'mail_templates',
                'description' => 'Subject for the dispute reopening email.'
            ],
            [
                'key' => 'mail_template_dispute_reopened_body',
                'value' => '<h3>Dispute Under Review</h3>
<p>Hello,</p>
<p>The dispute raised against Order <strong>#{order_number}</strong> has been reopened by the administrator for further review.</p>
<p>Our team will investigate the details again. We will contact you if any further evidence is required.</p>',
                'type' => 'string',
                'group' => 'mail_templates',
                'description' => 'Body content for the dispute reopening email.'
            ],
            // 10. Support Ticket Raised
            [
                'key' => 'mail_template_support_ticket_subject',
                'value' => 'New Support Ticket Raised',
                'type' => 'string',
                'group' => 'mail_templates',
                'description' => 'Subject for support ticket email notification.'
            ],
            [
                'key' => 'mail_template_support_ticket_body',
                'value' => '<h3>Support Ticket</h3>
<p>A new support ticket has been raised by <strong>{user_name}</strong> ({user_email}).</p>
<p><strong>Subject:</strong> {ticket_subject}</p>
<p><strong>Message:</strong></p>
<p>{ticket_message}</p>',
                'type' => 'string',
                'group' => 'mail_templates',
                'description' => 'Body content for support ticket email notification.'
            ]
        ];

        foreach ($settings as $setting) {
            SiteSetting::updateOrCreate(['key' => $setting['key']], $setting);
        }
    }
}
