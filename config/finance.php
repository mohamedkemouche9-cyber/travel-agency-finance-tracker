<?php

/**
 * ============================================================================
 *  Application-specific settings for the Personal Finance Tracker.
 * ----------------------------------------------------------------------------
 *  Verification / password-reset codes are DEMO ONLY: no e-mail is ever
 *  sent. The 6-digit code is flashed on screen for a few seconds (and
 *  written to the application log) so the whole flow can be demonstrated
 *  completely offline.
 * ============================================================================
 */

return [
    // Private, locally selected password used only when running demo seeders.
    'demo_password' => env('DEMO_PASSWORD'),

    // Default currency symbol for new accounts (changeable in Settings)
    'default_currency' => '£',

    // Currency symbols offered in the Settings form
    'currencies' => ['£', '$', '€', 'DZD ', '¥', '₹'],

    // Lifetime of a 6-digit code, in minutes
    'code_ttl_minutes' => 15,

    // Wrong guesses allowed before a code becomes void
    'code_max_attempts' => 5,

    // Seconds between "resend code" requests
    'resend_cooldown' => 60,

    // Failed logins before an account is temporarily locked
    'login_max_attempts' => 5,

    // Duration of the login lock, in minutes
    'login_lock_minutes' => 15,

];
