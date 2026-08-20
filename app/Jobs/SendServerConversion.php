<?php

namespace App\Jobs;

use App\Models\Order;
use App\Services\Tracking\TrackingManager;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

/**
 * Sends the server-side Purchase (Meta CAPI / TikTok Events API) for an order.
 * Queued so it never blocks the checkout response. No-ops when no server
 * tracking is configured.
 */
class SendServerConversion implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(public readonly int $orderId) {}

    public function handle(TrackingManager $tracking): void
    {
        $order = Order::with('product')->find($this->orderId);

        if (! $order || ! $tracking->hasServerTracking()) {
            return;
        }

        $tracking->sendServerPurchase($order);
    }
}
