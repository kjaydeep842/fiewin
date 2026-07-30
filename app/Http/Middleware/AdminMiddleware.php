<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // Check admin guard (dedicated admin session)
        if (auth()->guard('admin')->check()) {
            return $next($request);
        }

        // Redirect unauthenticated users to admin login page
        return redirect('/admin/login')->with('error', 'Please log in to access the Admin Panel.');
    }
}
