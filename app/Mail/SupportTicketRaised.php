<?php

namespace App\Mail;

use App\Models\SupportTicket;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SupportTicketRaised extends Mailable
{
    use Queueable, SerializesModels;

    public $ticket;
    public $buyerName;
    public $buyerEmail;

    /**
     * Create a new message instance.
     */
    public function __construct(SupportTicket $ticket, string $buyerName, string $buyerEmail)
    {
        $this->ticket = $ticket;
        $this->buyerName = $buyerName;
        $this->buyerEmail = $buyerEmail;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New Support Ticket Raised',
            using: [
                function ($message) {
                    $message->getHeaders()->addTextHeader('X-Template-Name', 'support_ticket_raised');
                    if ($this->ticket) {
                        $message->getHeaders()->addTextHeader('X-User-Id', $this->ticket->user_id);
                        if ($this->ticket->order_id) {
                            $message->getHeaders()->addTextHeader('X-Order-Id', $this->ticket->order_id);
                        }
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
            view: 'emails.support_ticket_raised',
        );
    }
}
