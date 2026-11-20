<?php

use Illuminate\Support\Facades\Route;
use Src\Receta\Application\Controllers\RecetaController;

Route::middleware(['auth', 'role:admin,optometrista'])->group(function (): void {
    Route::get('/recetas', [RecetaController::class, 'index'])->name('recetas.index');
    Route::get('/recetas/crear', [RecetaController::class, 'create'])->name('recetas.create');
    Route::post('/recetas', [RecetaController::class, 'store'])->name('recetas.store');
    Route::get('/recetas/{receta}', [RecetaController::class, 'show'])->name('recetas.show');
    Route::get('/recetas/{receta}/editar', [RecetaController::class, 'edit'])->name('recetas.edit');
    Route::put('/recetas/{receta}', [RecetaController::class, 'update'])->name('recetas.update');
    Route::delete('/recetas/{receta}', [RecetaController::class, 'destroy'])->name('recetas.destroy');
});
