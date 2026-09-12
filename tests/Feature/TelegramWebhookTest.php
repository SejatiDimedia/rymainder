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
