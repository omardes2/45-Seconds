<?php

namespace App\Services\Tracking\Drivers;

use App\Models\Order;
use App\Services\SettingsRepository;
use App\Services\Tracking\Contracts\ServerTracker;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Meta Conversions API. Fires the server-side Purchase with the SAME event_id
 * (order_{id}) as the browser pixel so Meta deduplicates. The access token is
 * read from encrypted settings and is never logged or sent to the client.
 */
class MetaServerTracker implements ServerTracker
{
    private const API_VERSION = 'v21.0';

    public function __construct(private readonly SettingsRepository $settings) {}

    public function provider(): string
    {
        return 'meta';
    }

    public function enabled(): bool
    {
        return (bool) $this->settings->get('tracking_meta', 'capi_enabled', false)
            && ! empty($this->settings->get('tracking_meta', 'pixel_id'))
            && ! empty($this->settings->get('tracking_meta', 'access_token'));
    }

    public function sendPurchase(Order $order): bool
    {
        if (! $this->enabled()) {
            return false;
        }

        $pixelId = $this->settings->get('tracking_meta', 'pixel_id');
        $token = $this->settings->get('tracking_meta', 'access_token');
        $testCode = $this->settings->get('tracking_meta', 'test_event_code');

        $payload = [
            'data' => [[
                'event_name' => 'Purchase',
                'event_time' => $order->created_at->timestamp,
                'event_id' => 'order_'.$order->id,
                'action_source' => 'website',
                'user_data' => array_filter([
                    'ph' => $order->phone ? [hash('sha256', preg_replace('/\D/', '', $order->phone))] : null,
                    'client_ip_address' => $order->ip_address,
                    'client_user_agent' => $order->user_agent,
                ]),
                'custom_data' => [
                    'currency' => $order->currency->value,
                    'value' => (float) $order->total,
                    'content_ids' => [(string) $order->product_id],
                    'content_name' => $order->product->name ?? null,
                    'num_items' => $order->quantity,
                ],
            ]],
        ];

        if ($testCode) {
            $payload['test_event_code'] = $testCode;
        }

        try {
            $response = Http::timeout(8)->post(
                'https://graph.facebook.com/'.self::API_VERSION."/{$pixelId}/events?access_token=".urlencode((string) $token),
                $payload,
            );

            if ($response->failed()) {
                // Log status only — never the token or full URL.
                Log::warning('Meta CAPI purchase failed', ['status' => $response->status(), 'order' => $order->id]);

                return false;
            }

            return true;
        } catch (\Throwable $e) {
            Log::warning('Meta CAPI purchase error', ['order' => $order->id, 'message' => $e->getMessage()]);

            return false;
        }
    }
}
