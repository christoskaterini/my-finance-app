@extends('layouts.studio')
@section('page-title', __('Manage Categories'))

@section('content')

    <div class="d-flex justify-content-start">
        <div style="max-width: 960px; width: 100%;">

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">

                    <ul class="nav nav-tabs card-header-tabs" id="categoryTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link {{ session('active_tab') !== 'expense' ? 'active' : '' }}" id="income-tab" data-bs-toggle="tab" data-bs-target="#income-panel" type="button" role="tab">{{ __('Income') }}</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link {{ session('active_tab') === 'expense' ? 'active' : '' }}" id="expense-tab" data-bs-toggle="tab" data-bs-target="#expense-panel" type="button" role="tab">{{ __('Expenses') }}</button>
                        </li>
                    </ul>

                    <div>
                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#categoryModal" data-action="create">
                            <i class="bi bi-plus-lg me-1"></i> {{ __('New Category') }}
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="tab-content" id="categoryTabsContent">

                        <div class="tab-pane fade {{ session('active_tab') !== 'expense' ? 'show active' : '' }}" id="income-panel" role="tabpanel">
                            @include('categories.partials.category-table', ['categories' => $incomeCategories, 'tableId' => 'income-category-list'])
                        </div>

                        <div class="tab-pane fade {{ session('active_tab') === 'expense' ? 'show active' : '' }}" id="expense-panel" role="tabpanel">
                            @include('categories.partials.category-table', ['categories' => $expenseCategories, 'tableId' => 'expense-category-list'])
                        </div>

                    </div>
                </div>
                <div class="card-footer text-end" id="save-order-footer" style="display: none;">
                    <button class="btn btn-success" id="save-order-btn">{{ __('Save New Order') }}</button>
                </div>
            </div>
        </div>
    </div>


    <!-- ============================================================== -->
    <!-- MODAL for Create/Edit Category -->
    <!-- ============================================================== -->
    <div class="modal fade" id="categoryModal" tabindex="-1" aria-labelledby="categoryModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="categoryModalLabel">{{ __('New Category') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="categoryForm" action="" method="POST">
                    @csrf
                    <div id="modal-method-field"></div>
                    <input type="hidden" name="active_tab" id="modalActiveTab">

                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="modalCategoryName" class="form-label">{{ __('Category Name') }}</label>
                            <input type="text" class="form-control" name="name" id="modalCategoryName" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ __('Type') }}</label>
                            <div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="type" id="modalTypeIncome" value="income" checked>
                                    <label class="form-check-label" for="modalTypeIncome">{{ __('Income') }}</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="type" id="modalTypeExpense" value="expense">
                                    <label class="form-check-label" for="modalTypeExpense">{{ __('Expense') }}</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Close') }}</button>
                        <button type="submit" class="btn btn-primary">{{ __('Save') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Logic for Create/Edit Modal
    const categoryModal = document.getElementById('categoryModal');
    if (categoryModal) {
        const categoryForm = document.getElementById('categoryForm');
        const modalTitle = document.getElementById('categoryModalLabel');
        const modalCategoryName = document.getElementById('modalCategoryName');
        const modalMethodField = document.getElementById('modal-method-field');
        const modalActiveTabInput = document.getElementById('modalActiveTab');
        const modalTypeIncome = document.getElementById('modalTypeIncome');
        const modalTypeExpense = document.getElementById('modalTypeExpense');

        categoryModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const action = button.getAttribute('data-action');
            const activeTab = document.querySelector('#categoryTabs .nav-link.active').id;

            modalActiveTabInput.value = (activeTab === 'expense-tab') ? 'expense' : 'income';

            if (action === 'create') {
                categoryForm.action = '{{ route("categories.store") }}';
                modalMethodField.innerHTML = '';
                modalTitle.textContent = '{{ __("New Category") }}';
                categoryForm.reset();

                if (modalActiveTabInput.value === 'expense') {
                    modalTypeExpense.checked = true;
                } else {
                    modalTypeIncome.checked = true;
                }
            } else if (action === 'edit') {
                const category = JSON.parse(button.getAttribute('data-category'));
                categoryForm.action = `/categories/${category.id}`;
                modalMethodField.innerHTML = '@method("PUT")';
                modalTitle.textContent = '{{ __("Edit Category") }}';
                modalCategoryName.value = category.name;

                if(category.type === 'expense') {
                    modalTypeExpense.checked = true;
                } else {
                    modalTypeIncome.checked = true;
                }
            }
        });
    }

    // Logic for SortableJS Drag-and-Drop
    const saveOrderFooter = document.getElementById('save-order-footer');
    const saveOrderBtn = document.getElementById('save-order-btn');
    let sortableInstances = {};
    let hasOrderChanged = false;

    const initSortable = (tableId) => {
        const tableBody = document.getElementById(tableId);
        if (tableBody) {
            sortableInstances[tableId] = new Sortable(tableBody, {
                animation: 150,
                handle: '.drag-handle',
                onEnd: function () {
                    hasOrderChanged = true;
                    saveOrderFooter.style.display = 'block';
                },
            });
        }
    };

    initSortable('income-category-list');
    initSortable('expense-category-list');

    if (saveOrderBtn) {
        saveOrderBtn.addEventListener('click', function() {
            if (!hasOrderChanged) return;

            const activeTabPane = document.querySelector('.tab-pane.active');
            const activeTableId = activeTabPane.querySelector('tbody').id;
            const newOrder = sortableInstances[activeTableId].toArray();

            fetch('{{ route("categories.updateOrder") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ ids: newOrder })
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    // Use the success toast instead of reloading
                    const toastContainer = document.querySelector('.toast-container');
                    const toastHTML = `<div class="toast align-items-center text-bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true"><div class="d-flex"><div class="toast-body">Order saved successfully!</div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button></div></div>`;
                    toastContainer.innerHTML = toastHTML;
                    new bootstrap.Toast(toastContainer.querySelector('.toast'), {delay: 3000}).show();

                    saveOrderFooter.style.display = 'none';
                    hasOrderChanged = false;
                }
            }).catch(error => console.error('Error:', error));
        });
    }
});
</script>
@endpush
