<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Src\Paciente\Infrastructure\Models\PacienteEloquentModel;

/**
 * @extends Factory<PacienteEloquentModel>
 */
class PacienteFactory extends Factory
{
    protected $model = PacienteEloquentModel::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nombres' => fake()->firstName(),
            'apellidos' => fake()->lastName(),
            'tipo_documento' => 'DNI',
            'numero_documento' => fake()->unique()->numerify('########'),
            'fecha_nacimiento' => fake()->dateTimeBetween('-70 years', '-8 years')->format('Y-m-d'),
            'sexo' => fake()->randomElement(['Masculino', 'Femenino']),
            'telefono' => fake()->numerify('9########'),
            'email' => fake()->optional()->safeEmail(),
            'direccion' => fake()->address(),
            'activo' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (): array => ['activo' => false]);
    }
}
