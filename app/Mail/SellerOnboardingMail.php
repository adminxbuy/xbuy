<?php

namespace App\Mail;

use App\Models\SiteSetting;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SellerOnboardingMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $subjectLine;
    public string $htmlBody;

    public ?int $userId = null;

    /**
     * Create a new message instance.
     */
    public function __construct(protected int $day, protected string $shopName, ?int $userId = null)
    {
        $this->userId = $userId;
        $appUrl = rtrim(config('app.url'), '/');
        
        switch ($day) {
            case 0:
                $this->subjectLine = "Welcome to X-Buy! Start Selling PC Components";
                $this->htmlBody = "
                    <h2>Welcome to X-Buy, {$shopName}!</h2>
                    <p>Your seller profile has been approved and is now active. You are now part of India's safest PC parts marketplace with built-in Escrow Protection.</p>
                    <p>Get started by visiting your dashboard to track your orders, listings, and earnings:</p>
                    <p style='margin: 25px 0;'>
                        <a href='{$appUrl}/admin/login' style='background-color: #fdd835; color: #000; padding: 12px 24px; text-decoration: none; font-weight: bold; border-radius: 8px;'>Go to Dashboard</a>
                    </p>
                    <p>Happy selling!</p>
                ";
                break;
                
            case 1:
                $this->subjectLine = "How to Create Your First Listing on X-Buy";
                $this->htmlBody = "
                    <h2>Create Your First Listing</h2>
                    <p>Hello {$shopName},</p>
                    <p>Ready to turn your computer components into cash? Creating a listing on X-Buy is extremely simple:</p>
                    <ol>
                        <li>Log in to your Seller Dashboard.</li>
                        <li>Click on the <strong>Sell</strong> button or navigate to <strong>Listings &gt; Create</strong>.</li>
                        <li>Enter the title, description, category, and exact specifications of your hardware.</li>
                        <li>Specify the pricing, warranty details, and your pickup address.</li>
                    </ol>
                    <p>Once submitted, our team will review and approve your listing shortly.</p>
                    <p style='margin: 25px 0;'>
                        <a href='{$appUrl}/dashboard/listings/create' style='background-color: #000; color: #fff; padding: 12px 24px; text-decoration: none; font-weight: bold; border-radius: 8px;'>Create a Listing Now</a>
                    </p>
                ";
                break;
                
            case 3:
                $this->subjectLine = "Pro-Tips: Take Better Photos & Grade Components Correctly";
                $this->htmlBody = "
                    <h2>Boost Your Sales with Better Listings</h2>
                    <p>Hello {$shopName},</p>
                    <p>Here are a few quick tips to help sell your components up to 3x faster:</p>
                    <h3>1. Quality Photos Matter</h3>
                    <ul>
                        <li>Use bright, natural lighting.</li>
                        <li>Show close-ups of the serial number, ports, and any cosmetic flaws.</li>
                        <li>Clean dust off fans and heat sinks before taking pictures.</li>
                    </ul>
                    <h3>2. Select the Correct Grade</h3>
                    <ul>
                        <li><strong>Grade A:</strong> Mint condition, minimal signs of wear, fully functional.</li>
                        <li><strong>Grade B:</strong> Normal signs of wear (minor scratches), fully functional.</li>
                        <li><strong>Grade C:</strong> Heavy cosmetic wear, but still fully functional.</li>
                    </ul>
                    <p>Accurate grading and clear photos reduce disputes and build trust with buyers.</p>
                ";
                break;
                
            case 7:
                $this->subjectLine = "Need Help Listing Your PC Components on X-Buy?";
                $this->htmlBody = "
                    <h2>Need a hand getting started?</h2>
                    <p>Hello {$shopName},</p>
                    <p>We noticed you haven't created your first listing on X-Buy yet. Is there anything holding you back?</p>
                    <p>Whether you have graphics cards, CPUs, RAM, or motherboard packages, our escrow-backed platform is ready to help you sell them safely.</p>
                    <p>If you have any questions or need technical support, feel free to reply to this email or reach out to our team.</p>
                    <p style='margin: 25px 0;'>
                        <a href='{$appUrl}/dashboard/listings/create' style='background-color: #fdd835; color: #000; padding: 12px 24px; text-decoration: none; font-weight: bold; border-radius: 8px;'>Sell Your Components</a>
                    </p>
                ";
                break;
                
            default:
                $this->subjectLine = "Onboarding Update from X-Buy";
                $this->htmlBody = "<p>Update for {$shopName} from X-Buy.</p>";
                break;
        }
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address('no-reply@x-buy.in', 'X-Buy Seller Onboarding'),
            subject: $this->subjectLine,
            using: [
                function ($message) {
                    $message->getHeaders()->addTextHeader('X-Template-Name', 'seller_onboarding_day_' . $this->day);
                    if ($this->userId) {
                        $message->getHeaders()->addTextHeader('X-User-Id', $this->userId);
                    }
                }
            ]
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.notification_layout',
            with: [
                'body' => $this->htmlBody,
            ],
        );
    }
}
