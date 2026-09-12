<?php

use App\Domain\Admin\Enums\UserRole;
use App\Domain\Reminder\Models\ReminderSetting;
use App\Domain\Sponsor\Enums\PaymentFrequency;
use App\Domain\Sponsor\Enums\SponsorStatus;
use App\Domain\Sponsor\Models\Sponsor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->staffUser = User::factory()->create([
        'role' => UserRole::STAFF,
    ]);

    $this->superAdminUser = User::factory()->create([
        'role' => UserRole::SUPER_ADMIN,
    ]);
});

it('redirects unauthenticated guest to login', function () {
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));

    $response2 = $this->get(route('admin.sponsors.index'));
    $response2->assertRedirect(route('login'));
});

it('allows authenticated user to view dashboard and sponsor list', function () {
    $this->actingAs($this->staffUser);

    $response = $this->get(route('dashboard'));
    $response->assertOk()
        ->assertSee('Dashboard Monitoring Reminder');

    $response2 = $this->get(route('admin.sponsors.index'));
    $response2->assertOk()
        ->assertSee('Sponsors & Donors', false);

});

it('creates a new sponsor and normalizes phone to E.164', function () {
    $this->actingAs($this->staffUser);

    $data = [
        'name' => 'Faris Maulana',
        'email' => 'faris@example.com',
        'phone' => '0812-9988-7766', // Raw local format
        'orphan_name' => 'Adit',
        'last_donation_date' => '2025-01-01',
        'frequency' => PaymentFrequency::ANNUAL->value,
        'amount' => 750000,
        'status' => SponsorStatus::ACTIVE->value,
        'channel_preferences' => ['email', 'whatsapp'],
        'notes' => 'Donatur prioritas',
    ];

    $response = $this->post(route('admin.sponsors.store'), $data);

    $sponsor = Sponsor::where('email', 'faris@example.com')->first();
    expect($sponsor)->not->toBeNull();
    // Normalized to E.164
    expect($sponsor->phone)->toBe('+6281299887766');
    expect($sponsor->telegram_onboard_code)->not->toBeEmpty();

    $response->assertRedirect(route('admin.sponsors.show', $sponsor));
});

it('rejects invalid inputs when creating sponsor', function () {
    $this->actingAs($this->staffUser);

    $response = $this->post(route('admin.sponsors.store'), [
        'name' => '',
        'email' => 'not-an-email',
        'phone' => '123', // invalid
        'last_donation_date' => now()->addDays(5)->toDateString(), // future date
        'frequency' => 'invalid_freq',
        'amount' => -100,
        'status' => 'invalid_status',
    ]);

    $response->assertSessionHasErrors([
        'name',
        'email',
        'phone',
        'last_donation_date',
        'frequency',
        'amount',
        'status',
    ]);
});

it('allows viewing and updating sponsor details', function () {
    $this->actingAs($this->staffUser);

    $sponsor = Sponsor::create([
        'name' => 'Rina Nose',
        'email' => 'rina@example.com',
        'phone' => '+6281234567890',
        'last_donation_date' => '2025-01-01',
        'frequency' => PaymentFrequency::SIX_MONTHS,
        'amount' => 300000,
        'status' => SponsorStatus::ACTIVE,
    ]);

    $showResponse = $this->get(route('admin.sponsors.show', $sponsor));
    $showResponse->assertOk()
        ->assertSee('Rina Nose')
        ->assertSee($sponsor->telegram_onboard_code);

    $updateResponse = $this->put(route('admin.sponsors.update', $sponsor), [
        'name' => 'Rina Nose Updated',
        'email' => 'rina@example.com',
        'phone' => '+6281234567890',
        'last_donation_date' => '2025-01-01',
        'frequency' => PaymentFrequency::ANNUAL->value,
        'amount' => 500000,
        'status' => SponsorStatus::PAUSED->value,
    ]);

    $updateResponse->assertRedirect(route('admin.sponsors.show', $sponsor));
    $sponsor->refresh();
    expect($sponsor->name)->toBe('Rina Nose Updated');
    expect($sponsor->status)->toBe(SponsorStatus::PAUSED);
});

it('enforces role permission: only super admin can access and edit reminder wave settings', function () {
    $wave = ReminderSetting::create([
        'label' => 'Wave H-7 Test',
        'days_before_due' => 7,
        'channels' => ['email'],
        'is_active' => true,
    ]);

    // Staff cannot access
    $this->actingAs($this->staffUser);
    $this->get(route('admin.settings.index'))->assertForbidden();

    // Super Admin can access
    $this->actingAs($this->superAdminUser);
    $this->get(route('admin.settings.index'))->assertOk()->assertSee('Wave H-7 Test');

    // Super Admin can update wave
    $response = $this->put(route('admin.settings.update', $wave), [
        'label' => 'Wave H-7 Updated',
        'days_before_due' => 7,
        'channels' => ['email', 'whatsapp'],
        'is_active' => 1,
    ]);

    $response->assertRedirect(route('admin.settings.index'));
    $wave->refresh();
    expect($wave->label)->toBe('Wave H-7 Updated');
    expect($wave->channels)->toContain('whatsapp');
});
