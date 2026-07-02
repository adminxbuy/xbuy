<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DynamicCampaignMail extends Mailable
{
    use Queueable, SerializesModels;

    public $subjectLine;
    public $htmlBody;
    public $unsubscribeUrl;

    /**
     * Create a new message instance.
     */
    public function __construct(string $subjectLine, string $htmlBody, string $unsubscribeUrl)
    {
        $this->subjectLine = $subjectLine;
        $this->htmlBody = $htmlBody;
        $this->unsubscribeUrl = $unsubscribeUrl;
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
                    $message->getHeaders()->addTextHeader('X-Template-Name', 'dynamic_campaign');
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
            view: 'emails.dynamic_campaign',
        );
    }
}
