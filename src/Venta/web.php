<?php

use Illuminate\Support\Facades\Route;
use Src\Venta\Application\Controllers\VentaController;

Route::middleware(['auth', 'role:admin,vendedor'])->group(function (): void {
    Route::get('/ventas', [VentaController::class, 'index'])->name('ventas.index');
    Route::get('/ventas/nueva', [VentaController::class, 'create'])->name('ventas.create');
    Route::post('/ventas', [VentaController::class, 'store'])->name('ventas.store');
    Route::get('/ventas/{venta}', [VentaController::class, 'show'])->name('ventas.show');
    Route::patch('/ventas/{venta}/estado', [VentaController::class, 'updateEstado'])->name('ventas.estado');

    Route::delete('/ventas/{venta}', [VentaController::class, 'destroy'])
        ->middleware('role:admin')
        ->name('ventas.destroy');
});
