<?php

namespace Tests\Feature;

use App\Domain\Communication\DataTransferObjects\NotificationPayload;
use App\Domain\Reminder\Enums\DeliveryStatus;
use App\Domain\Reminder\Enums\ReminderChannel;
use App\Domain\Reminder\Models\ReminderLog;
use App\Domain\Reminder\Models\ReminderSetting;
use App\Domain\Sponsor\Enums\PaymentFrequency;
use App\Domain\Sponsor\Enums\SponsorStatus;
use App\Domain\Sponsor\Models\Sponsor;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomReminderTest extends TestCase
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

    public function test_settings_page_renders_with_reminder_waves_and_template_editor(): void
    {
        ReminderSetting::create([
            'label' => 'H-7 Test Wave',
            'days_before_due' => 7,
            'channels' => ['whatsapp', 'email'],
            'is_active' => true,
            'message_template' => 'Hello {sponsor_name}',
        ]);

        $response = $this->actingAs($this->superAdmin)
            ->get(route('admin.settings.index'));

        $response->assertOk();
        $response->assertSee('Reminder Wave Settings');
        $response->assertSee('+ Add Custom Wave');
        $response->assertSee('H-7 Test Wave');
        $response->assertSee('{sponsor_name}');
        $response->assertSee('Create New Reminder Wave');
    }

    public function test_sponsor_show_page_renders_with_custom_reminder_modal(): void
    {
        $sponsor = Sponsor::create([
            'name' => 'Fatimah Az-Zahra',
            'phone' => '+6281299887766',
            'email' => 'fatimah@example.com',
            'amount' => 1000000,
            'frequency' => PaymentFrequency::ANNUAL,
            'last_donation_date' => Carbon::parse('2026-08-01'),
            'status' => SponsorStatus::ACTIVE,
            'channel_email' => true,
            'channel_whatsapp' => true,
        ]);

        $response = $this->actingAs($this->superAdmin)
            ->get(route('admin.sponsors.show', $sponsor));

        $response->assertOk();
        $response->assertSee('Send Custom Reminder');
        $response->assertSee('Delivery Channel');
        $response->assertSee('Message Text');
        $response->assertSee('Fatimah Az-Zahra');
    }

    public function test_super_admin_can_create_new_custom_reminder_wave(): void
    {
        $response = $this->actingAs($this->superAdmin)
            ->post(route('admin.settings.store'), [
                'label' => 'H-14 Early Wave',
                'days_before_due' => 14,
                'channels' => ['whatsapp', 'email'],
                'is_active' => '1',
                'message_template' => 'Hello {sponsor_name}, your contribution of {amount} is due on {due_date}.',
            ]);

        $response->assertRedirect(route('admin.settings.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('reminder_settings', [
            'label' => 'H-14 Early Wave',
            'days_before_due' => 14,
            'is_active' => true,
            'message_template' => 'Hello {sponsor_name}, your contribution of {amount} is due on {due_date}.',
        ]);
    }

    public function test_super_admin_can_update_reminder_wave_template(): void
    {
        $setting = ReminderSetting::create([
            'label' => 'H-7 Test Wave',
            'days_before_due' => 7,
            'channels' => ['email'],
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->superAdmin)
            ->put(route('admin.settings.update', $setting), [
                'label' => 'H-7 Updated Wave',
                'days_before_due' => 7,
                'channels' => ['email', 'whatsapp'],
                'is_active' => '1',
                'message_template' => 'Custom template for {sponsor_name}: {amount} due on {due_date}.',
            ]);

        $response->assertRedirect(route('admin.settings.index'));

        $this->assertDatabaseHas('reminder_settings', [
            'id' => $setting->id,
            'label' => 'H-7 Updated Wave',
            'message_template' => 'Custom template for {sponsor_name}: {amount} due on {due_date}.',
        ]);
    }

    public function test_super_admin_can_delete_reminder_wave(): void
    {
        $setting = ReminderSetting::create([
            'label' => 'Temporary Wave',
            'days_before_due' => 1,
            'channels' => ['whatsapp'],
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->superAdmin)
            ->delete(route('admin.settings.destroy', $setting));

        $response->assertRedirect(route('admin.settings.index'));
        $this->assertDatabaseMissing('reminder_settings', [
            'id' => $setting->id,
        ]);
    }

    public function test_staff_cannot_create_or_delete_reminder_waves(): void
    {
        $setting = ReminderSetting::create([
            'label' => 'Protected Wave',
            'days_before_due' => 3,
            'channels' => ['whatsapp'],
            'is_active' => true,
        ]);

        $this->actingAs($this->staff)
            ->post(route('admin.settings.store'), [
                'label' => 'Unauthorized Wave',
                'days_before_due' => 5,
                'channels' => ['email'],
            ])
            ->assertForbidden();

        $this->actingAs($this->staff)
            ->delete(route('admin.settings.destroy', $setting))
            ->assertForbidden();
    }

    public function test_dynamic_merge_tags_are_correctly_replaced_in_notification_payload(): void
    {
        $sponsor = Sponsor::create([
            'name' => 'Budi Santoso',
            'phone' => '+6281234567890',
            'email' => 'budi@example.com',
            'amount' => 500000,
            'frequency' => PaymentFrequency::ANNUAL,
            'last_donation_date' => Carbon::parse('2026-08-15'),
            'status' => SponsorStatus::ACTIVE,
            'orphan_name' => 'Adit',
            'channel_email' => true,
            'channel_whatsapp' => true,
        ]);

        $dueDate = Carbon::parse('2026-09-15');
        $customTemplate = 'Halo {sponsor_name}, donasi anak asuh {orphan_name} sebesar {amount} jatuh tempo {due_date}.';

        $payload = NotificationPayload::make(
            sponsor: $sponsor,
            dueDate: $dueDate,
            waveLabel: 'H-7 Wave',
            daysDifference: 7,
            customTemplate: $customTemplate,
        );

        $this->assertStringContainsString('Halo Budi Santoso', $payload->messageBody);
        $this->assertStringContainsString('anak asuh Adit', $payload->messageBody);
        $this->assertStringContainsString('Rp 500.000', $payload->messageBody);
        $this->assertStringContainsString('15 September 2026', $payload->messageBody);
    }

    public function test_admin_can_send_manual_custom_reminder_to_sponsor(): void
    {
        $sponsor = Sponsor::create([
            'name' => 'Siti Nurhaliza',
            'phone' => '+6289876543210',
            'email' => 'siti@example.com',
            'amount' => 750000,
            'frequency' => PaymentFrequency::ANNUAL,
            'last_donation_date' => Carbon::parse('2026-08-10'),
            'status' => SponsorStatus::ACTIVE,
            'orphan_name' => 'Rian',
            'channel_email' => true,
            'channel_whatsapp' => true,
        ]);

        $customMessage = "Special direct reminder for Siti Nurhaliza. Thank you for your generosity!";

        $response = $this->actingAs($this->superAdmin)
            ->post(route('admin.sponsors.send-reminder', $sponsor), [
                'channel' => 'whatsapp',
                'message' => $customMessage,
            ]);

        $response->assertSessionHas('success');

        $this->assertDatabaseHas('reminder_logs', [
            'sponsor_id' => $sponsor->id,
            'channel' => 'whatsapp',
            'is_manual' => true,
            'status' => DeliveryStatus::SENT->value,
            'custom_message' => $customMessage,
        ]);
    }
}
