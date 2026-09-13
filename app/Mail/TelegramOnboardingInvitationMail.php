<?php

namespace App\Mail;

use App\Domain\Sponsor\Models\Sponsor;
use App\Models\PlatformSetting;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TelegramOnboardingInvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $platformName;
    public string $onboardUrl;

    public function __construct(
        public readonly Sponsor $sponsor
    ) {
        $this->platformName = PlatformSetting::getName();
        $this->onboardUrl = $sponsor->getTelegramOnboardingUrl();
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Undangan Menghubungkan Bot Pengingat Donasi - {$this->platformName}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.telegram-invitation',
        );
    }
}
