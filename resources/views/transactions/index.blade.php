@extends('layouts.studio')
@section('page-title', __('Transactions'))

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<style>
    .filter-box {
        padding: 1rem;
        margin-bottom: 1rem;
        background-color: var(--bs-body-bg);
        border: 1px solid var(--bs-border-color);
        border-radius: var(--bs-border-radius);
    }

    .transactions-table .col-date {
        width: 10%;
    }

    .transactions-table .col-user {
        width: 15%;
    }

    .transactions-table .col-store {
        width: 15%;
    }

    .transactions-table .col-type {
        width: 8%;
    }

    .transactions-table .col-details {
        width: 25%;
    }

    .transactions-table .col-amount {
        width: 12%;
    }

    .transactions-table .col-actions {
        width: 10%;
    }
</style>
@endpush

@section('content')
{{-- Summary Cards --}}
<div class="row">
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card card-body d-flex flex-row justify-content-between align-items-center">
            <h6 class="text-muted mb-0">{{__('Total Income')}}</h6>
            <h4 class="text-success mb-0">@currency($totalIncome)</h4>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card card-body d-flex flex-row justify-content-between align-items-center">
            <h6 class="text-muted mb-0">{{__('Total Expenses')}}</h6>
            <h4 class="text-danger mb-0">@currency($totalExpenses)</h4>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card card-body d-flex flex-row justify-content-between align-items-center">
            <h6 class="text-muted mb-0">{{__('Net Total')}}</h6>
            <h4 class="{{ $netTotal >= 0 ? 'text-success' : 'text-danger' }} mb-0">@currency($netTotal)</h4>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card card-body d-flex flex-row justify-content-between align-items-center">
            <h6 class="text-muted mb-0">{{__('Transactions')}}</h6>
            <h4 class="mb-0">{{ $transactionCount }}</h4>
        </div>
    </div>
</div>

