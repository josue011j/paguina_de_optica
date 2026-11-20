<?php

namespace Src\Proteccion\Infrastructure\Models;

use Database\Factories\ProteccionFactory;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Src\Venta\Infrastructure\Models\VentaEloquentModel;

#[UseFactory(ProteccionFactory::class)]
class ProteccionEloquentModel extends Model
{
    /** @use HasFactory<ProteccionFactory> */
    use HasFactory;

    protected $table = 'protecciones';

    protected $fillable = [
        'nombre',
        'descripcion',
        'precio',
        'activo',
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

    public function scopeActivas(Builder $query): Builder
    {
        return $query->where('activo', true);
    }

    public function ventas(): BelongsToMany
    {
        return $this->belongsToMany(
            VentaEloquentModel::class,
            'venta_proteccion',
            'proteccion_id',
            'venta_id',
        )->withPivot('precio')->withTimestamps();
    }
}
