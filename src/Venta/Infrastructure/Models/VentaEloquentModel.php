<?php

namespace Src\Venta\Infrastructure\Models;

use Database\Factories\VentaFactory;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Src\Auth\Infrastructure\Models\UserEloquentModel;
use Src\Luna\Infrastructure\Models\LunaEloquentModel;
use Src\Marco\Infrastructure\Models\MarcoEloquentModel;
use Src\Paciente\Infrastructure\Models\PacienteEloquentModel;
use Src\Proteccion\Infrastructure\Models\ProteccionEloquentModel;
use Src\Receta\Infrastructure\Models\RecetaEloquentModel;
use Src\Venta\Domain\Enums\EstadoVenta;
use Src\Venta\Domain\Enums\MetodoPago;

#[UseFactory(VentaFactory::class)]
class VentaEloquentModel extends Model
{
    /** @use HasFactory<VentaFactory> */
    use HasFactory;

    protected $table = 'ventas';

    protected $fillable = [
        'codigo',
        'paciente_id',
        'vendedor_id',
        'receta_id',
        'marco_id',
        'luna_id',
        'cantidad',
        'subtotal',
        'descuento',
        'total',
        'metodo_pago',
        'estado',
        'fecha',
        'notas',
    ];

    protected $appends = [
        'estado_label',
        'estado_color',
        'metodo_pago_label',
        'fecha_label',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'fecha' => 'date',
            'cantidad' => 'integer',
            'subtotal' => 'decimal:2',
            'descuento' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::created(function (self $venta): void {
            if (blank($venta->codigo)) {
                $venta->forceFill([
                    'codigo' => 'V-'.str_pad((string) $venta->getKey(), 6, '0', STR_PAD_LEFT),
                ])->saveQuietly();
            }
        });
    }

    public function getEstadoLabelAttribute(): string
    {
        return (EstadoVenta::tryFrom((string) $this->estado) ?? EstadoVenta::Pendiente)->label();
    }

    public function getEstadoColorAttribute(): string
    {
        return (EstadoVenta::tryFrom((string) $this->estado) ?? EstadoVenta::Pendiente)->color();
    }

    public function getMetodoPagoLabelAttribute(): string
    {
        return (MetodoPago::tryFrom((string) $this->metodo_pago) ?? MetodoPago::Efectivo)->label();
    }

    public function getFechaLabelAttribute(): string
    {
        return $this->fecha?->format('d/m/Y') ?? '';
    }

    public function paciente(): BelongsTo
    {
        return $this->belongsTo(PacienteEloquentModel::class, 'paciente_id');
    }

    public function vendedor(): BelongsTo
    {
        return $this->belongsTo(UserEloquentModel::class, 'vendedor_id');
    }

    public function receta(): BelongsTo
    {
        return $this->belongsTo(RecetaEloquentModel::class, 'receta_id');
    }

    public function marco(): BelongsTo
    {
        return $this->belongsTo(MarcoEloquentModel::class, 'marco_id');
    }

    public function luna(): BelongsTo
    {
        return $this->belongsTo(LunaEloquentModel::class, 'luna_id');
    }

    public function protecciones(): BelongsToMany
    {
        return $this->belongsToMany(
            ProteccionEloquentModel::class,
            'venta_proteccion',
            'venta_id',
            'proteccion_id',
        )->withPivot('precio')->withTimestamps();
    }
}
