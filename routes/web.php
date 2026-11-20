<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ReportesController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function (): void {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
});

Route::middleware(['auth', 'role:admin'])->group(function (): void {
    Route::get('/reportes', [ReportesController::class, 'index'])->name('reportes.index');
});
