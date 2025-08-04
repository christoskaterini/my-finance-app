<?php

use App\Models\Setting;

if (! function_exists('settings')) {
    /**
     * Access the settings helper.
     */
    function settings($key = null, $default = null)
    {
        if (is_null($key)) {
            return app(Setting::class);
        }

        if (is_array($key)) {
            foreach ($key as $k => $v) {
                Setting::set($k, $v);
            }

            return;
        }

        return Setting::get($key, $default);
    }
}

if (!function_exists('format_number')) {
    /**
     * Formats a number according to the application's saved settings.
     * This is a reliable replacement for number_format() when using dynamic separators.
     */
    function format_number($number, $decimals = 2)
    {
        // START: This is the corrected logic
        // 1. Get the decimal separator from settings. Default to ',' as requested.
        $decimalSeparator = settings('app_number_format', ',');

        // 2. Determine the thousands separator based on the decimal one.
        // If decimal is ',', thousands is '.'. Otherwise, thousands is ','.
        $thousandsSeparator = ($decimalSeparator === ',') ? '.' : ',';
        // END: This is the corrected logic

        return number_format(
            (float) $number,
            $decimals,
            $decimalSeparator,
            $thousandsSeparator
        );
    }
}

if (!function_exists('format_currency')) {
    /**
     * Formats a number as a currency string according to the application's settings.
     */
    function format_currency($amount, $decimals = 2)
    {
        // 1. Get the number formatted correctly. format_number() will handle the comma default.
        $formatted_number = format_number($amount, $decimals);

        // 2. Get the currency symbol from settings. Default to '€'.
        $symbol = settings('app_currency_symbol', '€');

        // 3. Get the currency position from settings. Default to 'after' as requested.
        $position = settings('app_currency_position', 'after');

        // 4. Return the final string based on the position.
        if ($position === 'after') {
            // Example: 1.234,56 €
            return $formatted_number . ' ' . $symbol;
        }

        // Default 'before' position
        // Example: € 1.234,56
        return $symbol . ' ' . $formatted_number;
    }
}
