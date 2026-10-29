{{--
    View: account verification - enter the 6-digit code.
    DEMO delivery: the code appears in a banner at the top of this page for
    a few seconds (no e-mail is sent). Receives $maskedEmail and $cooldown.
--}}
@extends('layouts.auth', ['pageTitle' => 'Verify account'])

@section('content')
<h2 class="auth-title">Verify your account</h2>
<p class="auth-sub">
    A 6-digit code for <strong>{{ $maskedEmail }}</strong> was shown in the
    banner above (demo mode - nothing is e-mailed). Enter it below before
    it expires.
</p>

<form method="post" action="{{ route('verify') }}" data-validate novalidate>
    @csrf

    <div class="form-group{{ $errors->has('code') ? ' has-error' : '' }}">
        <label for="code">Verification code</label>
        <input class="input code-input" type="text" id="code" name="code"
               inputmode="numeric" pattern="\d{6}" maxlength="6" required
               placeholder="••••••" autocomplete="one-time-code" autofocus>
        @error('code')<span class="field-error">{{ $message }}</span>@enderror
    </div>

    <button type="submit" class="btn btn-primary btn-block">Verify account</button>
</form>

<form method="post" action="{{ route('resend') }}" class="resend-form">
    @csrf
    <input type="hidden" name="purpose" value="verify">
    <button type="submit" class="btn btn-ghost btn-block" id="resendBtn"
            data-cooldown="{{ (int) $cooldown }}">
        Show a new code
    </button>
</form>

<p class="auth-switch">
    Wrong account? <a href="{{ route('login') }}">Back to login</a>
</p>
@endsection
