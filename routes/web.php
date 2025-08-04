<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\Settings\ExpenseCategoryController;
use App\Http\Controllers\Settings\ShiftController;
use App\Http\Controllers\Settings\SourceController;
use App\Http\Controllers\Settings\PaymentMethodController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ChartController;
use App\Http\Controllers\Settings\UserController;
use Illuminate\Support\Facades\Auth;

// --- Publicly Accessible Login Route ---
Route::middleware('web')->group(function () {
    Route::get('/', function () {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    })->name('login');

    // This loads the routes from auth.php and also puts them in the web group.
    require __DIR__ . '/auth.php';

    Route::get('language/{locale}', function ($locale) {
        if (! in_array($locale, array_keys(config('languages')))) {
            abort(400);
        }
        session()->put('locale', $locale);
        return redirect()->back();
    })->name('language.set');
});


// --- All Authenticated Routes ---
Route::middleware(['web', 'auth'])->group(function () {

    // Dashboard (with email verification middleware)
    Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['verified'])->name('dashboard');

    // User Profile Routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Transaction Routes
    Route::delete('transactions/bulk-delete', [TransactionController::class, 'bulkDelete'])->name('transactions.bulkDelete');
    Route::resource('transactions', TransactionController::class);

    // Reports & Charts Group
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('/charts', [ChartController::class, 'index'])->name('charts');
        Route::get('/charts/data', [ChartController::class, 'fetchChartData'])->name('charts.data');
    });

    // Settings Group
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/', [SettingsController::class, 'index'])->name('index');
        Route::post('/general', [SettingsController::class, 'updateGeneral'])->name('updateGeneral');
        Route::post('/logo', [SettingsController::class, 'updateLogo'])->name('updateLogo');
        Route::post('/logo/remove', [SettingsController::class, 'removeLogo'])->name('removeLogo');

        // Resource Routes for other settings sections
        Route::post('stores/update-order', [StoreController::class, 'updateOrder'])->name('stores.updateOrder');
        Route::resource('stores', StoreController::class)->except(['index', 'show', 'create', 'edit'])->names('stores');

        Route::post('expense-categories/update-order', [ExpenseCategoryController::class, 'updateOrder'])->name('expense-categories.updateOrder');
        Route::resource('expense-categories', ExpenseCategoryController::class)->except(['index', 'show', 'create', 'edit'])->names('expense-categories');

        Route::post('shifts/update-order', [ShiftController::class, 'updateOrder'])->name('shifts.updateOrder');
        Route::resource('shifts', ShiftController::class)->except(['index', 'show', 'create', 'edit'])->names('shifts');

        Route::post('sources/update-order', [SourceController::class, 'updateOrder'])->name('sources.updateOrder');
        Route::resource('sources', SourceController::class)->except(['index', 'show', 'create', 'edit'])->names('sources');

        Route::post('payment-methods/update-order', [PaymentMethodController::class, 'updateOrder'])->name('payment-methods.updateOrder');
        Route::resource('payment-methods', PaymentMethodController::class)->except(['index', 'show', 'create', 'edit'])->names('payment-methods');

        Route::resource('users', UserController::class)->middleware('admin');
    });
});
