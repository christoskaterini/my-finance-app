@extends('layouts.studio')
@section('page-title', __('New Record'))

@section('content')
<form id="addRecordForm" action="{{ route('transactions.store') }}" method="POST">
    @csrf
    <input type="hidden" name="store_id" value="{{ $selectedStore->id }}">

    {{-- Page Header --}}
    <div class="row align-items-center mb-4">
        <div class="col-md-6">
            <h4 class="mb-0">{{ __('New Record for') }} <span class="text-primary">{{ $selectedStore->name }}</span></h4>
        </div>
        <div class="col-md-6 text-md-end mt-2 mt-md-0">
            <label for="transaction_date" class="form-label d-none">{{ __('Record Date') }}</label>
            <input type="date" class="form-control form-control-lg d-inline-block" style="width: auto;" id="transaction_date" name="transaction_date" value="{{ date('Y-m-d') }}">
        </div>
    </div>

    @if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
    @endif

    <div class="row g-4">
        {{-- EXPENSES --}}
        <div class="col-lg-6">
            <div class="record-section expense" id="expense-section">
                <div class="record-section-header">
                    <h6 class="mb-0 d-flex align-items-center">
                        <div class="d-inline-block rounded-circle bg-danger me-2" style="width: 10px; height: 10px;"></div>{{__('Expenses')}}
                    </h6>
                </div>
                <div id="expense-lines-container" class="py-2"></div>
                <div class="record-section-footer d-flex justify-content-between align-items-center">
                    <div class="fw-bold fs-5">{{__('Total Expenses')}}: <span id="expense-total">0.00</span></div>
                    <div class="d-flex gap-3">
                        <button type="button" class="btn btn-sm btn-outline-danger" id="remove-expense-btn"><i class="bi bi-trash"></i></button>
                        <button type="button" class="btn btn-sm btn-primary" id="add-expense-btn"><i class="bi bi-plus"></i></button>
                    </div>
                </div>
            </div>
        </div>

        {{-- INCOME --}}
        <div class="col-lg-6">
            <div class="record-section income" id="income-section">
                <div class="record-section-header">
                    <h6 class="mb-0 d-flex align-items-center">
                        <div class="d-inline-block rounded-circle bg-success me-2" style="width: 10px; height: 10px;"></div>{{__('Income')}}
                    </h6>
                </div>
                <div id="income-lines-container" class="py-2"></div>
                <div class="record-section-footer d-flex justify-content-between align-items-center">
                    <div class="fw-bold fs-5">{{__('Total Income')}}: <span id="income-total">0.00</span></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Action Buttons --}}
    <div class="mt-4 d-flex justify-content-end gap-4">
        <a href="{{ route('dashboard') }}" class="btn btn-secondary btn-md">{{ __('Cancel') }}</a>
        <button type="submit" class="btn btn-primary btn-md">{{ __('Save Records') }}</button>
    </div>
</form>
@endsection

@push('scripts')
{{-- Data script tag (no changes needed) --}}
<script id="page-data" type="application/json">
    {
        "expenseCategories": @json($expenseCategories),
        "shifts": @json($shifts),
        "sources": @json($sources),
        "paymentMethods": @json($paymentMethods)
    }
