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

it('allows super admin to view and update telegram message templates', function () {
    $superAdmin = \App\Models\User::factory()->create([
        'role' => \App\Domain\Admin\Enums\UserRole::SUPER_ADMIN,
    ]);

    $response = $this->actingAs($superAdmin)->get(route('admin.settings.telegram'));
    $response->assertOk()
        ->assertSee('Telegram Bot Message Templates');

    $updateResponse = $this->actingAs($superAdmin)->put(route('admin.settings.telegram.update'), [
        'telegram_msg_activation_success' => 'Halo {sponsor_name}, selamat datang di {platform_name}!',
        'telegram_msg_welcome' => 'Selamat datang di bot pengingat {platform_name}.',
        'telegram_msg_default_reply' => 'Pesan diterima oleh customer service {platform_name}.',
        'telegram_msg_invalid_code' => 'Kode aktivasi Anda tidak valid.',
    ]);

    $updateResponse->assertRedirect(route('admin.settings.telegram'))
        ->assertSessionHas('success');

    expect(\App\Models\PlatformSetting::getTelegramActivationSuccessMessage())
        ->toBe('Halo {sponsor_name}, selamat datang di {platform_name}!')
        ->and(\App\Models\PlatformSetting::getTelegramWelcomeMessage())
        ->toBe('Selamat datang di bot pengingat {platform_name}.');
});

it('uses custom template and replaces dynamic variables on activation', function () {
    config([
        'rymainder.channels.telegram.bot_token' => 'fake_token',
        'rymainder.channels.telegram.endpoint' => 'https://api.telegram.org',
    ]);

    \App\Models\PlatformSetting::set('platform_name', 'Yayasan Berkah Mulia');
    \App\Models\PlatformSetting::set('telegram_msg_activation_success', 'Selamat {sponsor_name}! Akun terhubung ke {platform_name}.');

    $sponsor = Sponsor::create([
        'name' => 'Fulan bin Fulan',
        'email' => 'fulan@example.com',
        'phone' => '+6281234567899',
        'telegram_onboard_code' => 'FULAN123',
        'last_donation_date' => '2025-01-01',
        'frequency' => PaymentFrequency::ANNUAL,
        'amount' => 500000,
        'status' => SponsorStatus::ACTIVE,
    ]);

    \Illuminate\Support\Facades\Http::fake([
        'https://api.telegram.org/botfake_token/sendMessage' => function (\Illuminate\Http\Client\Request $request) {
            expect($request['text'])->toBe('Selamat Fulan bin Fulan! Akun terhubung ke Yayasan Berkah Mulia.');
            return \Illuminate\Support\Facades\Http::response(['ok' => true]);
        },
    ]);

    $action = app(\App\Domain\Telegram\Actions\ProcessTelegramWebhookAction::class);
    $result = $action->execute([
        'message' => [
            'chat' => ['id' => 123456],
            'text' => '/start FULAN123',
        ],
    ]);

    expect($result['status'])->toBe('success');
});

it('can reset telegram message templates to defaults', function () {
    $superAdmin = \App\Models\User::factory()->create([
        'role' => \App\Domain\Admin\Enums\UserRole::SUPER_ADMIN,
    ]);

    \App\Models\PlatformSetting::set('telegram_msg_activation_success', 'Teks Kustom Sementara');

    $response = $this->actingAs($superAdmin)->post(route('admin.settings.telegram.reset'));
    $response->assertRedirect(route('admin.settings.telegram'))
        ->assertSessionHas('success');

    expect(\App\Models\PlatformSetting::getTelegramActivationSuccessMessage())
        ->toContain("Assalamu'alaikum Wr. Wb.");
});


