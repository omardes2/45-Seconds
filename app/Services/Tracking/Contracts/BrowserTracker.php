<?php

namespace App\Services\Tracking\Contracts;

/**
 * A client-side (pixel) tracking integration. New ad platforms (Google Ads,
 * Snapchat, GA4…) are added by implementing this contract and registering the
 * driver in TrackingManager — no changes to the public page or dispatcher.
 */
interface BrowserTracker
{
    public function provider(): string;

    public function enabled(): bool;

    public function pixelId(): ?string;

    /**
     * Raw JS that boots the pixel and fires its automatic PageView.
     * Returns '' when the integration is disabled.
     */
    public function baseScript(): string;

    /**
     * Map of internal event names to this vendor's event names.
     * e.g. ['ViewContent' => 'ViewContent', 'Purchase' => 'Purchase'].
     *
     * @return array<string, string>
     */
    public function eventMap(): array;
}
