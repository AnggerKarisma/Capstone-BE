<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\PoliController;

Route::prefix('admins')->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('admins.index');
    Route::get('/{id}', [AdminController::class, 'show'])->name('admins.show');
    Route::post('/', [AdminController::class, 'store'])->name('admins.store');
    Route::put('/{id}', [AdminController::class, 'update'])->name('admins.update');
    Route::delete('/{id}', [AdminController::class, 'destroy'])->name('admins.destroy');
});

// Route::prefix('polis')->group(function () {
//     Route::get('/', [PoliController::class, 'index'])->name('polis.index');
//     Route::get('/{id}', [PoliController::class, 'show'])->name('polis.show');
//     Route::post('/', [PoliController::class, 'store'])->name('polis.store');
//     Route::put('/{id}', [PoliController::class, 'update'])->name('polis.update');
//     Route::delete('/{id}', [PoliController::class, 'destroy'])->name('polis.destroy');
// });