<?php

use App\Domain\Reminder\Enums\DeliveryStatus;
use App\Domain\Reminder\Enums\ReminderChannel;
use App\Domain\Reminder\Models\ReminderLog;
use App\Domain\Reminder\Models\ReminderSetting;
use App\Domain\Sponsor\Enums\PaymentFrequency;
use App\Domain\Sponsor\Enums\SponsorStatus;
use App\Domain\Sponsor\Models\Sponsor;
use App\Jobs\SendSponsorReminderJob;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Seed default waves as described in PRD
    ReminderSetting::create([
        'label' => 'Reminder Pertama (H-7)',
        'days_before_due' => 7,
        'channels' => ['email', 'telegram'],
        'is_active' => true,
    ]);

    ReminderSetting::create([
        'label' => 'Reminder Kedua (H-3)',
        'days_before_due' => 3,
        'channels' => ['email', 'whatsapp', 'telegram'],
        'is_active' => true,
    ]);

    ReminderSetting::create([
        'label' => 'Reminder Hari-H (H-0)',
        'days_before_due' => 0,
        'channels' => ['email', 'whatsapp', 'telegram'],
        'is_active' => true,
    ]);
});

it('dispatches reminder jobs for sponsors matching H-7 wave', function () {
    Queue::fake();

    // Set today as 2025-05-10
    Carbon::setTestNow('2025-05-10');

    // Sponsor whose due date is in 7 days (2025-05-17), so last donation was 2024-05-17
    $sponsor = Sponsor::create([
        'name' => 'Siti Khadijah',
        'email' => 'siti@example.com',
        'phone' => '+6281234567891',
        'telegram_onboard_code' => 'SITI12345',
        'last_donation_date' => '2024-05-17',
        'frequency' => PaymentFrequency::ANNUAL,
        'amount' => 300000,
        'status' => SponsorStatus::ACTIVE,
    ]);

    $this->artisan('reminders:send')
        ->assertSuccessful();

    // H-7 wave enables Email and Telegram
    Queue::assertPushed(SendSponsorReminderJob::class, 2);

    Queue::assertPushed(SendSponsorReminderJob::class, function ($job) use ($sponsor) {
        return $job->sponsorId === $sponsor->id && $job->channel === ReminderChannel::EMAIL;
    });

    Queue::assertPushed(SendSponsorReminderJob::class, function ($job) use ($sponsor) {
        return $job->sponsorId === $sponsor->id && $job->channel === ReminderChannel::TELEGRAM;
    });
});

it('prevents double sending when command is executed multiple times (idempotency)', function () {
    Carbon::setTestNow('2025-05-10');

    $sponsor = Sponsor::create([
        'name' => 'Pak Hendra',
        'email' => 'hendra@example.com',
        'phone' => '+6281234567892',
        'telegram_onboard_code' => 'HENDRA12345',
        'last_donation_date' => '2024-05-17',
        'frequency' => PaymentFrequency::ANNUAL,
        'amount' => 500000,
        'status' => SponsorStatus::ACTIVE,
    ]);

    $setting = ReminderSetting::where('days_before_due', 7)->first();

    // Run first time without faking queue so job runs synchronously in sync/database queue
    $this->artisan('reminders:send');

    // Execute job directly to simulate queue worker processing
    $job = new SendSponsorReminderJob(
        sponsorId: $sponsor->id,
        reminderSettingId: $setting->id,
        channel: ReminderChannel::EMAIL,
        dueDateString: '2025-05-17'
    );
    app()->call([$job, 'handle']);

    // Assert log entry is created with status SENT
    $log = ReminderLog::where('sponsor_id', $sponsor->id)
        ->where('channel', ReminderChannel::EMAIL->value)
        ->first();

    expect($log)->not->toBeNull();
    expect($log->status)->toBe(DeliveryStatus::SENT);

    // Verify query
    $exists = ReminderLog::where('sponsor_id', $sponsor->id)
        ->whereDate('due_date', '2025-05-17')
        ->where('reminder_setting_id', $setting->id)
        ->where('channel', ReminderChannel::EMAIL->value)
        ->where('status', DeliveryStatus::SENT)
        ->exists();
    expect($exists)->toBeTrue();



    // Run command a second time on the same day
    Queue::fake();
    $this->artisan('reminders:send')
        ->expectsOutputToContain('Duplicate Dispatches Prevented (Already Sent)');


    // Assert that NO new job is pushed for the email channel of this sponsor
    Queue::assertNotPushed(SendSponsorReminderJob::class, function ($job) use ($sponsor) {
        return $job->sponsorId === $sponsor->id && $job->channel === ReminderChannel::EMAIL;
    });
});

it('marks telegram as SKIPPED instead of FAILED if sponsor has not onboarded', function () {
    $sponsor = Sponsor::create([
        'name' => 'Donatur Baru',
        'email' => 'donatur@example.com',
        'phone' => '+6281234567893',
        'telegram_chat_id' => null, // Not yet onboarded
        'telegram_onboard_code' => 'NEW12345',
        'last_donation_date' => '2024-05-17',
        'frequency' => PaymentFrequency::ANNUAL,
        'amount' => 500000,
        'status' => SponsorStatus::ACTIVE,
    ]);

    $setting = ReminderSetting::where('days_before_due', 7)->first();

    $job = new SendSponsorReminderJob(
        sponsorId: $sponsor->id,
        reminderSettingId: $setting->id,
        channel: ReminderChannel::TELEGRAM,
        dueDateString: '2025-05-17'
    );
    app()->call([$job, 'handle']);

    $log = ReminderLog::where('sponsor_id', $sponsor->id)
        ->where('channel', ReminderChannel::TELEGRAM->value)
        ->first();

    expect($log)->not->toBeNull();
    expect($log->status)->toBe(DeliveryStatus::SKIPPED);
    expect($log->error_message)->toContain('belum menghubungkan Telegram');
});

it('does not send reminders to paused or cancelled sponsors', function () {
    Queue::fake();
    Carbon::setTestNow('2025-05-10');

    Sponsor::create([
        'name' => 'Sponsor Cuti',
        'email' => 'cuti@example.com',
        'phone' => '+6281234567894',
        'last_donation_date' => '2024-05-17',
        'frequency' => PaymentFrequency::ANNUAL,
        'amount' => 500000,
        'status' => SponsorStatus::PAUSED,
    ]);

    Sponsor::create([
        'name' => 'Sponsor Berhenti',
        'email' => 'berhenti@example.com',
        'phone' => '+6281234567895',
        'last_donation_date' => '2024-05-17',
        'frequency' => PaymentFrequency::ANNUAL,
        'amount' => 500000,
        'status' => SponsorStatus::CANCELLED,
    ]);

    $this->artisan('reminders:send');

    Queue::assertNothingPushed();
});
