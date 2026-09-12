<?php

namespace App\Mail;

use App\Domain\Communication\DataTransferObjects\NotificationPayload;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SponsorPaymentReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly NotificationPayload $payload
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->payload->subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.sponsor-reminder',
        );
    }
}
