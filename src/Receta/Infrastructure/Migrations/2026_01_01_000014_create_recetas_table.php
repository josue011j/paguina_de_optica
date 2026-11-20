<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recetas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paciente_id')->constrained('pacientes')->cascadeOnDelete();
            $table->foreignId('optometrista_id')->constrained('users')->restrictOnDelete();
            $table->date('fecha');
            $table->string('motivo_consulta', 150)->nullable();

            $table->decimal('od_esfera', 5, 2)->nullable();
            $table->decimal('od_cilindro', 5, 2)->nullable();
            $table->unsignedSmallInteger('od_eje')->nullable();
            $table->decimal('od_adicion', 4, 2)->nullable();
            $table->string('od_av', 15)->nullable();

            $table->decimal('oi_esfera', 5, 2)->nullable();
            $table->decimal('oi_cilindro', 5, 2)->nullable();
            $table->unsignedSmallInteger('oi_eje')->nullable();
            $table->decimal('oi_adicion', 4, 2)->nullable();
            $table->string('oi_av', 15)->nullable();

            $table->decimal('dip', 4, 1)->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamps();

            $table->index(['paciente_id', 'fecha']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recetas');
    }
};
