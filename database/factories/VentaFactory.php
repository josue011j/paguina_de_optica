<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Src\Auth\Infrastructure\Models\UserEloquentModel;
use Src\Luna\Infrastructure\Models\LunaEloquentModel;
use Src\Marco\Infrastructure\Models\MarcoEloquentModel;
use Src\Paciente\Infrastructure\Models\PacienteEloquentModel;
use Src\Venta\Domain\Enums\EstadoVenta;
use Src\Venta\Domain\Enums\MetodoPago;
use Src\Venta\Infrastructure\Models\VentaEloquentModel;

/**
 * @extends Factory<VentaEloquentModel>
 */
class VentaFactory extends Factory
{
    protected $model = VentaEloquentModel::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $cantidad = fake()->numberBetween(1, 2);
        $precioMarco = fake()->randomFloat(2, 120, 500);
        $precioLuna = fake()->randomFloat(2, 80, 350);
        $subtotal = ($precioMarco + $precioLuna) * $cantidad;

        return [
            'codigo' => null,
            'paciente_id' => PacienteEloquentModel::factory(),
            'vendedor_id' => UserEloquentModel::factory()->vendedor(),
            'receta_id' => null,
            'marco_id' => MarcoEloquentModel::factory(),
            'luna_id' => LunaEloquentModel::factory(),
            'cantidad' => $cantidad,
            'subtotal' => $subtotal,
            'descuento' => 0,
            'total' => $subtotal,
            'metodo_pago' => fake()->randomElement(MetodoPago::values()),
            'estado' => EstadoVenta::Pendiente->value,
            'fecha' => now()->toDateString(),
            'notas' => null,
        ];
    }

    public function cancelada(): static
    {
        return $this->state(fn (): array => ['estado' => EstadoVenta::Cancelado->value]);
    }

    public function entregada(): static
    {
        return $this->state(fn (): array => ['estado' => EstadoVenta::Entregado->value]);
    }
}
