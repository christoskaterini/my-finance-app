{{-- This partial contains the form fields for both creating and editing users --}}
<div class="row">
    <div class="col-md-6 mb-3">
        <label for="name" class="form-label">{{ __('Name') }}</label>
        <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror"
            value="{{ old('name', $user->name ?? '') }}" required>
        @error('name')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-6 mb-3">
        <label for="email" class="form-label">{{ __('Email Address') }}</label>
        <input type="email" id="email" name="email" class="form-control @error('email') is-invalid @enderror"
            value="{{ old('email', $user->email ?? '') }}" required>
        @error('email')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-6 mb-3">
        <label for="role" class="form-label">{{ __('Role') }}</label>
        <select id="role" name="role" class="form-select @error('role') is-invalid @enderror"
            @if (isset($isLastAdmin) && $isLastAdmin) disabled @endif>
            <option value="user" @if (old('role', $user->role ?? '') == 'user') selected @endif>{{ __('User') }}</option>
            <option value="admin" @if (old('role', $user->role ?? '') == 'admin') selected @endif>{{ __('Admin') }}</option>
        </select>
        @error('role')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror

        @if (isset($isLastAdmin) && $isLastAdmin)
            <div class="alert alert-warning p-2 mt-2 small">
                <i class="bi bi-exclamation-triangle-fill"></i>
                {{ __('The role cannot be changed for the last administrator.') }}
            </div>
        @endif
    </div>
</div>
<div class="row">
    <div class="col-md-6 mb-3">
        <label for="password" class="form-label">{{ __('Password') }}</label>
        <input type="password" id="password" name="password"
            class="form-control @error('password') is-invalid @enderror"
            @if (!isset($user)) required @endif>
        @if (isset($user))
            <small class="text-muted">{{ __('Leave blank to keep current password.') }}</small>
        @endif
        @error('password')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-6 mb-3">
        <label for="password_confirmation" class="form-label">{{ __('Confirm Password') }}</label>
        <input type="password" id="password_confirmation" name="password_confirmation" class="form-control">
    </div>
</div>
