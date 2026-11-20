<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Src\Marco\Infrastructure\Models\MarcoEloquentModel;
use Src\Venta\Domain\Enums\EstadoVenta;
use Src\Venta\Infrastructure\Models\VentaEloquentModel;

class ReportesController extends Controller
{
    public function index(Request $request): Response
    {
        $desde = (string) ($request->input('desde') ?: now()->startOfMonth()->toDateString());
        $hasta = (string) ($request->input('hasta') ?: now()->toDateString());

        if ($desde > $hasta) {
            [$desde, $hasta] = [$hasta, $desde];
        }

        $inicio = Carbon::parse($desde)->startOfDay();
        $fin = Carbon::parse($hasta)->endOfDay();

        $ventasValidas = VentaEloquentModel::query()
            ->where('estado', '!=', EstadoVenta::Cancelado->value)
            ->whereBetween('fecha', [$inicio->toDateTimeString(), $fin->toDateTimeString()]);

        $cantidad = (clone $ventasValidas)->count();
        $ingresos = (float) (clone $ventasValidas)->sum('total');

        $summary = [
            'ingresos' => round($ingresos, 2),
            'ventas' => $cantidad,
            'ticketPromedio' => $cantidad > 0 ? round($ingresos / $cantidad, 2) : 0,
            'canceladas' => VentaEloquentModel::query()
                ->where('estado', EstadoVenta::Cancelado->value)
                ->whereBetween('fecha', [$inicio->toDateTimeString(), $fin->toDateTimeString()])
                ->count(),
        ];

        $chart = $this->chartData($inicio, $fin);

        $topProtecciones = DB::table('venta_proteccion as vp')
            ->join('ventas as v', 'v.id', '=', 'vp.venta_id')
            ->join('protecciones as p', 'p.id', '=', 'vp.proteccion_id')
            ->where('v.estado', '!=', EstadoVenta::Cancelado->value)
            ->whereBetween('v.fecha', [$inicio->toDateTimeString(), $fin->toDateTimeString()])
            ->select('p.nombre', DB::raw('COUNT(*) as cantidad'), DB::raw('SUM(vp.precio * v.cantidad) as ingresos'))
            ->groupBy('p.id', 'p.nombre')
            ->orderByDesc('cantidad')
            ->limit(5)
            ->get()
            ->map(fn ($row): array => [
                'nombre' => $row->nombre,
                'cantidad' => (int) $row->cantidad,
                'ingresos' => round((float) $row->ingresos, 2),
            ]);

        $topMarcos = DB::table('ventas as v')
            ->join('marcos as m', 'm.id', '=', 'v.marco_id')
            ->where('v.estado', '!=', EstadoVenta::Cancelado->value)
            ->whereBetween('v.fecha', [$inicio->toDateTimeString(), $fin->toDateTimeString()])
            ->select(
                'm.marca',
                'm.modelo',
                DB::raw('SUM(v.cantidad) as cantidad'),
                DB::raw('SUM(m.precio * v.cantidad) as ingresos'),
            )
            ->groupBy('m.id', 'm.marca', 'm.modelo')
            ->orderByDesc('cantidad')
            ->limit(5)
            ->get()
            ->map(fn ($row): array => [
                'nombre' => trim("{$row->marca} {$row->modelo}"),
                'cantidad' => (int) $row->cantidad,
                'ingresos' => round((float) $row->ingresos, 2),
            ]);

        $topLunas = DB::table('ventas as v')
            ->join('lunas as l', 'l.id', '=', 'v.luna_id')
            ->where('v.estado', '!=', EstadoVenta::Cancelado->value)
            ->whereBetween('v.fecha', [$inicio->toDateTimeString(), $fin->toDateTimeString()])
            ->select('l.nombre', DB::raw('SUM(v.cantidad) as cantidad'), DB::raw('SUM(l.precio * v.cantidad) as ingresos'))
            ->groupBy('l.id', 'l.nombre')
            ->orderByDesc('cantidad')
            ->limit(5)
            ->get()
            ->map(fn ($row): array => [
                'nombre' => $row->nombre,
                'cantidad' => (int) $row->cantidad,
                'ingresos' => round((float) $row->ingresos, 2),
            ]);

        $vendedores = DB::table('ventas as v')
            ->join('users as u', 'u.id', '=', 'v.vendedor_id')
            ->where('v.estado', '!=', EstadoVenta::Cancelado->value)
            ->whereBetween('v.fecha', [$inicio->toDateTimeString(), $fin->toDateTimeString()])
            ->select('u.name', DB::raw('COUNT(*) as ventas'), DB::raw('SUM(v.total) as ingresos'))
            ->groupBy('u.id', 'u.name')
            ->orderByDesc('ingresos')
            ->get()
            ->map(fn ($row): array => [
                'nombre' => $row->name,
                'ventas' => (int) $row->ventas,
                'ingresos' => round((float) $row->ingresos, 2),
            ]);

        return Inertia::render('Reportes/Index', [
            'filters' => [
                'desde' => $desde,
                'hasta' => $hasta,
            ],
            'summary' => $summary,
            'chart' => $chart,
            'topProtecciones' => $topProtecciones,
            'topMarcos' => $topMarcos,
            'topLunas' => $topLunas,
            'vendedores' => $vendedores,
            'lowStock' => MarcoEloquentModel::query()
                ->whereColumn('stock', '<=', 'stock_minimo')
                ->orderBy('stock')
                ->limit(8)
                ->get(['id', 'codigo', 'marca', 'modelo', 'stock', 'stock_minimo'])
                ->map(fn (MarcoEloquentModel $marco): array => [
                    'id' => $marco->id,
                    'codigo' => $marco->codigo,
                    'nombre' => $marco->nombre_completo,
                    'stock' => $marco->stock,
                    'stock_minimo' => $marco->stock_minimo,
                ]),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function chartData(Carbon $inicio, Carbon $fin): array
    {
        $ventas = VentaEloquentModel::query()
            ->where('estado', '!=', EstadoVenta::Cancelado->value)
            ->whereBetween('fecha', [$inicio->toDateTimeString(), $fin->toDateTimeString()])
            ->get(['fecha', 'total']);

        if ($inicio->diffInDays($fin) <= 62) {
            $porDia = $ventas->groupBy(fn (VentaEloquentModel $venta): string => $venta->fecha->toDateString())
                ->map(fn ($grupo): float => round((float) $grupo->sum('total'), 2));

            $labels = [];
            $values = [];

            for ($dia = $inicio->copy(); $dia->lte($fin); $dia->addDay()) {
                $labels[] = $dia->locale('es')->translatedFormat('d M');
                $values[] = (float) ($porDia[$dia->toDateString()] ?? 0);
            }

            return [
                'title' => 'Ingresos por dÃ­a',
                'labels' => $labels,
                'values' => $values,
            ];
        }

        $porMes = $ventas->groupBy(fn (VentaEloquentModel $venta): string => $venta->fecha->format('Y-m'))
            ->map(fn ($grupo): float => round((float) $grupo->sum('total'), 2));

        $labels = [];
        $values = [];

        for ($mes = $inicio->copy()->startOfMonth(); $mes->lte($fin); $mes->addMonth()) {
            $labels[] = $mes->locale('es')->translatedFormat('M Y');
            $values[] = (float) ($porMes[$mes->format('Y-m')] ?? 0);
        }

        return [
            'title' => 'Ingresos por mes',
            'labels' => $labels,
            'values' => $values,
        ];
    }
}
