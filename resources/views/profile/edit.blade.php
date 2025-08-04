@extends('layouts.studio')
@section('page-title', __('Profile'))

@section('content')
    <div class="row gy-4">
        {{-- gy-4 adds vertical spacing between the cards on mobile --}}
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-body">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-body">
                    @include('profile.partials.update-password-form')
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-body">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>
    @include('profile.partials.delete-user-modal')
@endsection
