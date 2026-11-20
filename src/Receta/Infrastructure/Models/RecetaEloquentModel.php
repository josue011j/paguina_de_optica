<?php

namespace Src\Receta\Infrastructure\Models;

use Database\Factories\RecetaFactory;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Src\Auth\Infrastructure\Models\UserEloquentModel;
use Src\Paciente\Infrastructure\Models\PacienteEloquentModel;
use Src\Venta\Infrastructure\Models\VentaEloquentModel;

#[UseFactory(RecetaFactory::class)]
class RecetaEloquentModel extends Model
{
    /** @use HasFactory<RecetaFactory> */
    use HasFactory;

    protected $table = 'recetas';

    protected $fillable = [
        'paciente_id',
        'optometrista_id',
        'fecha',
        'motivo_consulta',
        'od_esfera',
        'od_cilindro',
        'od_eje',
        'od_adicion',
        'od_av',
        'oi_esfera',
        'oi_cilindro',
        'oi_eje',
        'oi_adicion',
        'oi_av',
        'dip',
        'observaciones',
    ];

    protected $appends = [
        'fecha_label',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'fecha' => 'date',
            'od_esfera' => 'decimal:2',
            'od_cilindro' => 'decimal:2',
            'od_adicion' => 'decimal:2',
            'oi_esfera' => 'decimal:2',
            'oi_cilindro' => 'decimal:2',
            'oi_adicion' => 'decimal:2',
            'od_eje' => 'integer',
            'oi_eje' => 'integer',
            'dip' => 'decimal:1',
        ];
    }

    public function getFechaLabelAttribute(): string
    {
        return $this->fecha?->format('d/m/Y') ?? '';
    }

    public function paciente(): BelongsTo
    {
        return $this->belongsTo(PacienteEloquentModel::class, 'paciente_id');
    }

    public function optometrista(): BelongsTo
    {
        return $this->belongsTo(UserEloquentModel::class, 'optometrista_id');
    }

    public function ventas(): HasMany
    {
        return $this->hasMany(VentaEloquentModel::class, 'receta_id');
    }
}
