<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class EnsureStaffIsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated as staff
        if (Auth::guard('staff')->check()) {
            $staff = Auth::guard('staff')->user();

            // Check if staff role is 'admin'
            if ($staff->role === 'admin') {
                return $next($request);
            }
        }

        // If not admin, redirect with error
        return redirect()->route('staff.dashboard')
            ->with('error', '⛔ Access Denied: Only administrators can access this page.');
    }
}
