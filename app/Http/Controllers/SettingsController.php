<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use App\Models\Store;
use App\Models\ExpenseCategory;
use App\Models\Shift;
use App\Models\Source;
use App\Models\PaymentMethod;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;

class SettingsController extends Controller
{
    public function index(Request $request)
    {
        $settings = DB::table('settings')->pluck('value', 'key')->all();
        $stores = Store::orderBy('order_column')->get();
        $expenseCategories = ExpenseCategory::with('stores')->orderBy('order_column')->get();
        $shifts = Shift::with('stores')->orderBy('order_column')->get();
        $sources = Source::with('stores')->orderBy('order_column')->get();
        $paymentMethods = PaymentMethod::with('stores')->orderBy('order_column')->get();

        $activeTab = $request->query('tab', 'stores');
        $timezones = \DateTimeZone::listIdentifiers(\DateTimeZone::ALL);

        return view('settings.index', compact(
            'settings',
            'stores',
            'expenseCategories',
            'shifts',
            'sources',
            'paymentMethods',
            'activeTab',
            'timezones'
        ));
    }


    public function updateGeneral(Request $request)
    {
        $validated = $request->validate([
            'app_name'              => 'required|string|max:255',
            'app_theme'             => 'required|string|in:light,dark',
            'locale'                => 'required|string|in:en,el',
            'app_timezone'          => 'required|string|timezone',
            'app_currency_symbol'   => 'required|string|max:5',
            'app_number_format'     => 'required|string|in:".",","',
            'app_currency_position' => 'required|string|in:before,after',
        ]);

        Session::put('locale', $validated['locale']);

        if (Auth::check()) {
            DB::table('users')
                ->where('id', Auth::id())
                ->update(['locale' => $validated['locale']]);
        }

        foreach ($validated as $key => $value) {
            if ($key !== 'locale') {
                DB::table('settings')->updateOrInsert(['key' => $key], ['value' => $value]);
            }
        }

        Artisan::call('cache:clear');
        Artisan::call('config:clear');
        Artisan::call('view:clear');

        return redirect()->route('settings.index', ['tab' => 'general'])
            ->with('success', __('Settings saved successfully.'));
    }

    public function updateLogo(Request $request)
    {
        $request->validate([
            'app_logo' => 'required|image|mimes:png,jpg,jpeg,svg|max:2048',
        ]);

        // Find and delete the old logo before uploading the new one
        $oldLogoPath = DB::table('settings')->where('key', 'app_logo')->value('value');
        if ($oldLogoPath) {
            Storage::disk('public')->delete($oldLogoPath);
        }

        // Store the new logo and update the database
        $path = $request->file('app_logo')->store('logos', 'public');
        DB::table('settings')->updateOrInsert(['key' => 'app_logo'], ['value' => $path]);
        DB::table('settings')->updateOrInsert(['key' => 'app_favicon'], ['value' => $path]);

        Artisan::call('cache:clear');
        return redirect()->route('settings.index', ['tab' => 'general'])->with('success', 'Logo updated successfully.');
    }

    public function removeLogo()
    {
        $logoPath = DB::table('settings')->where('key', 'app_logo')->value('value');
        if ($logoPath) {
            Storage::disk('public')->delete($logoPath);
        }
        DB::table('settings')->whereIn('key', ['app_logo', 'app_favicon'])->delete();

        Artisan::call('cache:clear');
        return redirect()->route('settings.index', ['tab' => 'general'])->with('success', 'Logo removed successfully.');
    }
}
