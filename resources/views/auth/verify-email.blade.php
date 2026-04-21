@extends('layouts.auth')

@section('title', __('Verify Email'))

@section('content')
<div class="mb-4 text-sm text-secondary">
    {{ __('Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn\'t receive the email, we will gladly send you another.') }}
</div>

@if (session('status') == 'verification-link-sent')
    <div class="alert alert-success small mb-4" role="alert">
        {{ __('A new verification link has been sent to the email address you provided during registration.') }}
    </div>
@endif

<div class="mt-4 d-flex align-items-center justify-content-between">
    <form method="POST" action="{{ route('verification.send') }}">
        @csrf
        <button type="submit" class="btn btn-primary">
            {{ __('Resend Verification Email') }}
        </button>
    </form>

    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="btn btn-link text-decoration-none small text-secondary">
            {{ __('Log Out') }}
        </button>
    </form>
</div>
@endsection