{{-- Main Data Table --}}
<div class="card">
    <div class="card-header bg-body-tertiary">
        <div class="filter-box">
            <form action="{{ route('transactions.index') }}" method="GET" class="row g-2 align-items-end">
                {{-- Date Range --}}
                <div class="col-md-auto" id="date-range-pickers">
                    <input type="date" class="form-control form-control-sm" name="date_from" value="{{ $dateFrom ?? '' }}" placeholder="{{ __('Date From') }}">
                </div>
                <div class="col-md-auto" id="date-range-pickers2">
                    <input type="date" class="form-control form-control-sm" name="date_to" value="{{ $dateTo ?? '' }}" placeholder="{{ __('Date To') }}">
                </div>

                {{-- Year Selector --}}
                <div class="col-md-auto">
                    <select name="year" id="year-select" class="form-select form-select-sm">
                        <option value="">{{ __('Year') }}</option>
                        @foreach($years as $year)
                        <option value="{{ $year }}" @selected($year==$selectedYear)>{{ $year }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Month Selector --}}
                <div class="col-md-auto">
                    <select name="month" id="month-select" class="form-select form-select-sm">
                        <option value="">{{ __('Month') }}</option>
                        @for ($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" @selected($m==$selectedMonth)>{{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}</option>
                            @endfor
                    </select>
                </div>

                {{-- Other filters --}}
                <div class="col-md-auto">
                    <select name="type" class="form-select form-select-sm">
                        <option value="">{{__('All Types')}}</option>
                        <option value="income" @selected(request('type')=='income' )>{{__('Income')}}</option>
                        <option value="expense" @selected(request('type')=='expense' )>{{__('Expense')}}</option>
                    </select>
                </div>
                <div class="col-md-auto">
                    <select name="store_id" class="form-select form-select-sm">
                        <option value="">{{__('All Stores')}}</option>
                        @foreach($stores as $store)
                        <option value="{{ $store->id }}" @selected(request('store_id')==$store->id)>{{ $store->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-auto">
                    <select name="per_page" id="per_page" class="form-select form-select-sm">
                        <option value="100" @selected(request('per_page')==100)>100</option>
                        <option value="250" @selected(request('per_page')==250)>250</option>
                        <option value="500" @selected(request('per_page')==500)>500</option>
                        <option value="all" @selected(request('per_page')=='all' )>{{__('All')}}</option>
                    </select>
                </div>
                <div class="col-md-auto">
                    <input type="text" name="search" class="form-control form-control-sm" placeholder="{{ __('Search...') }}" value="{{ request('search') }}">
                </div>
                <div class="col-md-auto ms-auto">
                    <div class="d-flex justify-content-end gap-4">
                        <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-funnel-fill"></i></button>
                        <a href="{{ route('transactions.index') }}" class="btn btn-secondary btn-sm"><i class="bi bi-arrow-clockwise"></i></a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card-body">
        <form id="bulk-delete-form" action="{{ route('transactions.bulkDelete') }}" method="POST">
            @csrf
            @method('DELETE')
            <div id="bulk-actions-header" class="d-none align-items-center justify-content-between mb-3 p-2 rounded" style="background-color: rgba(var(--bs-danger-rgb), 0.1);">
                <div><span id="selected-count" class="fw-bold">0</span> {{__('items selected')}}</div>
                <button type="submit" class="btn btn-danger">{{__('Delete Selected')}}</button>
            </div>

            <div class="table-responsive">
                <table class="table table-hover responsive-card-table transactions-table">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 1%;"><input class="form-check-input" type="checkbox" id="select-all-checkbox"></th>
                            <th class="col-date">{{__('Date')}}</th>
                            <th class="col-user">{{__('User')}}</th>
                            <th class="col-store">{{__('Store')}}</th>
                            <th class="col-type">{{__('Type')}}</th>
                            <th class="col-details">{{__('Details')}}</th>
                            <th class="col-amount text-end">{{__('Amount')}}</th>
                            <th class="col-actions text-end">{{__('Actions')}}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($transactions as $transaction)
                        <tr>
                            <td><input class="form-check-input row-checkbox" type="checkbox" name="ids[]" value="{{ $transaction->id }}"></td>
                            <td data-label="{{__('Date')}}">{{ $transaction->transaction_date }}</td>
                            <td data-label="{{__('User')}}">{{ $transaction->user->name ?? __('(Deleted User)') }}</td>
                            <td data-label="{{__('Store')}}">{{ $transaction->store->name ?? __('(Deleted Store)') }}</td>
                            <td data-label="{{__('Type')}}">
                                <span class="badge bg-{{ $transaction->type == 'income' ? 'success' : 'danger' }}">{{ __(ucfirst($transaction->type)) }}</span>
                            </td>
                            <td data-label="{{__('Details')}}">
                                @if($transaction->type == 'income')
                                {{ $transaction->shift->name ?? __('(deleted)') }} /
                                {{ $transaction->source->name ?? __('(deleted)') }} /
                                {{ $transaction->paymentMethod->name ?? __('(deleted)') }}
                                @else
                                {{ $transaction->expenseCategory->name ?? __('(Uncategorized)') }}
                                @if($transaction->notes)
                                / <span class="text-muted">{{ $transaction->notes }}</span>
                                @endif
                                @endif
                            </td>
                            <td data-label="{{__('Amount')}}" class="text-end">@currency($transaction->amount)</td>
                            <td data-label="{{__('Actions')}}" class="text-end">
                                <a href="{{ route('transactions.edit', $transaction->id) }}" class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-pencil-fill"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-5">{{__('No transactions found.')}}</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </form>
        <div class="mt-3 d-flex justify-content-center">{{ $transactions->links() }}</div>
    </div>
</div>

{{-- Bulk Delete Confirmation Modal --}}
<div class="modal fade" id="deleteConfirmationModal" tabindex="-1" aria-labelledby="deleteConfirmationModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteConfirmationModalLabel">{{ __('Confirm Deletion') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
            </div>
            <div class="modal-body">
                {{ __('Are you sure you want to delete the selected transactions? This action cannot be undone.') }}
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                <button type="button" class="btn btn-danger" id="confirm-delete-btn">{{ __('Delete') }}</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // --- Bulk Delete Checkbox Logic ---
        const selectAllCheckbox = document.getElementById('select-all-checkbox');
        const rowCheckboxes = document.querySelectorAll('.row-checkbox');
        const bulkActionsHeader = document.getElementById('bulk-actions-header');
        const selectedCount = document.getElementById('selected-count');
        const bulkDeleteForm = document.getElementById('bulk-delete-form');
        const deleteModal = new bootstrap.Modal(document.getElementById('deleteConfirmationModal'));
        const confirmDeleteBtn = document.getElementById('confirm-delete-btn');

        function updateBulkActionsHeader() {
            const checkedCount = document.querySelectorAll('.row-checkbox:checked').length;
            if (selectedCount) {
                selectedCount.textContent = checkedCount;
            }
            if (bulkActionsHeader) {
                if (checkedCount > 0) {
                    bulkActionsHeader.classList.remove('d-none');
                    bulkActionsHeader.classList.add('d-flex');
                } else {
                    bulkActionsHeader.classList.add('d-none');
                    bulkActionsHeader.classList.remove('d-flex');
                }
            }
        }

        if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', function() {
                rowCheckboxes.forEach(checkbox => {
                    checkbox.checked = this.checked;
                });
                updateBulkActionsHeader();
            });
        }

        rowCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', updateBulkActionsHeader);
        });

        updateBulkActionsHeader();

        if (bulkDeleteForm) {
            bulkDeleteForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const checkedCount = document.querySelectorAll('.row-checkbox:checked').length;
                if (checkedCount > 0) {
                    deleteModal.show();
                }
            });
        }

        if (confirmDeleteBtn) {
            confirmDeleteBtn.addEventListener('click', function() {
                bulkDeleteForm.submit();
            });
        }

        // --- Filter Interaction Logic ---
        const yearSelect = document.getElementById('year-select');
        const monthSelect = document.getElementById('month-select');
        const dateFromInput = document.querySelector('input[name="date_from"]');
        const dateToInput = document.querySelector('input[name="date_to"]');

        function toggleDatePickers(disabled) {
            if (dateFromInput) dateFromInput.disabled = disabled;
            if (dateToInput) dateToInput.disabled = disabled;
            if (disabled) {
                if (dateFromInput) dateFromInput.value = '';
                if (dateToInput) dateToInput.value = '';
            }
        }

        function toggleYearMonth(disabled) {
            if (yearSelect) yearSelect.disabled = disabled;
            if (monthSelect) monthSelect.disabled = disabled;
            if (disabled) {
                if (yearSelect) yearSelect.value = '';
                if (monthSelect) monthSelect.value = '';
            }
        }

        function setupFilterListeners() {
            if (yearSelect) {
                yearSelect.addEventListener('change', () => toggleDatePickers(!!yearSelect.value || !!monthSelect.value));
            }
            if (monthSelect) {
                monthSelect.addEventListener('change', () => toggleDatePickers(!!yearSelect.value || !!monthSelect.value));
            }
            if (dateFromInput) {
                dateFromInput.addEventListener('input', () => toggleYearMonth(!!dateFromInput.value || !!dateToInput.value));
            }
            if (dateToInput) {
                dateToInput.addEventListener('input', () => toggleYearMonth(!!dateFromInput.value || !!dateToInput.value));
            }
        }

        setupFilterListeners();
    });
</script>
@endpush