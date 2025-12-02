<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserAuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\PoliController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DokterController;
use App\Http\Controllers\JadwalDokterController;
use App\Http\Controllers\RekamMedisController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\PenanggungJawabController;
use App\Http\Controllers\AntrianController;
use App\Http\Controllers\FeedbackController;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/
Route::post('/register',        [UserAuthController::class, 'register']);
Route::post('/login-user',      [UserAuthController::class, 'login']);
Route::post('/otp/verify',      [UserAuthController::class, 'verifyOtp']);
Route::post('/otp/resend',      [UserAuthController::class, 'resendOtp']);

Route::get('/polis',            [PoliController::class, 'index']);
Route::get('/jadwal-dokter',    [JadwalDokterController::class, 'index']);
Route::get('/public/jadwal-dokter/{poli_id}', [JadwalDokterController::class, 'getByPoli']);

/*
|--------------------------------------------------------------------------
| Pasien (guard: sanctum default)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {
    // auth pasien
    Route::post('/logout-user', [UserAuthController::class, 'logout']);

    // profil
    Route::get('/profile',  [ProfileController::class, 'show']);
    Route::post('/profile', [ProfileController::class, 'store']);

    // reservasi
    Route::post('/reservations', [ReservationController::class, 'store']);
    Route::get('/my-reservations', function (Request $request) {
        return $request->user()
            ->reservations()
            ->with('poli', 'jadwalDokter')
            ->latest()
            ->get();
    });
    Route::get('/reservations/{reservation}',          [ReservationController::class, 'show']);
    Route::post('/reservations/{reservation}/cancel',  [ReservationController::class, 'cancel']);

    // penanggung jawab
    Route::post('/penanggung-jawab', [PenanggungJawabController::class, 'store']);

    // chat pasien
    Route::post('/chat/send',                     [ChatController::class, 'sendMessage']);
    Route::get('/chat/contacts',                  [ChatController::class, 'getContacts']);
    Route::get('/chat/{receiverType}/{receiverId}', [ChatController::class, 'getConversation']);

    // antrian dashboard untuk pasien (FE dashboard antrian)
    Route::get('/antrian/dashboard', [AntrianController::class, 'getAntrianDashboard']);

    // feedback pasien
    Route::post('/feedback', [FeedbackController::class, 'store']);
});

/*
|--------------------------------------------------------------------------
| Admin & Superadmin
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:sanctum', 'role:superadmin,admin'])->group(function () {

    Route::post('/login',  [AuthController::class, 'login']);   // login admin
    Route::post('/logout', [AuthController::class, 'logout']);  // logout admin

    // pasien
    Route::get('/user', [UserAuthController::class, 'index']);

    // admin
    Route::get('/admins/{id}', [AdminController::class, 'show']);
    Route::put('/admins/{id}', [AdminController::class, 'update']);

    // poli
    Route::get('/polis/{poli_id}', [PoliController::class, 'show']);

    // penanggung jawab
    Route::get('/penanggung-jawabs',                 [PenanggungJawabController::class, 'index']);
    Route::get('/penanggung-jawabs/{penanggungJawab}', [PenanggungJawabController::class, 'show']);
    Route::put('/penanggung-jawabs/{penanggungJawab}', [PenanggungJawabController::class, 'update']);
    Route::delete('/penanggung-jawabs/{penanggungJawab}', [PenanggungJawabController::class, 'destroy']);

    // reservasi
    Route::get('/reservations', [ReservationController::class, 'index']);
    Route::post('/reservations/{reservation}/verify', [ReservationController::class, 'verify']);
    Route::post('/reservations/{reservation}/cancel', [ReservationController::class, 'cancel']);

    // chat admin
    Route::post('/admin/chat/send',                     [ChatController::class, 'sendMessage']);
    Route::get('/admin/chat/contacts',                  [ChatController::class, 'getContacts']);
    Route::get('/admin/chat/{receiverType}/{receiverId}', [ChatController::class, 'getConversation']);

    // antrian (admin)
    Route::post('/antrian/panggil-berikutnya', [AntrianController::class, 'panggilBerikutnya']);
    Route::post('/antrian/selesaikan',         [AntrianController::class, 'selesaikanPanggilan']);

    // rekam medis
    Route::apiResource('rekam-medis', RekamMedisController::class);

    // feedback
    Route::get('/feedback', [FeedbackController::class, 'index']);
});

/*
|--------------------------------------------------------------------------
| Superadmin Only
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:sanctum', 'role:superadmin'])->group(function () {

    Route::delete('/admins/{id}', [AdminController::class, 'destroy']);

    // poli manage
    Route::post('/polis',              [PoliController::class, 'store']);
    Route::put('/polis/{poli_id}',     [PoliController::class, 'update']);
    Route::delete('/polis/{poli_id}',  [PoliController::class, 'destroy']);

    // dokter
    Route::apiResource('dokters', DokterController::class);

    // jadwal dokter
    Route::post('/jadwal-dokter',                      [JadwalDokterController::class, 'store']);
    Route::get('/jadwal-dokter/{dokter_id}/{poli_id}', [JadwalDokterController::class, 'show']);
    Route::put('/jadwal-dokter/{dokter_id/{poli_id}',  [JadwalDokterController::class, 'update']);
    Route::delete('/jadwal-dokter/{dokter_id}/{poli_id}', [JadwalDokterController::class, 'destroy']);

    // user management
    Route::get('/admins', [AdminController::class, 'index']);
    Route::post('/admins', [AdminController::class, 'store']);

    Route::get('/users',        [UserController::class, 'index']);
    Route::get('/users/{id}',   [UserController::class, 'show']);
    Route::put('/users/{id}',   [UserController::class, 'update']);
    Route::delete('/users/{id}',[UserController::class, 'destroy']);
});
