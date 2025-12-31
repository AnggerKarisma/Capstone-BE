<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    //Login admin superadmin
    public function login(Request $request)
    {
        // Accept both capitalized and lowercase field names for compatibility
        $email = $request->input('email') ?? $request->input('Email');
        $password = $request->input('password') ?? $request->input('Password');

        $request->merge([
            'email' => $email,
            'password' => $password,
        ]);

        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $admin = Admin::where('Email', $email)->first();

        if (! $admin || ! Hash::check($password, $admin->Password)) {
            return response()->json(['message' => 'Email atau Password salah'], 401);
        }

        $token = $admin->createToken($admin->role . '-token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'role' => $admin->role,
            'admin' => $admin
        ]);
    }

    //Logout / hapus token
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged Out']);
    }
}
