@extends('layouts.studio')
@section('page-title', __('Dashboard'))

@section('content')
    @if(session('success'))
        <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1100">
            <div class="toast align-items-center text-bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex"><div class="toast-body">{{ session('success') }}</div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button></div>
            </div>
        </div>
    @endif

    <div class="row">
        <div class="col-xl-9 col-lg-10">
            {{-- Store Selector --}}
            <div class="card">
                <div class="card-header"><h5 class="card-title mb-0">{{ __('Select a Store to Begin') }}</h5></div>
                <div class="card-body">
                    @if($stores->isEmpty())
                        <div class="alert alert-warning mb-0"><a href="{{ url('/settings?tab=stores') }}" class="alert-link">{{ __('Please add a store to continue.') }}</a></div>
                    @else
                        <div id="store-selector" class="d-flex flex-wrap gap-3">
                            @foreach($stores as $store)
                                <button class="btn btn-outline-secondary btn-lg store-btn" data-store-id="{{ $store->id }}" data-store-name="{{ $store->name }}" data-expense-categories="{{ json_encode($expenseCategories->filter(fn($c) => $c->stores->contains($store->id))->values()->all()) }}" data-shifts="{{ json_encode($shifts->filter(fn($s) => $s->stores->contains($store->id))->values()->all()) }}" data-sources="{{ json_encode($sources->filter(fn($s) => $s->stores->contains($store->id))->values()->all()) }}" data-payment-methods="{{ json_encode($paymentMethods->filter(fn($p) => $p->stores->contains($store->id))->values()->all()) }}">
                                    <i class="bi bi-shop-window me-2"></i> {{ $store->name }}
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            {{-- Main Action Button --}}
            <div id="main-action-area" class="text-center mt-4" style="display: none;">
                <button class="btn btn-primary btn-lg" style="min-width: 250px;" data-bs-toggle="modal" data-bs-target="#addRecordModal"><i class="bi bi-plus-circle-dotted me-2"></i> {{ __('Add Record') }}</button>
                <div class="mt-2 text-muted" id="active-store-label"></div>
            </div>
        </div>
    </div>

    <!-- Add Record Modal -->
    <div class="modal fade" id="addRecordModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <form id="addRecordForm" action="{{ route('transactions.store') }}" method="POST">
                    @csrf
                    <div class="modal-header border-0">
                        <div class="container-fluid px-0">
                            <div class="row align-items-center">
                                <div class="col-md-6"><h5 class="modal-title" id="record-modal-title"></h5></div>
                                <div class="col-md-6 text-md-end mt-2 mt-md-0"><input type="date" class="form-control d-inline-block" style="width: auto;" name="transaction_date" value="{{ date('Y-m-d') }}"></div>
                            </div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <input type="hidden" name="store_id" id="modal_store_id">
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-lg-6">
                                <div class="record-section expense" id="expense-section">
                                    <div class="record-section-header"><h6 class="mb-0 d-flex align-items-center"><div class="d-inline-block rounded-circle bg-danger me-2" style="width: 10px; height: 10px;"></div>{{__('Expenses')}}</h6></div>
                                    
                                    {{-- Container for the expense warning message --> --}}
                                    <div id="expense-config-warning" class="p-2"></div>
                                    <div id="expense-lines-container" class="py-2"></div>
                                    <div class="record-section-footer d-flex justify-content-between align-items-center">
                                        <div class="fw-bold">{{__('Total Expenses')}}: <span id="expense-total">0.00</span></div>
                                        <div class="d-flex gap-4">
                                            <button type="button" class="btn btn-sm btn-outline-danger" id="remove-expense-btn"><i class="bi bi-trash"></i></button>
                                            <button type="button" class="btn btn-sm btn-primary" id="add-expense-btn"><i class="bi bi-plus"></i></button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="record-section income" id="income-section">
                                    <div class="record-section-header"><h6 class="mb-0 d-flex align-items-center"><div class="d-inline-block rounded-circle bg-success me-2" style="width: 10px; height: 10px;"></div>{{__('Income')}}</h6></div>
                                    <div id="income-config-warning" class="p-2"></div>
                                    <div id="income-lines-container" class="py-2"></div>
                                    <div class="record-section-footer d-flex justify-content-between align-items-center">
                                        <div class="fw-bold">{{__('Total Income')}}: <span id="income-total">0.00</span></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Close') }}</button>
                        <button type="submit" class="btn btn-primary" id="save-records-btn">{{ __('Save Records') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {

    // --- ELEMENT SELECTORS ---
    let selectedStoreData = {};
    const storeSelector = document.getElementById('store-selector');
    const mainActionArea = document.getElementById('main-action-area');
    const activeStoreLabel = document.getElementById('active-store-label');
    const modalStoreIdInput = document.getElementById('modal_store_id');
    const addRecordModal = document.getElementById('addRecordModal');
    const expenseContainer = document.getElementById('expense-lines-container');
    const incomeContainer = document.getElementById('income-lines-container');
    const expenseTotalEl = document.getElementById('expense-total');
    const incomeTotalEl = document.getElementById('income-total');
    const modalTitleEl = document.getElementById('record-modal-title');
    const expenseSection = document.getElementById('expense-section');
    const incomeSection = document.getElementById('income-section');
    const saveRecordsBtn = document.getElementById('save-records-btn');
    const addExpenseBtn = document.getElementById('add-expense-btn');
    const removeExpenseBtn = document.getElementById('remove-expense-btn');
    
    // <-- Selectors for warning containers -->
    const expenseWarningContainer = document.getElementById('expense-config-warning');
    const incomeWarningContainer = document.getElementById('income-config-warning');

    // --- STORE SELECTOR LOGIC ---
    if (storeSelector) {
        const storeButtons = storeSelector.querySelectorAll('.store-btn');

        const handleStoreButtonClick = (button) => {
            storeButtons.forEach(btn => btn.classList.replace('btn-primary', 'btn-outline-secondary'));
            button.classList.replace('btn-outline-secondary', 'btn-primary');

            selectedStoreData = {
                id: button.dataset.storeId,
                name: button.dataset.storeName,
                expenseCategories: JSON.parse(button.dataset.expenseCategories),
                shifts: JSON.parse(button.dataset.shifts),
                sources: JSON.parse(button.dataset.sources),
                paymentMethods: JSON.parse(button.dataset.paymentMethods)
            };

            mainActionArea.style.display = 'block';
            activeStoreLabel.textContent = `{{ __('for') }} "${selectedStoreData.name}"`;
            modalStoreIdInput.value = selectedStoreData.id;
        };

        storeButtons.forEach(button => {
            button.addEventListener('click', () => handleStoreButtonClick(button));
        });

        if (storeButtons.length > 0) {
            handleStoreButtonClick(storeButtons[0]);
        }
    }

    // --- DYNAMIC FORM LOGIC ---
    function populateForm() {
        modalTitleEl.innerHTML = `{{ __('New Record for') }} <span class="text-primary">${selectedStoreData.name}</span>`;
        expenseContainer.innerHTML = '';
        incomeContainer.innerHTML = '';
        // Clear previous warnings
        expenseWarningContainer.innerHTML = '';
        incomeWarningContainer.innerHTML = '';
        updateTotals();

        const expenseSection = document.getElementById('expense-section');
        const hasExpenseCategories = selectedStoreData.expenseCategories.length > 0;
        
        if (hasExpenseCategories) {
            addExpenseBtn.style.display = 'inline-block';
            removeExpenseBtn.style.display = 'inline-block';
            createExpenseLine(true);
        } else {
            addExpenseBtn.style.display = 'none';
            removeExpenseBtn.style.display = 'none';
            // Display the expense warning
            expenseWarningContainer.innerHTML = `
                <div class="alert alert-warning p-2">
                    <small>
                        <i class="bi bi-exclamation-triangle-fill me-1"></i>
                        This store has no <strong>Expense Categories</strong> assigned. 
                        <a href="{{ route('settings.index') }}" class="fw-bold">Go to Settings</a> to add them.
                    </small>
                </div>`;
        }

        const incomeSection = document.getElementById('income-section');
        const hasShifts = selectedStoreData.shifts.length > 0;
        const hasSources = selectedStoreData.sources.length > 0;
        const hasPaymentMethods = selectedStoreData.paymentMethods.length > 0;
        const isIncomeConfigValid = hasShifts && hasSources && hasPaymentMethods;
        
        if (isIncomeConfigValid) {
            let incomeIndex = 0;
            selectedStoreData.shifts.forEach(shift => {
                let shiftHtml = `<div class="p-3 rounded income-shift-group mb-3"><div class="text-center shift-group-header p-1 mb-3">${shift.name}</div>`;
                selectedStoreData.sources.forEach(source => {
                    selectedStoreData.paymentMethods.forEach(payment => {
                        shiftHtml += createIncomeLine(incomeIndex++, shift, source, payment);
                    });
                });
                shiftHtml += `</div>`;
                incomeContainer.innerHTML += shiftHtml;
            });
        } else {
            let warningList = '';
            if (!hasShifts) warningList += '<li><strong>{{ __("Shifts") }}</strong></li>';
            if (!hasSources) warningList += '<li><strong>{{ __("Sources") }}</strong></li>';
            if (!hasPaymentMethods) warningList += '<li><strong>{{ __("Payment Methods") }}</strong></li>';

            incomeWarningContainer.innerHTML = `
                <div class="alert alert-warning p-2">
                    <h6 class="alert-heading mb-1" style="font-size: 0.9rem;">{{ __('Configuration Required') }}</h6>
                    <small>
                        This store is missing required assignments:
                        <ul class="mb-0 ps-3">
                            ${warningList}
                        </ul>
                        Please <a href="{{ route('settings.index') }}" class="fw-bold">go to Settings</a> to configure it.
                    </small>
                </div>`;
        }
        
        saveRecordsBtn.disabled = !isIncomeConfigValid || !hasExpenseCategories;
    }

    // --- HELPER FUNCTIONS ---
    function createExpenseLine(focus = false) {
        const index = expenseContainer.children.length;
        let options = selectedStoreData.expenseCategories.map((c, i) => `<option value="${c.id}" ${i===0 ? 'selected':''}>${c.name}</option>`).join('');
        const expenseId = Date.now();
        const line = document.createElement('div');
        line.className = 'expense-line border border-gray-700 rounded p-3 mb-3';
        line.style.backgroundColor = '#2b2f33';
        line.dataset.expenseId = expenseId;

        line.innerHTML = `
            <div class="row g-2 align-items-center">
                <div class="col-12 col-lg-4 mb-2 mb-lg-0"><select class="form-select form-select-sm" name="expenses[${index}][expense_category_id]">${options}</select></div>
                <div class="col-12 col-lg-5 mb-2 mb-lg-0"><input type="text" class="form-control form-select-sm notes-input" name="expenses[${index}][notes]" placeholder="{{__('Notes')}}"></div>
                <div class="col-12 col-lg-3"><input type="number" inputmode="decimal" step="0.01" class="form-control form-control-sm amount-input" name="expenses[${index}][amount]" placeholder="{{__('Amount')}}"></div>
            </div>
        `;
        expenseContainer.appendChild(line);

        const notesInput = line.querySelector('.notes-input');
        const amountInput = line.querySelector('.amount-input');

        notesInput.addEventListener('keydown', function(event) {
            if (event.key === 'Enter') {
                event.preventDefault();
                amountInput.focus();
            }
        });

        amountInput.addEventListener('keydown', function(event) {
            if (event.key === 'Enter') {
                event.preventDefault();
                const nextExpenseGroup = line.nextElementSibling;
                if (nextExpenseGroup && nextExpenseGroup.classList.contains('expense-group')) {
                    nextExpenseGroup.querySelector('.notes-input').focus();
                } else {
                    const firstIncomeInput = incomeContainer.querySelector('.amount-input');
                    if (firstIncomeInput) {
                        firstIncomeInput.focus();
                    }
                }
            }
        });

        if (focus) {
            line.querySelector('.amount-input').focus();
        }
    }

    function createIncomeLine(index, shift, source, payment) {
        const isRequired = (shift.id && source.id && payment.id) ? 'required' : '';
        return `
            <div class="row g-2 align-items-center record-line-separator">
                <input type="hidden" name="income[${index}][shift_id]" value="${shift.id}">
                <input type="hidden" name="income[${index}][source_id]" value="${source.id}">
                <input type="hidden" name="income[${index}][payment_method_id]" value="${payment.id}">
                <div class="col-lg-9 col-md-12">
                    <div class="row g-2">
                        <div class="col-6"><input type="text" class="form-control-plaintext form-control-sm" value="${source.name}" readonly tabindex="-1"></div>
                        <div class="col-6"><input type="text" class="form-control-plaintext form-control-sm" value="${payment.name}" readonly tabindex="-1"></div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-12"><input type="number" inputmode="decimal" step="0.01" class="form-control form-control-sm amount-input" name="income[${index}][amount]" placeholder="{{__('Amount')}}"></div>
            </div>
        `;
    }

    // --- EVENT LISTENERS ---
    document.getElementById('add-expense-btn').addEventListener('click', () => createExpenseLine(true));
    document.getElementById('remove-expense-btn').addEventListener('click', () => { if (expenseContainer.children.length > 1) { expenseContainer.removeChild(expenseContainer.lastChild); updateTotals(); }});
    
    if (addRecordModal) {
        addRecordModal.addEventListener('show.bs.modal', populateForm);
    }

    function updateTotals() {
        let expenseTotal = 0;
        document.querySelectorAll('#expense-lines-container .amount-input').forEach(input => { expenseTotal += parseFloat(input.value) || 0; });
        expenseTotalEl.textContent = expenseTotal.toFixed(2);
        let incomeTotal = 0;
        document.querySelectorAll('#income-lines-container .amount-input').forEach(input => { incomeTotal += parseFloat(input.value) || 0; });
        incomeTotalEl.textContent = incomeTotal.toFixed(2);
    }

    document.getElementById('addRecordForm').addEventListener('input', function(e) { if (e.target.classList.contains('amount-input')) { updateTotals(); } });

    // --- KEYBOARD NAVIGATION ---
    document.getElementById('addRecordForm').addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            const target = e.target;
            const allInputs = Array.from(document.querySelectorAll('#addRecordForm .amount-input'));
            const currentIndex = allInputs.indexOf(target);

            if (currentIndex > -1) {
                e.preventDefault();
                const nextInput = allInputs[currentIndex + 1];

                if (nextInput) {
                    nextInput.focus();
                } else {
                    document.querySelector('#addRecordForm button[type="submit"]').focus();
                }
            }
        }
    });

    // --- SCRIPT TO SHOW THE SUCCESS TOAST ---
    const toastEl = document.querySelector('.toast-container .toast');
    if (toastEl) {
        const toast = new bootstrap.Toast(toastEl, { autohide: true, delay: 4000 });
        toast.show();
    }
});
</script>
@endpush