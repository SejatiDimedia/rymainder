<?php

use App\Domain\Admin\Enums\UserRole;
use App\Models\PlatformSetting;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->superAdmin = User::factory()->create([
        'role' => UserRole::SUPER_ADMIN,
    ]);

    $this->staffUser = User::factory()->create([
        'role' => UserRole::STAFF,
    ]);
});

it('allows super admin to view platform settings page', function () {
    $this->actingAs($this->superAdmin);

    $response = $this->get(route('admin.settings.platform'));
    $response->assertOk()
        ->assertSee('Platform & Branding Settings', false)
        ->assertSee('Platform / Organization Name');
});

it('forbids staff from accessing platform settings page', function () {
    $this->actingAs($this->staffUser);

    $response = $this->get(route('admin.settings.platform'));
    $response->assertForbidden();
});

it('allows super admin to update platform name and tagline', function () {
    $this->actingAs($this->superAdmin);

    $response = $this->put(route('admin.settings.platform.update'), [
        'platform_name' => 'Yayasan Peduli Anak',
        'platform_tagline' => 'Sistem Pengingat Donasi',
    ]);

    $response->assertSessionHasNoErrors()
        ->assertRedirect();

    expect(PlatformSetting::getName())->toBe('Yayasan Peduli Anak');
    expect(PlatformSetting::getTagline())->toBe('Sistem Pengingat Donasi');
});

it('allows super admin to upload a custom logo and reset to default', function () {
    Storage::fake('public');

    $this->actingAs($this->superAdmin);

    $file = UploadedFile::fake()->image('custom_logo.png', 200, 200);

    $response = $this->put(route('admin.settings.platform.update'), [
        'platform_name' => 'Rymainder Custom',
        'platform_tagline' => 'Custom Tagline',
        'logo' => $file,
    ]);

    $response->assertSessionHasNoErrors()
        ->assertRedirect();

    $logoPath = PlatformSetting::get('platform_logo');
    expect($logoPath)->not->toBeNull();
    Storage::disk('public')->assertExists($logoPath);
    expect(PlatformSetting::hasCustomLogo())->toBeTrue();

    // Now test reset logo
    $resetResponse = $this->delete(route('admin.settings.platform.reset-logo'));
    $resetResponse->assertSessionHasNoErrors()
        ->assertRedirect();

    expect(PlatformSetting::hasCustomLogo())->toBeFalse();
    Storage::disk('public')->assertMissing($logoPath);
});
