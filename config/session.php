<?php

return [

    // File-based sessions keep the XAMPP setup simple (no extra table)
    'driver' => env('SESSION_DRIVER', 'file'),

    'lifetime' => (int) env('SESSION_LIFETIME', 120),
    'expire_on_close' => false,

    'encrypt' => false,

    'files' => storage_path('framework/sessions'),

    'lottery' => [2, 100],

    'cookie' => env('SESSION_COOKIE', 'finance_tracker_session'),
    'path' => '/',
    'domain' => env('SESSION_DOMAIN'),
    'secure' => env('SESSION_SECURE_COOKIE', false),

    // The session cookie is not readable by JavaScript (XSS mitigation)
    'http_only' => true,

    // Lax blocks the cookie on cross-site POSTs (CSRF mitigation)
    'same_site' => 'lax',

];
