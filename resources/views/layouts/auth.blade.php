{{--
    Layout: guest pages (login, register, verify, forgot, reset).
    A split screen: brand panel on the left, the form card on the right.
    Demo verification codes appear in the flash stack for a few seconds.
--}}
@php $flashes = session('flashes', []); @endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $pageTitle ?? 'Welcome' }} · {{ config('app.name') }}</title>
    <link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'%3E%3Ctext y='.9em' font-size='90'%3E%F0%9F%92%B7%3C/text%3E%3C/svg%3E">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body class="auth-shell">

    <section class="auth-brand">
        <div class="auth-brand-inner">
            <a class="brand" href="{{ route('login') }}">
                <span class="brand-mark">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12V7H5a2 2 0 0 1 0-4h14v4"/><path d="M3 5v14a2 2 0 0 0 2 2h16v-5"/><path d="M18 12a2 2 0 0 0 0 4h4v-4Z"/></svg>
                </span>
                <span class="brand-name">Finance<strong>Tracker</strong></span>
            </a>

            <h1>Take control of your&nbsp;money.</h1>
            <p class="auth-tagline">
                Track income and spending, set monthly budgets and let the
                built-in forecast show where your finances are heading.
            </p>

            <div class="auth-points">
                <div class="auth-point">Secure account with on-screen demo verification</div>
                <div class="auth-point">Clear charts of your cash flow</div>
                <div class="auth-point">3-month financial forecast &amp; what-if simulator</div>
            </div>
        </div>
    </section>

    <section class="auth-panel">
        @if ($flashes)
            <div class="flash-stack">
                @foreach ($flashes as $f)
                    <div class="flash flash-{{ $f['type'] }}" role="alert">
                        <span>{{ $f['text'] }}</span>
                        <button class="flash-close" aria-label="Dismiss">&times;</button>
                    </div>
                @endforeach
            </div>
        @endif

        <div class="auth-card">
            @yield('content')
        </div>
    </section>

    <script src="{{ asset('js/app.js') }}"></script>
    <script src="{{ asset('js/validate.js') }}"></script>
</body>
</html>
