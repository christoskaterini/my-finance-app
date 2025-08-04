@extends('layouts.studio')

@section('content')
<div class="container-fluid">
    <div class="row g-4">
        <!-- Chart 1: Overall Financial Performance -->
        <div class="col-12">
            <div class="card shadow-sm h-100">
                <div class="card-header d-flex flex-wrap align-items-center justify-content-between">
                    <h5 class="mb-0 me-3">{{ __('Overall Financial Performance') }}</h5>
                    <div class="mt-2 mt-md-0 ms-auto d-flex flex-wrap gap-2 chart-filter-group" data-chart-target="performanceChart">
                        <select class="form-select form-select-sm" name="year" style="width: 120px;">
                            <option value="all">{{ __('All Years') }}</option>
                            @foreach ($years as $year) <option value="{{ $year }}" {{ $year == date('Y') ? 'selected' : '' }}>{{ $year }}</option> @endforeach
                        </select>
                        <select class="form-select form-select-sm" name="store_id" style="width: 180px;">
                            <option value="all" selected>{{ __('All Stores') }}</option>
                            @foreach ($stores as $store) <option value="{{ $store->id }}">{{ $store->name }}</option> @endforeach
                        </select>
                    </div>
                </div>
                <div class="card-body"><div style="height: 350px;"><canvas id="performanceChart"></canvas></div></div>
            </div>
        </div>

        <!-- Chart 2: Income by Source -->
        <div class="col-lg-6 col-md-12">
            <div class="card shadow-sm h-100">
                 <div class="card-header d-flex flex-wrap align-items-center justify-content-between">
                    <h5 class="mb-0 me-3">{{ __('Income by Source') }}</h5>
                    <div class="mt-2 mt-md-0 ms-auto d-flex flex-wrap gap-2 chart-filter-group" data-chart-target="incomeSourceChart">
                        <select class="form-select form-select-sm" name="year" style="width: 120px;">
                            <option value="all">{{ __('All Years') }}</option>
                            @foreach ($years as $year) <option value="{{ $year }}" {{ $year == date('Y') ? 'selected' : '' }}>{{ $year }}</option> @endforeach
                        </select>
                        <select class="form-select form-select-sm" name="store_id" style="width: 180px;">
                            <option value="all" selected>{{ __('All Stores') }}</option>
                            @foreach ($stores as $store) <option value="{{ $store->id }}">{{ $store->name }}</option> @endforeach
                        </select>
                    </div>
                </div>
                <div class="card-body d-flex align-items-center justify-content-center"><div style="height: 300px; max-width: 300px;"><canvas id="incomeSourceChart"></canvas></div></div>
            </div>
        </div>

        <!-- Chart 3: Income by Shift -->
        <div class="col-lg-6 col-md-12">
            <div class="card shadow-sm h-100">
                 <div class="card-header d-flex flex-wrap align-items-center justify-content-between">
                    <h5 class="mb-0 me-3">{{ __('Income by Shift') }}</h5>
                    <div class="mt-2 mt-md-0 ms-auto d-flex flex-wrap gap-2 chart-filter-group" data-chart-target="incomeByShiftChart">
                         <select class="form-select form-select-sm" name="year" style="width: 120px;">
                            <option value="all">{{ __('All Years') }}</option>
                            @foreach ($years as $year) <option value="{{ $year }}" {{ $year == date('Y') ? 'selected' : '' }}>{{ $year }}</option> @endforeach
                        </select>
                        <select class="form-select form-select-sm" name="store_id" style="width: 180px;">
                            <option value="all" selected>{{ __('All Stores') }}</option>
                            @foreach ($stores as $store) <option value="{{ $store->id }}">{{ $store->name }}</option> @endforeach
                        </select>
                    </div>
                </div>
                <div class="card-body d-flex align-items-center justify-content-center"><div style="height: 300px; max-width: 300px;"><canvas id="incomeByShiftChart"></canvas></div></div>
            </div>
        </div>
        
        <!-- Chart 4: Expense by Category -->
        <div class="col-12">
            <div class="card shadow-sm h-100">
                <div class="card-header d-flex flex-wrap align-items-center justify-content-between">
                    <h5 class="mb-0 me-3">{{ __('Expenses by Category') }}</h5>
                    <div class="mt-2 mt-md-0 ms-auto d-flex flex-wrap gap-2 chart-filter-group" data-chart-target="expenseCategoryChart">
                         <select class="form-select form-select-sm" name="year" style="width: 120px;">
                            <option value="all">{{ __('All Years') }}</option>
                            @foreach ($years as $year) <option value="{{ $year }}" {{ $year == date('Y') ? 'selected' : '' }}>{{ $year }}</option> @endforeach
                        </select>
                        <select class="form-select form-select-sm" name="store_id" style="width: 180px;">
                            <option value="all" selected>{{ __('All Stores') }}</option>
                            @foreach ($stores as $store) <option value="{{ $store->id }}">{{ $store->name }}</option> @endforeach
                        </select>
                    </div>
                </div>
                <div class="card-body"><div style="height: 300px;"><canvas id="expenseCategoryChart"></canvas></div></div>
            </div>
        </div>

        <!-- Chart 5: Store Performance Comparison -->
        <div class="col-12">
            <div class="card shadow-sm h-100">
                <div class="card-header d-flex flex-wrap align-items-center justify-content-between">
                    <h5 class="mb-0 me-3">{{ __('Store Performance Comparison') }}</h5>
                     <div class="mt-2 mt-md-0 ms-auto d-flex flex-wrap gap-2 chart-filter-group" data-chart-target="storePerformanceChart">
                        <select class="form-select form-select-sm" name="year" style="width: 120px;">
                            <option value="all">{{ __('All Years') }}</option>
                            @foreach ($years as $year) <option value="{{ $year }}" {{ $year == date('Y') ? 'selected' : '' }}>{{ $year }}</option> @endforeach
                        </select>
                        <div class="dropdown">
                            <button class="btn btn-secondary btn-sm dropdown-toggle" type="button" id="storeCompareDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                {{ __('Select Stores') }}
                            </button>
                            <div class="dropdown-menu p-2" aria-labelledby="storeCompareDropdown" style="width: 250px;">
                                @foreach ($stores as $store)
                                <div class="form-check">
                                    <input class="form-check-input store-compare-checkbox" type="checkbox" name="compare_stores[]" value="{{ $store->id }}" id="store_{{ $store->id }}" checked>
                                    <label class="form-check-label" for="store_{{ $store->id }}">{{ $store->name }}</label>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body"><div style="height: 350px;"><canvas id="storePerformanceChart"></canvas></div></div>
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
{{-- Chart.js via CDN --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    let activeCharts = {};

    const replaceCanvas = (chartId) => {
        if (activeCharts[chartId]) activeCharts[chartId].destroy();
        const oldCanvas = document.getElementById(chartId);
        if (!oldCanvas) return null;
        const newCanvas = document.createElement('canvas');
        newCanvas.id = chartId;
        oldCanvas.parentNode.replaceChild(newCanvas, oldCanvas);
        return newCanvas;
    };
    
    const getChartOptions = (baseOptions = {}) => {
        const isMobile = window.innerWidth < 768;
        const responsiveOptions = { plugins: { legend: { display: !isMobile } }, scales: { y: { ticks: { font: { size: isMobile ? 10 : 12 } } }, x: { ticks: { font: { size: isMobile ? 10 : 12 } } } } };
        return { ...baseOptions, plugins: { ...baseOptions.plugins, ...responsiveOptions.plugins }, scales: { x: { ...baseOptions.scales?.x, ...responsiveOptions.scales.x }, y: { ...baseOptions.scales?.y, ...responsiveOptions.scales.y } } };
    };
    
    const renderEmptyState = (canvas) => {
        if (!canvas) return;
        const parent = canvas.parentElement;
        parent.innerHTML = `<div class="d-flex align-items-center justify-content-center h-100 text-muted">{{ __('No data available for the selected filters.') }}</div>`;
    };

    async function updateChart(chartId) {
        const filterGroup = document.querySelector(`.chart-filter-group[data-chart-target="${chartId}"]`);
        if (!filterGroup) return;

        const params = new URLSearchParams();
        params.append('chartId', chartId);

        // Standard select filters
        filterGroup.querySelectorAll('select').forEach(select => {
            params.append(select.name, select.value);
        });
        
        // Special case for store comparison checkboxes
        if (chartId === 'storePerformanceChart') {
            filterGroup.querySelectorAll('input[name="compare_stores[]"]:checked').forEach(checkbox => {
                params.append('compare_stores[]', checkbox.value);
            });
        }
        
        const response = await fetch(`{{ route('reports.charts.data') }}?${params.toString()}`);
        const chartData = await response.json();
        
        const canvas = replaceCanvas(chartId);
        if (!canvas) return;
        
        let chartType = 'bar'; // Default
        let options = {};
        if (chartId === 'performanceChart') {
            chartType = 'line';
            options = getChartOptions({ responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true } } });
        } else if (chartId === 'incomeSourceChart' || chartId === 'incomeByShiftChart') {
            chartType = 'doughnut';
            options = getChartOptions({ responsive: true, maintainAspectRatio: false });
        } else if (chartId === 'expenseCategoryChart') {
            options = getChartOptions({ indexAxis: 'y', responsive: true, maintainAspectRatio: false, scales: { x: { beginAtZero: true } } });
        } else if (chartId === 'storePerformanceChart') {
            options = getChartOptions({ responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true } } });
        }

        const totalDataPoints = chartData.datasets.reduce((sum, dataset) => sum + dataset.data.reduce((s, val) => s + val, 0), 0);

        if (chartData.labels.length > 0 && totalDataPoints > 0) {
            activeCharts[chartId] = new Chart(canvas, { type: chartType, data: chartData, options: options });
        } else {
            renderEmptyState(canvas);
        }
    }

    // Attach event listeners to all filters
    document.querySelectorAll('.chart-filter-group select, .chart-filter-group input[type="checkbox"]').forEach(filter => {
        filter.addEventListener('change', (e) => {
            const chartId = e.currentTarget.closest('.chart-filter-group').dataset.chartTarget;
            updateChart(chartId);
        });
    });

    // Initial load for all charts
    document.querySelectorAll('.chart-filter-group').forEach(group => {
        updateChart(group.dataset.chartTarget);
    });

    // Responsive redraw on resize
    let resizeTimer;
    window.addEventListener('resize', () => {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(() => {
            document.querySelectorAll('.chart-filter-group').forEach(group => {
                updateChart(group.dataset.chartTarget);
            });
        }, 250);
    });
});
</script>
@endpush