<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="{{ $app_theme }}">

<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', $app_name)</title>
    <link rel="icon" href="{{ asset('favicon.png') }}">
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <link rel="apple-touch-icon" href="{{ asset('icons/ios/180.png') }}">
    <meta name="theme-color" content="#212529">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/themes/dark.css" id="flatpickr-dark-theme"
        disabled>
    <style>
        html,
        body {
            overflow-x: hidden;
            width: 100%;
        }

        body {
            background-color: var(--bs-body-bg);
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 250px;
            background-color: var(--bs-tertiary-bg);
            border-right: 1px solid var(--bs-border-color);
            padding: 1.5rem;
            transition: all 0.3s ease-in-out;
            position: fixed;
            height: 100%;
            top: 0;
            left: 0;
            z-index: 1030;
            display: flex;
            flex-direction: column;
            overflow-y: auto;
            overflow-x: hidden;
        }

        .sidebar .nav-link {
            color: var(--bs-secondary-color);
            font-weight: 500;
            padding: .75rem 1rem;
            border-radius: .5rem;
            white-space: nowrap;
            display: flex;
            align-items: center;
        }

        .sidebar .nav-link:hover {
            color: var(--bs-body-color);
            background-color: var(--bs-secondary-bg);
        }

        .sidebar .nav-link.active {
            color: var(--bs-body-emphasis-color);
            background-color: var(--bs-secondary-bg);
            font-weight: 600;
        }

        /* Submenu styles for the accordion */
        .sidebar .settings-submenu {
            padding-left: 2.5rem;
        }

        .sidebar .settings-submenu .nav-link {
            font-size: 0.9rem;
            padding-top: .4rem;
            padding-bottom: .4rem;
        }

        /* Chevron icon rotation for accordion */
        .sidebar .nav-link .toggle-icon {
            transition: transform 0.3s ease-in-out;
        }

        .sidebar a[aria-expanded="true"] .toggle-icon {
            transform: rotate(90deg);
        }

        .sidebar-brand {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--bs-body-emphasis-color);
            text-decoration: none;
            margin-bottom: 2rem;
            display: block;
            white-space: normal;
            word-break: break-word;
        }

        .sidebar-footer {
            margin-top: auto;
            padding-top: 1.5rem;
            border-top: 1px solid var(--bs-border-color);
            text-align: center;
            font-size: 0.9rem;
            white-space: nowrap;
        }

        .main-content {
            flex-grow: 1;
            margin-left: 250px;
            transition: margin-left 0.3s ease-in-out;
            padding: 0;
            display: flex;
            flex-direction: column;
            overflow-x: hidden;
        }

        .top-bar {
            background-color: var(--bs-body-bg);
            border-bottom: 1px solid var(--bs-border-color);
            padding: .75rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .top-bar .page-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--bs-body-emphasis-color);
        }

        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1029;
        }

        .page-content {
            padding: 2rem;
            flex-grow: 1;
        }

        .transactions-table th.col-date,
        .transactions-table td[data-label="{{ __('Date') }}"] {
            width: 145px;
        }

        .transactions-table th.col-store,
        .transactions-table td[data-label="{{ __('Store') }}"] {
            width: 150px;
        }

        .transactions-table th.col-type,
        .transactions-table td[data-label="{{ __('Type') }}"] {
            width: 100px;
        }

        .transactions-table th.col-amount,
        .transactions-table td.col-amount {
            width: 130px;
            white-space: nowrap;
        }

        .transactions-table th.col-actions,
        .transactions-table td[data-label="{{ __('Actions') }}"] {
            width: 120px;
        }

        @media (max-width: 992px) {
            .sidebar {
                margin-left: -250px;
            }

            .main-content {
                margin-left: 0;
            }

            .page-content {
                padding: 1rem;
            }

            body.sidebar-toggled .sidebar {
                margin-left: 0;
            }

            body.sidebar-toggled .sidebar-overlay {
                display: block;
            }

            .top-bar {
                padding: .75rem 1rem !important;
            }
        }

        @media (max-width: 575px) {
            .responsive-card-table thead {
                display: none;
            }

            .responsive-card-table tr {
                display: block;
                margin-bottom: 1rem;
                border: 1px solid var(--bs-border-color);
                border-radius: .5rem;
            }

            .responsive-card-table td {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: .5rem 1rem;
                border: none;
                text-align: right !important;
                flex-wrap: wrap;
                gap: 0.5rem;
            }

            .responsive-card-table td:not(:last-child) {
                border-bottom: 1px solid var(--bs-border-color-translucent);
            }

            .responsive-card-table td::before {
                content: attr(data-label);
                font-weight: 600;
                margin-right: 1rem;
                text-align: left;
            }

            /* Reset widths for mobile */
            .transactions-table th,
            .transactions-table td {
                width: auto !important;
            }

            .main-content {
                overflow-x: hidden;
            }
        }

        /* Styling for the "Add Record" modal*/
        [data-bs-theme="dark"] {
            --bs-body-bg: #212529;
            --bs-tertiary-bg: #262a2e;
            --bs-secondary-bg: #343a40;
            --bs-body-color: #dee2e6;
            --bs-border-color: #495057;
        }

        .record-section {
            background-color: var(--bs-tertiary-bg);
            padding: 1rem;
            border-radius: .5rem;
        }

        .record-section.expense {
            border-left: 5px solid var(--bs-danger);
        }

        .record-section.income {
            border-left: 5px solid var(--bs-success);
        }

        .record-line-separator:not(:last-child) {
            border-bottom: 1px solid var(--bs-border-color-translucent);
            padding-bottom: 0.75rem;
            margin-bottom: 0.75rem;
        }

        .record-section-header {
            border-bottom: 1px solid var(--bs-border-color-translucent);
            padding-bottom: 0.75rem;
            margin-bottom: 1rem;
        }

        .record-section-footer {
            border-top: 1px solid var(--bs-border-color-translucent);
            padding-top: 1rem;
            margin-top: 1rem;
        }

        .income-shift-group {
            background-color: rgba(var(--bs-body-color-rgb), 0.03);
            border: 1px solid var(--bs-border-color-translucent);
        }

        .shift-group-header {
            background-color: rgba(var(--bs-body-color-rgb), 0.05);
            font-weight: bold;
        }

        /* ================================================= */
        /*  "Gray Slate" Light Theme Overrides          */
        /* ================================================= */
        [data-bs-theme="light"] {
            /* Main page background - The lightest gray */
            --bs-body-bg: #edf0f3;
            /* Cards, Modals, Sidebar, Top bar - The mid-tone gray */
            --bs-tertiary-bg: #e9ecef;
            /* Card Headers - The darkest gray for contrast */
            --bs-secondary-bg: #dee2e6;
            /* Main text color */
            --bs-body-color: #212529;
            /* Border color */
            --bs-border-color: #ced4da;
        }

        [data-bs-theme="dark"] .table-light {
            --bs-table-bg: #343a40;
            --bs-table-border-color: #495057;
            --bs-table-color: #dee2e6;
        }
    </style>
    @stack('styles')

    <style>
        .filter-active {
            border-color: #0dcafd !important;
            border-width: 2px !important;
            box-shadow: 0 0 0 0.2rem rgba(13, 202, 240, 0.25) !important;
        }

        [data-bs-theme="dark"] .filter-active {
            border-color: #0dcafd !important;
        }
    </style>
