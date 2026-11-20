<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Src\Marco\Infrastructure\Models\MarcoEloquentModel;

/**
 * @extends Factory<MarcoEloquentModel>
 */
class MarcoFactory extends Factory
{
    protected $model = MarcoEloquentModel::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'codigo' => 'M-'.fake()->unique()->numerify('#####'),
            'marca' => fake()->randomElement(['Ray-Ban', 'Oakley', 'Vogue', 'Carrera', 'Nike', 'Prada']),
            'modelo' => strtoupper(fake()->bothify('??-####')),
            'color' => fake()->randomElement(['Negro', 'Carey', 'Azul', 'Dorado', 'Plateado']),
            'material' => fake()->randomElement(['Acetato', 'Metal', 'Titanio', 'TR90']),
            'forma' => fake()->randomElement(['Rectangular', 'Redonda', 'Aviador', 'Cuadrada']),
            'talla' => fake()->randomElement(['Adulto', 'Niño']),
            'precio' => fake()->randomFloat(2, 90, 650),
            'stock' => fake()->numberBetween(0, 15),
            'stock_minimo' => 2,
            'activo' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (): array => ['activo' => false]);
    }

    public function lowStock(): static
    {
        return $this->state(fn (): array => ['stock' => 1, 'stock_minimo' => 2]);
    }
}
