<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\PoliController;
use App\Http\Controllers\DokterController;
use App\Http\Controllers\JadwalDokterController;

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
    Route::get('/jadwal-dokter', [JadwalDokterController::class, 'index']);
    Route::get('/jadwal-dokter/{dokter_id}/{poli_id}', [JadwalDokterController::class, 'show']);
    Route::put('/jadwal-dokter/{dokter_id}/{poli_id}', [JadwalDokterController::class, 'update']);
    Route::delete('/jadwal-dokter/{dokter_id}/{poli_id}', [JadwalDokterController::class, 'destroy']);
    
});

Route::post('/jadwal-dokter', [JadwalDokterController::class, 'store']);

//Akses khusus admin 
Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    // Route::get('/reservasi', [ReservasiController::class, 'index']);
});

//Akses admin dan superadmin
Route::middleware(['auth:sanctum', 'role:superadmin,admin'])->group(function () {

    Route::get('/admins/{id}', [AdminController::class, 'show']); 
    Route::put('/admins/{id}', [AdminController::class, 'update']); 

    Route::get('/polis', [PoliController::class, 'index']);
    Route::get('/polis/{poli_id}', [PoliController::class, 'show']);

    Route::get('/jadwal-dokter', [JadwalDokterController::class, 'index']);
    Route::get('/jadwal-dokter/{dokter_id}/{poli_id}', [JadwalDokterController::class, 'show']);
    Route::put('/jadwal-dokter/{dokter_id}/{poli_id}', [JadwalDokterController::class, 'update']);
});
