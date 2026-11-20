<?php

use Illuminate\Support\Facades\Route;
use Src\Luna\Application\Controllers\LunaController;

Route::middleware('auth')->group(function (): void {
    Route::get('/lunas', [LunaController::class, 'index'])->name('lunas.index');

    Route::middleware('role:admin')->group(function (): void {
        Route::get('/lunas/crear', [LunaController::class, 'create'])->name('lunas.create');
        Route::post('/lunas', [LunaController::class, 'store'])->name('lunas.store');
        Route::get('/lunas/{luna}/editar', [LunaController::class, 'edit'])->name('lunas.edit');
        Route::put('/lunas/{luna}', [LunaController::class, 'update'])->name('lunas.update');
        Route::delete('/lunas/{luna}', [LunaController::class, 'destroy'])->name('lunas.destroy');
    });
});
