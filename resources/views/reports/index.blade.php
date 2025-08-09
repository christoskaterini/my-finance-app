@extends('layouts.studio')
@section('page-title', __('Reports'))

@section('content')
<style>
    .stat-card-dot {
        width: 16px;
        height: 24px;
        border-radius: 20%;
        margin-right: 8px;
        flex-shrink: 0;
    }

    .amount-cell {
        background-color: #f8f9fa;
        text-align: right;
        padding-right: 1rem;
        padding-left: 1rem;
    }

    .header-amount {
        text-align: right;
        padding-right: 1rem;
        padding-left: 1rem;
    }

    .table-no-vertical-lines td,
    .table-no-vertical-lines th {
        border-right: none;
        border-left: none;
    }

    .month-cell {
        font-weight: bold;
    }

    /* Custom table styling */
    .table-custom thead th {
        background-color: var(--bs-tertiary-bg);
        color: var(--bs-emphasis-color);
        font-weight: 600;
        vertical-align: middle;
    }

    .table-custom tbody tr:nth-of-type(odd) {
        background-color: var(--bs-tertiary-bg);
    }

    .table-custom td,
    .table-custom th {
        vertical-align: middle;
        color: var(--bs-emphasis-color);
        width: auto;
        padding-left: 1rem;
        padding-right: 2rem;
        padding-top: 0.75rem;
        padding-bottom: 0.75rem;
    }

    .table-custom .month-cell,
    .table-custom .month-header {
        text-align: left;
    }

    .table-custom .amount-cell,
    .table-custom .amount-header {
        text-align: right;
    }

    .table-custom .net-profit-cell {
        font-weight: bold;
    }

    /* Responsive cards for mobile */
    @media (max-width: 575px) {
        .table-responsive .table thead {
            display: none;
        }

        .table-responsive .table tbody tr,
        .table-responsive .table tfoot tr {
            display: block;
            margin-bottom: 1rem;
            border: 1px solid var(--bs-border-color);
            border-radius: 0.25rem;
            background-color: var(--bs-body-bg);
        }

        .table-responsive .table tbody td,
        .table-responsive .table tfoot td {
            display: block;
            text-align: right;
            border: none;
            border-bottom: 1px solid var(--bs-border-color);
            padding-left: 50%;
            position: relative;
            color: var(--bs-body-color);
        }

        .table-responsive .table tfoot tr .month-cell {
            text-align: center;
            font-size: 1.2rem;
            padding-left: 0;
            background-color: var(--bs-tertiary-bg);
        }

        .table-responsive .table tfoot tr {
            background-color: var(--bs-secondary-bg);
            font-weight: bold;
        }

        .table-responsive .table tbody tr .month-cell {
            text-align: center;
            font-size: 1.2rem;
            padding-left: 0;
            background-color: var(--bs-tertiary-bg);
        }

        .table-responsive .table tbody td:last-child,
        .table-responsive .table tfoot td:last-child {
            border-bottom: none;
        }

        .table-responsive .table tbody td::before,
        .table-responsive .table tfoot td::before {
            content: attr(data-label);
            position: absolute;
            left: 0.75rem;
            width: 45%;
            padding-right: 0.75rem;
            text-align: left;
            font-weight: bold;
            color: var(--bs-body-color);
        }

        .table-responsive .table tbody tr .month-cell::before,
        .table-responsive .table tfoot tr .month-cell::before {
            content: "";
            /* Remove label for month cell */
        }
    }
</style>

