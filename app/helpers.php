<?php

/**
 * ============================================================================
 *  Global helper functions (autoloaded through composer.json "files").
 *  Laravel already provides e(), old(), url(), asset(), redirect() etc.,
 *  so only the project-specific formatting helpers live here.
 * ============================================================================
 */

use Illuminate\Support\Facades\Auth;

if (! function_exists('money')) {
    /**
     * Format an amount of money with the user's currency symbol,
     * e.g. money(1234.5) -> "£1,234.50".
     */
    function money(float $amount, ?string $symbol = null): string
    {
        $symbol ??= Auth::user()->currency ?? config('finance.default_currency');
        $sign = $amount < 0 ? '-' : '';

        return $sign.$symbol.number_format(abs($amount), 2);
    }
}

if (! function_exists('nice_date')) {
    /**
     * Format a date stored as YYYY-MM-DD into a friendly form, e.g. 08 Jul 2026.
     */
    function nice_date(string $date): string
    {
        $ts = strtotime($date);

        return $ts ? date('d M Y', $ts) : $date;
    }
}

if (! function_exists('flash')) {
    /**
     * Queue a one-request status banner (success / info / warning / error).
     * Several banners can be queued in the same request - e.g. "account
     * created" plus the on-screen demo verification code. The layouts render
     * them and public/js/app.js dismisses them after a few seconds.
     */
    function flash(string $type, string $text): void
    {
        $flashes = session()->get('flashes', []);
        $flashes[] = ['type' => $type, 'text' => $text];
        session()->flash('flashes', $flashes);
    }
}
