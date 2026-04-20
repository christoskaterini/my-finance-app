@extends('layouts.studio')
@section('page-title', __('New Record'))

@section('content')
<style>
    .input-group:focus-within {
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
        border-radius: 0.375rem;
    }
    .input-group:focus-within .input-group-text,
    .input-group:focus-within .form-control {
        border-color: #0d6efd !important;
    }
    .input-group .form-control:focus {
        box-shadow: none !important;
    }
</style>
<form id="addRecordForm" action="{{ route('transactions.store') }}" method="POST">
    @csrf
    <input type="hidden" name="store_id" value="{{ $selectedStore->id }}">

    {{-- Page Header: Store Badge & Date Prompt --}}
    {{-- Simple & Modern Header --}}
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center gap-3 mb-4">
        <div class="w-100 w-sm-auto d-flex justify-content-center justify-content-sm-start">
            <span class="btn btn-primary btn-md rounded shadow-sm d-flex align-items-center justify-content-center px-4 cursor-default" style="pointer-events: none; width: auto;">
                <i class="bi bi-shop-window me-2"></i>{{ $selectedStore->name }}
            </span>
        </div>
        <div class="w-100 w-sm-auto d-flex justify-content-center justify-content-sm-end">
            <div class="input-group shadow-sm w-100" style="max-width: 250px;">
                <span class="input-group-text border-secondary-subtle text-primary bg-primary bg-opacity-10"><i class="bi bi-calendar-event"></i></span>
                <input type="date" class="form-control border-secondary-subtle fw-bold text-center" id="transaction_date" name="transaction_date" value="{{ date('Y-m-d') }}" required>
            </div>
        </div>
    </div>

    @if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
    @endif

    <div class="row g-3">
        {{-- EXPENSES --}}
        <div class="col-lg-6">
            <div class="record-section expense shadow-sm" id="expense-section">
                <div class="record-section-header border-bottom pb-2 mb-2">
                    <h5 class="mb-0 d-flex align-items-center">
                        <i class="bi bi-arrow-down-circle-fill text-danger me-2"></i>{{__('Expenses')}}
                    </h5>
                </div>
                <div id="expense-lines-container" class="py-1"></div>
                <div class="record-section-footer d-flex justify-content-between align-items-center border-top pt-2 mt-2">
                    <div class="fw-bold text-danger">{{__('Total')}}: <span id="expense-total">0.00</span></div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-sm btn-outline-danger" id="remove-expense-btn" tabindex="-1"><i class="bi bi-dash"></i></button>
                        <button type="button" class="btn btn-sm btn-danger" id="add-expense-btn" tabindex="-1"><i class="bi bi-plus"></i></button>
                    </div>
                </div>
            </div>
        </div>

        {{-- INCOME --}}
        <div class="col-lg-6">
            <div class="record-section income shadow-sm" id="income-section">
                <div class="record-section-header border-bottom pb-2 mb-2">
                    <h5 class="mb-0 d-flex align-items-center">
                        <i class="bi bi-arrow-up-circle-fill text-success me-2"></i>{{__('Income')}}
                    </h5>
                </div>
                <div id="income-lines-container" class="py-1"></div>
                <div class="record-section-footer border-top pt-2 mt-2">
                    <div class="fw-bold text-success text-center fs-5">{{__('Total Income')}}: <span id="income-total">0.00</span></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Extra space for sticky footer on mobile --}}
    <div class="d-block d-md-none" style="height: 80px;"></div>

    {{-- Main Action Buttons (Desktop fixed at bottom of form, Mobile Sticky) --}}
    <div class="sticky-footer-container fixed-bottom bg-body border-top p-3 shadow-lg d-md-none">
        <div class="d-flex justify-content-between gap-3">
            <a href="{{ route('dashboard') }}" class="btn btn-secondary flex-grow-1">{{ __('Cancel') }}</a>
            <button type="submit" class="btn btn-primary flex-grow-1" id="mobile-submit-btn">{{ __('Save Records') }}</button>
        </div>
    </div>

    <div class="mt-4 mb-5 d-none d-md-flex justify-content-end gap-3">
        <a href="{{ route('dashboard') }}" class="btn btn-secondary px-4">{{ __('Cancel') }}</a>
        <button type="submit" class="btn btn-primary px-5">{{ __('Save Records') }}</button>
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
                    let shiftHtml = `<div class="p-2 rounded income-shift-group mb-2 border"><div class="text-center shift-group-header fw-bold small mb-2 opacity-75">${shift.name}</div>`;
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
            line.className = 'row g-2 align-items-center mb-4 mb-sm-2';
            line.innerHTML = `
            <div class="col-sm-4 col-12">
                <select class="form-select" name="expenses[${index}][expense_category_id]">${options}</select>
            </div>
            <div class="col-sm-5 col-7">
                <input type="text" class="form-control notes-input" name="expenses[${index}][notes]" placeholder="{{__('Notes')}}">
            </div>
            <div class="col-sm-3 col-5">
                <input type="number" inputmode="decimal" step="0.01" class="form-control fw-bold amount-input" name="expenses[${index}][amount]" placeholder="{{__('Amount')}}">
            </div>
            `;

            expenseContainer.appendChild(line);
            if (focus && !isTouchDevice()) {
                line.querySelector('.amount-input').focus();
            }
        }

        function createIncomeLine(index, shift, source, payment) {
            return `<div class="row g-2 align-items-center mb-1"><input type="hidden" name="income[${index}][shift_id]" value="${shift.id}"><input type="hidden" name="income[${index}][source_id]" value="${source.id}"><input type="hidden" name="income[${index}][payment_method_id]" value="${payment.id}"><div class="col-7"><div class="row g-1"><div class="col-6"><input type="text" class="form-control-plaintext py-0 small" value="${source.name}" readonly tabindex="-1"></div><div class="col-6"><input type="text" class="form-control-plaintext py-0 small" value="${payment.name}" readonly tabindex="-1"></div></div></div><div class="col-5"><input type="number" inputmode="decimal" step="0.01" class="form-control fw-bold amount-input" name="income[${index}][amount]" placeholder="{{__('Amount')}}"></div></div>`;
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

                if (target.classList.contains('amount-input') || target.classList.contains('notes-input')) {
                    e.preventDefault();
                    if (e.repeat) return;

                    const allInputs = Array.from(addRecordForm.querySelectorAll('.amount-input, .notes-input'));
                    const currentIndex = allInputs.indexOf(target);
                    const nextInput = allInputs[currentIndex + 1];

                    if (nextInput) {
                        nextInput.focus();
                    }
                }
            }
        });

        addRecordForm.addEventListener('submit', function() {
            const submitBtns = addRecordForm.querySelectorAll('button[type="submit"]');
            submitBtns.forEach(btn => {
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';
            });
        });

        // --- Productivity: Auto-Focus Date & Auto-Highlight ---
        setTimeout(() => {
            const dateInput = document.getElementById('transaction_date');
            if (dateInput) {
                dateInput.focus();
                // Optional: Flash effect
                dateInput.style.transition = 'all 0.3s';
                dateInput.style.boxShadow = '0 0 10px rgba(13, 202, 240, 0.5)';
                setTimeout(() => dateInput.style.boxShadow = '', 1000);
            }
        }, 300);

        // Auto-highlight amount fields on focus
        document.addEventListener('focusin', function(e) {
            if (e.target.classList.contains('amount-input')) {
                e.target.select();
            }
        });

        populateForm();
    });
</script>
@endpush