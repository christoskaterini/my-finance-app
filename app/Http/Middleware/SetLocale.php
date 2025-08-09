<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = null;

        if (Session::has('locale')) {
            $locale = Session::get('locale');
        } elseif (Auth::check() && Auth::user()->locale) {
            $locale = Auth::user()->locale;
        }

        if ($locale) {
            App::setLocale($locale);
            \Carbon\Carbon::setLocale($locale);
            Session::put('locale', $locale);
        }

        return $next($request);
    }
}
