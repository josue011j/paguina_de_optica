<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Src\Auth\Domain\Enums\Role;
use Src\Marco\Infrastructure\Models\MarcoEloquentModel;
use Src\Paciente\Infrastructure\Models\PacienteEloquentModel;
use Src\Receta\Infrastructure\Models\RecetaEloquentModel;
use Src\Venta\Domain\Enums\EstadoVenta;
use Src\Venta\Infrastructure\Models\VentaEloquentModel;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        $role = $user->role();

        return match (true) {
            $role->isAdmin() => $this->adminDashboard(),
            $role === Role::Vendedor => $this->vendedorDashboard($user->id),
            default => $this->optometristaDashboard($user->id),
        };
    }

    private function adminDashboard(): Response
    {
        $hoy = now()->toDateString();
        $inicioMes = now()->startOfMonth();

        $stats = [
            $this->stat('Ingresos del mes', (float) VentaEloquentModel::query()
                ->where('estado', '!=', EstadoVenta::Cancelado->value)
                ->where('fecha', '>=', $inicioMes)
                ->sum('total'), 'wallet', 'teal', 'money'),
            $this->stat('Ventas de hoy', VentaEloquentModel::whereDate('fecha', $hoy)->count(), 'receipt', 'sky'),
            $this->stat('Pacientes registrados', PacienteEloquentModel::count(), 'users', 'violet'),
            $this->stat('Marcos con stock bajo', MarcoEloquentModel::whereColumn('stock', '<=', 'stock_minimo')->count(), 'package', 'amber'),
        ];

        $dias = $this->lastSevenDays();

        $values = $dias->map(fn (Carbon $dia): float => (float) VentaEloquentModel::query()
            ->where('estado', '!=', EstadoVenta::Cancelado->value)
            ->whereDate('fecha', $dia->toDateString())
            ->sum('total'))->all();

        $recientes = VentaEloquentModel::query()
            ->with('paciente')
            ->latest('fecha')
            ->latest('id')
            ->limit(6)
            ->get()
            ->map(fn (VentaEloquentModel $venta): array => $this->ventaResumen($venta));

        $topProtecciones = DB::table('venta_proteccion as vp')
            ->join('ventas as v', 'v.id', '=', 'vp.venta_id')
            ->join('protecciones as p', 'p.id', '=', 'vp.proteccion_id')
            ->where('v.estado', '!=', EstadoVenta::Cancelado->value)
            ->where('v.fecha', '>=', $inicioMes)
            ->select('p.nombre', DB::raw('COUNT(*) as total'))
            ->groupBy('p.id', 'p.nombre')
            ->orderByDesc('total')
            ->limit(5)
            ->get()
            ->map(fn ($row): array => ['nombre' => $row->nombre, 'total' => (int) $row->total]);

        return Inertia::render('Dashboard', [
            'role' => Role::Dueno->value,
            'stats' => $stats,
            'chart' => [
                'title' => 'Ingresos de los últimos 7 días',
                'labels' => $dias->map(fn (Carbon $dia): string => $dia->locale('es')->translatedFormat('D d'))->all(),
                'values' => $values,
                'format' => 'money',
            ],
            'recentSales' => $recientes,
            'recentRecetas' => [],
            'lowStock' => $this->lowStock(),
            'topProtecciones' => $topProtecciones,
        ]);
    }

    private function vendedorDashboard(int $userId): Response
    {
        $hoy = now()->toDateString();
        $inicioMes = now()->startOfMonth();

        $stats = [
            $this->stat('Mis ventas de hoy', VentaEloquentModel::where('vendedor_id', $userId)->whereDate('fecha', $hoy)->count(), 'receipt', 'sky'),
            $this->stat('Mis ingresos de hoy', (float) VentaEloquentModel::query()
                ->where('vendedor_id', $userId)
                ->where('estado', '!=', EstadoVenta::Cancelado->value)
                ->whereDate('fecha', $hoy)
                ->sum('total'), 'wallet', 'teal', 'money'),
            $this->stat('Pedidos pendientes', VentaEloquentModel::where('vendedor_id', $userId)
                ->whereIn('estado', [EstadoVenta::Pendiente->value, EstadoVenta::EnProceso->value])
                ->count(), 'clock', 'amber'),
            $this->stat('Entregadas este mes', VentaEloquentModel::where('vendedor_id', $userId)
                ->where('estado', EstadoVenta::Entregado->value)
                ->where('fecha', '>=', $inicioMes)
                ->count(), 'check', 'emerald'),
        ];

        $dias = $this->lastSevenDays();

        $values = $dias->map(fn (Carbon $dia): float => (float) VentaEloquentModel::query()
            ->where('vendedor_id', $userId)
            ->where('estado', '!=', EstadoVenta::Cancelado->value)
            ->whereDate('fecha', $dia->toDateString())
            ->sum('total'))->all();

        $recientes = VentaEloquentModel::query()
            ->with('paciente')
            ->where('vendedor_id', $userId)
            ->latest('fecha')
            ->latest('id')
            ->limit(6)
            ->get()
            ->map(fn (VentaEloquentModel $venta): array => $this->ventaResumen($venta));

        return Inertia::render('Dashboard', [
            'role' => Role::Vendedor->value,
            'stats' => $stats,
            'chart' => [
                'title' => 'Mis ventas de los últimos 7 días',
                'labels' => $dias->map(fn (Carbon $dia): string => $dia->locale('es')->translatedFormat('D d'))->all(),
                'values' => $values,
                'format' => 'money',
            ],
            'recentSales' => $recientes,
            'recentRecetas' => [],
            'lowStock' => $this->lowStock(),
            'topProtecciones' => [],
        ]);
    }

    private function optometristaDashboard(int $userId): Response
    {
        $hoy = now()->toDateString();
        $inicioMes = now()->startOfMonth();

        $stats = [
            $this->stat('Mis recetas de hoy', RecetaEloquentModel::where('optometrista_id', $userId)->whereDate('fecha', $hoy)->count(), 'clipboard', 'sky'),
            $this->stat('Mis recetas del mes', RecetaEloquentModel::where('optometrista_id', $userId)->where('fecha', '>=', $inicioMes)->count(), 'eye', 'teal'),
            $this->stat('Pacientes registrados', PacienteEloquentModel::count(), 'users', 'violet'),
            $this->stat('Nuevos pacientes del mes', PacienteEloquentModel::where('created_at', '>=', $inicioMes)->count(), 'userPlus', 'emerald'),
        ];

        $dias = $this->lastSevenDays();

        $values = $dias->map(fn (Carbon $dia): int => RecetaEloquentModel::where('optometrista_id', $userId)
            ->whereDate('fecha', $dia->toDateString())
            ->count())->all();

        $recetas = RecetaEloquentModel::query()
            ->with('paciente')
            ->where('optometrista_id', $userId)
            ->latest('fecha')
            ->latest('id')
            ->limit(6)
            ->get()
            ->map(fn (RecetaEloquentModel $receta): array => [
                'id' => $receta->id,
                'paciente' => $receta->paciente?->nombre_completo,
                'fecha' => $receta->fecha_label,
                'motivo' => $receta->motivo_consulta,
            ]);

        return Inertia::render('Dashboard', [
            'role' => Role::Optometrista->value,
            'stats' => $stats,
            'chart' => [
                'title' => 'Mis recetas de los últimos 7 días',
                'labels' => $dias->map(fn (Carbon $dia): string => $dia->locale('es')->translatedFormat('D d'))->all(),
                'values' => $values,
                'format' => 'number',
            ],
            'recentSales' => [],
            'recentRecetas' => $recetas,
            'lowStock' => [],
            'topProtecciones' => [],
        ]);
    }

    /**
     * @return Collection<int, Carbon>
     */
    private function lastSevenDays(): Collection
    {
        return collect(range(6, 0))->map(fn (int $daysAgo): Carbon => now()->subDays($daysAgo)->startOfDay());
    }

    /**
     * @return array<string, mixed>
     */
    private function stat(string $label, int|float $value, string $icon, string $tone, string $format = 'number'): array
    {
        return [
            'label' => $label,
            'value' => $value,
            'icon' => $icon,
            'tone' => $tone,
            'format' => $format,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function ventaResumen(VentaEloquentModel $venta): array
    {
        return [
            'id' => $venta->id,
            'codigo' => $venta->codigo,
            'paciente' => $venta->paciente?->nombre_completo,
            'total' => (float) $venta->total,
            'estado' => $venta->estado_label,
            'estado_color' => $venta->estado_color,
            'fecha' => $venta->fecha_label,
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function lowStock(): array
    {
        return MarcoEloquentModel::query()
            ->whereColumn('stock', '<=', 'stock_minimo')
            ->orderBy('stock')
            ->limit(5)
            ->get(['id', 'codigo', 'marca', 'modelo', 'stock', 'stock_minimo'])
            ->map(fn (MarcoEloquentModel $marco): array => [
                'id' => $marco->id,
                'codigo' => $marco->codigo,
                'nombre' => $marco->nombre_completo,
                'stock' => $marco->stock,
                'stock_minimo' => $marco->stock_minimo,
            ])
            ->all();
    }
}
