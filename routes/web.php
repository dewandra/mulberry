<?php

use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [App\Http\Controllers\AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [App\Http\Controllers\AuthController::class, 'login']);
});

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {
    // Logout
    Route::post('/logout', [App\Http\Controllers\AuthController::class, 'logout'])->name('logout');
    
    // Dashboard
    Route::get('/', [App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [App\Http\Controllers\DashboardController::class, 'index']);

    /*
    |--------------------------------------------------------------------------
    | Super Admin & Admin Routes
    |--------------------------------------------------------------------------
    */
    Route::middleware(['role:super_admin,admin'])->group(function () {
        // User Management
        Route::resource('users', App\Http\Controllers\DashboardController::class);
        
        // Client Management
        Route::resource('clients', App\Http\Controllers\DashboardController::class);
    });
});