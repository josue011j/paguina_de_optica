<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Src\Luna\Domain\Enums\TipoLuna;
use Src\Luna\Infrastructure\Models\LunaEloquentModel;

/**
 * @extends Factory<LunaEloquentModel>
 */
class LunaFactory extends Factory
{
    protected $model = LunaEloquentModel::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nombre' => fake()->randomElement(['Luna monofocal', 'Luna bifocal', 'Luna progresiva']),
            'tipo' => fake()->randomElement(TipoLuna::cases())->value,
            'precio' => fake()->randomFloat(2, 80, 450),
            'descripcion' => null,
            'activo' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (): array => ['activo' => false]);
    }
}
