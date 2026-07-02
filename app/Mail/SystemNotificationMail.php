<?php

namespace App\Mail;

use App\Models\SiteSetting;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SystemNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $templateKey;
    public $userId;
    public $orderId;

    /**
     * Create a new message instance.
     */
    public function __construct(string $templateKey, array $tokens = [])
    {
        $this->templateKey = $templateKey;
        $this->userId = $tokens['user_id'] ?? null;
        $this->orderId = $tokens['order_id'] ?? null;

        // Load templates from database
        $subjectTemplate = SiteSetting::getVal("mail_template_{$templateKey}_subject", "System Notification");
        $bodyTemplate = SiteSetting::getVal("mail_template_{$templateKey}_body", "<p>Activity notification from X-Buy.</p>");

        // Substitute tokens
        foreach ($tokens as $key => $val) {
            $placeholder = '{' . $key . '}';
            $subjectTemplate = str_replace($placeholder, (string)$val, $subjectTemplate);
            $bodyTemplate = str_replace($placeholder, (string)$val, $bodyTemplate);
        }

        $this->subjectLine = $subjectTemplate;
        $this->htmlBody = $bodyTemplate;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->subjectLine,
            using: [
                function ($message) {
                    $message->getHeaders()->addTextHeader('X-Template-Name', 'system_notification_' . $this->templateKey);
                    if ($this->orderId) {
                        $message->getHeaders()->addTextHeader('X-Order-Id', $this->orderId);
                    }
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
