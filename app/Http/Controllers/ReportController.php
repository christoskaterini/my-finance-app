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
    /**
     * A robust helper function to parse dates that might be in d/m/Y format.
     */
    private function parseDate($dateString)
    {
        if (empty($dateString)) {
            return null;
        }
        try {
            // Try to parse the European format first
            return Carbon::createFromFormat('d/m/Y', $dateString)->startOfDay();
        } catch (\Exception $e) {
            // If that fails, fall back to Carbon's more flexible parser
            return Carbon::parse($dateString)->startOfDay();
        }
    }

    public function index(Request $request)
    {
        $filterKeys = [
            'view', 'store_id', 'cards_year', 'year', 'type', 'analysis_year', 
            'source_id', 'payment_method_id', 'day_analysis_year', 
            'day_analysis_month', 'day_analysis_store_id', 
            'day_analysis_source_id', 'day_analysis_payment_method_id'
        ];
        $sessionPrefix = 'reports_';

        // Handle Reset
        if ($request->has('reset')) {
            foreach ($filterKeys as $key) {
                session()->forget($sessionPrefix . $key);
            }
            return redirect()->route('reports.index');
        }

        // --- Sticky Filters Logic ---
        $finalFilters = [];
        $needRedirect = false;

        foreach ($filterKeys as $key) {
            if ($request->has($key)) {
                // Parameter is in request: update session
                $val = $request->input($key);
                if (!is_null($val) && $val !== '') {
                    session([$sessionPrefix . $key => $val]);
                    $finalFilters[$key] = $val;
                } else {
                    session()->forget($sessionPrefix . $key);
                }
            } elseif (session()->has($sessionPrefix . $key)) {
                // Parameter is missing from request but exists in session: restore it
                $finalFilters[$key] = session($sessionPrefix . $key);
                $needRedirect = true;
            }
        }

        // If we restored any filters from session, redirect to the full URL
        if ($needRedirect) {
            return redirect()->route('reports.index', $finalFilters);
        }
    
        // Centralize filter parameters
        $currentView = $request->input('view', 'home');
        $selectedStoreId = $request->input('store_id', 'all');
        $selectedYearCards = $request->input('cards_year', date('Y'));
        $selectedYearTable = $request->input('year', date('Y'));
        $analysisType = $request->input('type', 'income');
        $selectedYearAnalysis = $request->input('analysis_year', date('Y'));
        $selectedSourceId = $request->input('source_id', 'all'); // This was missing
        $selectedPaymentMethodId = $request->input('payment_method_id', 'all'); // This was missing
        $dayAnalysisYear = $request->input('day_analysis_year', date('Y'));
        $dayAnalysisMonth = $request->input('day_analysis_month', date('m'));
        $dayAnalysisStoreId = $request->input('day_analysis_store_id', 'all');
        $dayAnalysisSourceId = $request->input('day_analysis_source_id', 'all');
        $dayAnalysisPaymentMethodId = $request->input('day_analysis_payment_method_id', 'all');

        // Data for dropdowns
        $stores = Store::orderBy('order_column')->get();
        $sources = Source::orderBy('order_column')->get();
        $paymentMethods = PaymentMethod::orderBy('order_column')->get();
        $years = Transaction::selectRaw('YEAR(transaction_date) as year')->distinct()->orderBy('year', 'desc')->pluck('year');

        // --- All Stores Overview (Cards) ---
        $cardsQuery = Transaction::query();
        if ($selectedYearCards !== 'all') {
            $cardsQuery->whereYear('transaction_date', $selectedYearCards);
        }
        $totalAllIncome = (clone $cardsQuery)->where('type', 'income')->sum('amount');
        $totalAllExpense = (clone $cardsQuery)->where('type', 'expense')->sum('amount');
        $totalAllNet = $totalAllIncome - $totalAllExpense;

        $storeReports = Store::orderBy('order_column')->get()->map(function ($store) use ($selectedYearCards) {
            $query = $store->transactions();
            if ($selectedYearCards !== 'all') {
                $query->whereYear('transaction_date', $selectedYearCards);
            }
            $income = (clone $query)->where('type', 'income')->sum('amount');
            $expense = (clone $query)->where('type', 'expense')->sum('amount');
            $store->income = $income;
            $store->expense = $expense;
            $store->net = $income - $expense;
            return $store;
        });

        // --- Monthly Sums Table ---
        $monthlyQuery = Transaction::selectRaw('MONTH(transaction_date) as month_number, SUM(CASE WHEN type = "income" THEN amount ELSE 0 END) as monthly_income, SUM(CASE WHEN type = "expense" THEN amount ELSE 0 END) as monthly_expense')
            ->groupBy('month_number')->orderBy('month_number');
        if ($selectedYearTable !== 'all') {
            $monthlyQuery->whereYear('transaction_date', $selectedYearTable);
        }
        if ($selectedStoreId !== 'all') {
            $monthlyQuery->where('store_id', $selectedStoreId);
        }
        $monthlyDataRaw = $monthlyQuery->get();
        $monthlyData = [];
        for ($i = 1; $i <= 12; $i++) {
            $monthData = $monthlyDataRaw->firstWhere('month_number', $i);
            $monthlyData[$i] = [
                'month' => Carbon::create()->month($i)->translatedFormat('F'),
                'income' => $monthData->monthly_income ?? 0,
                'expense' => $monthData->monthly_expense ?? 0,
                'net' => ($monthData->monthly_income ?? 0) - ($monthData->monthly_expense ?? 0),
            ];
        }
        $totalMonthlyIncome = collect($monthlyData)->sum('income');
        $totalMonthlyExpense = collect($monthlyData)->sum('expense');
        $totalMonthlyNet = $totalMonthlyIncome - $totalMonthlyExpense;

        // --- Analysis by Category/Shift Table ---
        $analysisType = $request->input('type', 'income');
        $analysisColumns = ($analysisType == 'income') ? Shift::orderBy('order_column')->get() : ExpenseCategory::orderBy('order_column')->get();
        $analysisQuery = Transaction::where('type', $analysisType);
        if ($selectedYearAnalysis !== 'all') {
            $analysisQuery->whereYear('transaction_date', $selectedYearAnalysis);
        }
        if ($selectedStoreId !== 'all') {
            $analysisQuery->where('store_id', $selectedStoreId);
        }
        if ($analysisType === 'income') {
            if ($request->input('source_id', 'all') !== 'all') {
                $analysisQuery->where('source_id', $request->input('source_id'));
            }
            if ($request->input('payment_method_id', 'all') !== 'all') {
                $analysisQuery->where('payment_method_id', $request->input('payment_method_id'));
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
            // Use the robust parseDate function
            $monthName = $this->parseDate($transaction->transaction_date)->translatedFormat('F');
            $columnId = ($analysisType === 'income') ? $transaction->shift_id : $transaction->expense_category_id;
            if (isset($analysisColumnTotals[$columnId])) {
                $analysisData[$monthName][$columnId] += $transaction->amount;
                $analysisData[$monthName]['row_total'] += $transaction->amount;
                $analysisColumnTotals[$columnId] += $transaction->amount;
            }
        }

        // --- Day Income Analysis Table ---
        $dayIncomeAnalysisQuery = Transaction::where('type', 'income')->whereYear('transaction_date', $dayAnalysisYear)->whereMonth('transaction_date', $dayAnalysisMonth);
        $dayIncomeShifts = Shift::query();
        if ($dayAnalysisStoreId !== 'all') {
            $dayIncomeAnalysisQuery->where('store_id', $dayAnalysisStoreId);
            $dayIncomeShifts->whereHas('stores', function ($q) use ($dayAnalysisStoreId) {
                $q->where('store_id', $dayAnalysisStoreId);
            });
        }
        if ($dayAnalysisSourceId !== 'all') {
            $dayIncomeAnalysisQuery->where('source_id', $dayAnalysisSourceId);
        }
        if ($dayAnalysisPaymentMethodId !== 'all') {
            $dayIncomeAnalysisQuery->where('payment_method_id', $dayAnalysisPaymentMethodId);
        }
        $dayIncomeShifts = $dayIncomeShifts->orderBy('order_column')->get();
        $dayIncomeTransactions = $dayIncomeAnalysisQuery->get();
        $dayIncomeData = [];
        foreach ($dayIncomeTransactions as $transaction) {
            // Use the robust parseDate function
            $carbonDate = $this->parseDate($transaction->transaction_date);
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
            'selectedStoreId',
            'storeReports',
            'totalAllIncome',
            'totalAllExpense',
            'totalAllNet',
            'monthlyData',
            'selectedYearTable',
            'years',
            'totalMonthlyIncome',
            'totalMonthlyExpense',
            'totalMonthlyNet',
            'analysisType',
            'selectedYearAnalysis',
            'sources',
            'paymentMethods',
            'analysisColumns',
            'analysisColumnTotals',
            'analysisData',
            'selectedYearCards',
            'selectedSourceId',
            'selectedPaymentMethodId',
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
