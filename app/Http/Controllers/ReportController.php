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
        $selectedYearTable = $request->input('year', date('Y')); // Year for the monthly table
        $selectedYearCards = $request->input('cards_year', date('Y')); // Year for the top cards

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
            DB::raw('MONTH(transaction_date) as month'),
            DB::raw('SUM(CASE WHEN type = \'income\' THEN amount ELSE 0 END) as monthly_income'),
            DB::raw('SUM(CASE WHEN type = \'expense\' THEN amount ELSE 0 END) as monthly_expense')
        )
            ->groupBy(DB::raw('MONTH(transaction_date)'))
            ->orderBy(DB::raw('MONTH(transaction_date)'));

        if ($selectedYearTable !== 'all') {
            $monthlyQuery->whereYear('transaction_date', $selectedYearTable);
        }

        // Filter monthly data if a specific store is selected
        if ($selectedStoreId !== 'all') {
            $monthlyQuery->where('store_id', $selectedStoreId);
        }

        $monthlyDataRaw = $monthlyQuery->get();

        $monthlyData = [];
        $totalMonthlyIncome = 0;
        $totalMonthlyExpense = 0;

        for ($i = 1; $i <= 12; $i++) {
            $monthData = $monthlyDataRaw->firstWhere('month', $i);
            $income = $monthData->monthly_income ?? 0;
            $expense = $monthData->monthly_expense ?? 0;
            $monthlyData[$i] = [
                'month' => date('F', mktime(0, 0, 0, $i, 1)),
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
        $analysisType = $request->input('type', 'income'); // 'income' or 'expense'
        // NOTE: This section uses `store_id` from the form, which is shared with the monthly table.
        $analysisStoreId = $request->input('store_id', 'all'); 
        $selectedSourceId = $request->input('source_id', 'all');
        $selectedPaymentMethodId = $request->input('payment_method_id', 'all');
        $selectedYearAnalysis = $request->input('analysis_year', date('Y'));

        $sources = Source::all();
        $paymentMethods = PaymentMethod::all();
        
        $analysisColumns = collect();
        if ($analysisType == 'income') {
            if ($analysisStoreId !== 'all') {
                $store = Store::find($analysisStoreId);
                $analysisColumns = $store ? $store->shifts()->get() : collect();
            } else {
                $analysisColumns = Shift::all();
            }
        } else { // expense
            $analysisColumns = ExpenseCategory::all();
        }

        $analysisQuery = Transaction::where('type', $analysisType);
        
        // Correctly apply filters only if they are not 'all'
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
            $monthName = date('F', mktime(0, 0, 0, $i, 1));
            $analysisData[$monthName] = ['month' => $monthName, 'row_total' => 0];
            foreach ($analysisColumns as $column) {
                $analysisData[$monthName][$column->id] = 0;
            }
        }
        
        foreach ($analysisTransactions as $transaction) {
            $monthName = Carbon::parse($transaction->getRawOriginal('transaction_date'))->format('F');
            $columnId = ($analysisType === 'income') ? $transaction->shift_id : $transaction->expense_category_id;

            if (isset($analysisColumnTotals[$columnId])) { // Ensure column exists before adding
                $analysisData[$monthName][$columnId] += $transaction->amount;
                $analysisData[$monthName]['row_total'] += $transaction->amount;
                $analysisColumnTotals[$columnId] += $transaction->amount;
            }
        }

        // Day Income Analysis Data
        $dayAnalysisYear = $request->input('day_analysis_year', date('Y'));
        $dayAnalysisMonth = $request->input('day_analysis_month', date('m')); // Add this line
        $dayAnalysisStoreId = $request->input('day_analysis_store_id', 'all');
        $dayAnalysisSourceId = $request->input('day_analysis_source_id', 'all');
        $dayAnalysisPaymentMethodId = $request->input('day_analysis_payment_method_id', 'all');

        $dayIncomeAnalysisQuery = Transaction::where('type', 'income')
            ->whereYear('transaction_date', $dayAnalysisYear);

        // -- Filter by Month --
        if ($dayAnalysisMonth && $dayAnalysisMonth !== 'all') {
            $dayIncomeAnalysisQuery->whereMonth('transaction_date', $dayAnalysisMonth);
        }

        $dayIncomeShifts = collect();

        // -- Filter by Store --
        if ($dayAnalysisStoreId && $dayAnalysisStoreId !== 'all') {
            $store = Store::find($dayAnalysisStoreId);
            if ($store) {
                $dayIncomeShifts = $store->shifts()->get();
            }
            $dayIncomeAnalysisQuery->where('store_id', $dayAnalysisStoreId);
        } else {
            // If all stores are selected, get all shifts
            $dayIncomeShifts = Shift::all();
        }

        // -- Filter by Source --
        if ($dayAnalysisSourceId && $dayAnalysisSourceId !== 'all') {
            $dayIncomeAnalysisQuery->where('source_id', $dayAnalysisSourceId);
        }

        // -- Filter by Payment Method --
        if ($dayAnalysisPaymentMethodId && $dayAnalysisPaymentMethodId !== 'all') {
            $dayIncomeAnalysisQuery->where('payment_method_id', $dayAnalysisPaymentMethodId);
        }

        $dayIncomeTransactions = $dayIncomeAnalysisQuery->get();

        $dayIncomeData = []; 
        foreach ($dayIncomeTransactions as $transaction) {
            $carbonDate = Carbon::parse($transaction->getRawOriginal('transaction_date'));
            $dateKey = $carbonDate->format('Y-m-d'); 
            $displayDate = $carbonDate->format('d/m/Y l'); 

            if (!isset($dayIncomeData[$dateKey])) {
                $dayIncomeData[$dateKey] = ['date' => $displayDate, 'row_total' => 0];
                foreach ($dayIncomeShifts as $shift) {
                    $dayIncomeData[$dateKey][$shift->id] = 0;
                }
            }
            
            // Check if the transaction's shift_id is a valid column before adding data.
            if (isset($dayIncomeData[$dateKey][$transaction->shift_id])) {
                $dayIncomeData[$dateKey][$transaction->shift_id] += $transaction->amount;
                $dayIncomeData[$dateKey]['row_total'] += $transaction->amount;
            }
        }
        
        // Sort the data by date
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