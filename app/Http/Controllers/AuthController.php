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
        $request->validate([
            'Email' => 'required|email',
            'Password' => 'required'
        ]);

        $admin = Admin::where('Email', $request->Email)->first();

        if (! $admin || ! Hash::check($request->Password, $admin->Password)) {
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
