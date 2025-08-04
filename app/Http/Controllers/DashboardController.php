<?php

namespace App\Http\Controllers;

use App\Models\ExpenseCategory;
use App\Models\PaymentMethod;
use App\Models\Shift;
use App\Models\Source;
use App\Models\Store;

class DashboardController extends Controller
{
    public function index()
    {
        $stores = Store::orderBy('order_column')->get();
        $expenseCategories = ExpenseCategory::with('stores')->orderBy('order_column')->get();
        $shifts = Shift::with('stores')->orderBy('order_column')->get();
        $sources = Source::with('stores')->orderBy('order_column')->get();
        $paymentMethods = PaymentMethod::with('stores')->orderBy('order_column')->get();

        return view('dashboard', compact(
            'stores',
            'expenseCategories',
            'shifts',
            'sources',
            'paymentMethods'
        ));
    }
}
