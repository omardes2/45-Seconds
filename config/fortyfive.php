<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default currency
    |--------------------------------------------------------------------------
    | Used as a fallback before a product/offer supplies its own currency and
    | for dashboard revenue formatting. Overridable from Settings -> General.
    */
    'default_currency' => env('FORTYFIVE_DEFAULT_CURRENCY', 'ILS'),

    /*
    |--------------------------------------------------------------------------
    | Order numbers
    |--------------------------------------------------------------------------
    | Human-readable order numbers are generated as {prefix}{seq} where seq is
    | a zero-padded incrementing counter starting at `start`. e.g. 450001.
    */
    'order_number' => [
        'prefix' => env('FORTYFIVE_ORDER_PREFIX', '45'),
        'start' => 10000,
        'pad' => 4,
    ],

    /*
    |--------------------------------------------------------------------------
    | Public checkout rate limiting
    |--------------------------------------------------------------------------
    | Max order submissions per IP within the decay window (seconds).
    */
    'checkout_rate_limit' => [
        'max_attempts' => env('FORTYFIVE_CHECKOUT_MAX', 8),
        'decay_seconds' => env('FORTYFIVE_CHECKOUT_DECAY', 60),
    ],

    /*
    |--------------------------------------------------------------------------
    | Visitor cookie
    |--------------------------------------------------------------------------
    */
    'visitor_cookie' => '45s_vid',
    'visitor_cookie_days' => 180,
];
