@extends('layouts.studio')
@section('page-title', __('Transactions'))

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<style>
    /* Removed .filter-box styles */

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

    /* Default Spacing */
    .transactions-table .col-checkbox {
        text-align: center;
        vertical-align: middle;
        padding-left: 0 !important;
        padding-right: 0 !important;
    }

    .transactions-table .col-checkbox .form-check-input {
        margin: 0 auto;
        float: none;
        display: block;
    }

    /* Spreadsheet Mode Styles - Grid on Desktop/Tablet, Cards on Mobile */
    @media (min-width: 576px) {
        .spreadsheet-active .transactions-table {
            table-layout: fixed;
            width: 100%;
        }

        .spreadsheet-active .col-checkbox { width: 50px !important; }
        .transactions-table .col-checkbox { width: 50px !important; }
        .spreadsheet-active .col-date { width: 150px !important; }
        .spreadsheet-active .col-user { width: 130px !important; }
        .spreadsheet-active .col-store { width: 160px !important; }
        .spreadsheet-active .col-type { width: 100px !important; }
        .spreadsheet-active .col-details { width: 320px !important; }
        .spreadsheet-active .col-amount { width: 130px !important; }
        .spreadsheet-active .col-actions { width: 80px !important; }

        /* Selection Highlight */
        .spreadsheet-active tr:has(.row-checkbox:checked) td {
            box-shadow: inset 0 0 0 9999px rgba(13, 110, 253, 0.1) !important;
        }

        /* Modified Row Highlighting */
        .spreadsheet-active tr.row-modified td {
            box-shadow: inset 0 0 0 9999px rgba(245, 158, 11, 0.15) !important;
            transition: box-shadow 0.3s ease;
        }
        
        .spreadsheet-active .ss-input {
            width: 100%;
        }
    }

    /* Mobile Spreadsheet Adjustments (Card Mode) - Compact Single Line */
    @media (max-width: 575px) {
        .spreadsheet-active .transactions-table {
            table-layout: auto !important;
            width: 100% !important;
        }

        .spreadsheet-active .responsive-card-table td {
            flex-direction: row !important;
            justify-content: flex-start !important;
            align-items: center !important;
            height: auto !important;
            padding: 0.5rem 0.75rem !important;
            overflow-x: hidden;
        }

        .spreadsheet-active .responsive-card-table td::before {
            margin-bottom: 0 !important;
            width: 90px !important; /* Aggressively reduced to fit 375px */
            flex-shrink: 0;
            margin-right: 10px;
            font-size: 0.8rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .spreadsheet-active .ss-input,
        .spreadsheet-active .responsive-card-table td > div {
            flex-grow: 1 !important;
            width: 100% !important;
            margin-top: 0;
            min-width: 0 !important;
        }
        
        .transactions-table .col-checkbox {
            width: 100% !important;
            text-align: center !important;
            display: flex !important;
            justify-content: center !important;
            align-items: center !important;
            padding: 0.5rem !important;
        }

        .transactions-table .col-checkbox::before {
            display: none !important;
        }

        /* Prevent filter form row from causing overflow */
        #filter-form.row {
            margin-left: 0;
            margin-right: 0;
        }
    }

    /* Input Field Highlighting with High Specificity */
    .spreadsheet-active .transactions-table .ss-input.is-modified {
        background-color: #fffde7 !important;
        border-color: #f59e0b !important;
        border-width: 1px !important;
        box-shadow: 0 0 0 0.2rem rgba(245, 158, 11, 0.25) !important;
        font-weight: 600 !important;
        color: #451a03 !important;
        padding-top: 0.15rem !important;
        padding-bottom: 0.15rem !important;
    }

    /* Dark Mode Overrides for Inputs */
    [data-bs-theme="dark"] .spreadsheet-active .transactions-table .ss-input.is-modified {
        background-color: #451a03 !important;
        color: #fff !important;
        border-color: #fbbf24 !important;
        box-shadow: 0 0 8px rgba(251, 191, 36, 0.3) !important;
    }

    input[type="number"].ss-input::-webkit-inner-spin-button,
    input[type="number"].ss-input::-webkit-outer-spin-button {
        margin-left: 10px; 
    }

    .ss-input:focus {
        border-color: #0dcafd;
        box-shadow: 0 0 0 0.25rem rgba(13, 202, 240, 0.25);
    }

    /* Modified Badge style removed */

    .save-count-badge {
        font-size: 0.7rem;
        padding: 0.15rem 0.4rem;
        border-radius: 4px;
        background-color: rgba(255, 255, 255, 0.25);
        color: #fff;
        font-weight: 800;
        margin-left: 6px;
        vertical-align: middle;
    }

    [data-bs-theme="dark"] .save-count-badge {
        background-color: rgba(0, 0, 0, 0.3);
        color: #fff;
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
    <div class="card-header bg-body-tertiary py-2">
        <form id="filter-form" action="{{ route('transactions.index') }}" method="GET" class="row g-2 align-items-center">
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
                    <div class="d-flex justify-content-end gap-2">
                        <button type="button" id="save-all-btn" class="btn btn-success btn-sm d-none" title="{{ __('Save Changes') }}" disabled>
                            <i class="bi bi-cloud-arrow-up-fill"></i>
                            <span id="save-count-container"></span>
                        </button>
                        <button type="button" id="cancel-spreadsheet-btn" class="btn btn-danger btn-sm d-none" title="{{ __('Discard Changes') }}">
                            <i class="bi bi-arrow-counterclockwise"></i>
                        </button>
                        <button type="button" id="toggle-spreadsheet-mode" class="btn btn-secondary btn-sm" title="{{ __('Spreadsheet Mode') }}">
                            <i class="bi bi-grid-3x3"></i>
                        </button>
                        <button type="submit" class="btn btn-primary btn-sm mx-1"><i class="bi bi-funnel-fill"></i></button>
                        <a href="{{ route('transactions.index', ['reset' => 1]) }}" class="btn btn-secondary btn-sm d-inline-flex align-items-center justify-content-center" title="{{ __('Reset Filters') }}"><i class="bi bi-arrow-clockwise"></i></a>
                    </div>
                </div>
            </form>
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
                            <th class="col-checkbox"><input class="form-check-input" type="checkbox" id="select-all-checkbox"></th>
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
                        <tr data-id="{{ $transaction->id }}"
                            data-type="{{ $transaction->type }}" 
                            data-date="{{ $transaction->transaction_date_for_edit }}"
                            data-store-id="{{ $transaction->store_id }}"
                            data-amount="{{ $transaction->amount }}"
                            data-notes="{{ $transaction->notes }}"
                            data-category-id="{{ $transaction->expense_category_id }}"
                            data-shift-id="{{ $transaction->shift_id }}"
                            data-source-id="{{ $transaction->source_id }}"
                            data-payment-method-id="{{ $transaction->payment_method_id }}"
                            data-can-edit="{{ Auth::user()->can('update', $transaction) ? 'true' : 'false' }}">
                            <td class="col-checkbox">
                                @can('delete', $transaction)
                                <input class="form-check-input row-checkbox" type="checkbox" name="ids[]" value="{{ $transaction->id }}">
                                @endcan
                            </td>
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
                                <div class="d-flex justify-content-end gap-1">
                                    @can('update', $transaction)
                                    <a href="{{ route('transactions.edit', $transaction->id) }}" class="btn btn-sm btn-outline-secondary" title="{{ __('Edit') }}">
                                        <i class="bi bi-pencil-fill"></i>
                                    </a>
                                    @endcan
                                </div>
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

{{-- Unsaved Changes Confirmation Modal --}}
<div class="modal fade" id="unsavedChangesModal" tabindex="-1" aria-labelledby="unsavedChangesModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="unsavedChangesModalLabel">{{ __('Unsaved Changes') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
            </div>
            <div class="modal-body">
                {{ __('You have unsaved changes in spreadsheet mode. Do you want to save them before leaving?') }}
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Stay in Spreadsheet Mode') }}</button>
                <button type="button" class="btn btn-danger" id="discard-exit-btn">{{ __('Discard and Exit') }}</button>
                <button type="button" class="btn btn-success" id="save-exit-btn">{{ __('Save and Exit') }}</button>
            </div>
        </div>
    </div>
</div>

{{-- Save Confirmation Modal --}}
<div class="modal fade" id="saveConfirmModal" tabindex="-1" aria-labelledby="saveConfirmModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="saveConfirmModalLabel">{{ __('Confirm Save') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
            </div>
            <div class="modal-body">
                {{ __('Are you sure you want to save the modified records?') }}
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                <button type="button" class="btn btn-success" id="confirm-save-btn">{{ __('Save Changes') }}</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
{{-- Reference Data for Spreadsheet Mode --}}
<script id="reference-data" type="application/json">
    {
        "stores": @json($stores),
        "expenseCategories": @json($expenseCategories),
        "shifts": @json($shifts),
        "sources": @json($sources),
        "paymentMethods": @json($paymentMethods)
    }
</script>

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

        if (confirmDeleteBtn) {
            confirmDeleteBtn.addEventListener('click', function() {
                bulkDeleteForm.submit();
            });
        }

        // --- Spreadsheet Mode Logic ---
        const refData = JSON.parse(document.getElementById('reference-data').textContent);
        const toggleSsBtn = document.getElementById('toggle-spreadsheet-mode');
        const saveAllBtn = document.getElementById('save-all-btn');
        const cancelSsBtn = document.getElementById('cancel-spreadsheet-btn');
        const unsavedModal = new bootstrap.Modal(document.getElementById('unsavedChangesModal'));
        const saveConfirmModal = new bootstrap.Modal(document.getElementById('saveConfirmModal'));
        const discardExitBtn = document.getElementById('discard-exit-btn');
        const saveExitBtn = document.getElementById('save-exit-btn');
        const confirmSaveBtn = document.getElementById('confirm-save-btn');
        const filterForm = document.getElementById('filter-form');
        let spreadsheetMode = false;
        let pendingAction = null;

        // Warn on page refresh/close
        window.addEventListener('beforeunload', function (e) {
            if (document.querySelectorAll('.row-modified').length > 0) {
                e.preventDefault();
                e.returnValue = '';
            }
        });

        // Intercept filter form submission
        filterForm.addEventListener('submit', function(e) {
            if (spreadsheetMode && document.querySelectorAll('.row-modified').length > 0) {
                e.preventDefault();
                pendingAction = () => filterForm.submit();
                unsavedModal.show();
            }
        });

        function handleExitSpreadsheet() {
            const modifiedRows = document.querySelectorAll('.row-modified');
            if (modifiedRows.length > 0) {
                pendingAction = () => window.location.reload();
                unsavedModal.show();
            } else {
                window.location.reload();
            }
        }

        toggleSsBtn.addEventListener('click', function() {
            if (!spreadsheetMode) {
                spreadsheetMode = true;
                document.body.classList.add('spreadsheet-active');
                this.classList.replace('btn-outline-secondary', 'btn-secondary');
                saveAllBtn.classList.remove('d-none');
                cancelSsBtn.classList.remove('d-none');
                renderTable();
            } else {
                handleExitSpreadsheet();
            }
        });

        cancelSsBtn.addEventListener('click', handleExitSpreadsheet);
        
        discardExitBtn.addEventListener('click', function() {
            if (pendingAction) {
                pendingAction();
            } else {
                window.location.reload();
            }
        });

        saveExitBtn.addEventListener('click', () => saveAllChanges(true));
        
        saveAllBtn.addEventListener('click', () => {
            if (saveAllBtn.disabled) return;
            saveConfirmModal.show();
        });

        confirmSaveBtn.addEventListener('click', () => saveAllChanges(false));

        async function saveAllChanges(isExiting = false) {
            const modifiedRows = document.querySelectorAll('.row-modified');
            if (modifiedRows.length === 0) return;

            saveAllBtn.disabled = true;
            saveExitBtn.disabled = true;
            confirmSaveBtn.disabled = true;
            const originalIcon = saveAllBtn.innerHTML;
            saveAllBtn.innerHTML = `<span class="spinner-border spinner-border-sm"></span>`;

            let successCount = 0;
            for (const row of modifiedRows) {
                const id = row.dataset.id;
                const data = { type: row.dataset.type };
                row.querySelectorAll('.ss-input').forEach(input => {
                    data[input.dataset.field] = input.value;
                });

                try {
                    const response = await fetch(`/transactions/${id}`, {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify(data)
                    });
                    if (response.ok) {
                        row.classList.remove('row-modified');
                        successCount++;
                    }
                } catch (e) {
                    console.error('Error saving row', id, e);
                }
            }

            saveAllBtn.innerHTML = originalIcon;
            if (successCount > 0) {
                window.showAlert(`${successCount} {{__('records updated successfully!')}}`, 'success');
                
                if (isExiting || pendingAction) {
                    unsavedModal.hide();
                    saveConfirmModal.hide();
                    setTimeout(() => {
                        if (pendingAction) {
                            pendingAction();
                            pendingAction = null;
                        } else {
                            window.location.reload();
                        }
                    }, 1000);
                } else {
                    saveConfirmModal.hide();
                    saveAllBtn.disabled = false;
                    confirmSaveBtn.disabled = false;
                    
                    // Reset save count badge
                    const countContainer = document.getElementById('save-count-container');
                    countContainer.innerHTML = '';
                    saveAllBtn.disabled = true;
                }
            } else {
                saveAllBtn.disabled = false;
                saveExitBtn.disabled = false;
                confirmSaveBtn.disabled = false;
            }
        }

        function renderTable() {
            const rows = document.querySelectorAll('.transactions-table tbody tr');
            rows.forEach(row => {
                const id = row.dataset.id;
                if (row.dataset.canEdit !== 'true') return;
                
                const transaction = getRowData(row);
                enableRowEditing(row, id, transaction);
            });
        }

        function getRowData(row) {
            return {
                date: row.dataset.date,
                store_id: row.dataset.storeId,
                type: row.dataset.type,
                amount: row.dataset.amount,
                notes: row.dataset.notes,
                category_id: row.dataset.categoryId,
                shift_id: row.dataset.shiftId,
                source_id: row.dataset.sourceId,
                payment_method_id: row.dataset.paymentMethodId
            };
        }

        function enableRowEditing(row, id, data) {
            const dateCell = row.querySelector('[data-label="{{__('Date')}}"]');
            const storeCell = row.querySelector('[data-label="{{__('Store')}}"]');
            const detailsCell = row.querySelector('[data-label="{{__('Details')}}"]');
            const amountCell = row.querySelector('[data-label="{{__('Amount')}}"]');

            const dateOriginalLabel = dateCell.textContent.trim();
            const storeOriginalLabel = storeCell.textContent.trim();
            const detailsOriginalLabel = detailsCell.textContent.trim();
            const amountOriginalLabel = amountCell.textContent.trim();

            dateCell.innerHTML = `<input type="date" class="form-control form-control-sm ss-input" data-field="transaction_date" data-original-label="${dateOriginalLabel}" value="${data.date}">`;
            
            let storeOptions = refData.stores.map(s => `<option value="${s.id}" ${s.id == data.store_id ? 'selected' : ''}>${s.name}</option>`).join('');
            storeCell.innerHTML = `<select class="form-select form-select-sm ss-input" data-field="store_id" data-original-label="${storeOriginalLabel}">${storeOptions}</select>`;

            if (data.type === 'expense') {
                let catOptions = refData.expenseCategories.map(c => `<option value="${c.id}" ${c.id == data.category_id ? 'selected' : ''}>${c.name}</option>`).join('');
                detailsCell.innerHTML = `
                    <div class="d-flex flex-column gap-1">
                        <select class="form-select form-select-sm ss-input" data-field="expense_category_id" data-original-label="${detailsOriginalLabel}">${catOptions}</select>
                        <input type="text" class="form-control form-control-sm ss-input" data-field="notes" data-original-label="${data.notes || ''}" value="${data.notes || ''}" placeholder="{{__('Notes')}}">
                    </div>
                `;
            } else {
                let shiftOptions = refData.shifts.map(s => `<option value="${s.id}" ${s.id == data.shift_id ? 'selected' : ''}>${s.name}</option>`).join('');
                let sourceOptions = refData.sources.map(s => `<option value="${s.id}" ${s.id == data.source_id ? 'selected' : ''}>${s.name}</option>`).join('');
                let pmOptions = refData.paymentMethods.map(p => `<option value="${p.id}" ${p.id == data.payment_method_id ? 'selected' : ''}>${p.name}</option>`).join('');
                
                detailsCell.innerHTML = `
                    <div class="d-flex flex-column gap-1">
                        <select class="form-select form-select-sm ss-input" data-field="shift_id" data-original-label="${detailsOriginalLabel}">${shiftOptions}</select>
                        <select class="form-select form-select-sm ss-input" data-field="source_id" data-original-label="${detailsOriginalLabel}">${sourceOptions}</select>
                        <select class="form-select form-select-sm ss-input" data-field="payment_method_id" data-original-label="${detailsOriginalLabel}">${pmOptions}</select>
                    </div>
                `;
            }

            amountCell.innerHTML = `<input type="number" step="0.01" class="form-control form-control-sm text-end ss-input" data-field="amount" data-original-label="${amountOriginalLabel}" value="${data.amount}">`;

            row.querySelectorAll('.ss-input').forEach(input => {
                // Store initial value for dirty checking
                input.dataset.original = input.value;

                // Auto-select text on focus (especially for amount)
                input.addEventListener('focus', function() {
                    if (this.tagName === 'INPUT') this.select();
                });

                // Enter key navigation
                input.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        const allInputs = Array.from(document.querySelectorAll('.transactions-table .ss-input'));
                        const index = allInputs.indexOf(this);
                        if (index > -1 && index < allInputs.length - 1) {
                            allInputs[index + 1].focus();
                        }
                    }
                });

                const checkChange = () => {
                    const isChanged = input.value !== input.dataset.original;
                    
                    if (isChanged) {
                        input.classList.add('is-modified');
                        input.title = `{{__('Original value')}}: ${input.dataset.originalLabel}`;
                    } else {
                        input.classList.remove('is-modified');
                        input.title = '';
                    }

                    // Check if any field in the row is modified
                    const rowIsModified = Array.from(row.querySelectorAll('.ss-input')).some(i => i.classList.contains('is-modified'));
                    
                    if (rowIsModified) {
                        row.classList.add('row-modified');
                    } else {
                        row.classList.remove('row-modified');
                    }

                    const modifiedRowCount = document.querySelectorAll('.row-modified').length;
                    saveAllBtn.disabled = modifiedRowCount === 0;
                    
                    const countContainer = document.getElementById('save-count-container');
                    if (modifiedRowCount > 0) {
                        countContainer.innerHTML = `<span class="save-count-badge">${modifiedRowCount}</span>`;
                    } else {
                        countContainer.innerHTML = '';
                    }
                };

                input.addEventListener('input', checkChange);
                input.addEventListener('change', checkChange);
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

        // --- Highlight Active Filters ---
        function highlightActiveFilters() {
            const filterInputs = document.querySelectorAll('.card-header form .form-control, .card-header form .form-select');
            filterInputs.forEach(input => {
                const isDefaultMonth = input.name === 'month' && input.value === "{{ date('m') }}";
                const isDefaultYear = input.name === 'year' && input.value === "{{ date('Y') }}";
                const isDefaultPerPage = input.name === 'per_page' && input.value === "100";
                
                // If it has a non-empty value AND it's not a harmless default, highlight it
                if (input.value && input.value !== '' && input.value !== 'all' && !isDefaultMonth && !isDefaultYear && !isDefaultPerPage) {
                    input.classList.add('filter-active');
                } else {
                    input.classList.remove('filter-active');
                }
            });
        }

        highlightActiveFilters();
    });
</script>
@endpush