<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Src\Auth\Infrastructure\Models\UserEloquentModel;
use Src\Paciente\Infrastructure\Models\PacienteEloquentModel;
use Src\Receta\Infrastructure\Models\RecetaEloquentModel;

/**
 * @extends Factory<RecetaEloquentModel>
 */
class RecetaFactory extends Factory
{
    protected $model = RecetaEloquentModel::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'paciente_id' => PacienteEloquentModel::factory(),
            'optometrista_id' => UserEloquentModel::factory()->optometrista(),
            'fecha' => now()->toDateString(),
            'motivo_consulta' => fake()->randomElement(['Control visual', 'Visión borrosa', 'Dolor de cabeza', 'Renovación de lentes']),
            'od_esfera' => fake()->randomFloat(2, -6, 2),
            'od_cilindro' => fake()->optional()->randomFloat(2, -3, 0),
            'od_eje' => fake()->numberBetween(0, 180),
            'od_adicion' => null,
            'od_av' => '20/20',
            'oi_esfera' => fake()->randomFloat(2, -6, 2),
            'oi_cilindro' => fake()->optional()->randomFloat(2, -3, 0),
            'oi_eje' => fake()->numberBetween(0, 180),
            'oi_adicion' => null,
            'oi_av' => '20/20',
            'dip' => fake()->randomFloat(1, 55, 70),
            'observaciones' => null,
        ];
    }
}
