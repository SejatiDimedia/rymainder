<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdminDailyDigestMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly int $sentCount,
        public readonly int $failedCount,
        public readonly int $skippedCount,
        public readonly mixed $failedLogs
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "[Rymainder Digest] Laporan Pengiriman Reminder " . now()->translatedFormat('d M Y'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.admin-daily-digest',
        );
    }
}
