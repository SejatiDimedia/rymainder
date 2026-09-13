<?php

use App\Domain\Sponsor\Enums\PaymentFrequency;
use App\Domain\Sponsor\Enums\SponsorStatus;
use App\Domain\Sponsor\Models\Sponsor;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('links telegram chat id when sponsor submits /start with valid code', function () {
    $sponsor = Sponsor::create([
        'name' => 'Budi Santoso',
        'email' => 'budi@example.com',
        'phone' => '+6281234567890',
        'telegram_onboard_code' => 'SPONSOR12345',
        'last_donation_date' => '2025-01-01',
        'frequency' => PaymentFrequency::ANNUAL,
        'amount' => 500000,
        'status' => SponsorStatus::ACTIVE,
    ]);

    expect($sponsor->telegram_chat_id)->toBeNull();

    $payload = [
        'update_id' => 99999,
        'message' => [
            'message_id' => 101,
            'from' => [
                'id' => 778899,
                'first_name' => 'Budi',
                'username' => 'budisantoso',
            ],
            'chat' => [
                'id' => 778899,
                'type' => 'private',
            ],
            'date' => time(),
            'text' => '/start SPONSOR12345',
        ],
    ];

    $response = $this->postJson('/webhooks/telegram', $payload);

    $response->assertOk()
        ->assertJson([
            'status' => 'success',
            'sponsor_id' => $sponsor->id,
            'chat_id' => 778899,
        ]);

    $sponsor->refresh();
    expect($sponsor->telegram_chat_id)->toBe('778899');
});

it('rejects invalid onboarding code gracefully', function () {
    $payload = [
        'update_id' => 99999,
        'message' => [
            'message_id' => 102,
            'chat' => ['id' => 12345],
            'text' => '/start INVALIDCODE',
        ],
    ];

    $response = $this->postJson('/webhooks/telegram', $payload);

    $response->assertOk()
        ->assertJson([
            'status' => 'invalid_code',
        ]);
});

it('enforces webhook secret when configured', function () {
    config(['rymainder.channels.telegram.webhook_secret' => 'supersecret123']);

    $payload = [
        'update_id' => 1,
        'message' => [
            'chat' => ['id' => 123],
            'text' => '/start',
        ],
    ];

    // Request without header
    $response = $this->postJson('/webhooks/telegram', $payload);
    $response->assertStatus(403);

    // Request with valid header
    $responseWithHeader = $this->withHeaders([
        'X-Telegram-Bot-Api-Secret-Token' => 'supersecret123',
    ])->postJson('/webhooks/telegram', $payload);

    $responseWithHeader->assertOk();
});

it('sends telegram onboarding invitation email to sponsor', function () {
    \Illuminate\Support\Facades\Mail::fake();
    config(['rymainder.channels.telegram.bot_username' => 'RymainderBot']);

    $user = \App\Models\User::factory()->create();
    $sponsor = Sponsor::create([
        'name' => 'Ahmad Dahlan',
        'email' => 'ahmad@example.com',
        'phone' => '+6281234567890',
        'last_donation_date' => '2025-01-01',
        'frequency' => PaymentFrequency::ANNUAL,
        'amount' => 500000,
        'status' => SponsorStatus::ACTIVE,
    ]);

    $response = $this->actingAs($user)->post(route('admin.sponsors.send-telegram-invitation', $sponsor));

    $response->assertRedirect()
        ->assertSessionHas('success');

    \Illuminate\Support\Facades\Mail::assertSent(\App\Mail\TelegramOnboardingInvitationMail::class, function ($mail) use ($sponsor) {
        return $mail->hasTo('ahmad@example.com') && $mail->sponsor->id === $sponsor->id;
    });
});

it('syncs telegram updates and connects sponsor', function () {
    config([
        'rymainder.channels.telegram.bot_token' => 'fake_token',
        'rymainder.channels.telegram.endpoint' => 'https://api.telegram.org',
    ]);

    $user = \App\Models\User::factory()->create();
    $sponsor = Sponsor::create([
        'name' => 'Siti Nurhaliza',
        'email' => 'siti@example.com',
        'phone' => '+6281234567891',
        'telegram_onboard_code' => 'SITI9999',
        'last_donation_date' => '2025-01-01',
        'frequency' => PaymentFrequency::ANNUAL,
        'amount' => 500000,
        'status' => SponsorStatus::ACTIVE,
    ]);

    \Illuminate\Support\Facades\Http::fake([
        'https://api.telegram.org/botfake_token/getUpdates' => \Illuminate\Support\Facades\Http::response([
            'ok' => true,
            'result' => [
                [
                    'update_id' => 1234,
                    'message' => [
                        'message_id' => 55,
                        'chat' => ['id' => 998877],
                        'text' => '/start SITI9999',
                    ],
                ],
            ],
        ]),
        'https://api.telegram.org/botfake_token/sendMessage' => \Illuminate\Support\Facades\Http::response(['ok' => true]),
    ]);

    $response = $this->actingAs($user)->post(route('admin.sponsors.check-telegram-status', $sponsor));

    $response->assertRedirect()
        ->assertSessionHas('success');

    $sponsor->refresh();
    expect($sponsor->telegram_chat_id)->toBe('998877');
});

it('can disconnect telegram account from sponsor', function () {
    $user = \App\Models\User::factory()->create();
    $sponsor = Sponsor::create([
        'name' => 'Bambang',
        'email' => 'bambang@example.com',
        'phone' => '+6281234567892',
        'telegram_chat_id' => '123456',
        'last_donation_date' => '2025-01-01',
        'frequency' => PaymentFrequency::ANNUAL,
        'amount' => 500000,
        'status' => SponsorStatus::ACTIVE,
    ]);

    expect($sponsor->hasConnectedTelegram())->toBeTrue();

    $response = $this->actingAs($user)->delete(route('admin.sponsors.disconnect-telegram', $sponsor));

    $response->assertRedirect()
        ->assertSessionHas('success');

    $sponsor->refresh();
    expect($sponsor->hasConnectedTelegram())->toBeFalse()
        ->and($sponsor->telegram_chat_id)->toBeNull();
});

