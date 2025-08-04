<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::defaultView('pagination.custom');

        try {
            if (Schema::hasTable('settings')) {
                $settings = DB::table('settings')->pluck('value', 'key')->all();

                // Set the global timezone
                config(['app.timezone' => $settings['app_timezone'] ?? 'UTC']);
                date_default_timezone_set(config('app.timezone'));

                // Share theme, app name, and logo with all views
                config(['settings' => $settings]);
                View::share('app_theme', $settings['app_theme'] ?? 'dark');
                View::share('app_name', $settings['app_name'] ?? 'Finance Studio');
                View::share('app_logo', $settings['app_logo'] ?? null);
                View::share('app_favicon', $settings['app_logo'] ?? null);

                // Define Blade directive
                Blade::directive('currency', function ($expression) {
                    return "<?php echo format_currency({$expression}); ?>";
                });
            }
        } catch (\Exception $e) {
        }
    }
}
