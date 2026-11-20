<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Src\Auth\Domain\Enums\Role;
use Src\Auth\Infrastructure\Models\UserEloquentModel;

/**
 * @extends Factory<UserEloquentModel>
 */
class UserFactory extends Factory
{
    protected $model = UserEloquentModel::class;

    protected static ?string $password;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'role' => Role::Vendedor->value,
            'activo' => true,
            'telefono' => fake()->numerify('9########'),
        ];
    }

    public function programador(): static
    {
        return $this->state(fn (): array => ['role' => Role::Programador->value]);
    }

    public function dueno(): static
    {
        return $this->state(fn (): array => ['role' => Role::Dueno->value]);
    }

    public function vendedor(): static
    {
        return $this->state(fn (): array => ['role' => Role::Vendedor->value]);
    }

    public function optometrista(): static
    {
        return $this->state(fn (): array => ['role' => Role::Optometrista->value]);
    }

    public function inactive(): static
    {
        return $this->state(fn (): array => ['activo' => false]);
    }

    public function unverified(): static
    {
        return $this->state(fn (): array => ['email_verified_at' => null]);
    }
}
