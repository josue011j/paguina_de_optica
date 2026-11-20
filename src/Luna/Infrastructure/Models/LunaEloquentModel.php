<?php

namespace Src\Luna\Infrastructure\Models;

use Database\Factories\LunaFactory;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Src\Luna\Domain\Enums\TipoLuna;
use Src\Venta\Infrastructure\Models\VentaEloquentModel;

#[UseFactory(LunaFactory::class)]
class LunaEloquentModel extends Model
{
    /** @use HasFactory<LunaFactory> */
    use HasFactory;

    protected $table = 'lunas';

    protected $fillable = [
        'nombre',
        'tipo',
        'precio',
        'descripcion',
        'activo',
    ];

    protected $appends = [
        'tipo_label',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'precio' => 'decimal:2',
            'activo' => 'boolean',
        ];
    }

    public function getTipoLabelAttribute(): string
    {
        return TipoLuna::tryFrom((string) $this->tipo)?->label() ?? $this->tipo;
    }

    public function scopeActivas(Builder $query): Builder
    {
        return $query->where('activo', true);
    }

    public function ventas(): HasMany
    {
        return $this->hasMany(VentaEloquentModel::class, 'luna_id');
    }
}
