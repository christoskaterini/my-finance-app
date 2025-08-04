@extends('layouts.auth')

@section('title', __('Reset Password'))

@section('content')
<form method="POST" action="{{ route('password.store') }}">
    @csrf

    <!-- Password Reset Token -->
    <input type="hidden" name="token" value="{{ $request->route('token') }}">

    <!-- Email Address -->
    <div class="mb-3">
        <label for="email" class="form-label">{{ __('Email') }}</label>
        <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email', $request->email) }}" required autofocus autocomplete="username">
        @error('email')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <!-- New Password -->
    <div class="mb-3">
        <label for="password" class="form-label">{{ __('New Password') }}</label>
        <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="new-password">
        @error('password')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <!-- Confirm Password -->
    <div class="mb-3">
        <label for="password_confirmation" class="form-label">{{ __('Confirm Password') }}</label>
        <input id="password_confirmation" type="password" class="form-control" name="password_confirmation" required autocomplete="new-password">
    </div>

    <div class="d-grid">
        <button type="submit" class="btn btn-primary">
            {{ __('Reset Password') }}
        </button>
    </div>
</form>
@endsection
