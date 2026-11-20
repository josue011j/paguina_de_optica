<?php

namespace Src\Venta\Application\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Src\Auth\Infrastructure\Models\UserEloquentModel;
use Src\Luna\Infrastructure\Models\LunaEloquentModel;
use Src\Marco\Infrastructure\Models\MarcoEloquentModel;
use Src\Proteccion\Infrastructure\Models\ProteccionEloquentModel;
use Src\Receta\Infrastructure\Models\RecetaEloquentModel;
use Src\Venta\Domain\Enums\EstadoVenta;
use Src\Venta\Infrastructure\Models\VentaEloquentModel;

class VentaService
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function registrar(array $data, UserEloquentModel $vendedor): VentaEloquentModel
    {
        return DB::transaction(function () use ($data, $vendedor): VentaEloquentModel {
            $cantidad = (int) $data['cantidad'];

            $marco = null;

            if (! empty($data['marco_id'])) {
                $marco = MarcoEloquentModel::query()
                    ->lockForUpdate()
                    ->findOrFail($data['marco_id']);

                if (! $marco->activo) {
                    throw ValidationException::withMessages([
                        'marco_id' => 'El marco seleccionado ya no está disponible.',
                    ]);
                }

                if ($marco->stock < $cantidad) {
                    throw ValidationException::withMessages([
                        'marco_id' => "Solo quedan {$marco->stock} unidades de este marco.",
                    ]);
                }
            }

            $luna = LunaEloquentModel::query()->findOrFail($data['luna_id']);

            if (! $luna->activo) {
                throw ValidationException::withMessages([
                    'luna_id' => 'El tipo de luna seleccionado ya no está disponible.',
                ]);
            }

            $proteccionIds = $data['protecciones'] ?? [];
            $protecciones = ProteccionEloquentModel::query()
                ->whereIn('id', $proteccionIds)
                ->where('activo', true)
                ->get();

            if ($protecciones->count() !== count($proteccionIds)) {
                throw ValidationException::withMessages([
                    'protecciones' => 'Una de las protecciones seleccionadas ya no está disponible.',
                ]);
            }

            if (! empty($data['receta_id'])) {
                $receta = RecetaEloquentModel::query()->findOrFail($data['receta_id']);

                if ($receta->paciente_id !== (int) $data['paciente_id']) {
                    throw ValidationException::withMessages([
                        'receta_id' => 'La receta seleccionada no pertenece al paciente.',
                    ]);
                }
            }

            $precioUnitario = (float) ($marco?->precio ?? 0)
                + (float) $luna->precio
                + (float) $protecciones->sum('precio');

            $subtotal = round($precioUnitario * $cantidad, 2);
            $descuento = round((float) ($data['descuento'] ?? 0), 2);

            if ($descuento > $subtotal) {
                throw ValidationException::withMessages([
                    'descuento' => 'El descuento no puede ser mayor que el subtotal.',
                ]);
            }

            $venta = VentaEloquentModel::create([
                'codigo' => null,
                'paciente_id' => $data['paciente_id'],
                'vendedor_id' => $vendedor->id,
                'receta_id' => $data['receta_id'] ?? null,
                'marco_id' => $marco?->id,
                'luna_id' => $luna->id,
                'cantidad' => $cantidad,
                'subtotal' => $subtotal,
                'descuento' => $descuento,
                'total' => round($subtotal - $descuento, 2),
                'metodo_pago' => $data['metodo_pago'],
                'estado' => $data['estado'],
                'fecha' => $data['fecha'],
                'notas' => $data['notas'] ?? null,
            ]);

            if ($protecciones->isNotEmpty()) {
                $venta->protecciones()->attach(
                    $protecciones->mapWithKeys(
                        fn (ProteccionEloquentModel $proteccion): array => [
                            $proteccion->id => ['precio' => $proteccion->precio],
                        ],
                    )->all(),
                );
            }

            if ($marco) {
                $marco->decrement('stock', $cantidad);
            }

            return $venta->load(['paciente', 'vendedor', 'marco', 'luna', 'protecciones', 'receta']);
        });
    }

    public function cambiarEstado(VentaEloquentModel $venta, EstadoVenta $nuevoEstado): VentaEloquentModel
    {
        return DB::transaction(function () use ($venta, $nuevoEstado): VentaEloquentModel {
            $venta = VentaEloquentModel::query()->lockForUpdate()->findOrFail($venta->id);

            if ($venta->estado === $nuevoEstado->value) {
                return $venta;
            }

            $estabaCancelada = $venta->estado === EstadoVenta::Cancelado->value;
            $seraCancelada = $nuevoEstado->isCancelado();

            if ($seraCancelada && ! $estabaCancelada) {
                $this->restaurarStock($venta);
            }

            if (! $seraCancelada && $estabaCancelada) {
                $this->descontarStock($venta);
            }

            $venta->update(['estado' => $nuevoEstado->value]);

            return $venta;
        });
    }

    public function eliminar(VentaEloquentModel $venta): void
    {
        DB::transaction(function () use ($venta): void {
            $venta = VentaEloquentModel::query()->lockForUpdate()->findOrFail($venta->id);

            if ($venta->estado !== EstadoVenta::Cancelado->value) {
                $this->restaurarStock($venta);
            }

            $venta->delete();
        });
    }

    private function restaurarStock(VentaEloquentModel $venta): void
    {
        if (! $venta->marco_id) {
            return;
        }

        MarcoEloquentModel::query()
            ->whereKey($venta->marco_id)
            ->increment('stock', $venta->cantidad);
    }

    private function descontarStock(VentaEloquentModel $venta): void
    {
        if (! $venta->marco_id) {
            return;
        }

        $marco = MarcoEloquentModel::query()
            ->lockForUpdate()
            ->find($venta->marco_id);

        if (! $marco) {
            return;
        }

        if ($marco->stock < $venta->cantidad) {
            throw ValidationException::withMessages([
                'estado' => "No hay stock suficiente del marco para reactivar la venta (disponible: {$marco->stock}).",
            ]);
        }

        $marco->decrement('stock', $venta->cantidad);
    }
}
