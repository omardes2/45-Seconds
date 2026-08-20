<?php

use App\Enums\CurrencyEnum;

if (! function_exists('money')) {
    /**
     * Format a monetary amount with its currency symbol (RTL-safe).
     */
    function money(string|float|int|null $amount, CurrencyEnum|string|null $currency = null): string
    {
        $currency = $currency instanceof CurrencyEnum
            ? $currency
            : (CurrencyEnum::tryFrom((string) ($currency ?? config('fortyfive.default_currency'))) ?? CurrencyEnum::ILS);

        return $currency->format($amount ?? 0);
    }
}
