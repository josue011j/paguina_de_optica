<?php

namespace Src\Paciente\Infrastructure\Models;

use Database\Factories\PacienteFactory;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Src\Receta\Infrastructure\Models\RecetaEloquentModel;
use Src\Venta\Infrastructure\Models\VentaEloquentModel;

#[UseFactory(PacienteFactory::class)]
class PacienteEloquentModel extends Model
{
    /** @use HasFactory<PacienteFactory> */
    use HasFactory, SoftDeletes;

    protected $table = 'pacientes';

    protected $fillable = [
        'nombres',
        'apellidos',
        'tipo_documento',
        'numero_documento',
        'fecha_nacimiento',
        'sexo',
        'telefono',
        'email',
        'direccion',
        'alergias',
        'observaciones',
        'activo',
    ];

    protected $appends = [
        'nombre_completo',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'fecha_nacimiento' => 'date',
            'activo' => 'boolean',
        ];
    }

    public function getNombreCompletoAttribute(): string
    {
        return trim("{$this->nombres} {$this->apellidos}");
    }

    public function recetas(): HasMany
    {
        return $this->hasMany(RecetaEloquentModel::class, 'paciente_id');
    }

    public function ventas(): HasMany
    {
        return $this->hasMany(VentaEloquentModel::class, 'paciente_id');
    }
}
