<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Transaction;
use App\Models\Store;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

class ChartController extends Controller
{
    /**
     * Display the initial charts page layout.
     */
    public function index()
    {
        $years = Transaction::selectRaw('YEAR(transaction_date) as year')->distinct()->orderBy('year', 'desc')->pluck('year');
        $stores = Store::all();

        return view('reports.charts.index', compact('years', 'stores'));
    }

    /**
     * Fetch data for a specific chart via AJAX.
     */
    public function fetchChartData(Request $request): JsonResponse
    {
        $chartId = $request->input('chartId');
        $year = $request->input('year', date('Y'));
        $storeId = $request->input('store_id', 'all');
        $compareStores = $request->input('compare_stores', []);

        switch ($chartId) {
            case 'performanceChart':
                return $this->getPerformanceData($year, $storeId);
            case 'incomeSourceChart':
                return $this->getIncomeBySourceData($year, $storeId);
            case 'incomeByShiftChart':
                return $this->getIncomeByShiftData($year, $storeId);
            case 'expenseCategoryChart':
                return $this->getExpenseByCategoryData($year, $storeId);
            case 'storePerformanceChart':
                return $this->getStoreComparisonData($year, $compareStores);
            default:
                return response()->json(['error' => 'Invalid chart ID'], 400);
        }
    }

    private function getPerformanceData($year, $storeId): JsonResponse
    {
        $query = Transaction::query()->select(
            DB::raw('MONTH(transaction_date) as month'),
            DB::raw('SUM(CASE WHEN type = "income" THEN amount ELSE 0 END) as total_income'),
            DB::raw('SUM(CASE WHEN type = "expense" THEN amount ELSE 0 END) as total_expense')
        )->groupBy('month')->orderBy('month');

        if ($year !== 'all') { $query->whereYear('transaction_date', $year); }
        if ($storeId !== 'all') { $query->where('store_id', $storeId); }
        
        $data = $query->get();
        $labels = []; $incomeData = []; $expenseData = []; $netProfitData = [];
        for ($m = 1; $m <= 12; $m++) {
            $labels[] = Carbon::create()->month($m)->format('F');
            $monthData = $data->firstWhere('month', $m);
            $income = $monthData->total_income ?? 0;
            $expense = $monthData->total_expense ?? 0;
            $incomeData[] = round($income, 2);
            $expenseData[] = round($expense, 2);
            $netProfitData[] = round($income - $expense, 2);
        }

        return response()->json([
            'labels' => $labels,
            'datasets' => [
                ['label' => __('Income'), 'data' => $incomeData, 'borderColor' => 'rgba(25, 135, 84, 1)', 'backgroundColor' => 'rgba(25, 135, 84, 0.1)', 'fill' => true, 'tension' => 0.3],
                ['label' => __('Expenses'), 'data' => $expenseData, 'borderColor' => 'rgba(220, 53, 69, 1)', 'backgroundColor' => 'rgba(220, 53, 69, 0.1)', 'fill' => true, 'tension' => 0.3],
                ['label' => __('Net Profit'), 'data' => $netProfitData, 'borderColor' => 'rgba(13, 110, 253, 1)', 'backgroundColor' => 'rgba(13, 110, 253, 0.1)', 'fill' => true, 'tension' => 0.3],
            ]
        ]);
    }

    private function getIncomeBySourceData($year, $storeId): JsonResponse
    {
        $query = Transaction::where('type', 'income')
            // Using leftJoin is the most robust method
            ->leftJoin('sources', 'transactions.source_id', '=', 'sources.id')
            ->select(
                DB::raw("COALESCE(sources.name, 'Uncategorized') as label"),
                DB::raw('SUM(transactions.amount) as total')
            )->groupBy('label');

        if ($year !== 'all') { $query->whereYear('transaction_date', $year); }
        if ($storeId !== 'all') { $query->where('store_id', $storeId); }
        $data = $query->pluck('total', 'label');

        return response()->json([
            'labels' => $data->keys(),
            'datasets' => [['data' => $data->values()->map(fn($v) => round($v,2)), 'backgroundColor' => ['#198754', '#0d6efd', '#6f42c1', '#fd7e14', '#20c997', '#dc3545'], 'hoverOffset' => 4]]
        ]);
    }

    private function getIncomeByShiftData($year, $storeId): JsonResponse
    {
        $query = Transaction::where('type', 'income')
            // Using leftJoin is the most robust method
            ->leftJoin('shifts', 'transactions.shift_id', '=', 'shifts.id')
            ->select(
                DB::raw("COALESCE(shifts.name, 'No Shift Assigned') as label"),
                DB::raw('SUM(transactions.amount) as total')
            )->groupBy('label');

        if ($year !== 'all') { $query->whereYear('transaction_date', $year); }
        if ($storeId !== 'all') { $query->where('store_id', $storeId); }
        $data = $query->pluck('total', 'label');

        return response()->json([
            'labels' => $data->keys(),
            'datasets' => [['data' => $data->values()->map(fn($v) => round($v,2)), 'backgroundColor' => ['#6f42c1', '#fd7e14', '#20c997', '#0dcaf0', '#ffc107', '#d63384'], 'hoverOffset' => 4]]
        ]);
    }
    
    private function getExpenseByCategoryData($year, $storeId): JsonResponse
    {
        $query = Transaction::where('type', 'expense')
            // Using leftJoin is the most robust method
            ->leftJoin('expense_categories', 'transactions.expense_category_id', '=', 'expense_categories.id')
            ->select(
                DB::raw("COALESCE(expense_categories.name, 'Uncategorized') as label"),
                DB::raw('SUM(transactions.amount) as total')
            )->groupBy('label');

        if ($year !== 'all') { $query->whereYear('transaction_date', $year); }
        if ($storeId !== 'all') { $query->where('store_id', $storeId); }
        $data = $query->pluck('total', 'label');

        return response()->json([
            'labels' => $data->keys(),
            'datasets' => [['label' => __('Total Expense'), 'data' => $data->values()->map(fn($v) => round($v,2)), 'backgroundColor' => 'rgba(220, 53, 69, 0.7)']]
        ]);
    }

    private function getStoreComparisonData($year, $storeIds): JsonResponse
    {
        if (empty($storeIds)) {
            return response()->json(['labels' => [], 'datasets' => []]);
        }
        $query = Transaction::query()
            ->join('stores', 'transactions.store_id', '=', 'stores.id')
            ->select(
                'stores.name as store_name',
                DB::raw('SUM(CASE WHEN type = "income" THEN amount ELSE 0 END) as total_income'),
                DB::raw('SUM(CASE WHEN type = "expense" THEN amount ELSE 0 END) as total_expense')
            )
            ->whereIn('transactions.store_id', $storeIds)
            ->groupBy('store_name');
        if ($year !== 'all') { $query->whereYear('transaction_date', $year); }
        $data = $query->get();

        return response()->json([
            'labels' => $data->pluck('store_name'),
            'datasets' => [
                ['label' => __('Income'), 'data' => $data->pluck('total_income')->map(fn($v) => round($v,2)), 'backgroundColor' => 'rgba(25, 135, 84, 0.7)'],
                ['label' => __('Expenses'), 'data' => $data->pluck('total_expense')->map(fn($v) => round($v,2)), 'backgroundColor' => 'rgba(220, 53, 69, 0.7)'],
            ]
        ]);
    }
}