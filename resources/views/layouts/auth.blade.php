<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="{{ config('settings.app_theme', 'dark') }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') | {{ config('settings.app_name', 'My Finance') }}</title>
    <link rel="icon" href="{{ asset('favicon.png') }}">
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <link rel="apple-touch-icon" href="{{ asset('icons/ios/180.png') }}">
    <meta name="theme-color" content="#212529">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body {
            background-color: var(--bs-body-bg);
            padding-bottom: 20vh;
        }
    </style>
</head>

<body class="d-flex align-items-center justify-content-center min-vh-100">

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5 col-xl-4">
                <div class="text-center mb-4">
                    @if(config('settings.app_logo'))
                    <img src="{{ asset('storage/' . config('settings.app_logo')) }}" alt="Logo" class="mb-2" style="max-width: 150px; height: auto;">
                    @endif
                    <h1 class="h3 text-body-emphasis">{{ config('settings.app_name', 'My Finance') }}</h1>
                </div>
                <div class="card shadow-sm">
                    <div class="card-body p-4">
                        @yield('content')
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
