<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticated
{
    public function handle(Request $request, Closure $next, ...$guards)
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                // Kalau kamu ingin biarkan login ulang lewat API, cukup return JSON
                return response()->json([
                    'message' => 'Anda sudah login.'
                ], 403);
            }
        }

        return $next($request);
    }
}
