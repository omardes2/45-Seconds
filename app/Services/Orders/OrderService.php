<?php

namespace App\Services\Orders;

use App\Enums\AuditAction;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Events\OrderCreated;
use App\Models\LandingPage;
use App\Models\Offer;
use App\Models\Order;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderService
{
    public function __construct(private readonly AuditLogger $audit) {}

    /**
     * Create an order for a landing page. The price is ALWAYS recomputed from
     * the offer row in the database — nothing monetary is trusted from the
     * client. The caller only supplies the chosen offer id and customer data.
     *
     * @param  array<string, mixed>  $customer  full_name, phone, city, area, address, notes
     * @param  array<string, mixed>  $meta  visitor_id, session_id, user_agent, ip_address
     * @param  array<string, mixed>  $attribution  UTM / click ids / referrer snapshot
     */
    public function create(LandingPage $page, Offer $offer, array $customer, array $meta = [], array $attribution = []): Order
    {
        // Defensive re-check: the offer must belong to this page and be active.
        if ($offer->landing_page_id !== $page->id || ! $offer->is_active) {
            throw ValidationException::withMessages(['offer_id' => 'العرض غير متاح.']);
        }

        $page->loadMissing('product');
        $currency = $page->product->currency;

        // --- Money computed server-side from the DB offer only ---
        $quantity = max(1, (int) $offer->quantity);
        $total = round((float) $offer->price, 2);
        $unitPrice = round($total / $quantity, 2);

        return DB::transaction(function () use ($page, $offer, $customer, $meta, $attribution, $currency, $quantity, $unitPrice, $total) {
            $order = Order::create([
                'order_number' => 'PENDING', // replaced below using the id
                'landing_page_id' => $page->id,
                'product_id' => $page->product_id,
                'offer_id' => $offer->id,
                'full_name' => $customer['full_name'],
                'phone' => $customer['phone'],
                'city' => $customer['city'],
                'area' => $customer['area'] ?? null,
                'address' => $customer['address'],
                'notes' => $customer['notes'] ?? null,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'subtotal' => $total,
                'total' => $total,
                'currency' => $currency,
                'payment_method' => PaymentMethod::CashOnDelivery,
                'status' => OrderStatus::New,
                'visitor_id' => $meta['visitor_id'] ?? null,
                'session_id' => $meta['session_id'] ?? null,
                'user_agent' => $meta['user_agent'] ?? null,
                'ip_address' => $meta['ip_address'] ?? null,
            ]);

            $order->update(['order_number' => $this->makeOrderNumber($order->id)]);

            $order->statusHistories()->create([
                'from_status' => null,
                'to_status' => OrderStatus::New,
                'user_id' => null,
                'note' => 'تم إنشاء الطلب',
            ]);

            // Snapshot the marketing attribution at order time so it survives
            // even after the visitor's session data is gone.
            $order->attribution()->create([
                'landing_page_id' => $page->id,
                'visitor_id' => $attribution['visitor_id'] ?? ($meta['visitor_id'] ?? null),
                'session_id' => $attribution['session_id'] ?? ($meta['session_id'] ?? null),
                'utm_source' => $attribution['utm_source'] ?? null,
                'utm_medium' => $attribution['utm_medium'] ?? null,
                'utm_campaign' => $attribution['utm_campaign'] ?? null,
                'utm_content' => $attribution['utm_content'] ?? null,
                'utm_term' => $attribution['utm_term'] ?? null,
                'fbclid' => $attribution['fbclid'] ?? null,
                'ttclid' => $attribution['ttclid'] ?? null,
                'referrer' => $attribution['referrer'] ?? null,
            ]);

            OrderCreated::dispatch($order->fresh(['attribution']));

            return $order;
        });
    }

    /**
     * Change an order's status with transition validation and history.
     */
    public function changeStatus(Order $order, OrderStatus $to, ?User $user = null, ?string $note = null): Order
    {
        $from = $order->status;

        if ($from === $to) {
            return $order;
        }

        if (! $from->canTransitionTo($to)) {
            throw ValidationException::withMessages([
                'status' => "لا يمكن الانتقال من «{$from->label()}» إلى «{$to->label()}».",
            ]);
        }

        DB::transaction(function () use ($order, $from, $to, $user, $note) {
            $order->update(['status' => $to]);
            $order->statusHistories()->create([
                'from_status' => $from,
                'to_status' => $to,
                'user_id' => $user?->getKey(),
                'note' => $note,
            ]);
        });

        $this->audit->log(AuditAction::OrderStatusChanged, $order, [
            'from' => $from->value,
            'to' => $to->value,
        ], $user);

        return $order->refresh();
    }

    private function makeOrderNumber(int $id): string
    {
        $config = config('fortyfive.order_number');
        $sequence = $id + ($config['start'] ?? 0);

        return $config['prefix'].str_pad((string) $sequence, $config['pad'] ?? 4, '0', STR_PAD_LEFT);
    }
}
