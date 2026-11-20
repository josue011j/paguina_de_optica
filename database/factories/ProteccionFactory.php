<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Src\Proteccion\Infrastructure\Models\ProteccionEloquentModel;

/**
 * @extends Factory<ProteccionEloquentModel>
 */
class ProteccionFactory extends Factory
{
    protected $model = ProteccionEloquentModel::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nombre' => fake()->unique()->randomElement([
                'Antirreflejo',
                'Filtro luz azul',
                'Fotocromático',
                'Polarizado',
                'Endurecido',
            ]),
            'descripcion' => null,
            'precio' => fake()->randomFloat(2, 30, 180),
            'activo' => true,
        ];
    }
}
