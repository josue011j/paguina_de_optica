<?php

namespace Src\Marco\Infrastructure\Models;

use Database\Factories\MarcoFactory;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Src\Venta\Infrastructure\Models\VentaEloquentModel;

#[UseFactory(MarcoFactory::class)]
class MarcoEloquentModel extends Model
{
    /** @use HasFactory<MarcoFactory> */
    use HasFactory;

    protected $table = 'marcos';

    protected $fillable = [
        'codigo',
        'marca',
        'modelo',
        'color',
        'material',
        'forma',
        'talla',
        'precio',
        'stock',
        'stock_minimo',
        'descripcion',
        'foto',
        'activo',
    ];

    protected $appends = [
        'nombre_completo',
        'stock_bajo',
        'foto_url',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'precio' => 'decimal:2',
            'stock' => 'integer',
            'stock_minimo' => 'integer',
            'activo' => 'boolean',
        ];
    }

    public function getNombreCompletoAttribute(): string
    {
        return trim("{$this->marca} {$this->modelo}");
    }

    public function getStockBajoAttribute(): bool
    {
        return $this->stock <= $this->stock_minimo;
    }

    public function getFotoUrlAttribute(): ?string
    {
        return $this->foto ? Storage::disk('public')->url($this->foto) : null;
    }

    public function scopeActivos(Builder $query): Builder
    {
        return $query->where('activo', true);
    }

    public function scopeConStock(Builder $query): Builder
    {
        return $query->where('stock', '>', 0);
    }

    public function ventas(): HasMany
    {
        return $this->hasMany(VentaEloquentModel::class, 'marco_id');
    }
}
