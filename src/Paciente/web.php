<?php

use Illuminate\Support\Facades\Route;
use Src\Paciente\Application\Controllers\PacienteController;

Route::middleware('auth')->group(function (): void {
    Route::get('/pacientes', [PacienteController::class, 'index'])->name('pacientes.index');
    Route::get('/pacientes/crear', [PacienteController::class, 'create'])->name('pacientes.create');
    Route::post('/pacientes', [PacienteController::class, 'store'])->name('pacientes.store');

    Route::get('/pacientes/{paciente}/ultima-receta', [PacienteController::class, 'ultimaReceta'])
        ->name('pacientes.ultima-receta');

    Route::get('/pacientes/{paciente}', [PacienteController::class, 'show'])->name('pacientes.show');

    Route::middleware('role:admin,optometrista')->group(function (): void {
        Route::get('/pacientes/{paciente}/editar', [PacienteController::class, 'edit'])->name('pacientes.edit');
        Route::put('/pacientes/{paciente}', [PacienteController::class, 'update'])->name('pacientes.update');
    });

    Route::delete('/pacientes/{paciente}', [PacienteController::class, 'destroy'])
        ->middleware('role:admin')
        ->name('pacientes.destroy');
});
