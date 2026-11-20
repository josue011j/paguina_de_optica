<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Src\Auth\Infrastructure\Models\UserEloquentModel;
use Src\Luna\Infrastructure\Models\LunaEloquentModel;
use Src\Marco\Infrastructure\Models\MarcoEloquentModel;
use Src\Paciente\Infrastructure\Models\PacienteEloquentModel;
use Src\Proteccion\Infrastructure\Models\ProteccionEloquentModel;
use Src\Venta\Domain\Enums\EstadoVenta;
use Src\Venta\Infrastructure\Models\VentaEloquentModel;
use Tests\TestCase;

class VentaFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_sale_is_registered_with_totals_and_stock_discount(): void
    {
        $vendedor = UserEloquentModel::factory()->vendedor()->create();
        $paciente = PacienteEloquentModel::factory()->create();
        $marco = MarcoEloquentModel::factory()->create(['precio' => 500, 'stock' => 5]);
        $luna = LunaEloquentModel::factory()->create(['precio' => 200]);
        $proteccion = ProteccionEloquentModel::factory()->create(['precio' => 80]);

        $response = $this->actingAs($vendedor)->post(route('ventas.store'), [
            'paciente_id' => $paciente->id,
            'marco_id' => $marco->id,
            'luna_id' => $luna->id,
            'protecciones' => [$proteccion->id],
            'cantidad' => 2,
            'descuento' => 50,
            'metodo_pago' => 'efectivo',
            'estado' => 'pendiente',
            'fecha' => now()->toDateString(),
        ]);

        $venta = VentaEloquentModel::query()->firstOrFail();

        $response->assertRedirect(route('ventas.show', $venta));

        $this->assertSame(1560.0, (float) $venta->subtotal);
        $this->assertSame(1510.0, (float) $venta->total);
        $this->assertSame($vendedor->id, $venta->vendedor_id);
        $this->assertSame('V-000001', $venta->codigo);
        $this->assertSame(3, $marco->fresh()->stock);
        $this->assertSame(80.0, (float) $venta->protecciones()->first()->pivot->precio);
    }

    public function test_sale_without_stock_is_rejected(): void
    {
        $vendedor = UserEloquentModel::factory()->vendedor()->create();
        $paciente = PacienteEloquentModel::factory()->create();
        $marco = MarcoEloquentModel::factory()->create(['stock' => 1]);
        $luna = LunaEloquentModel::factory()->create();

        $this->actingAs($vendedor)->post(route('ventas.store'), [
            'paciente_id' => $paciente->id,
            'marco_id' => $marco->id,
            'luna_id' => $luna->id,
            'cantidad' => 3,
            'metodo_pago' => 'efectivo',
            'estado' => 'pendiente',
            'fecha' => now()->toDateString(),
        ])->assertSessionHasErrors('marco_id');

        $this->assertDatabaseCount('ventas', 0);
        $this->assertSame(1, $marco->fresh()->stock);
    }

    public function test_cancelling_a_sale_restores_stock(): void
    {
        $vendedor = UserEloquentModel::factory()->vendedor()->create();
        $paciente = PacienteEloquentModel::factory()->create();
        $marco = MarcoEloquentModel::factory()->create(['precio' => 300, 'stock' => 4]);
        $luna = LunaEloquentModel::factory()->create(['precio' => 100]);

        $this->actingAs($vendedor)->post(route('ventas.store'), [
            'paciente_id' => $paciente->id,
            'marco_id' => $marco->id,
            'luna_id' => $luna->id,
            'cantidad' => 2,
            'metodo_pago' => 'tarjeta',
            'estado' => 'pendiente',
            'fecha' => now()->toDateString(),
        ]);

        $venta = VentaEloquentModel::query()->firstOrFail();
        $this->assertSame(2, $marco->fresh()->stock);

        $this->actingAs($vendedor)
            ->patch(route('ventas.estado', $venta), ['estado' => EstadoVenta::Cancelado->value])
            ->assertRedirect(route('ventas.show', $venta));

        $this->assertSame(4, $marco->fresh()->stock);
        $this->assertSame(EstadoVenta::Cancelado->value, $venta->fresh()->estado);
    }

    public function test_vendedor_cannot_change_another_sellers_sale(): void
    {
        $vendedor = UserEloquentModel::factory()->vendedor()->create();
        $otroVendedor = UserEloquentModel::factory()->vendedor()->create();
        $venta = VentaEloquentModel::factory()->create(['vendedor_id' => $otroVendedor->id]);

        $this->actingAs($vendedor)
            ->patch(route('ventas.estado', $venta), ['estado' => EstadoVenta::Entregado->value])
            ->assertForbidden();
    }

    public function test_admin_can_delete_a_sale_and_restore_stock(): void
    {
        $admin = UserEloquentModel::factory()->dueno()->create();
        $marco = MarcoEloquentModel::factory()->create(['stock' => 6]);
        $venta = VentaEloquentModel::factory()->create([
            'marco_id' => $marco->id,
            'cantidad' => 2,
            'estado' => EstadoVenta::Pendiente->value,
        ]);

        $this->actingAs($admin)
            ->delete(route('ventas.destroy', $venta))
            ->assertRedirect(route('ventas.index'));

        $this->assertDatabaseMissing('ventas', ['id' => $venta->id]);
        $this->assertSame(8, $marco->fresh()->stock);
    }
}
