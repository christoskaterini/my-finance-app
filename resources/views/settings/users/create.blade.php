@extends('layouts.studio')
@section('page-title', __('Add New User'))

@section('content')
 <div class="row">
        <div class="col-xl-9 col-lg-10">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0">{{ __('Create a New User') }}</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('settings.users.store') }}" method="POST">
                        @csrf
                        @include('settings.users._form')
                        <div class="text-end">
                            <a href="{{ route('settings.users.index') }}" class="btn btn-secondary">{{ __('Cancel') }}</a>
                            <button type="submit" class="btn btn-primary">{{ __('Create User') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection