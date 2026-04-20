@extends('layouts.studio')
@section('page-title', __('Bulk Add Records'))

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
    .record-block {
        transition: all 0.3s ease;
        border: 1px solid var(--bs-border-color);
        background-color: var(--bs-body-bg);
    }
    .record-block:hover {
        border-color: var(--bs-primary);
    }
    .record-block-header {
        background-color: var(--bs-tertiary-bg);
    }
</style>

<div class="mb-4 d-flex flex-column flex-sm-row justify-content-between align-items-center gap-3">
    <div class="d-flex justify-content-center justify-content-sm-start">
        <span class="btn btn-primary btn-md rounded shadow-sm d-flex align-items-center justify-content-center px-4 cursor-default" style="pointer-events: none; width: auto;">
            <i class="bi bi-shop-window me-2"></i>{{ $selectedStore->name }}
        </span>
    </div>
    <div class="text-muted small text-center text-sm-end">
        {{ __('Adding multiple days for') }} <strong>{{ $selectedStore->name }}</strong>
    </div>
</div>

<form id="bulkRecordForm" action="{{ route('transactions.bulkStore') }}" method="POST">
    @csrf
    <input type="hidden" name="store_id" value="{{ $selectedStore->id }}">

    <div id="record-blocks-container">
        <!-- Record blocks will be injected here -->
    </div>

    <div class="d-flex flex-column flex-md-row gap-3 mt-4 mb-5">
        <button type="button" class="btn btn-outline-primary btn-lg flex-grow-1" id="add-another-day-btn">
            <i class="bi bi-calendar-plus me-2"></i>{{ __('Add Another Day') }} (+1)
        </button>
    </div>

    {{-- Extra space for sticky footer on mobile --}}
    <div class="d-block d-md-none" style="height: 80px;"></div>

    {{-- Main Action Buttons (Desktop fixed at bottom of form, Mobile Sticky) --}}
    <div class="sticky-footer-container fixed-bottom bg-body border-top p-3 shadow-lg d-md-none">
        <div class="d-flex justify-content-between gap-3">
            <a href="{{ route('dashboard') }}" class="btn btn-secondary flex-grow-1">{{ __('Cancel') }}</a>
            <button type="submit" class="btn btn-primary flex-grow-1" id="mobile-submit-btn">{{ __('Save All Records') }}</button>
        </div>
    </div>

    <div class="mt-4 mb-5 d-none d-md-flex justify-content-end gap-3">
        <a href="{{ route('dashboard') }}" class="btn btn-secondary px-4">{{ __('Cancel') }}</a>
        <button type="submit" class="btn btn-primary px-5">{{ __('Save All Records') }}</button>
    </div>
</form>
@endsection

@push('scripts')
{{-- Data script tag --}}
<script id="page-data" type="application/json">
    {
        "expenseCategories": @json($expenseCategories),
        "shifts": @json($shifts),
        "sources": @json($sources),
        "paymentMethods": @json($paymentMethods),
        "labels": {
            "expenses": "{{__('Expenses')}}",
            "income": "{{__('Income')}}",
            "total": "{{__('Total')}}",
            "totalIncome": "{{__('Total Income')}}",
            "notes": "{{__('Notes')}}",
            "amount": "{{__('Amount')}}",
            "noShift": "{{__('No Shift')}}",
            "noSource": "{{__('No Source')}}",
            "noPayment": "{{__('No Payment')}}",
            "removeDay": "{{__('Remove This Day')}}"
        }
    }
