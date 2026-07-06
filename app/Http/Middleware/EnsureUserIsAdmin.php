<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        // Check if the user is authenticated and their role matches 'admin'
        // Note: Change 'admin' if your database role string uses capital letters (e.g., 'Admin')
        if (!auth()->check() || auth()->user()->role !== 'Admin') {
            abort(403, 'Unauthorized action. Admin access required.');
        }

        return $next($request);
    }
}