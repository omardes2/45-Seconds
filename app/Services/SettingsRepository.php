<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;

/**
 * Thin typed accessor over the settings table. Values are cached per-group and
 * secrets (CAPI tokens) are transparently encrypted at rest.
 */
class SettingsRepository
{
    private const CACHE_PREFIX = 'settings:group:';

    /**
     * @return array<string, mixed>
     */
    public function group(string $group): array
    {
        return Cache::rememberForever(self::CACHE_PREFIX.$group, function () use ($group) {
            return Setting::query()
                ->where('group', $group)
                ->get()
                ->mapWithKeys(function (Setting $setting) {
                    $value = $setting->value;

                    if ($setting->is_encrypted && $value !== null) {
                        try {
                            $value = Crypt::decryptString($value);
                        } catch (\Throwable) {
                            $value = null;
                        }
                    }

                    return [$setting->key => $value];
                })
                ->all();
        });
    }

    public function get(string $group, string $key, mixed $default = null): mixed
    {
        return $this->group($group)[$key] ?? $default;
    }

    public function set(string $group, string $key, mixed $value, bool $encrypt = false): void
    {
        $stored = $value;

        if ($encrypt && $value !== null && $value !== '') {
            $stored = Crypt::encryptString((string) $value);
        }

        Setting::updateOrCreate(
            ['group' => $group, 'key' => $key],
            ['value' => $stored, 'is_encrypted' => $encrypt],
        );

        $this->forget($group);
    }

    /**
     * @param  array<string, mixed>  $values
     * @param  array<int, string>  $encryptedKeys
     */
    public function setMany(string $group, array $values, array $encryptedKeys = []): void
    {
        foreach ($values as $key => $value) {
            $this->set($group, $key, $value, in_array($key, $encryptedKeys, true));
        }
    }

    public function forget(string $group): void
    {
        Cache::forget(self::CACHE_PREFIX.$group);
    }
}
