<?php

namespace App\Enums;

enum OrderStatus: string
{
    case New = 'new';
    case Contacted = 'contacted';
    case Confirmed = 'confirmed';
    case Preparing = 'preparing';
    case Shipped = 'shipped';
    case Delivered = 'delivered';
    case Cancelled = 'cancelled';
    case Returned = 'returned';

    public function label(): string
    {
        return match ($this) {
            self::New => 'جديد',
            self::Contacted => 'تم التواصل',
            self::Confirmed => 'مؤكد',
            self::Preparing => 'جاري التجهيز',
            self::Shipped => 'تم الشحن',
            self::Delivered => 'تم التسليم',
            self::Cancelled => 'ملغي',
            self::Returned => 'مرتجع',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::New => 'sky',
            self::Contacted => 'indigo',
            self::Confirmed => 'violet',
            self::Preparing => 'amber',
            self::Shipped => 'cyan',
            self::Delivered => 'emerald',
            self::Cancelled => 'rose',
            self::Returned => 'orange',
        };
    }

    /**
     * A delivered order counts as a completed conversion / realised revenue.
     */
    public function isRevenue(): bool
    {
        return ! in_array($this, [self::Cancelled, self::Returned], true);
    }

    public function isTerminal(): bool
    {
        return in_array($this, [self::Delivered, self::Cancelled, self::Returned], true);
    }

    /**
     * Statuses an operator may transition to from the current one.
     * Kept permissive but forbids leaving a terminal state except Delivered->Returned.
     *
     * @return array<int, self>
     */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::New => [self::Contacted, self::Confirmed, self::Cancelled],
            self::Contacted => [self::Confirmed, self::Cancelled],
            self::Confirmed => [self::Preparing, self::Cancelled],
            self::Preparing => [self::Shipped, self::Cancelled],
            self::Shipped => [self::Delivered, self::Returned],
            self::Delivered => [self::Returned],
            self::Cancelled => [],
            self::Returned => [],
        };
    }

    public function canTransitionTo(self $to): bool
    {
        return $to === $this || in_array($to, $this->allowedTransitions(), true);
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $c) => [$c->value => $c->label()])
            ->all();
    }
}