{{-- Section 1: Overview (All Stores Totals) --}}
@if ($currentView === 'home')
<div class="container-fluid">
    <div class="card shadow-sm mb-4">
        <div class="card-header d-flex flex-wrap justify-content-between align-items-center">
            <h5 class="mb-0 me-3">{{ __('All Stores - Total Sums') }}</h5>
            {{-- Year Filter for Cards --}}
            <form action="{{ route('reports.index') }}" method="GET" class="d-flex flex-wrap gap-2 mt-2 mt-md-0" id="cardsFilterForm">
                <input type="hidden" name="view" value="home">
                <select name="cards_year" class="form-select form-select-md" onchange="document.getElementById('cardsFilterForm').submit();" style="width: auto;">
                    <option value="all" {{ $selectedYearCards == 'all' ? 'selected' : '' }}>{{ __('All Years') }}</option>
                    @foreach ($years as $year)
                    <option value="{{ $year }}" {{ $selectedYearCards == $year ? 'selected' : '' }}>{{ $year }}</option>
                    @endforeach
                </select>
                <input type="hidden" name="year" value="{{ $selectedYearTable }}">
                <input type="hidden" name="store_id" value="{{ $selectedStoreId ?? 'all' }}">
            </form>
        </div>
        <div class="card-body">
            <div class="row g-2">
                {{-- All Stores Total Income --}}
                <div class="col-md-4">
                    <div class="card stat-card">
                        <div class="card-body bg-body-tertiary d-flex align-items-center">
                            <div class="stat-card-dot bg-success"></div>
                            <div class="flex-grow-1 d-flex justify-content-between align-items-center">
                                <div class="text-xs font-weight-bold mb-1">{{ __('Total Income') }}</div>
                                <div class="h5 mb-0 font-weight-bold">@currency($totalAllIncome)</div>
                            </div>
                        </div>
                    </div>
                </div>
                {{-- Total Expense --}}
                <div class="col-md-4">
                    <div class="card stat-card">
                        <div class="card-body bg-body-tertiary d-flex align-items-center">
                            <div class="stat-card-dot bg-danger"></div>
                            <div class="flex-grow-1 d-flex justify-content-between align-items-center">
                                <div class="text-xs font-weight-bold mb-1">{{ __('Total Expenses') }}</div>
                                <div class="h5 mb-0 font-weight-bold">@currency($totalAllExpense)</div>
                            </div>
                        </div>
                    </div>
                </div>
                {{-- Net Profit --}}
                <div class="col-md-4">
                    <div class="card stat-card">
                        <div class="card-body bg-body-tertiary d-flex align-items-center">
                            <div class="stat-card-dot {{ $totalAllNet >= 0 ? 'bg-success' : 'bg-danger' }}"></div>
                            <div class="flex-grow-1 d-flex justify-content-between align-items-center">
                                <div class="text-xs font-weight-bold mb-1">{{ __('Net Profit') }}</div>
                                <div class="h4 mb-0 font-weight-bold">@currency($totalAllNet)</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Store-specific reports --}}
    <div class="row">
        @forelse ($storeReports as $report)
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card h-100 shadow-sm">
                <div class="card-header d-flex flex-row align-items-center justify-content-between">
                    <h5 class="mb-0">
                        <i class="fas fa-store me-2"></i>
                        {{ $report->name }} - {{ $selectedYearCards == 'all' ? __('All Time') : $selectedYearCards }}
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-2">
                        {{-- Total Income --}}
                        <div class="col-12">
                            <div class="card stat-card">
                                <div class="card-body bg-body-tertiary d-flex align-items-center">
                                    <div class="stat-card-dot bg-success"></div>
                                    <div class="flex-grow-1 d-flex justify-content-between align-items-center">
                                        <div class="text-xs font-weight-bold mb-1">{{ __('Total Income') }}</div>
                                        <div class="h5 mb-0 font-weight-bold">@currency($report->income)</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Total Expense --}}
                        <div class="col-12">
                            <div class="card stat-card">
                                <div class="card-body bg-body-tertiary d-flex align-items-center">
                                    <div class="stat-card-dot bg-danger"></div>
                                    <div class="flex-grow-1 d-flex justify-content-between align-items-center">
                                        <div class="text-xs font-weight-bold mb-1">{{ __('Total Expenses') }}</div>
                                        <div class="h5 mb-0 font-weight-bold">@currency($report->expense)</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Net Profit --}}
                        <div class="col-12">
                            <div class="card stat-card">
                                <div class="card-body bg-body-tertiary d-flex align-items-center">
                                    <div class="stat-card-dot {{ $report->net >= 0 ? 'bg-success' : 'bg-danger' }}"></div>
                                    <div class="flex-grow-1 d-flex justify-content-between align-items-center">
                                        <div class="text-xs font-weight-bold mb-1">{{ __('Net Profit') }}</div>
                                        <div class="h4 mb-0 font-weight-bold">@currency($report->net)</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @empty
        {{-- Empty State --}}
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body text-center">
                    <p class="mb-0">{{ __('No store data available. Please add a store to see reports.') }}</p>
                </div>
            </div>
        </div>
        @endforelse
    </div>
