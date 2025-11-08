<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserAuthController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\PoliController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DokterController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\PenanggungJawabController;
use App\Http\Controllers\JadwalDokterController;

Route::post('/register', [UserAuthController::class, 'register']);
Route::post('/login-user', [UserAuthController::class, 'login']);
Route::post('/logout-user', [UserAuthController::class, 'logout'])->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->group(function () {

    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::post('/profile', [ProfileController::class, 'store'])->name('profile.store');
    Route::post('/reservations', [ReservationController::class, 'store']);
    Route::get('/my-reservations', function (Request $request) {
        return $request->user()->reservations()->with('poli', 'jadwalDokter.dokter')->latest()->get();
    });
    Route::get('/reservations/{reservation}', [ReservationController::class, 'show']);
    Route::post('/reservations/{reservation}/cancel', [ReservationController::class, 'cancel']);
    Route::post('/penanggung-jawab', [PenanggungJawabController::class, 'store']);

    Route::post('/chat/send', [ChatController::class, 'sendMessage']);
    Route::get('/chat/contacts', [ChatController::class, 'getContacts']);
    Route::get('/chat/{receiverType}/{receiverId}', [ChatController::class, 'getConversation']);
});

//auth
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

//Akses khusus superadmin
Route::middleware(['auth:sanctum', 'role:superadmin'])->group(function () {

    Route::get('/admins', [AdminController::class, 'index']);      
    Route::post('/admins', [AdminController::class, 'store']);    
    Route::delete('/admins/{id}', [AdminController::class, 'destroy']); 
    
    Route::post('/polis', [PoliController::class, 'store']);
    Route::put('/polis/{poli_id}', [PoliController::class, 'update']);
    Route::delete('/polis/{poli_id}', [PoliController::class, 'destroy']);    

    Route::apiResource('dokters', DokterController::class);

    Route::post('/jadwal-dokter', [JadwalDokterController::class, 'store']);
    Route::delete('/jadwal-dokter/{dokter_id}/{poli_id}', [JadwalDokterController::class, 'destroy']);
    
});

Route::post('/jadwal-dokter', [JadwalDokterController::class, 'store']);

//Akses khusus admin 
Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::get('/reservasi', [ReservationController::class, 'index']);
});

//Akses admin dan superadmin
Route::middleware(['auth:sanctum', 'role:superadmin,admin'])->group(function () {

    Route::get('/user', [UserAuthController::class, 'index']);

    Route::get('/admins/{id}', [AdminController::class, 'show']); 
    Route::put('/admins/{id}', [AdminController::class, 'update']); 
    Route::get('/polis', [PoliController::class, 'index']);
    Route::get('/penanggung-jawabs', [PenanggungJawabController::class, 'index']);
    Route::get('/penanggung-jawabs/{penanggungJawab}', [PenanggungJawabController::class, 'show']);
    Route::put('/penanggung-jawabs/{penanggungJawab}', [PenanggungJawabController::class, 'update']);
    Route::delete('/penanggung-jawabs/{penanggungJawab}', [PenanggungJawabController::class, 'destroy']);
    Route::get('/reservasi/{reservation}', [ReservationController::class, 'show']);
    Route::post('/reservasi/{reservation}/verify', [ReservationController::class, 'verify']);
    Route::post('/reservasi/{reservation}/cancel', [ReservationController::class, 'cancel']);
    Route::post('/admin/chat/send', [ChatController::class, 'sendMessage']);
    Route::get('/admin/chat/contacts', [ChatController::class, 'getContacts']);
    Route::get('/admin/chat/{receiverType}/{receiverId}', [ChatController::class, 'getConversation']);
    Route::get('/polis/{poli_id}', [PoliController::class, 'show']);

    Route::get('/jadwal-dokter', [JadwalDokterController::class, 'index']);
    Route::get('/jadwal-dokter/{dokter_id}/{poli_id}', [JadwalDokterController::class, 'show']);
    Route::put('/jadwal-dokter/{dokter_id}/{poli_id}', [JadwalDokterController::class, 'update']);
});
