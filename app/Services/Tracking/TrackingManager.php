<?php

namespace App\Services\Tracking;

use App\Models\Order;
use App\Services\SettingsRepository;
use App\Services\Tracking\Contracts\BrowserTracker;
use App\Services\Tracking\Contracts\ServerTracker;
use App\Services\Tracking\Drivers\MetaBrowserTracker;
use App\Services\Tracking\Drivers\MetaServerTracker;
use App\Services\Tracking\Drivers\TikTokBrowserTracker;
use App\Services\Tracking\Drivers\TikTokServerTracker;

/**
 * Central registry for tracking integrations. Adding Google Ads / Snapchat /
 * GA4 later means adding a driver to one of the arrays below — the public page
 * and order pipeline stay unchanged.
 */
class TrackingManager
{
    /** @var array<int, BrowserTracker> */
    private array $browserDrivers;

    /** @var array<int, ServerTracker> */
    private array $serverDrivers;

    public function __construct(SettingsRepository $settings)
    {
        $this->browserDrivers = [
            new MetaBrowserTracker($settings),
            new TikTokBrowserTracker($settings),
        ];

        $this->serverDrivers = [
            new MetaServerTracker($settings),
            new TikTokServerTracker($settings),
        ];
    }

    /**
     * @return array<int, BrowserTracker>
     */
    public function enabledBrowserDrivers(): array
    {
        return array_values(array_filter($this->browserDrivers, fn (BrowserTracker $d) => $d->enabled()));
    }

    public function hasBrowserTracking(): bool
    {
        return $this->enabledBrowserDrivers() !== [];
    }

    /**
     * Concatenated pixel base scripts for the <head>.
     */
    public function baseScripts(): string
    {
        return collect($this->enabledBrowserDrivers())
            ->map(fn (BrowserTracker $d) => $d->baseScript())
            ->filter()
            ->implode("\n");
    }

    /**
     * provider => internal-to-vendor event map, for the JS dispatcher.
     *
     * @return array<string, array<string, string>>
     */
    public function eventMaps(): array
    {
        $maps = [];
        foreach ($this->enabledBrowserDrivers() as $driver) {
            $maps[$driver->provider()] = $driver->eventMap();
        }

        return $maps;
    }

    /**
     * Fire the server-side Purchase across every enabled CAPI driver.
     *
     * @return array<string, bool> provider => success
     */
    public function sendServerPurchase(Order $order): array
    {
        $results = [];
        foreach ($this->serverDrivers as $driver) {
            if ($driver->enabled()) {
                $results[$driver->provider()] = $driver->sendPurchase($order);
            }
        }

        return $results;
    }

    public function hasServerTracking(): bool
    {
        foreach ($this->serverDrivers as $driver) {
            if ($driver->enabled()) {
                return true;
            }
        }

        return false;
    }
}
