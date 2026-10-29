{{--
    View: settings - profile, password, demo-code info, danger zone.
    Receives $currencies.
--}}
@extends('layouts.app')

@section('content')
<div class="page-head">
    <div>
        <h1>Settings</h1>
        <p class="page-sub">Manage your profile and account security.</p>
    </div>
</div>

<div class="grid-2">
    {{-- ============================ PROFILE =========================== --}}
    <div class="card">
        <div class="card-head"><h3>Profile</h3></div>
        <form method="post" action="{{ route('settings.profile') }}" data-validate novalidate>
            @csrf

            <div class="form-group">
                <label for="username">Username</label>
                <input class="input" type="text" id="username" name="username"
                       value="{{ auth()->user()->username }}" required
                       minlength="3" maxlength="20" pattern="[A-Za-z0-9_]+">
            </div>

            <div class="form-group">
                <label>E-mail address</label>
                <input class="input" type="email" value="{{ auth()->user()->email }}" disabled>
                <span class="hint">The e-mail address is verified and cannot be changed.</span>
            </div>

            <div class="form-group">
                <label for="currency">Currency symbol</label>
                <select class="input" id="currency" name="currency">
                    @foreach ($currencies as $c)
                        <option value="{{ $c }}" {{ auth()->user()->currency === $c ? 'selected' : '' }}>
                            {{ trim($c) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <button class="btn btn-primary" type="submit">Save profile</button>
        </form>
    </div>

    {{-- =========================== PASSWORD =========================== --}}
    <div class="card">
        <div class="card-head"><h3>Change password</h3></div>
        <form method="post" action="{{ route('settings.password') }}" data-validate novalidate>
            @csrf

            <div class="form-group">
                <label for="current_password">Current password</label>
                <input class="input" type="password" id="current_password" name="current_password"
                       required autocomplete="current-password">
            </div>

            <div class="form-group">
                <label for="new_password">New password</label>
                <div class="input-wrap">
                    <input class="input" type="password" id="new_password" name="new_password"
                           required minlength="8" maxlength="72" autocomplete="new-password"
                           data-hint="At least 8 characters with letters and numbers">
                    <button type="button" class="peek-btn" data-peek="new_password" aria-label="Show password">Show</button>
                </div>
                <div class="pw-meter" aria-hidden="true"><span></span></div>
            </div>

            <div class="form-group">
                <label for="new_password_confirmation">Confirm new password</label>
                <input class="input" type="password" id="new_password_confirmation" name="new_password_confirmation"
                       required maxlength="72" autocomplete="new-password">
            </div>

            <button class="btn btn-primary" type="submit">Change password</button>
        </form>
    </div>
</div>

<div class="grid-2">
    {{-- ======================= CODE DELIVERY ========================== --}}
    <div class="card">
        <div class="card-head"><h3>Verification codes</h3></div>
        <p class="muted">
            This build delivers verification and password-reset codes in
            <strong>demo mode only</strong>: no e-mail is ever sent.
        </p>
        <p class="muted">
            The 6-digit code appears as a banner at the top of the page for a
            few seconds and is also written to
            <code>storage/logs/laravel.log</code>, so the whole flow can be
            demonstrated completely offline.
        </p>
    </div>

    {{-- ========================= DANGER ZONE ========================== --}}
    <div class="card danger-card">
        <div class="card-head"><h3>Danger zone</h3></div>
        <p class="muted">
            Deleting your account permanently removes your profile and ALL your
            transactions, categories and budgets. This cannot be undone.
        </p>
        <form method="post" action="{{ route('settings.delete') }}" class="js-confirm"
              data-confirm="Really delete your account and ALL data? This cannot be undone.">
            @csrf
            <div class="form-group">
                <label for="confirm_password">Confirm with your password</label>
                <input class="input" type="password" id="confirm_password" name="confirm_password"
                       required autocomplete="current-password">
            </div>
            <button class="btn btn-danger" type="submit">Delete my account</button>
        </form>
    </div>
</div>
@endsection
