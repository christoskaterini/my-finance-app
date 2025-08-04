<section>
    <header>
        @if (session('status') === 'error-last-admin')
        <div class="alert alert-danger mt-3 small">
            {{ __('You cannot delete your account because you are the only administrator.') }}
        </div>
        @endif
        <h5 class="mb-1">{{ __('Delete Account') }}</h5>
        <p class="text-muted small">{{ __(' Your account will be deactivated, and you will no longer be able to log in. Your data will be preserved. Please enter your password to confirm.') }}</p>
    </header>

    <div class="d-flex justify-content-end">
        <button type="button" class="btn btn-danger mt-3" data-bs-toggle="modal" data-bs-target="#confirmUserDeletionModal">
            {{ __('Delete Account') }}
        </button>
    </div>
</section>