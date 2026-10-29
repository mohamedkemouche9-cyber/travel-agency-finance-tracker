<?php

return [

    'name' => env('APP_NAME', 'Personal Finance Tracker'),

    'env' => env('APP_ENV', 'production'),

    'debug' => (bool) env('APP_DEBUG', false),

    'url' => env('APP_URL', 'http://localhost'),

    'timezone' => 'Europe/London',

    'locale' => 'en',
    'fallback_locale' => 'en',
    'faker_locale' => 'en_GB',

    'cipher' => 'AES-256-CBC',
    'key' => env('APP_KEY'),

    'previous_keys' => [
        ...array_filter(explode(',', (string) env('APP_PREVIOUS_KEYS', ''))),
    ],

    'maintenance' => [
        'driver' => 'file',
    ],

];
