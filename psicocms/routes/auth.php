<?php

use App\Http\Controllers\Auth\LoginController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/acceso-psicologa', [LoginController::class, 'show'])->name('login');
    Route::post('/acceso-psicologa', [LoginController::class, 'store'])->name('login.store');
});

Route::post('/panel-psicologa/cerrar-sesion', [LoginController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');
