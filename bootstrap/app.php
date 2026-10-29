<?php

/**
 * ============================================================================
 *  bootstrap/app.php - builds the Laravel application instance.
 * ----------------------------------------------------------------------------
 *  This replaces the hand-written front controller of the previous version:
 *  Laravel wires the router, middleware pipeline and exception handler for
 *  us. Guests who hit a protected page are redirected to the login route,
 *  and logged-in users who open a guest page (login/register) are sent to
 *  the dashboard.
 * ============================================================================
 */

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Where the 'auth' middleware sends visitors who are not logged in
        $middleware->redirectGuestsTo(fn () => route('login'));

        // Where the 'guest' middleware sends users who are already logged in
        $middleware->redirectUsersTo(fn () => route('dashboard'));
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
