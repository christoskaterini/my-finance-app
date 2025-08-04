@extends('layouts.studio')
@section('page-title', __('Edit Transaction'))

@section('content')
<div class="row">
    <div class="col-lg-6 mx-auto">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">{{ __('Edit Transaction Details') }}</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('transactions.update', $transaction) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="transaction_date" class="form-label">{{ __('Transaction Date') }}</label>
                        <input type="date" class="form-control" id="transaction_date" name="transaction_date" value="{{ old('transaction_date', $transaction->transaction_date_for_edit) }}" required>
                    </div>

                    <div class="mb-3">
                        <label for="store_id" class="form-label">{{ __('Store') }}</label>
                        <select class="form-select" id="store_id" name="store_id" required>
                            @foreach($stores as $store)
                                <option value="{{ $store->id }}" {{ old('store_id', $transaction->store_id) == $store->id ? 'selected' : '' }}>{{ $store->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="type" class="form-label">{{ __('Type') }}</label>
                        <select class="form-select" id="type" name="type" required>
                            <option value="income" {{ old('type', $transaction->type) == 'income' ? 'selected' : '' }}>{{ __('Income') }}</option>
                            <option value="expense" {{ old('type', $transaction->type) == 'expense' ? 'selected' : '' }}>{{ __('Expense') }}</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="amount" class="form-label">{{ __('Amount') }}</label>
                        <input type="number" step="0.01" class="form-control" id="amount" name="amount" value="{{ old('amount', $transaction->amount) }}" required>
                    </div>

                    <div id="expense-fields" class="{{ old('type', $transaction->type) == 'expense' ? '' : 'd-none' }}">
                        <div class="mb-3">
                            <label for="expense_category_id" class="form-label">{{ __('Expense Category') }}</label>
                            <select class="form-select" id="expense_category_id" name="expense_category_id">
                                @foreach($expenseCategories as $category)
                                    <option value="{{ $category->id }}" {{ old('expense_category_id', $transaction->expense_category_id) == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="notes" class="form-label">{{ __('Notes') }}</label>
                            <input type="text" class="form-control" id="notes" name="notes" value="{{ old('notes', $transaction->notes) }}">
                        </div>
                    </div>

                    <div id="income-fields" class="{{ old('type', $transaction->type) == 'income' ? '' : 'd-none' }}">
                        <div class="mb-3">
                            <label for="shift_id" class="form-label">{{ __('Shift') }}</label>
                            <select class="form-select" id="shift_id" name="shift_id">
                                @foreach($shifts as $shift)
                                    <option value="{{ $shift->id }}" {{ old('shift_id', $transaction->shift_id) == $shift->id ? 'selected' : '' }}>{{ $shift->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="source_id" class="form-label">{{ __('Source') }}</label>
                            <select class="form-select" id="source_id" name="source_id">
                                @foreach($sources as $source)
                                    <option value="{{ $source->id }}" {{ old('source_id', $transaction->source_id) == $source->id ? 'selected' : '' }}>{{ $source->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="payment_method_id" class="form-label">{{ __('Payment Method') }}</label>
                            <select class="form-select" id="payment_method_id" name="payment_method_id">
                                @foreach($paymentMethods as $method)
                                    <option value="{{ $method->id }}" {{ old('payment_method_id', $transaction->payment_method_id) == $method->id ? 'selected' : '' }}>{{ $method->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end">
                        <a href="{{ route('transactions.index') }}" class="btn btn-secondary me-2">{{ __('Cancel') }}</a>
                        <button type="submit" class="btn btn-primary">{{ __('Update Transaction') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const typeSelect = document.getElementById('type');
    const incomeFields = document.getElementById('income-fields');
    const expenseFields = document.getElementById('expense-fields');

    function toggleFields() {
        if (typeSelect.value === 'income') {
            incomeFields.classList.remove('d-none');
            expenseFields.classList.add('d-none');
        } else {
            incomeFields.classList.add('d-none');
            expenseFields.classList.remove('d-none');
        }
    }

    typeSelect.addEventListener('change', toggleFields);
    toggleFields(); // Initial call
});
</script>
@endpush