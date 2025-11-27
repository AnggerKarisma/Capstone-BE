<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!Auth::check()) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $user = Auth::user();

        $allowedRoles = [];
        foreach ($roles as $role) {
            // Pecah string jika ada koma
            $parts = explode(',', $role);
            foreach ($parts as $part) {
                $allowedRoles[] = trim($part);
            }
        }

        if (!in_array($user->role, $allowedRoles)) {
            return response()->json([
                'message' => 'Forbidden. Role Anda (' . $user->role . ') tidak diizinkan.',
                'allowed_roles' => $allowedRoles 
            ], 403);
        }

        return $next($request);
    }
}