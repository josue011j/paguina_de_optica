<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('venta_proteccion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('venta_id')->constrained('ventas')->cascadeOnDelete();
            $table->foreignId('proteccion_id')->constrained('protecciones')->restrictOnDelete();
            $table->decimal('precio', 10, 2);
            $table->timestamps();

            $table->unique(['venta_id', 'proteccion_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('venta_proteccion');
    }
};
