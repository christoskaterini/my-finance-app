@extends('layouts.studio')
@section('page-title', __('Edit User'))

@section('content')
 <div class="row">
        <div class="col-xl-9 col-lg-10">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0">{{ __('Edit User') }}: {{ $user->name }}</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('settings.users.update', $user) }}" method="POST">
                        @csrf
                        @method('PUT')
                        @include('settings.users._form')
                        <div class="text-end">
                            <a href="{{ route('settings.users.index') }}" class="btn btn-secondary">{{ __('Cancel') }}</a>
                            <button type="submit" class="btn btn-primary">{{ __('Update User') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection