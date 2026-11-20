<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Src\Auth\Infrastructure\Models\UserEloquentModel;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_vendedor_can_sell_but_cannot_manage_health_or_admin_areas(): void
    {
        $vendedor = UserEloquentModel::factory()->vendedor()->create();

        $this->actingAs($vendedor)->get(route('ventas.index'))->assertOk();
        $this->actingAs($vendedor)->get(route('ventas.create'))->assertOk();
        $this->actingAs($vendedor)->get(route('pacientes.index'))->assertOk();
        $this->actingAs($vendedor)->get(route('marcos.index'))->assertOk();

        $this->actingAs($vendedor)->get(route('recetas.index'))->assertForbidden();
        $this->actingAs($vendedor)->get(route('usuarios.index'))->assertForbidden();
        $this->actingAs($vendedor)->get(route('reportes.index'))->assertForbidden();
        $this->actingAs($vendedor)->get(route('marcos.create'))->assertForbidden();
        $this->actingAs($vendedor)->get(route('lunas.create'))->assertForbidden();
        $this->actingAs($vendedor)->get(route('protecciones.create'))->assertForbidden();
    }

    public function test_optometrista_manages_patients_and_prescriptions_only(): void
    {
        $optometrista = UserEloquentModel::factory()->optometrista()->create();

        $this->actingAs($optometrista)->get(route('pacientes.index'))->assertOk();
        $this->actingAs($optometrista)->get(route('pacientes.create'))->assertOk();
        $this->actingAs($optometrista)->get(route('recetas.index'))->assertOk();
        $this->actingAs($optometrista)->get(route('recetas.create'))->assertOk();
        $this->actingAs($optometrista)->get(route('marcos.index'))->assertOk();

        $this->actingAs($optometrista)->get(route('ventas.index'))->assertForbidden();
        $this->actingAs($optometrista)->get(route('ventas.create'))->assertForbidden();
        $this->actingAs($optometrista)->get(route('usuarios.index'))->assertForbidden();
        $this->actingAs($optometrista)->get(route('reportes.index'))->assertForbidden();
        $this->actingAs($optometrista)->get(route('marcos.create'))->assertForbidden();
    }

    public function test_admin_can_access_every_module(): void
    {
        $dueno = UserEloquentModel::factory()->dueno()->create();

        $this->actingAs($dueno)->get(route('dashboard'))->assertOk();
        $this->actingAs($dueno)->get(route('ventas.index'))->assertOk();
        $this->actingAs($dueno)->get(route('recetas.index'))->assertOk();
        $this->actingAs($dueno)->get(route('pacientes.index'))->assertOk();
        $this->actingAs($dueno)->get(route('marcos.index'))->assertOk();
        $this->actingAs($dueno)->get(route('lunas.index'))->assertOk();
        $this->actingAs($dueno)->get(route('protecciones.index'))->assertOk();
        $this->actingAs($dueno)->get(route('usuarios.index'))->assertOk();
        $this->actingAs($dueno)->get(route('reportes.index'))->assertOk();
    }
}
