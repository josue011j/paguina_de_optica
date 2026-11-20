<?php

use Illuminate\Support\Facades\Route;
use Src\Auth\Application\Controllers\WebAuthController;

Route::middleware('guest')->group(function (): void {
    Route::get('/', function () {
        return redirect()->route('login');
    })->name('home');

    Route::get('/login', [WebAuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [WebAuthController::class, 'login'])
        ->middleware('throttle:8,1')
        ->name('login.store');
});

Route::middleware('auth')->group(function (): void {
    Route::post('/logout', [WebAuthController::class, 'logout'])->name('logout');
});