</script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // --- UTILITY FUNCTION ---
        function isTouchDevice() {
            return ('ontouchstart' in window) || (navigator.maxTouchPoints > 0) || (navigator.msMaxTouchPoints > 0);
        }

        // --- DATA & ELEMENT SELECTORS ---
        const pageData = JSON.parse(document.getElementById('page-data').textContent);
        const {
            expenseCategories,
            shifts,
            sources,
            paymentMethods
        } = pageData;
        const expenseContainer = document.getElementById('expense-lines-container');
        const incomeContainer = document.getElementById('income-lines-container');
        const expenseTotalEl = document.getElementById('expense-total');
        const incomeTotalEl = document.getElementById('income-total');
        const addRecordForm = document.getElementById('addRecordForm');

        // --- DYNAMIC FORM LOGIC ---
        function populateForm() {
            expenseContainer.innerHTML = '';
            incomeContainer.innerHTML = '';

            const expenseSection = document.getElementById('expense-section');
            if (expenseCategories.length > 0) {
                expenseSection.style.display = 'block';
                createExpenseLine(true);
            } else {
                expenseSection.style.display = 'none';
            }

            const incomeSection = document.getElementById('income-section');
            if (shifts.length > 0 || sources.length > 0 || paymentMethods.length > 0) {
                incomeSection.style.display = 'block';
                const s = shifts.length > 0 ? shifts : [{
                    id: null,
                    name: '{{__("No Shift")}}'
                }];
                const o = sources.length > 0 ? sources : [{
                    id: null,
                    name: '{{__("No Source")}}'
                }];
                const p = paymentMethods.length > 0 ? paymentMethods : [{
                    id: null,
                    name: '{{__("No Payment")}}'
                }];
                let incomeIndex = 0;
                s.forEach(shift => {
                    let shiftHtml = `<div class="p-3 rounded income-shift-group mb-3"><div class="text-center shift-group-header p-1 mb-3">${shift.name}</div>`;
                    o.forEach(source => {
                        p.forEach(payment => {
                            shiftHtml += createIncomeLine(incomeIndex++, shift, source, payment);
                        });
                    });
                    shiftHtml += `</div>`;
                    incomeContainer.innerHTML += shiftHtml;
                });
            } else {
                incomeContainer.style.display = 'none';
            }
            updateTotals();
        }

        function createExpenseLine(focus = false) {
            const index = expenseContainer.children.length;
            let options = expenseCategories.map((c, i) => `<option value="${c.id}" ${i===0 ? 'selected':''}>${c.name}</option>`).join('');
            const line = document.createElement('div');
            line.className = 'row g-3 align-items-center record-line-separator flex-column flex-sm-row mb-3';
            line.innerHTML = `<div class="col-sm-6"><select class="form-select form-select-lg" name="expenses[${index}][expense_category_id]">${options}</select></div><div class="col-sm-6"><input type="number" inputmode="decimal" step="0.01" class="form-control form-control-lg amount-input" name="expenses[${index}][amount]" placeholder="{{__('Amount')}}"></div>`;
            expenseContainer.appendChild(line);

            if (focus && !isTouchDevice()) {
                line.querySelector('.amount-input').focus();
            }
        }

        function createIncomeLine(index, shift, source, payment) {
            return `<div class="row g-2 align-items-center record-line-separator"><input type="hidden" name="income[${index}][shift_id]" value="${shift.id}"><input type="hidden" name="income[${index}][source_id]" value="${source.id}"><input type="hidden" name="income[${index}][payment_method_id]" value="${payment.id}"><div class="col-md-6"><div class="row g-2"><div class="col-6"><input type="text" class="form-control-plaintext form-control-lg" value="${source.name}" readonly tabindex="-1"></div><div class="col-6"><input type="text" class="form-control-plaintext form-control-lg" value="${payment.name}" readonly tabindex="-1"></div></div></div><div class="col-md-6"><input type="number" inputmode="decimal" step="0.01" class="form-control form-control-lg amount-input" name="income[${index}][amount]" placeholder="{{__('Amount')}}"></div></div>`;
        }

        // --- EVENT LISTENERS ---
        document.getElementById('add-expense-btn').addEventListener('click', () => createExpenseLine(true));
        document.getElementById('remove-expense-btn').addEventListener('click', () => {
            if (expenseContainer.children.length > 1) {
                expenseContainer.removeChild(expenseContainer.lastChild);
                updateTotals();
            }
        });

        function updateTotals() {
            let expenseTotal = 0;
            document.querySelectorAll('#expense-lines-container .amount-input').forEach(input => {
                expenseTotal += parseFloat(input.value) || 0;
            });
            expenseTotalEl.textContent = expenseTotal.toFixed(2);
            let incomeTotal = 0;
            document.querySelectorAll('#income-lines-container .amount-input').forEach(input => {
                incomeTotal += parseFloat(input.value) || 0;
            });
            incomeTotalEl.textContent = incomeTotal.toFixed(2);
        }

        addRecordForm.addEventListener('input', function(e) {
            if (e.target.classList.contains('amount-input')) {
                updateTotals();
            }
        });

        addRecordForm.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                const target = e.target;

                if (target.classList.contains('amount-input')) {
                    e.preventDefault();

                    const allInputs = Array.from(addRecordForm.querySelectorAll('.amount-input'));
                    const currentIndex = allInputs.indexOf(target);
                    const nextInput = allInputs[currentIndex + 1];

                    if (nextInput) {
                        nextInput.focus();
                    } else {
                        addRecordForm.querySelector('button[type="submit"]').focus();
                    }
                }
            }
        });

        populateForm();
    });
</script>
@endpush