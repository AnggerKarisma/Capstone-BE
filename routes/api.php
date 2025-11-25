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
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\PenanggungJawabController;
use App\Http\Controllers\JadwalDokterController;
use App\Http\Controllers\AntrianController;


// Auth Pasien
Route::post('/register', [UserAuthController::class, 'register']);
// Route::post('/login-user', [UserAuthController::class, 'login']);
Route::post('/otp/request', [UserAuthController::class, 'requestOtp']);
Route::post('/otp/login', [UserAuthController::class, 'loginWithOtp']);

// Auth Admin
Route::post('/login', [AuthController::class, 'login']); // Harusnya /admin/login tapi biarkan saja



// 'auth:sanctum' akan otomatis menggunakan guard 'api' (pasien)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout-user', [UserAuthController::class, 'logout']);
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::post('/profile', [ProfileController::class, 'store']);
    Route::post('/reservations', [ReservationController::class, 'store']);
    Route::get('/my-reservations', function (Request $request) {
        return $request->user()->reservations()->with('poli', 'jadwalDokter')->latest()->get(); 
    });
    Route::get('/reservations/{reservation}', [ReservationController::class, 'show']);
    Route::post('/reservations/{reservation}/cancel', [ReservationController::class, 'cancel']);
    Route::post('/penanggung-jawab', [PenanggungJawabController::class, 'store']);

    // Chat (Pasien)
    Route::post('/chat/send', [ChatController::class, 'sendMessage']);
    Route::get('/chat/contacts', [ChatController::class, 'getContacts']);
    Route::get('/chat/{receiverType}/{receiverId}', [ChatController::class, 'getConversation']);

    // Antrian (Pasien)
    Route::get('/antrian/dashboard', [AntrianController::class, 'getAntrianDashboard']);
});


Route::post('/jadwal-dokter', [JadwalDokterController::class, 'store']);

//Akses khusus admin 
Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    // Route::get('/reservasi', [ReservasiController::class, 'index']);
});

//Akses admin dan superadmin
Route::middleware(['auth:sanctum', 'role:superadmin,admin'])->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']); // Logout Admin

    Route::get('/user', [UserAuthController::class, 'index']); // Daftar semua pasien

    Route::get('/admins/{id}', [AdminController::class, 'show']); 
    Route::put('/admins/{id}', [AdminController::class, 'update']); 
    Route::get('/polis', [PoliController::class, 'index']);
    Route::get('/polis/{poli_id}', [PoliController::class, 'show']);
    
    // Manajemen PJ
    Route::get('/penanggung-jawabs', [PenanggungJawabController::class, 'index']);
    Route::get('/penanggung-jawabs/{penanggungJawab}', [PenanggungJawabController::class, 'show']);
    Route::put('/penanggung-jawabs/{penanggungJawab}', [PenanggungJawabController::class, 'update']);
    Route::delete('/penanggung-jawabs/{penanggungJawab}', [PenanggungJawabController::class, 'destroy']);
    
    // Manajemen Reservasi
    Route::get('/reservasi', [ReservationController::class, 'index']); // Typo sudah diperbaiki
    Route::get('/reservasi/{reservation}', [ReservationController::class, 'show']);
    Route::post('/reservasi/{reservation}/verify', [ReservationController::class, 'verify']);
    Route::post('/reservasi/{reservation}/cancel', [ReservationController::class, 'cancel']);
    
    // Chat (Admin)
    Route::post('/admin/chat/send', [ChatController::class, 'sendMessage']);
    Route::get('/admin/chat/contacts', [ChatController::class, 'getContacts']);
    Route::get('/admin/chat/{receiverType}/{receiverId}', [ChatController::class, 'getConversation']);
    
    // Jadwal Dokter (Rute duplikat dihapus)
    Route::get('/jadwal-dokter', [JadwalDokterController::class, 'index']);
    Route::get('/jadwal-dokter/{dokter_id}/{poli_id}', [JadwalDokterController::class, 'show']);
    Route::put('/jadwal-dokter/{dokter_id}/{poli_id}', [JadwalDokterController::class, 'update']);

    // Antrian (Admin)
    Route::post('/antrian/panggil-berikutnya', [AntrianController::class, 'panggilBerikutnya']);
});

//Akses khusus superadmin (Perlu token 'admin-api')
Route::middleware(['auth:sanctum', 'role:superadmin'])->group(function () {
    Route::delete('/admins/{id}', [AdminController::class, 'destroy']); 
    
    Route::post('/polis', [PoliController::class, 'store']);
    Route::put('/polis/{poli_id}', [PoliController::class, 'update']);
    Route::delete('/polis/{poli_id}', [PoliController::class, 'destroy']);

    Route::apiResource('dokters', DokterController::class);

    Route::post('/jadwal-dokter', [JadwalDokterController::class, 'store']);
    Route::delete('/jadwal-dokter/{dokter_id}/{poli_id}', [JadwalDokterController::class, 'destroy']);
    
    Route::get('/admins', [AdminController::class, 'index']);      
    Route::post('/admins', [AdminController::class, 'store']);    
    Route::delete('/admins/{id}', [AdminController::class, 'destroy']); 

    Route::get('/users', [UserController::class, 'index']);        
    Route::get('/users/{id}', [UserController::class, 'show']);    
    Route::put('/users/{id}', [UserController::class, 'update']);  
    Route::delete('/users/{id}', [UserController::class, 'destroy']);
    
    Route::post('/polis', [PoliController::class, 'store']);
    Route::put('/polis/{poli_id}', [PoliController::class, 'update']);
    Route::delete('/polis/{poli_id}', [PoliController::class, 'destroy']);    

    Route::apiResource('dokters', DokterController::class);

    
});