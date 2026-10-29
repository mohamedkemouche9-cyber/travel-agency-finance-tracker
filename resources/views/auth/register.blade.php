{{-- View: registration form. --}}
@extends('layouts.auth', ['pageTitle' => 'Create account'])

@section('content')
<h2 class="auth-title">Create your account</h2>
<p class="auth-sub">Free, private and ready in less than a minute.</p>

<form method="post" action="{{ route('register') }}" data-validate novalidate>
    @csrf

    <div class="form-group{{ $errors->has('username') ? ' has-error' : '' }}">
        <label for="username">Username</label>
        <input class="input" type="text" id="username" name="username"
               value="{{ old('username') }}" required minlength="3" maxlength="20"
               pattern="[A-Za-z0-9_]+" autocomplete="username"
               data-hint="3-20 characters: letters, numbers, underscore">
        @error('username')<span class="field-error">{{ $message }}</span>@enderror
    </div>

    <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
        <label for="email">E-mail address</label>
        <input class="input" type="email" id="email" name="email"
               value="{{ old('email') }}" required maxlength="120"
               autocomplete="email"
               data-hint="A 6-digit demo verification code will be shown on screen">
        @error('email')<span class="field-error">{{ $message }}</span>@enderror
    </div>

    <div class="form-group{{ $errors->has('password') ? ' has-error' : '' }}">
        <label for="password">Password</label>
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
        <label for="password_confirmation">Confirm password</label>
        <input class="input" type="password" id="password_confirmation" name="password_confirmation"
               required maxlength="72" autocomplete="new-password">
        @error('password_confirmation')<span class="field-error">{{ $message }}</span>@enderror
    </div>

    <button type="submit" class="btn btn-primary btn-block">Create account</button>
</form>

<p class="auth-switch">
    Already have an account? <a href="{{ route('login') }}">Log in</a>
</p>
@endsection
