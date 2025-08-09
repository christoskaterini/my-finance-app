<?php

namespace App\Http\Controllers;

use App\Models\Store;
use App\Models\Transaction;
use App\Models\Shift;
use App\Models\Source;
use App\Models\PaymentMethod;
use App\Models\ExpenseCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $currentView = $request->input('view', 'home');
        $selectedStoreId = $request->input('store_id', 'all');

        // Get selected store and year from request
        $selectedStoreId = $request->input('store_id', 'all');
        $selectedYearTable = $request->input('year', date('Y'));
        $selectedYearCards = $request->input('cards_year', date('Y'));

        // Get all stores for the dropdown
        $stores = Store::all();

        // Get distinct years for the filter dropdowns
        $years = Transaction::selectRaw('YEAR(transaction_date) as year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year');

        // Initialize totals for all stores
        $totalAllIncome = 0;
        $totalAllExpense = 0;

        // Always get all store reports for the top cards, filtered by the selected year for cards
        $storeReports = Store::with(['transactions' => function ($query) use ($selectedYearCards) {
            if ($selectedYearCards !== 'all') {
                $query->whereYear('transaction_date', $selectedYearCards);
            }
        }])->get();

        foreach ($storeReports as $store) {
            $income = $store->transactions->where('type', 'income')->sum('amount');
            $expense = $store->transactions->where('type', 'expense')->sum('amount');

            $totalAllIncome += $income;
            $totalAllExpense += $expense;

            $store->income = $income;
            $store->expense = $expense;
            $store->net = $income - $expense;
        }

        $totalAllNet = $totalAllIncome - $totalAllExpense;

        // Monthly data query
        $monthlyQuery = Transaction::select(
            DB::raw('MONTH(transaction_date) as month_number'),
            DB::raw('SUM(CASE WHEN type = \'income\' THEN amount ELSE 0 END) as monthly_income'),
            DB::raw('SUM(CASE WHEN type = \'expense\' THEN amount ELSE 0 END) as monthly_expense')
        )
            ->groupBy('month_number')
            ->orderBy('month_number');

        if ($selectedYearTable !== 'all') {
            $monthlyQuery->whereYear('transaction_date', $selectedYearTable);
        }
        if ($selectedStoreId !== 'all') {
            $monthlyQuery->where('store_id', $selectedStoreId);
        }

        $monthlyDataRaw = $monthlyQuery->get();
        $monthlyData = [];
        $totalMonthlyIncome = 0;
        $totalMonthlyExpense = 0;

        for ($i = 1; $i <= 12; $i++) {
            $monthData = $monthlyDataRaw->firstWhere('month_number', $i);
            $income = $monthData->monthly_income ?? 0;
            $expense = $monthData->monthly_expense ?? 0;

            $monthlyData[$i] = [
                'month' => Carbon::create()->month($i)->translatedFormat('F'),
                'income' => $income,
                'expense' => $expense,
                'net' => $income - $expense,
            ];
            $totalMonthlyIncome += $income;
            $totalMonthlyExpense += $expense;
        }

        $totalMonthlyNet = $totalMonthlyIncome - $totalMonthlyExpense;

        $selectedStore = ($selectedStoreId !== 'all') ? Store::find($selectedStoreId) : null;

        // Analysis Section Data
        $analysisType = $request->input('type', 'income');
        $analysisStoreId = $request->input('store_id', 'all');
        $selectedSourceId = $request->input('source_id', 'all');
        $selectedPaymentMethodId = $request->input('payment_method_id', 'all');
        $selectedYearAnalysis = $request->input('analysis_year', date('Y'));

        $sources = Source::all();
        $paymentMethods = PaymentMethod::all();
        $analysisColumns = ($analysisType == 'income') ? Shift::all() : ExpenseCategory::all();

        $analysisQuery = Transaction::where('type', $analysisType);
        if ($selectedYearAnalysis !== 'all') {
            $analysisQuery->whereYear('transaction_date', $selectedYearAnalysis);
        }
        if ($analysisStoreId !== 'all') {
            $analysisQuery->where('store_id', $analysisStoreId);
        }
        if ($analysisType === 'income') {
            if ($selectedSourceId !== 'all') {
                $analysisQuery->where('source_id', $selectedSourceId);
            }
            if ($selectedPaymentMethodId !== 'all') {
                $analysisQuery->where('payment_method_id', $selectedPaymentMethodId);
            }
        }

        $analysisTransactions = $analysisQuery->get();
        $analysisData = [];
        $analysisColumnTotals = array_fill_keys($analysisColumns->pluck('id')->toArray(), 0);

        for ($i = 1; $i <= 12; $i++) {
            $monthName = Carbon::create()->month($i)->translatedFormat('F');
            $analysisData[$monthName] = ['month' => $monthName, 'row_total' => 0];
            foreach ($analysisColumns as $column) {
                $analysisData[$monthName][$column->id] = 0;
            }
        }

        foreach ($analysisTransactions as $transaction) {
            $monthName = Carbon::parse($transaction->transaction_date)->translatedFormat('F');
            $columnId = ($analysisType === 'income') ? $transaction->shift_id : $transaction->expense_category_id;
            if (isset($analysisColumnTotals[$columnId])) {
                $analysisData[$monthName][$columnId] += $transaction->amount;
                $analysisData[$monthName]['row_total'] += $transaction->amount;
                $analysisColumnTotals[$columnId] += $transaction->amount;
            }
        }

        // Day Income Analysis Data
        $dayAnalysisYear = $request->input('day_analysis_year', date('Y'));
        $dayAnalysisMonth = $request->input('day_analysis_month', date('m'));
        $dayAnalysisStoreId = $request->input('day_analysis_store_id', 'all');
        $dayAnalysisSourceId = $request->input('day_analysis_source_id', 'all');
        $dayAnalysisPaymentMethodId = $request->input('day_analysis_payment_method_id', 'all');

        $dayIncomeAnalysisQuery = Transaction::where('type', 'income')->whereYear('transaction_date', $dayAnalysisYear);
        if ($dayAnalysisMonth && $dayAnalysisMonth !== 'all') {
            $dayIncomeAnalysisQuery->whereMonth('transaction_date', $dayAnalysisMonth);
        }
        if ($dayAnalysisStoreId && $dayAnalysisStoreId !== 'all') {
            $dayIncomeAnalysisQuery->where('store_id', $dayAnalysisStoreId);
        }
        if ($dayAnalysisSourceId && $dayAnalysisSourceId !== 'all') {
            $dayIncomeAnalysisQuery->where('source_id', $dayAnalysisSourceId);
        }
        if ($dayAnalysisPaymentMethodId && $dayAnalysisPaymentMethodId !== 'all') {
            $dayIncomeAnalysisQuery->where('payment_method_id', $dayAnalysisPaymentMethodId);
        }

        $dayIncomeShifts = ($dayAnalysisStoreId && $dayAnalysisStoreId !== 'all') ? Store::find($dayAnalysisStoreId)->shifts()->get() : Shift::all();
        $dayIncomeTransactions = $dayIncomeAnalysisQuery->get();
        $dayIncomeData = [];

        foreach ($dayIncomeTransactions as $transaction) {
            $carbonDate = Carbon::parse($transaction->transaction_date);
            $dateKey = $carbonDate->format('Y-m-d');
            $displayDate = $carbonDate->translatedFormat('d/m/Y l');
            if (!isset($dayIncomeData[$dateKey])) {
                $dayIncomeData[$dateKey] = ['date' => $displayDate, 'row_total' => 0];
                foreach ($dayIncomeShifts as $shift) {
                    $dayIncomeData[$dateKey][$shift->id] = 0;
                }
            }
            if (isset($dayIncomeData[$dateKey][$transaction->shift_id])) {
                $dayIncomeData[$dateKey][$transaction->shift_id] += $transaction->amount;
                $dayIncomeData[$dateKey]['row_total'] += $transaction->amount;
            }
        }
        ksort($dayIncomeData);

        return view('reports.index', compact(
            'currentView',
            'stores',
            'selectedStore',
            'selectedStoreId',
            'storeReports',
            'totalAllIncome',
            'totalAllExpense',
            'totalAllNet',
            'monthlyData',
            'selectedYearTable',
            'selectedYearCards',
            'years',
            'totalMonthlyIncome',
            'totalMonthlyExpense',
            'totalMonthlyNet',
            // Analysis Data
            'analysisType',
            'selectedSourceId',
            'selectedPaymentMethodId',
            'selectedYearAnalysis',
            'sources',
            'paymentMethods',
            'analysisColumns',
            'analysisColumnTotals',
            'analysisData',
            // Day Income Analysis
            'dayAnalysisYear',
            'dayAnalysisStoreId',
            'dayAnalysisSourceId',
            'dayAnalysisPaymentMethodId',
            'dayAnalysisMonth',
            'dayIncomeShifts',
            'dayIncomeData'
        ));
    }
}
