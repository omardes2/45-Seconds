<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AuditAction;
use App\Enums\CurrencyEnum;
use App\Http\Controllers\Controller;
use App\Services\AuditLogger;
use App\Services\SettingsRepository;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function __construct(
        private readonly SettingsRepository $settings,
        private readonly AuditLogger $audit,
    ) {}

    public function edit(): View
    {
        return view('admin.settings.edit', [
            'general' => $this->settings->group('general'),
            'currencies' => CurrencyEnum::options(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = Validator::make($request->all(), [
            'site_name' => ['required', 'string', 'max:255'],
            'default_currency' => ['required', 'string', 'in:'.implode(',', array_keys(CurrencyEnum::options()))],
            'logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp,svg', 'max:2048'],
            'default_city' => ['nullable', 'string', 'max:255'],
        ])->validate();

        $this->settings->set('general', 'site_name', $data['site_name']);
        $this->settings->set('general', 'default_currency', $data['default_currency']);
        $this->settings->set('general', 'default_city', $data['default_city'] ?? null);

        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('branding', 'public');
            $this->settings->set('general', 'logo', $path);
        }

        $this->audit->log(AuditAction::SettingsUpdated, null, ['group' => 'general']);

        return back()->with('success', 'تم حفظ الإعدادات.');
    }
}
