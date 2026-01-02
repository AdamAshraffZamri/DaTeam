<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureStaffIsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is logged in as staff AND has 'admin' role
        if (Auth::guard('staff')->check() && Auth::guard('staff')->user()->role === 'admin') {
            return $next($request);
        }

        abort(403, 'Unauthorized access. Admins only.');
    }
}