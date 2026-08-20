<?php

namespace App\Services\Tracking\Drivers;

use App\Services\SettingsRepository;
use App\Services\Tracking\Contracts\BrowserTracker;

class MetaBrowserTracker implements BrowserTracker
{
    public function __construct(private readonly SettingsRepository $settings) {}

    public function provider(): string
    {
        return 'meta';
    }

    public function enabled(): bool
    {
        return (bool) $this->settings->get('tracking_meta', 'enabled', false)
            && ! empty($this->pixelId());
    }

    public function pixelId(): ?string
    {
        $id = $this->settings->get('tracking_meta', 'pixel_id');

        return $id ? (string) $id : null;
    }

    public function baseScript(): string
    {
        if (! $this->enabled()) {
            return '';
        }

        // Pixel ID is injected from settings — never hardcoded.
        $pixelId = json_encode($this->pixelId());

        return <<<JS
        !function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?
        n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;
        n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;
        t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,
        document,'script','https://connect.facebook.net/en_US/fbevents.js');
        fbq('init', {$pixelId});
        fbq('track', 'PageView');
        JS;
    }

    public function eventMap(): array
    {
        return [
            'ViewContent' => 'ViewContent',
            'InitiateCheckout' => 'InitiateCheckout',
            'Purchase' => 'Purchase',
        ];
    }
}