</div>
</div>
@endif

{{-- Section 2: Monthly Data Table --}}
@if ($currentView === 'monthly_sums')
<div class="container-fluid">
    <div class="card shadow-sm mb-4">
        <div class="card-header d-flex flex-wrap justify-content-between align-items-center">
            <h5 class="mb-0 me-3">{{ __('Monthly Sums') }}</h5>
            {{-- Filters for Table --}}
            <form action="{{ route('reports.index') }}#monthly-sums-section" method="GET" class="d-flex flex-wrap gap-2 mt-2 mt-md-0" id="tableFilterForm">
                <input type="hidden" name="view" value="monthly_sums">
                <select name="year" class="form-select form-select-md" onchange="this.form.submit();" style="width: auto;">
                    <option value="all" {{ $selectedYearTable == 'all' ? 'selected' : '' }}>{{ __('All Years') }}</option>
                    @foreach ($years as $year)
                    <option value="{{ $year }}" {{ $selectedYearTable == $year ? 'selected' : '' }}>{{ $year }}</option>
                    @endforeach
                </select>
                <select name="store_id" class="form-select form-select-md" onchange="this.form.submit();" style="width: auto; min-width: 150px;">
                    <option value="all" {{ $selectedStoreId == 'all' ? 'selected' : '' }}>{{ __('All Stores') }}</option>
                    @foreach ($stores as $store)
                    <option value="{{ $store->id }}" {{ $selectedStoreId == $store->id ? 'selected' : '' }}>{{ $store->name }}</option>
                    @endforeach
                </select>
                <input type="hidden" name="cards_year" value="{{ $selectedYearCards }}">
            </form>
        </div>
        <div class="table-responsive">
            <table class="table table-striped table-hover table-bordered table-custom">
                <thead>
                    <tr>
                        <th>{{ __('Month') }}</th>
                        <th class="header-amount">{{ __('Income') }}</th>
                        <th class="header-amount">{{ __('Expenses') }}</th>
                        <th class="header-amount">{{ __('Net Profit') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($monthlyData as $data)
                    <tr>
                        <td data-label="{{ __('Month') }}" class="month-cell">{{ $data['month'] }}</td>
                        <td data-label="{{ __('Income') }}" class="amount-cell">@currency($data['income'])</td>
                        <td data-label="{{ __('Expenses') }}" class="amount-cell">@currency($data['expense'])</td>
                        <td data-label="{{ __('Net Profit') }}" class="amount-cell net-profit-cell">@currency($data['net'])</td>
                    </tr>
                    @endforeach
                </tbody>
                {{-- Table Footer with Totals --}}
                <tfoot>
                    <tr class="fw-bold">
                        <td data-label="{{ __('Total') }}" class="month-cell">{{ __('Total') }}</td>
                        <td data-label="{{ __('Total Income') }}" class="amount-cell">@currency($totalMonthlyIncome)</td>
                        <td data-label="{{ __('Total Expense') }}" class="amount-cell">@currency($totalMonthlyExpense)</td>
                        <td data-label="{{ __('Total Net') }}" class="amount-cell net-profit-cell">@currency($totalMonthlyNet)</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endif

{{-- Section 3: Analysis for Income/Expenses by Category/Shift --}}
@if ($currentView === 'category_analysis')
<div class="container-fluid">
    <div class="card shadow-sm mb-4">
        <div class="card-header d-flex flex-wrap justify-content-between align-items-center">
            <h5 class="mb-0 me-3">{{ __('Analysis for Income - Expenses') }}</h5>
            {{-- Filters for Analysis --}}
            <form action="{{ route('reports.index') }}#category-analysis-section" method="GET" class="d-flex flex-wrap gap-2 mt-2 mt-md-0" id="analysisFilterForm">
                {{-- Hidden fields to retain other filters --}}
                <input type="hidden" name="view" value="category_analysis">
                <input type="hidden" name="year" value="{{ $selectedYearTable }}">
                <input type="hidden" name="cards_year" value="{{ $selectedYearCards }}">

                <select name="type" class="form-select form-select-md" onchange="this.form.submit()" style="width: auto;">
                    <option value="income" {{ $analysisType == 'income' ? 'selected' : '' }}>{{ __('Income') }}</option>
                    <option value="expense" {{ $analysisType == 'expense' ? 'selected' : '' }}>{{ __('Expenses') }}</option>
                </select>

                <select name="analysis_year" class="form-select form-select-md" onchange="this.form.submit()" style="width: auto;">
                    @foreach ($years as $year)
                    <option value="{{ $year }}" {{ $selectedYearAnalysis == $year ? 'selected' : '' }}>{{ $year }}</option>
                    @endforeach
                </select>

                <select name="store_id" class="form-select form-select-md" onchange="this.form.submit()" style="width: auto; min-width: 150px;">
                    <option value="all" {{ $selectedStoreId == 'all' ? 'selected' : '' }}>{{ __('All Stores') }}</option>
                    @foreach ($stores as $store)
                    <option value="{{ $store->id }}" {{ $selectedStoreId == $store->id ? 'selected' : '' }}>{{ $store->name }}</option>
                    @endforeach
                </select>

                @if ($analysisType === 'income')
                <select name="source_id" class="form-select form-select-md" onchange="this.form.submit()" style="width: auto;">
                    <option value="all" {{ $selectedSourceId == 'all' ? 'selected' : '' }}>{{ __('All Sources') }}</option>
                    @foreach ($sources as $source)
                    <option value="{{ $source->id }}" {{ $selectedSourceId == $source->id ? 'selected' : '' }}>{{ $source->name }}</option>
                    @endforeach
                </select>

                <select name="payment_method_id" class="form-select form-select-md" onchange="this.form.submit()" style="width: auto;">
                    <option value="all" {{ $selectedPaymentMethodId == 'all' ? 'selected' : '' }}>{{ __('All Payment Methods') }}</option>
                    @foreach ($paymentMethods as $paymentMethod)
                    <option value="{{ $paymentMethod->id }}" {{ $selectedPaymentMethodId == $paymentMethod->id ? 'selected' : '' }}>{{ $paymentMethod->name }}</option>
                    @endforeach
                </select>
                @endif
            </form>
        </div>
        <div class="table-responsive">
            <table class="table table-striped table-hover table-bordered table-custom">
                <thead>
                    <tr>
                        <th class="month-header">{{ __('Month') }}</th>
                        @foreach ($analysisColumns as $column)
                        <th class="header-amount">{{ $column->name }}</th>
                        @endforeach
                        <th class="header-amount">{{ __('Total') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($analysisData as $monthData)
                    <tr>
                        <td data-label="{{ __('Month') }}" class="month-cell">{{ $monthData['month'] }}</td>
                        @foreach ($analysisColumns as $column)
                        <td data-label="{{ $column->name }}" class="amount-cell">@currency($monthData[$column->id] ?? 0)</td>
                        @endforeach
                        <td data-label="{{ __('Total') }}" class="amount-cell fw-bold">@currency($monthData['row_total'])</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="fw-bold">
                        <td data-label="{{ __('Total') }}" class="month-cell">{{ __('Total') }}</td>
                        @foreach ($analysisColumns as $column)
                        <td data-label="{{ $column->name }}" class="amount-cell">@currency($analysisColumnTotals[$column->id] ?? 0)</td>
                        @endforeach
                        <td data-label="{{ __('Grand Total') }}" class="amount-cell">@currency(array_sum($analysisColumnTotals))</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endif

{{-- Section 4: Day Income Analysis --}}
@if ($currentView === 'day_income_analysis')
<div class="container-fluid">
    <div class="card shadow-sm mb-4">
        <div class="card-header d-flex flex-wrap justify-content-between align-items-center">
            <h5 class="mb-0 me-3">{{ __('Day Income Analysis') }}</h5>
            <form method="GET" action="{{ route('reports.index') }}#day-income-analysis-section" id="dayIncomeAnalysisForm" class="d-flex flex-wrap gap-2 mt-2 mt-md-0">
                <input type="hidden" name="view" value="day_income_analysis">
                {{-- Other hidden fields are correct --}}

                {{-- Year Selector - "All Years" option removed --}}
                <select name="day_analysis_year" class="form-select form-select-md" onchange="this.form.submit()" style="width: auto;">
                    @foreach ($years as $year)
                    <option value="{{ $year }}" {{ $dayAnalysisYear == $year ? 'selected' : '' }}>{{ $year }}</option>
                    @endforeach
                </select>

                {{-- NEW: Month Selector --}}
                <select name="day_analysis_month" class="form-select form-select-md" onchange="this.form.submit()" style="width: auto;">
                    @for ($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" {{ $dayAnalysisMonth == $m ? 'selected' : '' }}>
                        {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
                        </option>
                        @endfor
                </select>

                {{-- Store, Source, and Payment Method selectors are correct --}}
                <select name="day_analysis_store_id" class="form-select form-select-md" onchange="this.form.submit()" style="width: auto; min-width: 150px;">
                    <option value="all" {{ $dayAnalysisStoreId == 'all' ? 'selected' : '' }}>{{ __('All Stores') }}</option>
                    @foreach ($stores as $store)<option value="{{ $store->id }}" {{ $dayAnalysisStoreId == $store->id ? 'selected' : '' }}>{{ $store->name }}</option>@endforeach
                </select>
                <select name="day_analysis_source_id" class="form-select form-select-md" onchange="this.form.submit()" style="width: auto;">
                    <option value="all" {{ $dayAnalysisSourceId == 'all' ? 'selected' : '' }}>{{ __('All Sources') }}</option>
                    @foreach ($sources as $source)<option value="{{ $source->id }}" {{ $dayAnalysisSourceId == $source->id ? 'selected' : '' }}>{{ $source->name }}</option>@endforeach
                </select>
                <select name="day_analysis_payment_method_id" class="form-select form-select-md" onchange="this.form.submit()" style="width: auto;">
                    <option value="all" {{ $dayAnalysisPaymentMethodId == 'all' ? 'selected' : '' }}>{{ __('All Payment Methods') }}</option>
                    @foreach ($paymentMethods as $paymentMethod)<option value="{{ $paymentMethod->id }}" {{ $dayAnalysisPaymentMethodId == $paymentMethod->id ? 'selected' : '' }}>{{ $paymentMethod->name }}</option>@endforeach
                </select>
            </form>
        </div>
        <div class="table-responsive">
            <table class="table table-striped table-hover table-bordered table-custom">
                <thead>
                    <tr>
                        <th class="month-header">{{ __('Day') }}</th>
                        @foreach ($dayIncomeShifts as $shift)
                        <th class="header-amount">{{ $shift->name }}</th>
                        @endforeach
                        <th class="header-amount">{{ __('Total') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @php $columnTotals = array_fill_keys($dayIncomeShifts->pluck('id')->toArray(), 0); @endphp
                    @forelse ($dayIncomeData as $dateKey => $shifts)
                    <tr>
                        <td data-label="{{ __('Day') }}" class="month-cell">{{ $shifts['date'] }}</td>
                        @php $rowTotal = 0; @endphp
                        @foreach ($dayIncomeShifts as $shift)
                        @php
                        $amount = $shifts[$shift->id] ?? 0;
                        $rowTotal += $amount;
                        $columnTotals[$shift->id] += $amount;
                        @endphp
                        <td data-label="{{ $shift->name }}" class="amount-cell">@currency($amount)</td>
                        @endforeach
                        <td data-label="{{ __('Total') }}" class="amount-cell fw-bold">@currency($rowTotal)</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="{{ count($dayIncomeShifts) + 2 }}" class="text-center p-4">{{ __('No data available for the selected filters.') }}</td>
                    </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr class="fw-bold">
                        <td data-label="{{ __('Total') }}" class="month-cell">{{ __('Total') }}</td>
                        @foreach ($dayIncomeShifts as $shift)
                        <td data-label="{{ $shift->name }}" class="amount-cell">@currency($columnTotals[$shift->id])</td>
                        @endforeach
                        <td data-label="{{ __('Grand Total') }}" class="amount-cell">@currency(array_sum($columnTotals))</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endif
@endsection