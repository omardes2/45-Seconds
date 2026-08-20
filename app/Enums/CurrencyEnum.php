<?php

namespace App\Enums;

enum CurrencyEnum: string
{
    case ILS = 'ILS';
    case USD = 'USD';

    public function label(): string
    {
        return match ($this) {
            self::ILS => 'شيكل',
            self::USD => 'دولار',
        };
    }

    /**
     * Symbol placed next to the amount in public / admin UI.
     */
    public function symbol(): string
    {
        return match ($this) {
            self::ILS => '₪',
            self::USD => '$',
        };
    }

    /**
     * Number of minor-unit decimals used when formatting money.
     */
    public function decimals(): int
    {
        return 2;
    }

    public function format(string|float|int $amount): string
    {
        $number = number_format((float) $amount, $this->decimals(), '.', ',');

        return $this->symbol().' '.$number;
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
