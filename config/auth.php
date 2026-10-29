<?php

return [

    'defaults' => [
        'guard' => 'web',
        'passwords' => 'users',
    ],

    /*
     * One session-based guard backed by the Eloquent User model - exactly
     * the classic "logged-in user stored in the session" pattern, but
     * implemented by the framework instead of hand-written code.
     */
    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],
    ],

    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => App\Models\User::class,
        ],
    ],

    /*
     * Laravel's e-mail-link password broker is NOT used: this application
     * resets passwords with its own 6-digit code flow (see AuthController),
     * which works fully offline in demo mode.
     */
    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table' => 'password_reset_tokens',
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    'password_timeout' => 10800,

];
