<?php

namespace Src\Auth\Infrastructure\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Src\Auth\Domain\Enums\Role;
use Src\Receta\Infrastructure\Models\RecetaEloquentModel;
use Src\Venta\Infrastructure\Models\VentaEloquentModel;

#[UseFactory(UserFactory::class)]
class UserEloquentModel extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $table = 'users';

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'activo',
        'telefono',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'activo' => 'boolean',
        ];
    }

    public function role(): Role
    {
        return Role::tryFrom((string) $this->role) ?? Role::Vendedor;
    }

    public function isAdmin(): bool
    {
        return $this->role()->isAdmin();
    }

    public function canDo(string $permission): bool
    {
        return $this->role()->permissions()[$permission] ?? false;
    }

    public function recetas(): HasMany
    {
        return $this->hasMany(RecetaEloquentModel::class, 'optometrista_id');
    }

    public function ventas(): HasMany
    {
        return $this->hasMany(VentaEloquentModel::class, 'vendedor_id');
    }
}
