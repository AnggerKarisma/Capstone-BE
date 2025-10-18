<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\PoliController;

//auth
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

//Akses khusus superadmin
Route::middleware(['auth:sanctum', 'role:superadmin'])->group(function () {
    
    // CRUD Admin
    Route::get('/admins', [AdminController::class, 'index']);      
    Route::post('/admins', [AdminController::class, 'store']);    
    Route::delete('/admins/{id}', [AdminController::class, 'destroy']); 
    
    Route::post('/polis', [PoliController::class, 'store']);
    Route::put('/polis/{id}', [PoliController::class, 'update']);
    Route::delete('/polis/{id}', [PoliController::class, 'destroy']);    
    // CRUD Poli
    // Route::apiResource('polis', PoliController::class);
});

//Akses khusus admin 
Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    // Route::get('/reservasi', [ReservasiController::class, 'index']);
});

//Akses admin dan superadmin
Route::middleware(['auth:sanctum', 'role:superadmin,admin'])->group(function () {

    Route::get('/admins/{id}', [AdminController::class, 'show']); 
    Route::put('/admins/{id}', [AdminController::class, 'update']); 

    // CRUD Poli yang bisa diakses semua
    Route::get('/polis', [PoliController::class, 'index']);
    Route::get('/polis/{id}', [PoliController::class, 'show']);
});
