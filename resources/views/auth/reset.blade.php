{{--
    View: set a new password using the on-screen 6-digit reset code.
    Receives $resetEmail (prefill).
--}}
@extends('layouts.auth', ['pageTitle' => 'Choose new password'])

@section('content')
<h2 class="auth-title">Choose a new password</h2>
<p class="auth-sub">
    Enter the 6-digit code shown on screen together with your new password.
</p>

<form method="post" action="{{ route('reset') }}" data-validate novalidate>
    @csrf

    <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
        <label for="email">E-mail address</label>
        <input class="input" type="email" id="email" name="email"
               value="{{ old('email', $resetEmail ?? '') }}" required maxlength="120"
               autocomplete="email">
        @error('email')<span class="field-error">{{ $message }}</span>@enderror
    </div>

    <div class="form-group{{ $errors->has('code') ? ' has-error' : '' }}">
        <label for="code">Reset code</label>
        <input class="input code-input" type="text" id="code" name="code"
               inputmode="numeric" pattern="\d{6}" maxlength="6" required
               placeholder="••••••" autocomplete="one-time-code">
        @error('code')<span class="field-error">{{ $message }}</span>@enderror
    </div>

    <div class="form-group{{ $errors->has('password') ? ' has-error' : '' }}">
        <label for="password">New password</label>
        <div class="input-wrap">
            <input class="input" type="password" id="password" name="password"
                   required minlength="8" maxlength="72" autocomplete="new-password"
                   data-hint="At least 8 characters with letters and numbers">
            <button type="button" class="peek-btn" data-peek="password" aria-label="Show password">Show</button>
        </div>
        <div class="pw-meter" aria-hidden="true"><span></span></div>
        @error('password')<span class="field-error">{{ $message }}</span>@enderror
    </div>

    <div class="form-group{{ $errors->has('password_confirmation') ? ' has-error' : '' }}">
        <label for="password_confirmation">Confirm new password</label>
        <input class="input" type="password" id="password_confirmation" name="password_confirmation"
               required maxlength="72" autocomplete="new-password">
        @error('password_confirmation')<span class="field-error">{{ $message }}</span>@enderror
    </div>

    <button type="submit" class="btn btn-primary btn-block">Update password</button>
</form>

<form method="post" action="{{ route('resend') }}" class="resend-form">
    @csrf
    <input type="hidden" name="purpose" value="reset">
    <button type="submit" class="btn btn-ghost btn-block">Show a new code</button>
</form>

<p class="auth-switch">
    <a href="{{ route('login') }}">Back to login</a>
</p>
@endsection
