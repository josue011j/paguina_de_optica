<?php

namespace Src\Venta\Application\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Src\Luna\Infrastructure\Models\LunaEloquentModel;
use Src\Marco\Infrastructure\Models\MarcoEloquentModel;
use Src\Paciente\Infrastructure\Models\PacienteEloquentModel;
use Src\Proteccion\Infrastructure\Models\ProteccionEloquentModel;
use Src\Venta\Application\Services\VentaService;
use Src\Venta\Domain\Enums\EstadoVenta;
use Src\Venta\Domain\Enums\MetodoPago;
use Src\Venta\Infrastructure\Models\VentaEloquentModel;
use Src\Venta\Infrastructure\Requests\StoreVentaRequest;
use Src\Venta\Infrastructure\Requests\UpdateEstadoVentaRequest;

class VentaController extends Controller
{
    public function __construct(private readonly VentaService $ventas) {}

    public function index(Request $request): Response
    {
        $search = trim((string) $request->input('search'));
        $estado = (string) $request->input('estado');
        $desde = (string) ($request->input('desde') ?: now()->startOfMonth()->toDateString());
        $hasta = (string) ($request->input('hasta') ?: now()->toDateString());

        $base = $this->filteredQuery($search, $estado, $desde, $hasta);

        $ventas = (clone $base)
            ->with(['paciente', 'vendedor', 'marco', 'luna', 'protecciones'])
            ->latest('fecha')
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        $stats = [
            'total' => (clone $base)->count(),
            'ingresos' => round((float) (clone $base)
                ->where('estado', '!=', EstadoVenta::Cancelado->value)
                ->sum('total'), 2),
            'pendientes' => (clone $base)
                ->whereIn('estado', [EstadoVenta::Pendiente->value, EstadoVenta::EnProceso->value])
                ->count(),
            'entregadas' => (clone $base)->where('estado', EstadoVenta::Entregado->value)->count(),
        ];

        return Inertia::render('Ventas/Index', [
            'ventas' => $ventas,
            'filters' => [
                'search' => $search,
                'estado' => $estado,
                'desde' => $desde,
                'hasta' => $hasta,
            ],
            'estados' => EstadoVenta::options(),
            'stats' => $stats,
        ]);
    }

    public function create(Request $request): Response
    {
        return Inertia::render('Ventas/Create', [
            'pacientes' => PacienteEloquentModel::query()
                ->where('activo', true)
                ->orderBy('apellidos')
                ->orderBy('nombres')
                ->get(['id', 'nombres', 'apellidos', 'numero_documento', 'telefono'])
                ->map(fn (PacienteEloquentModel $paciente): array => [
                    'id' => $paciente->id,
                    'nombre_completo' => $paciente->nombre_completo,
                    'numero_documento' => $paciente->numero_documento,
                    'telefono' => $paciente->telefono,
                ]),
            'marcos' => MarcoEloquentModel::query()
                ->activos()
                ->conStock()
                ->orderBy('marca')
                ->orderBy('modelo')
                ->get(['id', 'codigo', 'marca', 'modelo', 'color', 'precio', 'stock', 'foto']),
            'lunas' => LunaEloquentModel::query()
                ->activas()
                ->orderBy('tipo')
                ->orderBy('nombre')
                ->get(['id', 'nombre', 'tipo', 'precio', 'descripcion']),
            'protecciones' => ProteccionEloquentModel::query()
                ->activas()
                ->orderBy('nombre')
                ->get(['id', 'nombre', 'descripcion', 'precio']),
            'metodosPago' => MetodoPago::options(),
            'estados' => array_diff_key(EstadoVenta::options(), [EstadoVenta::Cancelado->value => '']),
            'fechaHoy' => now()->toDateString(),
            'pacienteSeleccionado' => $request->integer('paciente') ?: null,
        ]);
    }

    public function store(StoreVentaRequest $request): RedirectResponse
    {
        $venta = $this->ventas->registrar($request->validated(), $request->user());

        return redirect()
            ->route('ventas.show', $venta)
            ->with('success', "Venta {$venta->codigo} registrada correctamente.");
    }

    public function show(Request $request, VentaEloquentModel $venta): Response
    {
        $venta->load(['paciente', 'vendedor', 'receta.optometrista', 'marco', 'luna', 'protecciones']);

        $user = $request->user();

        return Inertia::render('Ventas/Show', [
            'venta' => $venta,
            'estados' => EstadoVenta::options(),
            'puedeEditarEstado' => $user->isAdmin() || $venta->vendedor_id === $user->id,
            'puedeEliminar' => $user->isAdmin(),
        ]);
    }

    public function updateEstado(UpdateEstadoVentaRequest $request, VentaEloquentModel $venta): RedirectResponse
    {
        $user = $request->user();

        abort_unless($user->isAdmin() || $venta->vendedor_id === $user->id, 403);

        $estado = EstadoVenta::from($request->validated('estado'));

        $this->ventas->cambiarEstado($venta, $estado);

        return redirect()
            ->route('ventas.show', $venta)
            ->with('success', "La venta pasó al estado \"{$estado->label()}\".");
    }

    public function destroy(VentaEloquentModel $venta): RedirectResponse
    {
        $codigo = $venta->codigo;

        $this->ventas->eliminar($venta);

        return redirect()
            ->route('ventas.index')
            ->with('success', "La venta {$codigo} fue eliminada y el stock fue restaurado.");
    }

    private function filteredQuery(string $search, string $estado, string $desde, string $hasta): Builder
    {
        return VentaEloquentModel::query()
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('codigo', 'like', "%{$search}%")
                        ->orWhereHas('paciente', function (Builder $query) use ($search): void {
                            $query->where('nombres', 'like', "%{$search}%")
                                ->orWhere('apellidos', 'like', "%{$search}%")
                                ->orWhere('numero_documento', 'like', "%{$search}%");
                        });
                });
            })
            ->when($estado !== '', fn (Builder $query) => $query->where('estado', $estado))
            ->when($desde !== '', fn (Builder $query) => $query->whereDate('fecha', '>=', $desde))
            ->when($hasta !== '', fn (Builder $query) => $query->whereDate('fecha', '<=', $hasta));
    }
}
