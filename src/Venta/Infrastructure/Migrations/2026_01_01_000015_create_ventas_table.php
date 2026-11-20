<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ventas', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 20)->nullable()->unique();
            $table->foreignId('paciente_id')->constrained('pacientes')->restrictOnDelete();
            $table->foreignId('vendedor_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('receta_id')->nullable()->constrained('recetas')->nullOnDelete();
            $table->foreignId('marco_id')->nullable()->constrained('marcos')->nullOnDelete();
            $table->foreignId('luna_id')->constrained('lunas')->restrictOnDelete();
            $table->unsignedInteger('cantidad')->default(1);
            $table->decimal('subtotal', 10, 2);
            $table->decimal('descuento', 10, 2)->default(0);
            $table->decimal('total', 10, 2);
            $table->string('metodo_pago', 30);
            $table->string('estado', 20)->default('pendiente');
            $table->date('fecha');
            $table->string('notas', 500)->nullable();
            $table->timestamps();

            $table->index(['fecha', 'estado']);
            $table->index('vendedor_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ventas');
    }
};
