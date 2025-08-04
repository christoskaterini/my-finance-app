<div class="modal fade" id="confirmUserDeletionModal" tabindex="-1" aria-labelledby="confirmUserDeletionModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" action="{{ route('profile.destroy') }}">
                @csrf
                @method('delete')

                <div class="modal-header">
                    <h5 class="modal-title" id="confirmUserDeletionModalLabel">{{ __('Are you sure you want to delete your account?') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    {{-- <-- ADD THIS: Updated, more accurate warning text --> --}}
                    <p class="small text-muted">
                        {{ __('Your account will be deactivated, and you will no longer be able to log in. Your data will be preserved. Please enter your password to confirm.') }}
                    </p>
                    <div class="mt-3">
                        <label for="password" class="form-label visually-hidden">{{ __('Password') }}</label>
                        <input id="password" name="password" type="password" class="form-control @error('password', 'userDeletion') is-invalid @enderror" placeholder="{{ __('Password') }}">
                        @error('password', 'userDeletion')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-danger">{{ __('Deactivate Account') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>