<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, string $roles): Response
    {
        $user = $request->user();

        // Jika tidak login
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        // Split roles jika ada koma (misalnya "superadmin,admin")
        $allowedRoles = explode(',', $roles);

        // Jika role-nya tidak sesuai
        if (!in_array($user->role, $allowedRoles)) {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        return $next($request);
    }
}