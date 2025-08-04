<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\File;

class PreventAccessAfterInstallation
{
    public function handle(Request $request, Closure $next): Response
    {
        // Check if the installation lock file exists.
        if (File::exists(storage_path('installed.lock'))) {
            // If it exists, block access by redirecting to the login page.
            return redirect()->route('login');
        }

        return $next($request);
    }
}