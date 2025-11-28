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

        if(!Auth::attempt($request->only('email', 'password'))){
            return response()->json([
                'success' => false,
                'message' => 'Email atau Password salah'
            ], 401);
        }
        // Cari user
        $user = User::where('email', $request->email)->first();

        // Cek user dan password
        if (is_null($user->email_verified_at)){
            return response()->json([
                'success' => false,
                'message' => 'Email belum terverifikasi. Silakan verifikasi email Anda terlebih dahulu.', 
                'needs_verification' => true
            ], 403);
        }

        // Buat token
        $token = $user->createToken('user-auth-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login berhasil',
            'data' => [
                'access_token' => $token,
                'token_type' => 'Bearer',
                'user' => $user
            ]
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

    public function verifyOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
            'otp' => 'required|digits:6',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $user = User::where('email', $request->email)->first();

        // 1. Cek Hash OTP
        if (!Hash::check($request->otp, $user->otp_hash)) {
            return response()->json([
                'success' => false, 
                'message' => 'Kode OTP salah.'
            ], 400);
        }

        // 2. Cek Kedaluwarsa
        if (Carbon::now()->greaterThan($user->otp_expires_at)) {
            return response()->json([
                'success' => false, 
                'message' => 'Kode OTP sudah kedaluwarsa. Silakan minta kirim ulang.'
            ], 400);
        }

        // 3. Verifikasi Berhasil -> Aktifkan User
        $user->email_verified_at = Carbon::now();
        $user->otp_hash = null;
        $user->otp_expires_at = null;
        $user->save();
         
            

        // Opsional: Langsung login setelah verifikasi
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Akun berhasil diverifikasi.',
            'data' => [
                'access_token' => $token, // User langsung dapat token
                'user' => $user
            ]
        ]);
    }

    public function resendOtp(Request $request)
    {
        $request->validate(['email' => 'required|email|exists:users,email']);
        
        $user = User::where('email', $request->email)->first();
        
        // Cek jika sudah verifikasi (Opsional)
        if ($user->email_verified_at) {
             return response()->json(['message' => 'Akun sudah aktif.'], 400);
        }

        $otp = rand(100000, 999999);
        $user->update([
            'otp_hash' => Hash::make($otp),
            'otp_expires_at' => Carbon::now()->addMinutes(10)
        ]);

        dispatch(new SendEmailOtpJob($user->email, $otp));

        return response()->json(['success' => true, 'message' => 'OTP baru telah dikirim.']);
    }
}