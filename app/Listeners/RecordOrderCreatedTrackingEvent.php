<?php

namespace App\Listeners;

use App\Enums\TrackingEventType;
use App\Events\OrderCreated;
use App\Services\Tracking\VisitorTracker;

class RecordOrderCreatedTrackingEvent
{
    public function __construct(private readonly VisitorTracker $tracker) {}

    public function handle(OrderCreated $event): void
    {
        $order = $event->order;

        $this->tracker->recordEvent(
            type: TrackingEventType::OrderCreated,
            page: $order->landingPage,
            visitorId: $order->visitor_id,
            sessionId: $order->session_id,
            metadata: [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'value' => (float) $order->total,
                'currency' => $order->currency->value,
                'quantity' => $order->quantity,
            ],
            // Deterministic id shared by the browser Purchase pixel and CAPI
            // for deduplication (Sprint 7).
            eventId: 'order_'.$order->id,
        );
    }
}
