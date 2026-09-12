<?php

namespace Tests\Feature;

use App\Domain\Communication\DataTransferObjects\DeliveryResult;
use App\Domain\Communication\ReminderChannelManager;
use App\Domain\Reminder\Enums\DeliveryStatus;
use App\Domain\Reminder\Enums\ReminderChannel;
use App\Domain\Reminder\Models\CustomReminder;
use App\Domain\Reminder\Models\ReminderLog;
use App\Domain\Sponsor\Enums\PaymentFrequency;
use App\Domain\Sponsor\Enums\SponsorStatus;
use App\Domain\Sponsor\Models\Sponsor;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StandaloneCustomReminderTest extends TestCase
{
    use RefreshDatabase;

    private User $superAdmin;
    private User $staff;

    protected function setUp(): void
    {
        parent::setUp();

        $this->superAdmin = User::factory()->create([
            'role' => 'super_admin',
        ]);

        $this->staff = User::factory()->create([
            'role' => 'staff',
        ]);
    }

    public function test_super_admin_can_view_custom_reminders_list(): void
    {
        CustomReminder::create([
            'title' => 'Weekly Friday Announcement',
            'message' => 'Laporan perkembangan anak asuh',
            'channels' => ['whatsapp', 'email'],
            'target_type' => 'all_active',
            'schedule_type' => 'weekly',
            'schedule_times' => ['09:00'],
            'schedule_day' => 5,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->superAdmin)
            ->get(route('admin.custom-reminders.index'));

        $response->assertOk();
        $response->assertSee('Custom Reminders');
        $response->assertSee('Weekly Friday Announcement');
        $response->assertSee('Create Custom Reminder');
    }

    public function test_super_admin_can_view_create_and_edit_pages(): void
    {
        $sponsor = Sponsor::create([
            'name' => 'Kyai Dahlan',
            'phone' => '+6281122334455',
            'email' => 'dahlan@example.com',
            'amount' => 500000,
            'frequency' => PaymentFrequency::ANNUAL,
            'last_donation_date' => Carbon::parse('2026-08-01'),
            'status' => SponsorStatus::ACTIVE,
            'orphan_name' => 'Bilal',
        ]);

        $reminder = CustomReminder::create([
            'title' => 'Reminder to Edit',
            'message' => 'Sample message',
            'channels' => ['whatsapp'],
            'target_type' => 'selected',
            'schedule_type' => 'daily',
            'schedule_times' => ['12:00'],
            'is_active' => true,
        ]);
        $reminder->sponsors()->attach([$sponsor->id]);

        $createResponse = $this->actingAs($this->superAdmin)
            ->get(route('admin.custom-reminders.create'));

        $createResponse->assertOk();
        $createResponse->assertSee('Create Custom Reminder');
        $createResponse->assertSee('Kyai Dahlan');
        $createResponse->assertSee('Bilal');

        $editResponse = $this->actingAs($this->superAdmin)
            ->get(route('admin.custom-reminders.edit', $reminder));

        $editResponse->assertOk();
        $editResponse->assertSee('Edit Custom Reminder');
        $editResponse->assertSee('Reminder to Edit');
        $editResponse->assertSee('Kyai Dahlan');
    }

    public function test_super_admin_can_create_custom_reminder_with_specific_sponsors(): void
    {
        $sponsor1 = Sponsor::create([
            'name' => 'Ahmad Dahlan',
            'phone' => '+6281122334455',
            'email' => 'ahmad@example.com',
            'amount' => 500000,
            'frequency' => PaymentFrequency::ANNUAL,
            'last_donation_date' => Carbon::parse('2026-08-01'),
            'status' => SponsorStatus::ACTIVE,
            'orphan_name' => 'Bilal',
        ]);

        $sponsor2 = Sponsor::create([
            'name' => 'Hasyim Asyari',
            'phone' => '+6282233445566',
            'email' => 'hasyim@example.com',
            'amount' => 1000000,
            'frequency' => PaymentFrequency::ANNUAL,
            'last_donation_date' => Carbon::parse('2026-08-01'),
            'status' => SponsorStatus::ACTIVE,
            'orphan_name' => 'Usman',
        ]);

        $response = $this->actingAs($this->superAdmin)
            ->post(route('admin.custom-reminders.store'), [
                'title' => 'Executive Sponsor Monthly Report',
                'message' => 'Assalamu alaikum {sponsor_name}, ini laporan untuk {orphan_name}.',
                'channels' => ['whatsapp', 'email'],
                'target_type' => 'selected',
                'sponsor_ids' => [$sponsor1->id, $sponsor2->id],
                'schedule_type' => 'interval_hours',
                'interval_hours' => 4,
                'is_active' => '1',
            ]);

        $response->assertRedirect(route('admin.custom-reminders.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('custom_reminders', [
            'title' => 'Executive Sponsor Monthly Report',
            'target_type' => 'selected',
            'schedule_type' => 'interval_hours',
            'interval_hours' => 4,
            'is_active' => true,
        ]);

        $reminder = CustomReminder::where('title', 'Executive Sponsor Monthly Report')->first();
        $this->assertCount(2, $reminder->sponsors);
        $this->assertTrue($reminder->sponsors->contains($sponsor1));
        $this->assertTrue($reminder->sponsors->contains($sponsor2));
    }

    public function test_super_admin_can_update_custom_reminder_and_modify_selected_sponsors(): void
    {
        $sponsor1 = Sponsor::create([
            'name' => 'Sponsor Satu',
            'email' => 'satu@example.com',
            'phone' => '+6281111111111',
            'amount' => 500000,
            'frequency' => PaymentFrequency::ANNUAL,
            'last_donation_date' => Carbon::parse('2026-08-01'),
            'status' => SponsorStatus::ACTIVE,
        ]);

        $sponsor2 = Sponsor::create([
            'name' => 'Sponsor Dua',
            'email' => 'dua@example.com',
            'phone' => '+6282222222222',
            'amount' => 500000,
            'frequency' => PaymentFrequency::ANNUAL,
            'last_donation_date' => Carbon::parse('2026-08-01'),
            'status' => SponsorStatus::ACTIVE,
        ]);

        $reminder = CustomReminder::create([
            'title' => 'Initial Title',
            'message' => 'Initial message',
            'channels' => ['email'],
            'target_type' => 'selected',
            'schedule_type' => 'daily',
            'schedule_times' => ['12:00'],
            'is_active' => true,
        ]);
        $reminder->sponsors()->attach([$sponsor1->id]);

        $response = $this->actingAs($this->superAdmin)
            ->put(route('admin.custom-reminders.update', $reminder), [
                'title' => 'Updated Title',
                'message' => 'Updated message for {sponsor_name}',
                'channels' => ['whatsapp', 'email'],
                'target_type' => 'selected',
                'sponsor_ids' => [$sponsor2->id],
                'schedule_type' => 'multiple_daily',
                'schedule_times' => ['08:00', '13:00', '18:00'],
                'is_active' => '1',
            ]);

        $response->assertRedirect(route('admin.custom-reminders.index'));

        $this->assertDatabaseHas('custom_reminders', [
            'id' => $reminder->id,
            'title' => 'Updated Title',
            'schedule_type' => 'multiple_daily',
        ]);

        $reminder->refresh();
        $this->assertCount(1, $reminder->sponsors);
        $this->assertTrue($reminder->sponsors->contains($sponsor2));
        $this->assertFalse($reminder->sponsors->contains($sponsor1));
    }

    public function test_updating_one_time_reminder_resets_last_run_and_reactivates_schedule(): void
    {
        $pastDate = Carbon::parse('2026-09-01 10:00:00');
        $newFutureDate = Carbon::parse('2026-09-20 15:30:00');

        $reminder = CustomReminder::create([
            'title' => 'One Time Event Reminder',
            'message' => 'Event starts soon!',
            'channels' => ['email'],
            'target_type' => 'all_active',
            'schedule_type' => 'once',
            'scheduled_at' => $pastDate,
            'last_run_at' => $pastDate,
            'next_run_at' => null,
            'is_active' => false,
        ]);

        $response = $this->actingAs($this->superAdmin)
            ->put(route('admin.custom-reminders.update', $reminder), [
                'title' => 'One Time Event Reminder - Rescheduled',
                'message' => 'Event starts soon!',
                'channels' => ['email'],
                'target_type' => 'all_active',
                'schedule_type' => 'once',
                'scheduled_at' => $newFutureDate->format('Y-m-d\TH:i'),
                'is_active' => '1',
            ]);

        $response->assertRedirect(route('admin.custom-reminders.index'));

        $reminder->refresh();
        $this->assertTrue($reminder->is_active);
        $this->assertNull($reminder->last_run_at);
        $this->assertNotNull($reminder->next_run_at);
        $this->assertEquals($newFutureDate->format('Y-m-d H:i'), $reminder->next_run_at->format('Y-m-d H:i'));
    }

    public function test_super_admin_can_toggle_active_status_of_custom_reminder(): void
    {
        $reminder = CustomReminder::create([
            'title' => 'Toggle Me',
            'message' => 'Test message',
            'channels' => ['email'],
            'target_type' => 'all_active',
            'schedule_type' => 'daily',
            'schedule_times' => ['12:00'],
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->superAdmin)
            ->patch(route('admin.custom-reminders.toggle', $reminder));

        $response->assertRedirect(route('admin.custom-reminders.index'));
        $this->assertFalse($reminder->fresh()->is_active);

        $this->actingAs($this->superAdmin)
            ->patch(route('admin.custom-reminders.toggle', $reminder));

        $this->assertTrue($reminder->fresh()->is_active);
    }

    public function test_super_admin_can_delete_custom_reminder(): void
    {
        $reminder = CustomReminder::create([
            'title' => 'To Be Deleted',
            'message' => 'Delete me',
            'channels' => ['email'],
            'target_type' => 'all_active',
            'schedule_type' => 'daily',
            'schedule_times' => ['12:00'],
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->superAdmin)
            ->delete(route('admin.custom-reminders.destroy', $reminder));

        $response->assertRedirect(route('admin.custom-reminders.index'));
        $this->assertDatabaseMissing('custom_reminders', ['id' => $reminder->id]);
    }

    public function test_run_now_dispatches_reminders_to_selected_sponsors(): void
    {
        $targetSponsor = Sponsor::create([
            'name' => 'Targeted Donor',
            'phone' => '+6281234567890',
            'email' => 'target@example.com',
            'amount' => 750000,
            'frequency' => PaymentFrequency::ANNUAL,
            'last_donation_date' => Carbon::parse('2026-08-01'),
            'status' => SponsorStatus::ACTIVE,
            'orphan_name' => 'Rahmat',
            'channel_whatsapp' => true,
            'channel_email' => false,
        ]);

        $untargetedSponsor = Sponsor::create([
            'name' => 'Ignored Donor',
            'phone' => '+6289876543210',
            'email' => 'ignored@example.com',
            'amount' => 500000,
            'frequency' => PaymentFrequency::ANNUAL,
            'last_donation_date' => Carbon::parse('2026-08-01'),
            'status' => SponsorStatus::ACTIVE,
            'orphan_name' => 'Sholeh',
        ]);

        $reminder = CustomReminder::create([
            'title' => 'Targeted Alert',
            'message' => 'Hello {sponsor_name}, your child {orphan_name} has a message.',
            'channels' => ['whatsapp'],
            'target_type' => 'selected',
            'schedule_type' => 'daily',
            'schedule_times' => ['12:00'],
            'is_active' => true,
        ]);
        $reminder->sponsors()->attach([$targetSponsor->id]);

        $response = $this->actingAs($this->superAdmin)
            ->post(route('admin.custom-reminders.run-now', $reminder));

        $response->assertSessionHas('success');

        // Run the action synchronously to test delivery and logging
        $action = app(\App\Domain\Reminder\Actions\DispatchCustomReminderAction::class);
        $action->execute($reminder, sync: true);

        $this->assertDatabaseHas('reminder_logs', [
            'sponsor_id' => $targetSponsor->id,
            'custom_reminder_id' => $reminder->id,
            'channel' => 'whatsapp',
            'status' => DeliveryStatus::SENT->value,
        ]);

        $this->assertDatabaseMissing('reminder_logs', [
            'sponsor_id' => $untargetedSponsor->id,
            'custom_reminder_id' => $reminder->id,
        ]);
    }

    public function test_console_command_evaluates_and_executes_due_custom_reminders(): void
    {
        $sponsor = Sponsor::create([
            'name' => 'Cron Test Donor',
            'phone' => '+6281199887766',
            'email' => 'crontest@example.com',
            'amount' => 250000,
            'frequency' => PaymentFrequency::ANNUAL,
            'last_donation_date' => Carbon::parse('2026-08-01'),
            'status' => SponsorStatus::ACTIVE,
            'orphan_name' => 'Zaid',
            'channel_whatsapp' => true,
        ]);

        $dueReminder = CustomReminder::create([
            'title' => 'Due Reminder',
            'message' => 'Special reminder for {sponsor_name}',
            'channels' => ['whatsapp'],
            'target_type' => 'all_active',
            'schedule_type' => 'interval_hours',
            'interval_hours' => 2,
            'is_active' => true,
            'next_run_at' => now()->subMinute(),
        ]);

        $notDueReminder = CustomReminder::create([
            'title' => 'Future Reminder',
            'message' => 'Future reminder for {sponsor_name}',
            'channels' => ['whatsapp'],
            'target_type' => 'all_active',
            'schedule_type' => 'daily',
            'schedule_times' => ['12:00'],
            'is_active' => true,
            'next_run_at' => now()->addHours(5),
        ]);

        $this->artisan('custom-reminders:run')
            ->assertSuccessful()
            ->expectsOutputToContain("Processing 'Due Reminder'");

        $dueReminder->refresh();
        $this->assertNotNull($dueReminder->last_run_at);
        $this->assertTrue($dueReminder->next_run_at->isFuture());

        $notDueReminder->refresh();
        $this->assertNull($notDueReminder->last_run_at);
    }

    public function test_staff_cannot_manage_custom_reminders(): void
    {
        $this->actingAs($this->staff)
            ->get(route('admin.custom-reminders.index'))
            ->assertForbidden();

        $this->actingAs($this->staff)
            ->post(route('admin.custom-reminders.store'), [
                'title' => 'Unauthorized',
                'message' => 'Test',
                'channels' => ['email'],
                'target_type' => 'all_active',
                'schedule_type' => 'daily',
            ])
            ->assertForbidden();
    }
}
