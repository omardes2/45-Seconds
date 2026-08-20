<?php

namespace App\Services\Tracking\Contracts;

use App\Models\Order;

/**
 * A server-side (Conversions API) tracking integration. Kept separate from the
 * browser pixel so the same platform can share an event_id for deduplication.
 * Secrets are read from encrypted settings and never leave the server.
 */
interface ServerTracker
{
    public function provider(): string;

    public function enabled(): bool;

    /**
     * Send the server-side Purchase conversion for an order.
     * MUST NOT log the access token. Returns true on success.
     */
    public function sendPurchase(Order $order): bool;
}
