<?php

namespace App\Listeners;

use App\Events\OrderCreated;
use App\Jobs\SendServerConversion;
use App\Services\Tracking\TrackingManager;

class QueueServerConversion
{
    public function __construct(private readonly TrackingManager $tracking) {}

    public function handle(OrderCreated $event): void
    {
        file_put_contents(storage_path('qsc.log'), 'FIRE has='.var_export($this->tracking->hasServerTracking(), true)."\n", FILE_APPEND);
        if ($this->tracking->hasServerTracking()) {
            SendServerConversion::dispatch($event->order->id);
        }
    }
}
