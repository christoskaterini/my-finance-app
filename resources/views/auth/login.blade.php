@extends('layouts.auth')

@section('title', __('Log in'))

@section('content')
<!-- Session Status -->
@if (session('status'))
<div class="alert alert-success small mb-3" role="alert">
    {{ session('status') }}
</div>
@endif

<form method="POST" action="{{ route('login') }}">
    @csrf

    <!-- Email Address -->
    <div class="mb-3">
        <label for="email" class="form-label">{{ __('Email') }}</label>
        <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autofocus autocomplete="username">
        @error('email')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <!-- Password -->
    <div class="mb-3">
        <label for="password" class="form-label">{{ __('Password') }}</label>
        <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="current-password">
        @error('password')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <!-- Remember Me -->
    <div class="mb-3 form-check">
        <input id="remember_me" type="checkbox" class="form-check-input" name="remember">
        <label for="remember_me" class="form-check-label small">{{ __('Remember me') }}</label>
    </div>

    <div class="d-flex align-items-center justify-content-between">
        @if (Route::has('password.request'))
        <a class="small text-decoration-none" href="{{ route('password.request') }}">
            {{ __('Forgot your password?') }}
        </a>
        @endif

        <button type="submit" class="btn btn-primary">
            {{ __('Log in') }}
        </button>
    </div>

    <!-- Language switcher -->
    <div style="text-align: left; padding: 2rem 0rem 0rem 0rem;">
        <span>{{ __('Language') }}:</span>
        @foreach (config('languages') as $localeCode => $name)
        {{--
            Only show a link for languages that are NOT the currently active one.
        --}}
        @if (App::currentLocale() !== $localeCode)
        <a href="{{ route('language.set', $localeCode) }}" style="margin: 0 5px;">{{ $name }}</a>
        @endif
        @endforeach
    </div>
</form>
@endsection
