<?php

namespace App\Services\Tracking\Drivers;

use App\Models\Order;
use App\Services\SettingsRepository;
use App\Services\Tracking\Contracts\ServerTracker;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * TikTok Events API. Mirrors the browser Purchase event with a shared event_id
 * for deduplication. The access token stays server-side (encrypted settings).
 */
class TikTokServerTracker implements ServerTracker
{
    private const ENDPOINT = 'https://business-api.tiktok.com/open_api/v1.3/event/track/';

    public function __construct(private readonly SettingsRepository $settings) {}

    public function provider(): string
    {
        return 'tiktok';
    }

    public function enabled(): bool
    {
        return (bool) $this->settings->get('tracking_tiktok', 'capi_enabled', false)
            && ! empty($this->settings->get('tracking_tiktok', 'pixel_id'))
            && ! empty($this->settings->get('tracking_tiktok', 'access_token'));
    }

    public function sendPurchase(Order $order): bool
    {
        if (! $this->enabled()) {
            return false;
        }

        $pixelId = $this->settings->get('tracking_tiktok', 'pixel_id');
        $token = $this->settings->get('tracking_tiktok', 'access_token');

        $payload = [
            'event_source' => 'web',
            'event_source_id' => $pixelId,
            'data' => [[
                'event' => 'Purchase',
                'event_time' => $order->created_at->timestamp,
                'event_id' => 'order_'.$order->id,
                'user' => array_filter([
                    'phone' => $order->phone ? hash('sha256', preg_replace('/\D/', '', $order->phone)) : null,
                    'ip' => $order->ip_address,
                    'user_agent' => $order->user_agent,
                ]),
                'properties' => [
                    'currency' => $order->currency->value,
                    'value' => (float) $order->total,
                    'contents' => [[
                        'content_id' => (string) $order->product_id,
                        'content_name' => $order->product->name ?? null,
                        'quantity' => $order->quantity,
                    ]],
                ],
            ]],
        ];

        try {
            $response = Http::timeout(8)
                ->withHeaders(['Access-Token' => (string) $token])
                ->post(self::ENDPOINT, $payload);

            if ($response->failed() || ($response->json('code') ?? 0) !== 0) {
                Log::warning('TikTok Events API purchase failed', ['status' => $response->status(), 'order' => $order->id]);

                return false;
            }

            return true;
        } catch (\Throwable $e) {
            Log::warning('TikTok Events API purchase error', ['order' => $order->id, 'message' => $e->getMessage()]);

            return false;
        }
    }
}
