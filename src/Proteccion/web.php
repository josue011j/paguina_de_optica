<?php

use Illuminate\Support\Facades\Route;
use Src\Proteccion\Application\Controllers\ProteccionController;

Route::middleware('auth')->group(function (): void {
    Route::get('/protecciones', [ProteccionController::class, 'index'])->name('protecciones.index');

    Route::middleware('role:admin')->group(function (): void {
        Route::get('/protecciones/crear', [ProteccionController::class, 'create'])->name('protecciones.create');
        Route::post('/protecciones', [ProteccionController::class, 'store'])->name('protecciones.store');
        Route::get('/protecciones/{proteccion}/editar', [ProteccionController::class, 'edit'])->name('protecciones.edit');
        Route::put('/protecciones/{proteccion}', [ProteccionController::class, 'update'])->name('protecciones.update');
        Route::delete('/protecciones/{proteccion}', [ProteccionController::class, 'destroy'])->name('protecciones.destroy');
    });
});
