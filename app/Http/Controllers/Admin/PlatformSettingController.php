<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PlatformSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class PlatformSettingController extends Controller
{
    /**
     * Display platform & branding settings.
     */
    public function index(): View
    {
        return view('admin.settings.platform', [
            'platformName' => PlatformSetting::getName(),
            'platformTagline' => PlatformSetting::getTagline(),
            'platformLogo' => PlatformSetting::getLogoUrl(),
            'hasCustomLogo' => PlatformSetting::hasCustomLogo(),
            'platformFavicon' => PlatformSetting::getFaviconUrl(),
            'platformTimezone' => PlatformSetting::getTimezone(),
            'supportedTimezones' => PlatformSetting::supportedTimezones(),
            'currentTime' => \Carbon\Carbon::now(PlatformSetting::getTimezone())->format('H:i:s'),
            'currentTimezoneLabel' => PlatformSetting::getTimezoneLabel(),
        ]);
    }

    /**
     * Update platform & branding settings.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'platform_name' => ['required', 'string', 'max:100'],
            'platform_tagline' => ['nullable', 'string', 'max:150'],
            'platform_timezone' => ['nullable', 'string', 'in:Asia/Jakarta,Asia/Makassar,Asia/Jayapura,UTC'],
            'logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,svg,webp', 'max:2048'],
            'favicon' => ['nullable', 'file', 'mimes:png,ico,svg', 'max:1024'],
        ]);

        PlatformSetting::set('platform_name', trim($validated['platform_name']));
        PlatformSetting::set('platform_tagline', !empty($validated['platform_tagline']) ? trim($validated['platform_tagline']) : null);

        if ($request->filled('platform_timezone')) {
            PlatformSetting::set('platform_timezone', $validated['platform_timezone']);
        }

        // Handle Logo Upload
        if ($request->hasFile('logo')) {
            $oldLogo = PlatformSetting::get('platform_logo');
            if ($oldLogo && Storage::disk('public')->exists($oldLogo)) {
                Storage::disk('public')->delete($oldLogo);
            }

            $path = $request->file('logo')->store('branding', 'public');
            PlatformSetting::set('platform_logo', $path);
        }

        // Handle Favicon Upload
        if ($request->hasFile('favicon')) {
            $oldFavicon = PlatformSetting::get('platform_favicon');
            if ($oldFavicon && Storage::disk('public')->exists($oldFavicon)) {
                Storage::disk('public')->delete($oldFavicon);
            }

            $path = $request->file('favicon')->store('branding', 'public');
            PlatformSetting::set('platform_favicon', $path);
        }

        return back()->with('success', 'Platform branding settings updated successfully.');
    }

    /**
     * Reset logo to the default system logo.
     */
    public function resetLogo(): RedirectResponse
    {
        $oldLogo = PlatformSetting::get('platform_logo');
        if ($oldLogo && Storage::disk('public')->exists($oldLogo)) {
            Storage::disk('public')->delete($oldLogo);
        }

        PlatformSetting::remove('platform_logo');

        return back()->with('success', 'Platform logo reset to default system brand mark.');
    }

    /**
     * Reset favicon to default.
     */
    public function resetFavicon(): RedirectResponse
    {
        $oldFavicon = PlatformSetting::get('platform_favicon');
        if ($oldFavicon && Storage::disk('public')->exists($oldFavicon)) {
            Storage::disk('public')->delete($oldFavicon);
        }

        PlatformSetting::remove('platform_favicon');

        return back()->with('success', 'Platform favicon reset to default.');
    }
}
