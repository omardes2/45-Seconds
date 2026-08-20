<?php

namespace Database\Seeders;

use App\Services\SettingsRepository;
use Illuminate\Database\Seeder;

class GeneralSettingsSeeder extends Seeder
{
    public function run(SettingsRepository $settings): void
    {
        $settings->set('general', 'site_name', '45 Seconds');
        $settings->set('general', 'default_currency', config('fortyfive.default_currency', 'ILS'));

        // Tracking integrations default to disabled until configured.
        $settings->setMany('tracking_meta', [
            'enabled' => false,
            'pixel_id' => null,
            'capi_enabled' => false,
        ]);
        $settings->setMany('tracking_tiktok', [
            'enabled' => false,
            'pixel_id' => null,
            'capi_enabled' => false,
        ]);
    }
}
