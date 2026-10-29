{{-- View: login form. --}}
@extends('layouts.auth', ['pageTitle' => 'Log in'])

@section('content')
<h2 class="auth-title">Welcome back</h2>
<p class="auth-sub">Log in to your account to continue.</p>

<form method="post" action="{{ route('login') }}" data-validate novalidate>
    @csrf

    <div class="form-group{{ $errors->has('identifier') ? ' has-error' : '' }}">
        <label for="identifier">Username or e-mail</label>
        <input class="input" type="text" id="identifier" name="identifier"
               value="{{ old('identifier') }}" required maxlength="120"
               autocomplete="username" autofocus>
        @error('identifier')<span class="field-error">{{ $message }}</span>@enderror
    </div>

    <div class="form-group{{ $errors->has('password') ? ' has-error' : '' }}">
        <div class="label-row">
            <label for="password">Password</label>
            <a class="muted-link" href="{{ route('forgot') }}">Forgot password?</a>
        </div>
        <div class="input-wrap">
            <input class="input" type="password" id="password" name="password"
                   required maxlength="72" autocomplete="current-password">
            <button type="button" class="peek-btn" data-peek="password" aria-label="Show password">Show</button>
        </div>
        @error('password')<span class="field-error">{{ $message }}</span>@enderror
    </div>

    <button type="submit" class="btn btn-primary btn-block">Log in</button>
</form>

<p class="auth-switch">
    New here? <a href="{{ route('register') }}">Create an account</a>
</p>
@endsection
