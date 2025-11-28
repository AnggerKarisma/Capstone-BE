<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Carbon\Carbon;
use App\Jobs\SendEmailOtpJob;

class UserAuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'nomor_telepon' => 'required|string|max:15|unique:users,nomor_telepon',
            'password' => ['required', 'confirmed', Password::min(6)],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }
        try{

            $otpCode = random_int(100000, 999999);
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'nomor_telepon' => $request->nomor_telepon,
                'password' => Hash::make($request->password),
                'otp_hash' => Hash::make($otpCode),
                'otp_expires_at' => Carbon::now()->addMinutes(15),
                'email_verified_at' => null,
            ]);
            dispatch(new SendEmailOtpJob($user->email, $otpCode));
            return response()->json([
                'success' => true,
                'message' => 'Registrasi berhasil. Silakan cek email Anda untuk kode OTP.'
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Registrasi gagal',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        if(!Auth)
        // Cari user
        $user = User::where('email', $request->email)->first();

        // Cek user dan password
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Email atau Password salah'
            ], 401);
        }

        // Buat token
        $token = $user->createToken('user-auth-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login berhasil',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user
        ]);
    }
    
    public function index()
    {
        $users = User::with('profile')->latest()->get(); 
        
        return response()->json([
            'success' => true,
            'data' => $users
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged Out']);
    }

    public function requestOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|exists:users,email',
        ],[ 
            'email.exists' => 'Email tidak terdaftar',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $user = User::where('email', $request->email)->first();

        $otpCode = random_int(100000, 999999);
        $expiresAt = Carbon::now()->addMinutes(15);

        $user->update([
            'otp_hash' => Hash::make($otpCode),
            'otp_expires_at' => $expiresAt,
        ]);

        SendEmailOtpJob::dispatch($user->email, $otpCode);

        return response()->json([
            'success' => true,
            'message' => 'OTP telah dikirim ke email Anda. (' . $user->email . '). Cek Inbox atau Spam.'
        ]);
    }

    public function loginWithOtp(Request $request){
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|exists:users,email',
            'otp_code' => 'required|numeric|digits:6',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user->otp_hash || $user->otp_expires_at < Carbon::now()) {
            return response()->json([
                'success' => false,
                'message' => 'Kode OTP tidak valid atau kadaluarsa.'
            ], 401);
        }

        if (!Hash::check($request->otp_code, $user->otp_hash)) {
            return response()->json([
                'success' => false,
                'message' => 'Kode OTP salah'
            ], 401);
        }
        $user->update([
            'otp_hash' => null,
            'otp_expires_at' => null,
        ]);

        $token = $user->createToken('user-auth-token-otp')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login OTP berhasil',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user
        ]);
    }
}