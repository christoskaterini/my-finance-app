@extends('layouts.auth')

@section('title', __('Application Setup'))

@section('content')
    
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    {{-- ====================================================== --}}
    {{-- Step 1: Server Requirements Check --}}
    {{-- ====================================================== --}}
    @if ($step === 1)
        <h5 class="card-title text-center mb-4">{{ __('Step 1: Server Requirements') }}</h5>
        <ul class="list-group list-group-flush mb-4">
            @foreach($requirements as $requirement)
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    {{ $requirement['name'] }}
                    @if($requirement['check'])
                        <span class="badge bg-success"><i class="bi bi-check-lg"></i></span>
                    @else
                        <span class="badge bg-danger"><i class="bi bi-x-lg"></i></span>
                    @endif
                </li>
            @endforeach
        </ul>
        @if($allRequirementsMet)
            <div class="alert alert-success small">{{ __('All requirements met. You can proceed.') }}</div>
            <form method="GET" action="{{ route('setup.index') }}">
                <input type="hidden" name="step" value="2">
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary">{{ __('Next: Configure Database') }}</button>
                </div>
            </form>
        @else
            <div class="alert alert-danger small">{{ __('Please fix the server requirements before proceeding.') }}</div>
            <div class="d-grid">
                <a href="{{ route('setup.index') }}" class="btn btn-secondary">{{ __('Re-check') }}</a>
            </div>
        @endif
    @endif

    {{-- ====================================================== --}}
    {{-- Step 2: Database Configuration --}}
    {{-- ====================================================== --}}
    @if (request()->query('step') === '2')
        <h5 class="card-title text-center mb-4">{{ __('Step 2: Database Configuration') }}</h5>
        <form method="POST" action="{{ route('setup.database') }}">
            @csrf
            <div class="mb-3">
                <label for="db_host" class="form-label">{{ __('Database Host') }}</label>
                <input type="text" class="form-control" id="db_host" name="db_host" value="{{ old('db_host', '127.0.0.1') }}" required>
            </div>
            <div class="mb-3">
                <label for="db_port" class="form-label">{{ __('Database Port') }}</label>
                <input type="text" class="form-control" id="db_port" name="db_port" value="{{ old('db_port', '3306') }}" required>
            </div>
            <div class="mb-3">
                <label for="db_database" class="form-label">{{ __('Database Name') }}</label>
                <input type="text" class="form-control" id="db_database" name="db_database" value="{{ old('db_database') }}" required>
            </div>
            <div class="mb-3">
                <label for="db_username" class="form-label">{{ __('Database Username') }}</label>
                <input type="text" class="form-control" id="db_username" name="db_username" value="{{ old('db_username') }}" required>
            </div>
            <div class="mb-3">
                <label for="db_password" class="form-label">{{ __('Database Password') }}</label>
                <input type="password" class="form-control" id="db_password" name="db_password">
            </div>
            <div class="d-grid">
                <button type="submit" class="btn btn-primary">{{ __('Test Connection & Save') }}</button>
            </div>
        </form>
    @endif

    {{-- ====================================================== --}}
    {{-- Step 3: Configure Email --}}
    {{-- ====================================================== --}}
    @if ($step === '2b_ask_mail')
        <h5 class="card-title text-center mb-4">{{ __('Step 3: Configure Email') }}</h5>
        <p class="text-muted small">{{ __('Enter your SMTP credentials. This will be used for password resets and notifications.') }}</p>
        <form method="POST" action="{{ route('setup.mail') }}">
            @csrf
            <input type="hidden" name="mail_mailer" value="smtp">
            <div class="row">
                <div class="col-md-12 mb-3"><label for="mail_host" class="form-label">{{ __('SMTP Host') }}</label><input type="text" class="form-control" id="mail_host" name="mail_host" placeholder="smtp.example.com" required></div>
                <div class="col-md-6 mb-3"><label for="mail_port" class="form-label">{{ __('SMTP Port') }}</label><input type="text" class="form-control" id="mail_port" name="mail_port" placeholder="587" required></div>
                <div class="col-md-6 mb-3"><label for="mail_encryption" class="form-label">{{ __('Encryption') }}</label><select id="mail_encryption" name="mail_encryption" class="form-select"><option value="tls">TLS</option><option value="ssl">SSL</option><option value="">None</option></select></div>
                <div class="col-md-12 mb-3"><label for="mail_username" class="form-label">{{ __('SMTP Username') }}</label><input type="text" class="form-control" id="mail_username" name="mail_username" placeholder="your-email@example.com" required></div>
                <div class="col-md-12 mb-3"><label for="mail_password" class="form-label">{{ __('SMTP Password') }}</label><input type="password" class="form-control" id="mail_password" name="mail_password" required></div>
                <div class="col-md-12 mb-3"><label for="mail_from_address" class="form-label">{{ __('From Email Address') }}</label><input type="email" class="form-control" id="mail_from_address" name="mail_from_address" placeholder="noreply@example.com" required></div>
            </div>
            <div class="d-grid"><button type="submit" class="btn btn-primary">{{ __('Save & Continue') }}</button></div>
        </form>
    @endif

    {{-- ====================================================== --}}
    {{-- Step 4: Run Migrations --}}
    {{-- ====================================================== --}}
    @if ($step === 3)
        <h5 class="card-title text-center mb-4">{{ __('Step 4: Create Database Tables') }}</h5>
        <div class="alert alert-success">{{ __('Configuration saved successfully!') }}</div>
        <p class="text-muted small">{{ __('Click the button below to create all the necessary tables in your database. This will delete any existing tables.') }}</p>
        <form method="POST" action="{{ route('setup.migrate') }}">
            @csrf
            <div class="d-grid">
                <button type="submit" class="btn btn-primary">{{ __('Create Tables') }}</button>
            </div>
        </form>
    @endif
    
    {{-- ====================================================== --}}
    {{-- Step 5: Install Sample Data (Optional) --}}
    {{-- ====================================================== --}}
    @if ($step === '3b_ask_seeder')
        <h5 class="card-title text-center mb-4">{{ __('Step 5: Install Sample Data (Optional)') }}</h5>
        <div class="alert alert-info small">{{ __('Database tables created successfully!') }}</div>
        <p class="text-muted">{{ __('Would you like to install some default sample data (stores, shifts, categories)? This is recommended for first-time users.') }}</p>
        <div class="d-flex justify-content-between mt-4">
            <form method="POST" action="{{ route('setup.seed') }}">@csrf<input type="hidden" name="seed_data" value="no"><button type="submit" class="btn btn-secondary">{{ __('No, Start with a Blank App') }}</button></form>
            <form method="POST" action="{{ route('setup.seed') }}">@csrf<input type="hidden" name="seed_data" value="yes"><button type="submit" class="btn btn-primary">{{ __('Yes, Install Sample Data') }}</button></form>
        </div>
    @endif

    {{-- ====================================================== --}}
    {{-- Step 6: Create Admin Account --}}
    {{-- ====================================================== --}}
    @if ($step === 4)
        <h5 class="card-title text-center mb-4">{{ __('Step 6: Create Admin Account') }}</h5>
        <p class="text-muted small">{{ __('Your application is ready. Now, create the primary administrator account.') }}</p>
        <form method="POST" action="{{ route('setup.admin') }}">
            @csrf
            <div class="mb-3"><label for="name" class="form-label">{{ __('Your Name') }}</label><input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>@error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
            <div class="mb-3"><label for="email" class="form-label">{{ __('Your Email') }}</label><input type="email" id="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required>@error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
            <div class="mb-3"><label for="password" class="form-label">{{ __('Password') }}</label><input type="password" id="password" name="password" class="form-control @error('password') is-invalid @enderror" required>@error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
            <div class="mb-3"><label for="password_confirmation" class="form-label">{{ __('Confirm Password') }}</label><input type="password" id="password_confirmation" name="password_confirmation" class="form-control" required></div>
            <div class="d-grid"><button type="submit" class="btn btn-primary">{{ __('Create Admin & Finish') }}</button></div>
        </form>
    @endif

    {{-- ====================================================== --}}
    {{-- Step 7: Finalization --}}
    {{-- ====================================================== --}}
    @if ($step === 5)
        <h5 class="card-title text-center mb-4">{{ __('Setup Complete!') }}</h5>
        <div class="alert alert-success text-center">
            <i class="bi bi-check-circle-fill fs-2 mb-2"></i><br>
            {{ __('Your application has been installed successfully.') }}
        </div>
        <div class="d-grid">
            <a href="{{ route('login') }}" class="btn btn-primary">{{ __('Go to Login Page') }}</a>
        </div>
    @endif

@endsection