@extends('layouts.studio')
@section('page-title', __('User Management'))

@section('content')
<div class="row">
    <div class="col-xl-9 col-lg-10">
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">{{ __('All Users') }}</h5>
                <a href="{{ route('settings.users.create') }}" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-lg"></i> {{ __('Add New User') }}
                </a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>{{ __('Name') }}</th>
                            <th>{{ __('Email') }}</th>
                            <th>{{ __('Role') }}</th>
                            <th class="text-end">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($users as $user)
                        <tr>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td><span class="badge bg-secondary">{{ ucfirst($user->role) }}</span></td>
                            <td class="text-end">
                                <a href="{{ route('settings.users.edit', $user) }}" class="btn btn-sm btn-outline-secondary">{{ __('Edit') }}</a>
                                @if($user->id !== auth()->id())
                                <button type="button" class="btn btn-sm btn-outline-danger"
                                    data-bs-toggle="modal"
                                    data-bs-target="#confirmAdminUserDeletionModal"
                                    data-delete-url="{{ route('settings.users.destroy', $user) }}">
                                    {{ __('Delete') }}
                                </button>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center">{{ __('No users found.') }}</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- The modal for admins deleting users --}}
<div class="modal fade" id="confirmAdminUserDeletionModal" tabindex="-1"
    data-has-error="{{ $errors->userDeletion->any() ? 'true' : 'false' }}">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="adminDeleteUserForm" method="POST" action="">
                @csrf
                @method('DELETE')
                <div class="modal-header">
                    <h5 class="modal-title">{{__('Confirm User Deletion')}}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="small text-muted">{{ __('To confirm, please enter your administrator password. This will deactivate the user account.') }}</p>
                    <div class="mt-3">
                        <label for="admin_delete_password" class="form-label visually-hidden">{{ __('Your Password') }}</label>
                        <input id="admin_delete_password" name="password" type="password" class="form-control" placeholder="{{ __('Your Password') }}" required>
                        @if ($errors->userDeletion->any())
                        <div class="text-danger small mt-2">{{ $errors->userDeletion->first('password') }}</div>
                        @endif
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{__('Cancel')}}</button>
                    <button type="submit" class="btn btn-danger">{{__('Delete User')}}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const confirmModal = document.getElementById('confirmAdminUserDeletionModal');
        if (confirmModal) {
            const deleteForm = confirmModal.querySelector('#adminDeleteUserForm');
            const passwordInput = confirmModal.querySelector('#admin_delete_password');

            confirmModal.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;
                const deleteUrl = button.dataset.deleteUrl;
                deleteForm.action = deleteUrl;
            });

            confirmModal.addEventListener('hidden.bs.modal', function() {
                passwordInput.value = '';
                deleteForm.action = '';
            });

            const hasDeletionError = confirmModal.dataset.hasError === 'true';
            if (hasDeletionError) {
                const errorModal = new bootstrap.Modal(confirmModal);
                errorModal.show();
            }
        }
    });
</script>
@endpush