</script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // --- DATA & ELEMENT SELECTORS ---
        const pageData = JSON.parse(document.getElementById('page-data').textContent);
        const { expenseCategories, shifts, sources, paymentMethods, labels } = pageData;
        const container = document.getElementById('record-blocks-container');
        const form = document.getElementById('bulkRecordForm');
        let recordCount = 0;

        function isTouchDevice() {
            return ('ontouchstart' in window) || (navigator.maxTouchPoints > 0) || (navigator.msMaxTouchPoints > 0);
        }

        function createRecordBlock(suggestedDate = null) {
            const index = recordCount++;
            const dateValue = suggestedDate || new Date().toISOString().split('T')[0];
            
            const block = document.createElement('div');
            block.className = 'record-block rounded shadow-sm mb-5 overflow-hidden';
            block.id = `record-block-${index}`;
            block.dataset.index = index;

            block.innerHTML = `
                <div class="record-block-header p-3 border-bottom d-flex flex-column flex-sm-row justify-content-between align-items-center gap-3">
                    <div class="input-group shadow-sm" style="max-width: 250px;">
                        <span class="input-group-text border-secondary-subtle text-primary bg-primary bg-opacity-10"><i class="bi bi-calendar-event"></i></span>
                        <input type="date" class="form-control border-secondary-subtle fw-bold text-center transaction-date-input" 
                               name="records[${index}][transaction_date]" value="${dateValue}" required>
                    </div>
                    ${index > 0 ? `<div class="w-100 w-sm-auto d-flex justify-content-center justify-content-sm-end"><button type="button" class="btn btn-sm btn-outline-danger remove-block-btn" data-index="${index}"><i class="bi bi-trash"></i> ${labels.removeDay}</button></div>` : ''}
                </div>
                <div class="p-3">
                    <div class="row g-3">
                        {{-- EXPENSES --}}
                        <div class="col-lg-6">
                            <div class="record-section expense shadow-sm h-100">
                                <div class="record-section-header border-bottom pb-2 mb-2">
                                    <h5 class="mb-0 d-flex align-items-center text-danger">
                                        <i class="bi bi-arrow-down-circle-fill me-2"></i>${labels.expenses}
                                    </h5>
                                </div>
                                <div id="expense-lines-${index}" class="py-1 line-container"></div>
                                <div class="record-section-footer d-flex justify-content-between align-items-center border-top pt-2 mt-2">
                                    <div class="fw-bold text-danger">${labels.total}: <span class="expense-total">0.00</span></div>
                                    <div class="d-flex gap-2">
                                        <button type="button" class="btn btn-sm btn-outline-danger remove-expense-line-btn" tabindex="-1"><i class="bi bi-dash"></i></button>
                                        <button type="button" class="btn btn-sm btn-danger add-expense-line-btn" tabindex="-1"><i class="bi bi-plus"></i></button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- INCOME --}}
                        <div class="col-lg-6">
                            <div class="record-section income shadow-sm h-100">
                                <div class="record-section-header border-bottom pb-2 mb-2">
                                    <h5 class="mb-0 d-flex align-items-center text-success">
                                        <i class="bi bi-arrow-up-circle-fill me-2"></i>${labels.income}
                                    </h5>
                                </div>
                                <div id="income-lines-${index}" class="py-1 line-container"></div>
                                <div class="record-section-footer border-top pt-2 mt-2">
                                    <div class="fw-bold text-success text-center fs-5">${labels.totalIncome}: <span class="income-total">0.00</span></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;

            container.appendChild(block);
            populateBlock(index);
            
            // Focus on the date field of the new block
            if (index > 0) {
                 const dateInput = block.querySelector('.transaction-date-input');
                 if(dateInput) dateInput.focus();
            }

            return block;
        }

        function populateBlock(blockIndex) {
            const expenseContainer = document.getElementById(`expense-lines-${blockIndex}`);
            const incomeContainer = document.getElementById(`income-lines-${blockIndex}`);

            // Initial Expense Line
            if (expenseCategories.length > 0) {
                createExpenseLine(blockIndex, true);
            }

            // Income Lines
            if (shifts.length > 0 || sources.length > 0 || paymentMethods.length > 0) {
                const s = shifts.length > 0 ? shifts : [{ id: null, name: labels.noShift }];
                const o = sources.length > 0 ? sources : [{ id: null, name: labels.noSource }];
                const p = paymentMethods.length > 0 ? paymentMethods : [{ id: null, name: labels.noPayment }];
                
                let incomeIndex = 0;
                s.forEach(shift => {
                    let shiftHtml = `<div class="p-2 rounded income-shift-group mb-2 border"><div class="text-center shift-group-header fw-bold small mb-2 opacity-75">${shift.name}</div>`;
                    o.forEach(source => {
                        p.forEach(payment => {
                            shiftHtml += createIncomeLineHtml(blockIndex, incomeIndex++, shift, source, payment);
                        });
                    });
                    shiftHtml += `</div>`;
                    incomeContainer.innerHTML += shiftHtml;
                });
            }
        }

        function createExpenseLine(blockIndex, focus = false) {
            const expenseContainer = document.getElementById(`expense-lines-${blockIndex}`);
            const expenseIndex = expenseContainer.children.length;
            let options = expenseCategories.map((c, i) => `<option value="${c.id}" ${i===0 ? 'selected':''}>${c.name}</option>`).join('');
            
            const line = document.createElement('div');
            line.className = 'row g-2 align-items-center mb-4 mb-sm-2 expense-line';
            line.innerHTML = `
                <div class="col-sm-4 col-12">
                    <select class="form-select" name="records[${blockIndex}][expenses][${expenseIndex}][expense_category_id]">${options}</select>
                </div>
                <div class="col-sm-5 col-7">
                    <input type="text" class="form-control notes-input" name="records[${blockIndex}][expenses][${expenseIndex}][notes]" placeholder="${labels.notes}">
                </div>
                <div class="col-sm-3 col-5">
                    <input type="number" inputmode="decimal" step="0.01" class="form-control fw-bold amount-input" name="records[${blockIndex}][expenses][${expenseIndex}][amount]" placeholder="${labels.amount}">
                </div>
            `;

            expenseContainer.appendChild(line);
            if (focus && !isTouchDevice() && expenseIndex > 0) {
                line.querySelector('.amount-input').focus();
            }
        }

        function createIncomeLineHtml(blockIndex, incomeIndex, shift, source, payment) {
            return `
                <div class="row g-2 align-items-center mb-1 income-line">
                    <input type="hidden" name="records[${blockIndex}][income][${incomeIndex}][shift_id]" value="${shift.id}">
                    <input type="hidden" name="records[${blockIndex}][income][${incomeIndex}][source_id]" value="${source.id}">
                    <input type="hidden" name="records[${blockIndex}][income][${incomeIndex}][payment_method_id]" value="${payment.id}">
                    <div class="col-7">
                        <div class="row g-1">
                            <div class="col-6"><input type="text" class="form-control-plaintext py-0 small" value="${source.name}" readonly tabindex="-1"></div>
                            <div class="col-6"><input type="text" class="form-control-plaintext py-0 small" value="${payment.name}" readonly tabindex="-1"></div>
                        </div>
                    </div>
                    <div class="col-5">
                        <input type="number" inputmode="decimal" step="0.01" class="form-control fw-bold amount-input" name="records[${blockIndex}][income][${incomeIndex}][amount]" placeholder="${labels.amount}">
                    </div>
                </div>`;
        }

        function updateTotals(block) {
            let expenseTotal = 0;
            block.querySelectorAll('.expense-line .amount-input').forEach(input => {
                expenseTotal += parseFloat(input.value) || 0;
            });
            block.querySelector('.expense-total').textContent = expenseTotal.toFixed(2);

            let incomeTotal = 0;
            block.querySelectorAll('.income-line .amount-input').forEach(input => {
                incomeTotal += parseFloat(input.value) || 0;
            });
            block.querySelector('.income-total').textContent = incomeTotal.toFixed(2);
        }

        // --- GLOBAL EVENT LISTENERS ---
        container.addEventListener('click', function(e) {
            const recordBlock = e.target.closest('.record-block');
            if (!recordBlock) return;
            const blockIndex = recordBlock.dataset.index;

            // Add Expense Line
            if (e.target.closest('.add-expense-line-btn')) {
                createExpenseLine(blockIndex, true);
            }

            // Remove Expense Line
            if (e.target.closest('.remove-expense-line-btn')) {
                const expenseLinesContainer = document.getElementById(`expense-lines-${blockIndex}`);
                const lines = expenseLinesContainer.children;
                if (lines.length > 1) {
                    expenseLinesContainer.removeChild(lines[lines.length - 1]);
                    updateTotals(recordBlock);
                }
            }

            // Remove Record Block
            if (e.target.closest('.remove-block-btn')) {
                container.removeChild(recordBlock);
            }
        });

        form.addEventListener('input', function(e) {
            if (e.target.classList.contains('amount-input')) {
                const recordBlock = e.target.closest('.record-block');
                updateTotals(recordBlock);
            }
        });

        // Add Another Day Logic
        document.getElementById('add-another-day-btn').addEventListener('click', function() {
            const allBlocks = container.querySelectorAll('.record-block');
            let lastDateStr = new Date().toISOString().split('T')[0];
            
            if (allBlocks.length > 0) {
                const lastBlock = allBlocks[allBlocks.length - 1];
                const lastDateInput = lastBlock.querySelector('.transaction-date-input');
                if (lastDateInput.value) {
                    const lastDate = new Date(lastDateInput.value);
                    lastDate.setDate(lastDate.getDate() + 1);
                    lastDateStr = lastDate.toISOString().split('T')[0];
                }
            }
            createRecordBlock(lastDateStr);
        });

        // Form Submit
        form.addEventListener('submit', function() {
            const submitBtns = form.querySelectorAll('button[type="submit"]');
            submitBtns.forEach(btn => {
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';
            });
        });

        // Auto-highlight amount fields on focus
        document.addEventListener('focusin', function(e) {
            if (e.target.classList.contains('amount-input')) {
                e.target.select();
            }
        });

        // Initial Block
        createRecordBlock();
    });
</script>
@endpush
