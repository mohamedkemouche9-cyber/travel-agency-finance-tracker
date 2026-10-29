{{--
    Layout: authenticated application shell (sidebar + topbar + content).
    Pages extend this layout and fill the 'content' section.
--}}
@php
    $flashes = session('flashes', []);
    $active = request()->segment(1) ?: 'dashboard';
    $initial = strtoupper(mb_substr(auth()->user()->username ?? '?', 0, 1));
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $pageTitle ?? 'Home' }} · {{ config('app.name') }}</title>
    <link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'%3E%3Ctext y='.9em' font-size='90'%3E%F0%9F%92%B7%3C/text%3E%3C/svg%3E">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <script>
        /* Values shared with the JavaScript layer */
        window.APP = {
            base:     '{{ rtrim(url('/'), '/') }}',
            currency: '{{ auth()->user()->currency ?? config('finance.default_currency') }}'
        };
    </script>
</head>
<body class="app-shell">

    {{-- ============================ SIDEBAR ============================ --}}
    <aside class="sidebar" id="sidebar">
        <a class="brand" href="{{ route('dashboard') }}">
            <span class="brand-mark">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12V7H5a2 2 0 0 1 0-4h14v4"/><path d="M3 5v14a2 2 0 0 0 2 2h16v-5"/><path d="M18 12a2 2 0 0 0 0 4h4v-4Z"/></svg>
            </span>
            <span class="brand-name">Finance<strong>Tracker</strong></span>
        </a>

        <nav class="side-nav" aria-label="Main navigation">
            <a href="{{ route('dashboard') }}" class="{{ $active === 'dashboard' ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="9" rx="1"/><rect x="14" y="3" width="7" height="5" rx="1"/><rect x="14" y="12" width="7" height="9" rx="1"/><rect x="3" y="16" width="7" height="5" rx="1"/></svg>
                Dashboard
            </a>
            <a href="{{ route('transactions') }}" class="{{ $active === 'transactions' ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M8 6h13"/><path d="M8 12h13"/><path d="M8 18h13"/><path d="M3 6h.01"/><path d="M3 12h.01"/><path d="M3 18h.01"/></svg>
                Transactions
            </a>
            <a href="{{ route('categories') }}" class="{{ $active === 'categories' ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2H2v10l9.29 9.29a1 1 0 0 0 1.42 0l8.58-8.58a1 1 0 0 0 0-1.42Z"/><circle cx="7" cy="7" r="1.5"/></svg>
                Categories
            </a>
            <a href="{{ route('budgets') }}" class="{{ $active === 'budgets' ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="6"/><circle cx="12" cy="12" r="2"/></svg>
                Budgets
            </a>
            <a href="{{ route('settings') }}" class="{{ $active === 'settings' ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/><circle cx="12" cy="12" r="3"/></svg>
                Settings
            </a>
        </nav>

        <div class="side-footer">
            <div class="user-chip">
                <span class="avatar">{{ $initial }}</span>
                <span class="user-meta">
                    <span class="user-name">{{ auth()->user()->username ?? '' }}</span>
                    <span class="user-mail">{{ auth()->user()->email ?? '' }}</span>
                </span>
            </div>
            <form method="post" action="{{ route('logout') }}" class="logout-form">
                @csrf
                <button type="submit" class="logout-btn" title="Log out">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                    Log out
                </button>
            </form>
        </div>
    </aside>
    <div class="sidebar-backdrop" id="sidebarBackdrop"></div>

    {{-- ============================= MAIN ============================== --}}
    <div class="main-area">
        <header class="topbar">
            <button class="icon-btn menu-btn" id="menuBtn" aria-label="Open menu">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="4" y1="6" x2="20" y2="6"/><line x1="4" y1="12" x2="20" y2="12"/><line x1="4" y1="18" x2="20" y2="18"/></svg>
            </button>
            <span class="topbar-title">{{ $pageTitle ?? '' }}</span>
            <span class="topbar-date">{{ date('l, d F Y') }}</span>
        </header>

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

        <main class="content">
            @yield('content')
        </main>
    </div>

    <script src="{{ asset('js/app.js') }}"></script>
    @stack('scripts')
</body>
</html>
