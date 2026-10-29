{{-- View: forgot password - request a reset code (shown on screen, demo mode). --}}
@extends('layouts.auth', ['pageTitle' => 'Reset password'])

@section('content')
<h2 class="auth-title">Reset your password</h2>
<p class="auth-sub">
    Enter the e-mail address of your account and a 6-digit reset code will
    be shown on screen (demo mode - nothing is e-mailed).
</p>

<form method="post" action="{{ route('forgot') }}" data-validate novalidate>
    @csrf

    <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
        <label for="email">E-mail address</label>
        <input class="input" type="email" id="email" name="email"
               value="{{ old('email') }}" required maxlength="120"
               autocomplete="email" autofocus>
        @error('email')<span class="field-error">{{ $message }}</span>@enderror
    </div>

    <button type="submit" class="btn btn-primary btn-block">Get reset code</button>
</form>

<p class="auth-switch">
    Remembered it after all? <a href="{{ route('login') }}">Back to login</a>
</p>
@endsection
