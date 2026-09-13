<?php

namespace Tests\Feature;

use App\Domain\Admin\Enums\UserRole;
use App\Domain\Reminder\Enums\DeliveryStatus;
use App\Domain\Reminder\Enums\ReminderChannel;
use App\Domain\Reminder\Models\CustomReminder;
use App\Domain\Reminder\Models\ReminderLog;
use App\Domain\Reminder\Models\ReminderSetting;
use App\Domain\Sponsor\Enums\PaymentFrequency;
use App\Domain\Sponsor\Enums\SponsorStatus;
use App\Domain\Sponsor\Models\Sponsor;
use App\Jobs\SendCustomReminderJob;
use App\Jobs\SendManualReminderJob;
use App\Jobs\SendSponsorReminderJob;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class ReminderRetryTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Sponsor $sponsor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => UserRole::SUPER_ADMIN,
        ]);

        $this->sponsor = Sponsor::create([
            'name' => 'Budi Prakoso',
            'email' => 'budi@example.com',
            'phone' => '+6281234567890',
            'amount' => 500000,
            'frequency' => PaymentFrequency::ANNUAL,
            'last_donation_date' => now()->subMonths(11),
            'status' => SponsorStatus::ACTIVE,
            'telegram_chat_id' => '12345678',
        ]);
    }

    public function test_can_retry_failed_standard_wave_reminder(): void
    {
        Queue::fake([SendSponsorReminderJob::class]);

        $setting = ReminderSetting::create([
            'days_before_due' => 7,
            'label' => 'H-7 Wave',
            'channels' => ['email', 'telegram'],
            'message_template' => 'Reminder H-7',
            'is_active' => true,
        ]);

        $log = ReminderLog::create([
            'sponsor_id' => $this->sponsor->id,
            'reminder_setting_id' => $setting->id,
            'channel' => ReminderChannel::EMAIL,
            'due_date' => now()->addDays(7)->toDateString(),
            'status' => DeliveryStatus::FAILED,
            'error_message' => 'Connection timeout to SMTP host.',
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.logs.retry', $log));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        Queue::assertPushed(SendSponsorReminderJob::class, function ($job) use ($setting) {
            return $job->sponsorId === $this->sponsor->id
                && $job->reminderSettingId === $setting->id
                && $job->channel === ReminderChannel::EMAIL;
        });

        $log->refresh();
        $this->assertEquals(DeliveryStatus::PENDING, $log->status);
        $this->assertNull($log->error_message);
    }

    public function test_can_retry_failed_custom_reminder(): void
    {
        Queue::fake([SendCustomReminderJob::class]);

        $customReminder = CustomReminder::create([
            'title' => 'Ramadan Special Campaign',
            'message' => 'Dear {sponsor_name}, Ramadan blessings!',
            'channels' => ['telegram'],
            'target_type' => 'all',
            'schedule_type' => 'one_time',
            'is_active' => true,
        ]);

        $log = ReminderLog::create([
            'sponsor_id' => $this->sponsor->id,
            'custom_reminder_id' => $customReminder->id,
            'channel' => ReminderChannel::TELEGRAM,
            'due_date' => now()->toDateString(),
            'status' => DeliveryStatus::FAILED,
            'error_message' => 'Telegram Bot API returned 400 Bad Request.',
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.logs.retry', $log));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        Queue::assertPushed(SendCustomReminderJob::class, function ($job) use ($customReminder) {
            return $job->sponsorId === $this->sponsor->id
                && $job->customReminderId === $customReminder->id
                && $job->channel === ReminderChannel::TELEGRAM;
        });

        $log->refresh();
        $this->assertEquals(DeliveryStatus::PENDING, $log->status);
        $this->assertNull($log->error_message);
    }

    public function test_can_retry_failed_manual_direct_reminder(): void
    {
        Queue::fake([SendManualReminderJob::class]);

        $log = ReminderLog::create([
            'sponsor_id' => $this->sponsor->id,
            'channel' => ReminderChannel::WHATSAPP,
            'due_date' => now()->toDateString(),
            'is_manual' => true,
            'custom_message' => 'Halo Pak Budi, ini pengingat langsung.',
            'status' => DeliveryStatus::FAILED,
            'error_message' => 'WhatsApp gateway unreachable.',
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.logs.retry', $log));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        Queue::assertPushed(SendManualReminderJob::class, function ($job) use ($log) {
            return $job->reminderLogId === $log->id;
        });

        $log->refresh();
        $this->assertEquals(DeliveryStatus::PENDING, $log->status);
        $this->assertNull($log->error_message);
    }

    public function test_retry_all_failed_reminders_bulk_action(): void
    {
        Queue::fake([SendSponsorReminderJob::class, SendCustomReminderJob::class, SendManualReminderJob::class]);

        $setting = ReminderSetting::create([
            'days_before_due' => 3,
            'label' => 'H-3 Wave',
            'channels' => ['email'],
            'message_template' => 'Template',
            'is_active' => true,
        ]);

        $custom = CustomReminder::create([
            'title' => 'Broadcast Notice',
            'message' => 'Notice',
            'channels' => ['telegram'],
            'target_type' => 'all',
            'schedule_type' => 'one_time',
            'is_active' => true,
        ]);

        $log1 = ReminderLog::create([
            'sponsor_id' => $this->sponsor->id,
            'reminder_setting_id' => $setting->id,
            'channel' => ReminderChannel::EMAIL,
            'due_date' => now()->addDays(3)->toDateString(),
            'status' => DeliveryStatus::FAILED,
            'error_message' => 'Failed 1',
        ]);

        $log2 = ReminderLog::create([
            'sponsor_id' => $this->sponsor->id,
            'custom_reminder_id' => $custom->id,
            'channel' => ReminderChannel::TELEGRAM,
            'due_date' => now()->toDateString(),
            'status' => DeliveryStatus::FAILED,
            'error_message' => 'Failed 2',
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.logs.retry-all'));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        Queue::assertPushed(SendSponsorReminderJob::class);
        Queue::assertPushed(SendCustomReminderJob::class);

        $this->assertEquals(DeliveryStatus::PENDING, $log1->fresh()->status);
        $this->assertEquals(DeliveryStatus::PENDING, $log2->fresh()->status);
    }

    public function test_logs_index_renders_failed_alert_banner_and_retry_all_button(): void
    {
        ReminderLog::create([
            'sponsor_id' => $this->sponsor->id,
            'channel' => ReminderChannel::EMAIL,
            'due_date' => now()->toDateString(),
            'status' => DeliveryStatus::FAILED,
            'error_message' => 'Connection refused',
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.logs.index'));

        $response->assertOk();
        $response->assertSee('Attention: 1 Failed Reminder Detected');
        $response->assertSee('Retry All Failed Reminders (1)');
    }

    public function test_sponsors_show_renders_retry_button_for_failed_log(): void
    {
        $log = ReminderLog::create([
            'sponsor_id' => $this->sponsor->id,
            'channel' => ReminderChannel::EMAIL,
            'due_date' => now()->toDateString(),
            'status' => DeliveryStatus::FAILED,
            'error_message' => 'Connection refused',
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.sponsors.show', $this->sponsor));

        $response->assertOk();
        $response->assertSee(route('admin.logs.retry', $log));
        $response->assertSee('Retry');
    }

    public function test_retry_failed_reminders_artisan_command(): void
    {
        Queue::fake([SendManualReminderJob::class]);

        $log = ReminderLog::create([
            'sponsor_id' => $this->sponsor->id,
            'channel' => ReminderChannel::TELEGRAM,
            'due_date' => now()->toDateString(),
            'is_manual' => true,
            'custom_message' => 'Manual text',
            'status' => DeliveryStatus::FAILED,
            'error_message' => 'Chat not found',
        ]);

        // Test dry-run
        $this->artisan('reminders:retry-failed --dry-run')
            ->expectsOutputToContain('[DRY RUN] Found 1 failed reminder(s). No retries were dispatched.')
            ->assertExitCode(0);

        Queue::assertNothingPushed();

        // Test actual execution
        $this->artisan('reminders:retry-failed')
            ->expectsOutputToContain('Successfully queued 1 of 1 failed reminder(s)')
            ->assertExitCode(0);

        Queue::assertPushed(SendManualReminderJob::class);
        $this->assertEquals(DeliveryStatus::PENDING, $log->fresh()->status);
    }
}
