<?php

namespace App\Models;

use App\Enums\CurrencyEnum;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use Database\Factories\OrderFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    /** @use HasFactory<OrderFactory> */
    use HasFactory;

    protected $fillable = [
        'order_number',
        'landing_page_id',
        'product_id',
        'offer_id',
        'full_name',
        'phone',
        'city',
        'area',
        'address',
        'notes',
        'options',
        'quantity',
        'unit_price',
        'subtotal',
        'total',
        'currency',
        'payment_method',
        'status',
        'visitor_id',
        'session_id',
        'user_agent',
        'ip_address',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'options' => 'array',
            'unit_price' => 'decimal:2',
            'subtotal' => 'decimal:2',
            'total' => 'decimal:2',
            'currency' => CurrencyEnum::class,
            'status' => OrderStatus::class,
            'payment_method' => PaymentMethod::class,
        ];
    }

    public function landingPage(): BelongsTo
    {
        return $this->belongsTo(LandingPage::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function offer(): BelongsTo
    {
        return $this->belongsTo(Offer::class);
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class)->latest();
    }

    public function attribution(): HasOne
    {
        return $this->hasOne(OrderAttribution::class);
    }
}
