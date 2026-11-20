<?php

use Illuminate\Support\Facades\Route;
use Src\Marco\Application\Controllers\MarcoController;

Route::middleware('auth')->group(function (): void {
    Route::get('/marcos', [MarcoController::class, 'index'])->name('marcos.index');

    Route::middleware('role:admin')->group(function (): void {
        Route::get('/marcos/crear', [MarcoController::class, 'create'])->name('marcos.create');
        Route::post('/marcos', [MarcoController::class, 'store'])->name('marcos.store');
        Route::get('/marcos/{marco}/editar', [MarcoController::class, 'edit'])->name('marcos.edit');
        Route::put('/marcos/{marco}', [MarcoController::class, 'update'])->name('marcos.update');
        Route::delete('/marcos/{marco}', [MarcoController::class, 'destroy'])->name('marcos.destroy');
    });
});
