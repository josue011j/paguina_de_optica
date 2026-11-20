<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('marcos', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 40)->unique();
            $table->string('marca', 80);
            $table->string('modelo', 80);
            $table->string('color', 60)->nullable();
            $table->string('material', 60)->nullable();
            $table->string('forma', 60)->nullable();
            $table->string('talla', 40)->nullable();
            $table->decimal('precio', 10, 2);
            $table->unsignedInteger('stock')->default(0);
            $table->unsignedInteger('stock_minimo')->default(2);
            $table->string('descripcion', 255)->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->index(['marca', 'activo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marcos');
    }
};
