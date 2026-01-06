<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class CheckUnpaidFines
{
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Pastikan user dah login
        if (Auth::check()) {
            $user = Auth::user();

            // 2. Guna function yang kita buat kat Langkah 1 tadi
            if ($user->hasUnpaidFines()) {
                
                // 3. Redirect user dan bagi warning
                return redirect()->route('customer.penalties') // Atau route home
                    ->with('error', 'You have outstanding penalties. Please pay them before booking a vehicle.');
            }
        }

        return $next($request);
    }
}