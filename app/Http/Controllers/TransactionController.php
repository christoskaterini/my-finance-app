<?php

namespace App\Http\Controllers;

use App\Models\Store;
use App\Models\Transaction;
use App\Models\ExpenseCategory;
use App\Models\PaymentMethod;
use App\Models\Shift;
use App\Models\Source;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class TransactionController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $query = Transaction::with(['user', 'store', 'expenseCategory', 'shift', 'source', 'paymentMethod']);

        if ($request->filled('search')) {
            $searchTerm = $request->input('search');
            $query->where(function ($q) use ($searchTerm) {
                $q->where('notes', 'like', "%{$searchTerm}%")
                    ->orWhereHas('user', function ($subQuery) use ($searchTerm) {
                        $subQuery->where('name', 'like', "%{$searchTerm}%");
                    })
                    ->orWhereHas('store', function ($subQuery) use ($searchTerm) {
                        $subQuery->where('name', 'like', "%{$searchTerm}%");
                    });

                $q->orWhere(function ($subQ) use ($searchTerm) {
                    $subQ->where('type', 'expense')
                        ->whereHas('expenseCategory', function ($catQuery) use ($searchTerm) {
                            $catQuery->where('name', 'like', "%{$searchTerm}%");
                        });
                });

                $q->orWhere(function ($subQ) use ($searchTerm) {
                    $subQ->where('type', 'income')
                        ->where(function ($incomeQuery) use ($searchTerm) {
                            $incomeQuery->orWhereHas('shift', function ($shiftQuery) use ($searchTerm) {
                                $shiftQuery->where('name', 'like', "%{$searchTerm}%");
                            })
                                ->orWhereHas('source', function ($sourceQuery) use ($searchTerm) {
                                    $sourceQuery->where('name', 'like', "%{$searchTerm}%");
                                })
                                ->orWhereHas('paymentMethod', function ($pmQuery) use ($searchTerm) {
                                    $pmQuery->where('name', 'like', "%{$searchTerm}%");
                                });
                        });
                });
            });
        }

        // --- Apply Filters ---
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        $selectedYear = $request->input('year');
        $selectedMonth = $request->input('month');

        if ($selectedYear && $selectedMonth) {
            $dateFrom = Carbon::create($selectedYear, $selectedMonth, 1)->startOfMonth()->toDateString();
            $dateTo = Carbon::create($selectedYear, $selectedMonth, 1)->endOfMonth()->toDateString();
        } elseif (!$dateFrom && !$dateTo) {
            $dateFrom = now()->startOfMonth()->toDateString();
            $dateTo = now()->endOfMonth()->toDateString();
        }

        if ($dateFrom && $dateTo) {
            $query->whereBetween('transaction_date', [$dateFrom, $dateTo]);
        }

        if ($request->filled('store_id')) {
            $query->where('store_id', $request->store_id);
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $summaryQuery = clone $query;
        $totalIncome = $summaryQuery->where('type', 'income')->sum('amount');
        $summaryQuery = clone $query;
        $totalExpenses = $summaryQuery->where('type', 'expense')->sum('amount');

        $perPage = $request->input('per_page', 100);

        if ($perPage === 'all') {
            $total = (clone $query)->count();
            $transactions = $query->latest('transaction_date')->latest('created_at')->paginate($total > 0 ? $total : 1)->withQueryString();
        } else {
            $transactions = $query->latest('transaction_date')->latest('created_at')->paginate($perPage)->withQueryString();
        }

        $netTotal = $totalIncome - $totalExpenses;
        $transactionCount = $transactions->total();

        $stores = Store::orderBy('order_column')->get();

        $years = Transaction::selectRaw('YEAR(transaction_date) as year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year');

        return view('transactions.index', compact(
            'transactions',
            'totalIncome',
            'totalExpenses',
            'netTotal',
            'transactionCount',
            'stores',
            'dateFrom',
            'dateTo',
            'years',
            'selectedYear',
            'selectedMonth'
        ));
    }

    public function create(Request $request)
    {
        $selectedStoreId = $request->query('store_id');
        $selectedStore = Store::find($selectedStoreId);

        if (!$selectedStore) {
            return redirect()->route('dashboard')->withErrors(['general' => __('Please select a valid store first.')]);
        }

        $expenseCategories = ExpenseCategory::whereHas('stores', fn($q) => $q->where('store_id', $selectedStoreId))->orderBy('order_column')->get();
        $shifts = Shift::whereHas('stores', fn($q) => $q->where('store_id', $selectedStoreId))->orderBy('order_column')->get();
        $sources = Source::whereHas('stores', fn($q) => $q->where('store_id', $selectedStoreId))->orderBy('order_column')->get();
        $paymentMethods = PaymentMethod::whereHas('stores', fn($q) => $q->where('store_id', $selectedStoreId))->orderBy('order_column')->get();

        return view('transactions.create', compact(
            'selectedStore',
            'expenseCategories',
            'shifts',
            'sources',
            'paymentMethods'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'store_id' => 'required|exists:stores,id',
            'transaction_date' => 'required|date',
            'expenses' => 'nullable|array',
            'expenses.*.expense_category_id' => 'required_with:expenses.*.amount|exists:expense_categories,id',
            'expenses.*.amount' => 'nullable|numeric|gt:0',
            'expenses.*.notes' => 'nullable|string|max:255',
            'income' => 'nullable|array',
            'income.*.shift_id' => 'required_with:income.*.amount|exists:shifts,id',
            'income.*.source_id' => 'required_with:income.*.amount|exists:sources,id',
            'income.*.payment_method_id' => 'required_with:income.*.amount|exists:payment_methods,id',
            'income.*.amount' => 'nullable|numeric|gt:0',
        ]);

        $user_id = Auth::id();
        $transactionCount = 0;

        if (!empty($validated['expenses'])) {
            foreach ($validated['expenses'] as $expense) {
                if (!empty($expense['amount']) && !empty($expense['expense_category_id'])) {
                    Transaction::create([
                        'user_id' => $user_id,
                        'store_id' => $validated['store_id'],
                        'transaction_date' => $validated['transaction_date'],
                        'type' => 'expense',
                        'expense_category_id' => $expense['expense_category_id'],
                        'amount' => $expense['amount'],
                        'notes' => $expense['notes'] ?? null
                    ]);
                    $transactionCount++;
                }
            }
        }

        if (!empty($validated['income'])) {
            foreach ($validated['income'] as $income) {
                if (!empty($income['amount'])) {
                    Transaction::create(['user_id' => $user_id, 'store_id' => $validated['store_id'], 'transaction_date' => $validated['transaction_date'], 'type' => 'income', 'shift_id' => $income['shift_id'], 'source_id' => $income['source_id'], 'payment_method_id' => $income['payment_method_id'], 'amount' => $income['amount']]);
                    $transactionCount++;
                }
            }
        }

        if ($transactionCount == 0) {
            return back()->withErrors(['general' => __('You must enter at least one valid amount to save.')])->withInput();
        }

        $message = __(':count record(s) saved successfully!', ['count' => $transactionCount]);
        return redirect()->route('dashboard')->with('success', $message);
    }

    public function edit(Transaction $transaction)
    {
        $this->authorize('update', $transaction);

        $stores = Store::orderBy('order_column')->get();
        $expenseCategories = ExpenseCategory::orderBy('order_column')->get();
        $shifts = Shift::orderBy('order_column')->get();
        $sources = Source::orderBy('order_column')->get();
        $paymentMethods = PaymentMethod::orderBy('order_column')->get();

        return view('transactions.edit', compact('transaction', 'stores', 'expenseCategories', 'shifts', 'sources', 'paymentMethods'));
    }

    public function update(Request $request, Transaction $transaction)
    {
        $this->authorize('update', $transaction);

        $validated = $request->validate([
            'store_id' => 'required|exists:stores,id',
            'transaction_date' => 'required|date',
            'type' => 'required|in:income,expense',
            'amount' => 'required|numeric|gt:0',
            'expense_category_id' => 'required_if:type,expense|exists:expense_categories,id',
            'shift_id' => 'required_if:type,income|exists:shifts,id',
            'source_id' => 'required_if:type,income|exists:sources,id',
            'payment_method_id' => 'required_if:type,income|exists:payment_methods,id',
            'notes' => 'nullable|string|max:255',
        ]);

        $transaction->update($validated);

        $message = __('Transaction for :store on :date updated successfully!', [
            'store' => $transaction->store->name,
            'date' => $transaction->transaction_date,
        ]);

        return redirect()->route('transactions.index')->with('success', $message);
    }

    public function destroy(Transaction $transaction)
    {
        $this->authorize('delete', $transaction);
        $transaction->delete();
        return redirect()->route('transactions.index')->with('success', __('Transaction deleted successfully!'));
    }

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:transactions,id',
        ]);

        $transactions = Transaction::whereIn('id', $request->ids)->get();
        foreach ($transactions as $transaction) {
            $this->authorize('delete', $transaction);
        }

        Transaction::whereIn('id', $request->ids)->delete();

        return redirect()->route('transactions.index')->with('success', __(':count transactions deleted successfully!', ['count' => count($request->ids)]));
    }
}
