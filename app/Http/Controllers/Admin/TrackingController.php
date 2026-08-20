<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AuditAction;
use App\Http\Controllers\Controller;
use App\Services\AuditLogger;
use App\Services\SettingsRepository;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TrackingController extends Controller
{
    public function __construct(
        private readonly SettingsRepository $settings,
        private readonly AuditLogger $audit,
    ) {}

    public function edit(): View
    {
        return view('admin.tracking.edit', [
            'meta' => $this->settings->group('tracking_meta'),
            'tiktok' => $this->settings->group('tracking_tiktok'),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'meta_enabled' => ['nullable', 'boolean'],
            'meta_pixel_id' => ['nullable', 'string', 'max:64', 'regex:/^[0-9]+$/'],
            'meta_capi_enabled' => ['nullable', 'boolean'],
            'meta_access_token' => ['nullable', 'string', 'max:512'],
            'meta_test_event_code' => ['nullable', 'string', 'max:64'],

            'tiktok_enabled' => ['nullable', 'boolean'],
            'tiktok_pixel_id' => ['nullable', 'string', 'max:64'],
            'tiktok_capi_enabled' => ['nullable', 'boolean'],
            'tiktok_access_token' => ['nullable', 'string', 'max:512'],
        ], [
            'meta_pixel_id.regex' => 'معرف Meta Pixel يجب أن يكون أرقامًا فقط.',
        ]);

        $this->settings->setMany('tracking_meta', [
            'enabled' => (bool) ($validated['meta_enabled'] ?? false),
            'pixel_id' => $validated['meta_pixel_id'] ?? null,
            'capi_enabled' => (bool) ($validated['meta_capi_enabled'] ?? false),
            'test_event_code' => $validated['meta_test_event_code'] ?? null,
        ]);

        // Only overwrite the secret token when a new value is supplied.
        if ($request->filled('meta_access_token')) {
            $this->settings->set('tracking_meta', 'access_token', $validated['meta_access_token'], encrypt: true);
        }

        $this->settings->setMany('tracking_tiktok', [
            'enabled' => (bool) ($validated['tiktok_enabled'] ?? false),
            'pixel_id' => $validated['tiktok_pixel_id'] ?? null,
            'capi_enabled' => (bool) ($validated['tiktok_capi_enabled'] ?? false),
        ]);

        if ($request->filled('tiktok_access_token')) {
            $this->settings->set('tracking_tiktok', 'access_token', $validated['tiktok_access_token'], encrypt: true);
        }

        $this->audit->log(AuditAction::TrackingUpdated, null, [
            'meta_enabled' => (bool) ($validated['meta_enabled'] ?? false),
            'tiktok_enabled' => (bool) ($validated['tiktok_enabled'] ?? false),
        ]);

        return back()->with('success', 'تم حفظ إعدادات التتبع.');
    }
}