</head>

<body>
    {{-- Toast Notification for Success Messages --}}
    <div class="toast-container position-fixed top-0 end-0 p-3" id="main-toast-container"
        style="z-index: 1100; pointer-events: none;">
        @if (session('success'))
            <div id="successToast" class="toast align-items-center text-bg-success border-0 show" role="alert"
                aria-live="assertive" aria-atomic="true" style="pointer-events: auto;">
                <div class="d-flex">
                    <div class="toast-body">{{ session('success') }}</div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"
                        aria-label="Close"></button>
                </div>
            </div>
        @endif
    </div>

    <aside class="sidebar">
        <div>
            <a href="/" class="sidebar-brand d-flex flex-column align-items-center">
                @if ($app_logo)
                    {{-- Changed margin from right (me-2) to bottom (mb-2) --}}
                    <img src="{{ asset('storage/' . $app_logo) }}" alt="Logo" height="60" class="mb-2">
                @endif
                <span>{{ $app_name }}</span>
            </a>
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"
                        href="{{ route('dashboard') }}">
                        <i class="bi bi-grid-1x2-fill me-2"></i> <span>{{ __('Dashboard') }}</span>
                    </a>
                </li>
                <li class="nav-item mt-2">
                    <a class="nav-link {{ request()->routeIs('transactions.index') ? 'active' : '' }}"
                        href="{{ route('transactions.index') }}">
                        <i class="bi bi-wallet2 me-2"></i> <span>{{ __('Transactions') }}</span>
                    </a>
                </li>
                {{-- New Reports Accordion Menu --}}
                <li class="nav-item mt-2">
                    <a class="nav-link d-flex justify-content-between {{ request()->routeIs('reports.*') ? 'active' : '' }}"
                        data-bs-toggle="collapse" href="#reports-submenu" role="button"
                        aria-expanded="{{ request()->routeIs('reports.*') ? 'true' : 'false' }}">
                        <span><i class="bi bi-pie-chart-fill me-2"></i> <span>{{ __('Reports') }}</span></span>
                        <i class="bi bi-chevron-right toggle-icon"></i>
                    </a>
                    <div class="collapse settings-submenu {{ request()->routeIs('reports.*') ? 'show' : '' }}"
                        id="reports-submenu">
                        <ul class="nav flex-column">
                            {{-- Link to the Overview page (formerly Home) --}}
                            <li class="nav-item">
                                <a class="nav-link {{ request()->is('reports') && request()->query('view', 'home') == 'home' ? 'active' : '' }}"
                                    href="{{ route('reports.index', ['view' => 'home']) }}"><span>{{ __('Overview') }}</span></a>
                            </li>
                            {{-- Link to the Monthly Sums page --}}
                            <li class="nav-item">
                                <a class="nav-link {{ request()->query('view') == 'monthly_sums' ? 'active' : '' }}"
                                    href="{{ route('reports.index', ['view' => 'monthly_sums']) }}"><span>{{ __('Monthly Sums') }}</span></a>
                            </li>
                            {{-- Link to the Category Analysis page --}}
                            <li class="nav-item">
                                <a class="nav-link {{ request()->query('view') == 'category_analysis' ? 'active' : '' }}"
                                    href="{{ route('reports.index', ['view' => 'category_analysis']) }}"><span>{{ __('Category Analysis') }}</span></a>
                            </li>
                            {{-- Link to the Day Income Analysis page --}}
                            <li class="nav-item">
                                <a class="nav-link {{ request()->query('view') == 'day_income_analysis' ? 'active' : '' }}"
                                    href="{{ route('reports.index', ['view' => 'day_income_analysis']) }}"><span>{{ __('Day Income Analysis') }}</span></a>
                            </li>
                            {{-- Link to the Charts page --}}
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('reports.charts') ? 'active' : '' }}"
                                    href="{{ route('reports.charts') }}"><span>{{ __('Charts') }}</span></a>
                            </li>
                        </ul>
                    </div>
                </li>

                @if (Auth::user()->isAdmin())
                    {{-- ACCORDION MENU SETTINGS --}}
                    <li class="nav-item mt-2">
                        <a class="nav-link d-flex justify-content-between {{ request()->routeIs('settings.index') ? 'active' : '' }}"
                            data-bs-toggle="collapse" href="#settings-submenu" role="button"
                            aria-expanded="{{ request()->routeIs('settings.index') ? 'true' : 'false' }}">
                            <span><i class="bi bi-gear-fill me-2"></i> <span>{{ __('Settings') }}</span></span>
                            <i class="bi bi-chevron-right toggle-icon"></i>
                        </a>
                        <div class="collapse settings-submenu {{ request()->routeIs('settings.index') ? 'show' : '' }}"
                            id="settings-submenu">
                            <ul class="nav flex-column">
                                <li class="nav-item"><a
                                        class="nav-link {{ request()->query('tab') == 'stores' ? 'active' : '' }}"
                                        href="{{ url('/settings?tab=stores') }}"><span>{{ __('Stores') }}</span></a>
                                </li>
                                <li class="nav-item"><a
                                        class="nav-link {{ request()->query('tab') == 'expense-categories' ? 'active' : '' }}"
                                        href="{{ url('/settings?tab=expense-categories') }}"><span>{{ __('Expense Categories') }}</span></a>
                                </li>
                                <li class="nav-item"><a
                                        class="nav-link {{ request()->query('tab') == 'shifts' ? 'active' : '' }}"
                                        href="{{ url('/settings?tab=shifts') }}"><span>{{ __('Shifts') }}</span></a>
                                </li>
                                <li class="nav-item"><a
                                        class="nav-link {{ request()->query('tab') == 'sources' ? 'active' : '' }}"
                                        href="{{ url('/settings?tab=sources') }}"><span>{{ __('Sources') }}</span></a>
                                </li>
                                <li class="nav-item"><a
                                        class="nav-link {{ request()->query('tab') == 'payment-methods' ? 'active' : '' }}"
                                        href="{{ url('/settings?tab=payment-methods') }}"><span>{{ __('Payment Methods') }}</span></a>
                                </li>
                                <li class="nav-item"><a
                                        class="nav-link {{ request()->query('tab') == 'general' ? 'active' : '' }}"
                                        href="{{ url('/settings?tab=general') }}"><span>{{ __('General') }}</span></a>
                                </li>
                                <li class="nav-item"><a
                                        class="nav-link {{ request()->routeIs('settings.users.*') ? 'active' : '' }}"
                                        href="{{ route('settings.users.index') }}"><span>{{ __('Users') }}</span></a>
                                </li>
                            </ul>
                        </div>
                    </li>
                @endif
            </ul>
        </div>
        <!-- --- SIDEBAR FOOTER --- -->
        <div class="sidebar-footer">
            <div class="fw-bold">My Finance</div>
            <small class="text-muted">
                Version {{ config('app.version', '1.0.0') }}
            </small>

            <div class="mt-2" style="font-size: 0.8rem;">
                <small class="text-muted">
                    by <a href="{{ config('app.creator_url', 'https://www.cloudorder.gr') }}" target="_blank"
                        class="text-muted text-decoration-none">{{ config('app.creator_name', 'ChristosK.') }}</a>
                </small>
            </div>
        </div>
    </aside>

    <div class="sidebar-overlay" id="sidebar-overlay"></div>

    <div class="main-content">
        <header class="top-bar">
            <button class="btn d-lg-none" id="sidebar-toggler"><i class="bi bi-list fs-3"></i></button>
            <h1 class="page-title d-none d-lg-block">@yield('page-title', 'Dashboard')</h1>
            <div class="top-bar-controls">
                <div class="dropdown"><a href="#"
                        class="d-flex align-items-center text-decoration-none dropdown-toggle"
                        data-bs-toggle="dropdown" aria-expanded="false"><i class="bi bi-person-circle fs-4 me-2"></i>
                        <div>
                            <div class="fw-bold">{{ Auth::user()->name }}</div>
                        </div>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end text-small shadow">
                        <li><a class="dropdown-item" href="{{ route('profile.edit') }}">{{ __('Profile') }}</a></li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item">
                                    {{ __('Sign out') }}
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </header>
        <main class="page-content">
            @yield('content')
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/gr.js"></script>
    <style>
        .ios-amount-keypad {
            --ios-keyboard-bg: #d1d3d9;
            --ios-key-bg: #ffffff;
            --ios-key-color: #1c1c1e;
            --ios-key-border: #b7bac1;
            position: fixed;
            z-index: 1080;
            right: 0;
            bottom: 0;
            left: 0;
            max-height: 55dvh;
            overflow-y: auto;
            overscroll-behavior: contain;
            padding: 0.75rem max(0.75rem, env(safe-area-inset-left)) calc(0.75rem + env(safe-area-inset-bottom));
            background: var(--ios-keyboard-bg);
            border-top: 1px solid var(--ios-key-border);
            box-shadow: 0 -0.5rem 1.5rem rgb(0 0 0 / 18%);
        }

        [data-bs-theme="dark"] .ios-amount-keypad {
            --ios-keyboard-bg: #1c1c1e;
            --ios-key-bg: #3a3a3c;
            --ios-key-color: #ffffff;
            --ios-key-border: #4a4a4d;
        }

        .ios-amount-keypad-open .page-content {
            padding-bottom: calc(2rem + var(--ios-amount-keypad-height, 18rem)) !important;
        }

        @media (max-width: 992px) {
            .ios-amount-keypad-open .page-content {
                padding-bottom: calc(1rem + var(--ios-amount-keypad-height, 18rem)) !important;
            }
        }

        .ios-amount-keypad[hidden] {
            display: none !important;
        }

        .ios-amount-keypad-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.5rem;
            max-width: 32rem;
            margin: 0 auto 0.5rem;
        }

        .ios-amount-keypad-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 0.4rem;
            max-width: 32rem;
            margin: 0 auto;
        }

        .ios-amount-keypad-key,
        .ios-amount-keypad-action {
            min-height: 2.8rem;
            font-size: 1.25rem;
            touch-action: manipulation;
            color: var(--ios-key-color);
            background-color: var(--ios-key-bg);
            border: 1px solid var(--ios-key-border);
            border-radius: 0.35rem;
            box-shadow: 0 1px 0 rgb(0 0 0 / 18%);
        }

        .ios-amount-keypad-key:hover,
        .ios-amount-keypad-key:active,
        .ios-amount-keypad-action:hover,
        .ios-amount-keypad-action:active {
            color: var(--ios-key-color);
            background-color: color-mix(in srgb, var(--ios-key-bg) 88%, var(--ios-key-color));
        }

        .ios-amount-keypad-action {
            min-height: 2.4rem;
            min-width: 3.25rem;
            padding-inline: 0.5rem;
            border: 0;
            box-shadow: none;
        }

        .ios-amount-keypad-next {
            color: #007aff;
            background: transparent;
            font-size: 1rem;
            font-weight: 600;
        }

        [data-bs-theme="dark"] .ios-amount-keypad-next {
            color: #0a84ff;
        }
    </style>
    <div class="ios-amount-keypad" id="ios-amount-keypad" hidden role="group"
        aria-label="{{ __('Amount keypad') }}" data-decimal-separator="{{ settings('app_number_format', ',') }}">
        <div class="ios-amount-keypad-header">
            <button type="button" class="btn ios-amount-keypad-action" data-amount-key="minus"
                aria-label="{{ __('Minus') }}">−</button>
            <button type="button" class="btn ios-amount-keypad-action ios-amount-keypad-next"
                data-amount-key="next">{{ __('Next') }}</button>
        </div>
        <div class="ios-amount-keypad-grid">
            <button type="button" class="btn ios-amount-keypad-key" data-amount-key="1">1</button>
            <button type="button" class="btn ios-amount-keypad-key" data-amount-key="2">2</button>
            <button type="button" class="btn ios-amount-keypad-key" data-amount-key="3">3</button>
            <button type="button" class="btn ios-amount-keypad-key" data-amount-key="4">4</button>
            <button type="button" class="btn ios-amount-keypad-key" data-amount-key="5">5</button>
            <button type="button" class="btn ios-amount-keypad-key" data-amount-key="6">6</button>
            <button type="button" class="btn ios-amount-keypad-key" data-amount-key="7">7</button>
            <button type="button" class="btn ios-amount-keypad-key" data-amount-key="8">8</button>
            <button type="button" class="btn ios-amount-keypad-key" data-amount-key="9">9</button>
            <button type="button" class="btn ios-amount-keypad-key" data-amount-key="decimal"
                aria-label="{{ __('Decimal point') }}">{{ settings('app_number_format', ',') }}</button>
            <button type="button" class="btn ios-amount-keypad-key" data-amount-key="0">0</button>
            <button type="button" class="btn ios-amount-keypad-key" data-amount-key="backspace"
                aria-label="{{ __('Backspace') }}">⌫</button>
        </div>
    </div>
    <script>
        (function() {
            const keypad = document.getElementById('ios-amount-keypad');
            const decimalSeparator = keypad.dataset.decimalSeparator || '.';
            window.appDecimalSeparator = decimalSeparator;
            const isIOS = /iPhone|iPad|iPod/.test(navigator.userAgent) ||
                (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);
            if (!isIOS) return;

            const amountSelector = '.amount-input, #amount, [data-field="amount"]';
            let activeInput = null;

            function getAmountInputs() {
                return Array.from(document.querySelectorAll(amountSelector)).filter(input =>
                    !input.disabled && input.getClientRects().length > 0
                );
            }

            function closeKeypad() {
                if (activeInput) {
                    activeInput.dispatchEvent(new Event('change', {
                        bubbles: true
                    }));
                    activeInput.blur();
                }
                activeInput = null;
                keypad.hidden = true;
                document.body.classList.remove('ios-amount-keypad-open');
                document.body.style.removeProperty('--ios-amount-keypad-height');
            }

            function normalizeAmount(value) {
                return decimalSeparator === ',' ? value.replace(',', '.') : value;
            }

            function showKeypad(input) {
                configureAmountInput(input);
                activeInput = input;
                keypad.hidden = false;
                document.body.classList.add('ios-amount-keypad-open');

                requestAnimationFrame(() => {
                    if (!activeInput) return;
                    document.body.style.setProperty('--ios-amount-keypad-height', `${keypad.offsetHeight}px`);
                    activeInput.scrollIntoView({
                        block: 'start',
                        behavior: 'smooth'
                    });
                });
            }

            function configureAmountInput(input) {
                if (!input.matches(amountSelector)) return;
                input.type = 'text';
                input.inputMode = 'none';
                input.autocomplete = 'off';
                input.spellcheck = false;
                if (decimalSeparator === ',' && input.value.includes('.')) {
                    input.value = input.value.replace('.', ',');
                }
            }

            document.querySelectorAll(amountSelector).forEach(configureAmountInput);

            const amountInputObserver = new MutationObserver(mutations => {
                mutations.forEach(mutation => mutation.addedNodes.forEach(node => {
                    if (node.nodeType !== Node.ELEMENT_NODE) return;
                    configureAmountInput(node);
                    node.querySelectorAll(amountSelector).forEach(configureAmountInput);
                }));
            });
            amountInputObserver.observe(document.body, {
                childList: true,
                subtree: true
            });

            document.addEventListener('focusin', function(event) {
                if (!event.target.matches(amountSelector)) return;
                showKeypad(event.target);
            });

            document.addEventListener('click', function(event) {
                if (!activeInput || keypad.contains(event.target) || event.target.matches(amountSelector))
                    return;
                closeKeypad();
            });

            keypad.addEventListener('pointerdown', function(event) {
                if (event.target.closest('[data-amount-key]')) event.preventDefault();
            });

            document.addEventListener('submit', function(event) {
                event.target.querySelectorAll(amountSelector).forEach(input => {
                    input.value = normalizeAmount(input.value);
                });
            }, true);

            keypad.addEventListener('click', function(event) {
                const button = event.target.closest('[data-amount-key]');
                if (!button || !activeInput) return;

                const key = button.dataset.amountKey;
                if (key === 'next') {
                    const amountInputs = getAmountInputs();
                    const nextInput = amountInputs[amountInputs.indexOf(activeInput) + 1];
                    if (nextInput) {
                        nextInput.focus({
                            preventScroll: true
                        });
                    } else {
                        closeKeypad();
                    }
                    return;
                }

                const value = activeInput.value;
                let start = activeInput.selectionStart ?? value.length;
                let end = activeInput.selectionEnd ?? value.length;

                if (key === 'minus') {
                    if (value.startsWith('-')) {
                        activeInput.value = value.slice(1);
                        start = Math.max(0, start - 1);
                        end = Math.max(0, end - 1);
                    } else {
                        activeInput.value = `-${value}`;
                        start++;
                        end++;
                    }
                    activeInput.setSelectionRange(start, end);
                } else if (key === 'decimal') {
                    if (value.includes('.') || value.includes(',')) return;
                    activeInput.setRangeText(decimalSeparator, start, end, 'end');
                } else if (key === 'backspace') {
                    if (start === end && start > 0) start--;
                    activeInput.setRangeText('', start, end, 'start');
                } else {
                    activeInput.setRangeText(key, start, end, 'end');
                }

                activeInput.dispatchEvent(new Event('input', {
                    bubbles: true
                }));
            });
        })();
    </script>
    @stack('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const mobileToggler = document.getElementById('sidebar-toggler');
            const sidebarOverlay = document.getElementById('sidebar-overlay');
            if (mobileToggler) {
                mobileToggler.addEventListener('click', () => {
                    document.body.classList.toggle('sidebar-toggled');
                });
            }
            if (sidebarOverlay) {
                sidebarOverlay.addEventListener('click', () => {
                    document.body.classList.remove('sidebar-toggled');
                });
            }

            const confirmDeleteModal = document.getElementById('confirmDeleteModal');
            if (confirmDeleteModal) {
                const confirmDeleteButton = document.getElementById('confirmDeleteButton');
                let formToSubmit = null;
                const modal = new bootstrap.Modal(confirmDeleteModal);
                document.querySelectorAll('.delete-trigger-btn').forEach(b => {
                    b.addEventListener('click', function(e) {
                        e.preventDefault();
                        const fid = this.getAttribute('data-form-id');
                        formToSubmit = document.getElementById(fid);
                        modal.show();
                    });
                });
                confirmDeleteButton.addEventListener('click', function() {
                    if (formToSubmit) {
                        formToSubmit.submit();
                    }
                });
            }
            const toastEl = document.getElementById('successToast');
            if (toastEl) {
                const toast = new bootstrap.Toast(toastEl, {
                    delay: 5000
                });
                toast.show();
            }

            // Global helper for JS-triggered toasts
            window.showAlert = function(message, type = 'success') {
                const container = document.getElementById('main-toast-container');
                const toastId = 'toast-' + Date.now();
                const html = `
                    <div id="${toastId}" class="toast align-items-center text-bg-${type} border-0" role="alert" aria-live="assertive" aria-atomic="true" style="pointer-events: auto;">
                        <div class="d-flex">
                            <div class="toast-body">${message}</div>
                            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                        </div>
                    </div>
                `;
                const wrapper = document.createElement('div');
                wrapper.innerHTML = html.trim();
                const el = wrapper.firstChild;
                container.appendChild(el);

                const bToast = new bootstrap.Toast(el, {
                    delay: 5000
                });
                bToast.show();

                el.addEventListener('hidden.bs.toast', () => el.remove());
            };

            // --- Flatpickr Global Initialization ---
            const flatpickrDarkTheme = document.getElementById('flatpickr-dark-theme');

            function updateFlatpickrTheme() {
                const isDark = document.documentElement.getAttribute('data-bs-theme') === 'dark';
                if (isDark) {
                    flatpickrDarkTheme.removeAttribute('disabled');
                } else {
                    flatpickrDarkTheme.setAttribute('disabled', 'disabled');
                }
            }
            updateFlatpickrTheme();

            // Observe theme changes
            const observer = new MutationObserver((mutations) => {
                mutations.forEach((mutation) => {
                    if (mutation.type === 'attributes' && mutation.attributeName ===
                        'data-bs-theme') {
                        updateFlatpickrTheme();
                    }
                });
            });
            observer.observe(document.documentElement, {
                attributes: true
            });

            window.initDatePickers = function(container = document) {
                const appLocale = document.documentElement.lang;
                const fpLocale = (appLocale === 'el' || appLocale === 'gr') ? 'gr' : 'en';

                const inputs = container.querySelectorAll('input[type="date"], .datepicker');
                inputs.forEach(el => {
                    // Prevent double initialization
                    if (el._flatpickr) return;

                    const config = {
                        altInput: true,
                        altFormat: "d/m/Y",
                        dateFormat: "Y-m-d",
                        locale: fpLocale,
                        allowInput: true,
                        disableMobile: "true",
                        onReady: function(selectedDates, dateStr, instance) {
                            if (el.hasAttribute('data-auto-open')) {
                                setTimeout(() => instance.open(), 300);
                            }
                            // Ensure the alt input has the same classes as the original
                            if (instance.altInput) {
                                instance.altInput.className = el.className;
                                // If it was centered, keep it centered
                                if (el.classList.contains('text-center')) {
                                    instance.altInput.classList.add('text-center');
                                }
                            }
                        }
                    };

                    flatpickr(el, config);
                });
            };

            window.initDatePickers();
        });
    </script>
    <div class="modal fade" id="confirmDeleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('Confirm Action') }}</h5><button type="button" class="btn-close"
                        data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">{{ __('Are you sure?') }}</div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary"
                        data-bs-dismiss="modal">{{ __('Cancel') }}</button><button type="button"
                        class="btn btn-danger" id="confirmDeleteButton">{{ __('Delete') }}</button></div>
            </div>
        </div>
    </div>
</body>

</html>
