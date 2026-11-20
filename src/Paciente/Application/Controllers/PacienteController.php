<?php

namespace Src\Paciente\Application\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Src\Paciente\Infrastructure\Models\PacienteEloquentModel;
use Src\Paciente\Infrastructure\Requests\StorePacienteRequest;
use Src\Paciente\Infrastructure\Requests\UpdatePacienteRequest;

class PacienteController extends Controller
{
    public function index(Request $request): Response
    {
        $search = trim((string) $request->input('search'));
        $estado = (string) $request->input('estado');

        $pacientes = PacienteEloquentModel::query()
            ->withCount(['recetas', 'ventas'])
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('nombres', 'like', "%{$search}%")
                        ->orWhere('apellidos', 'like', "%{$search}%")
                        ->orWhere('numero_documento', 'like', "%{$search}%")
                        ->orWhere('telefono', 'like', "%{$search}%");
                });
            })
            ->when($estado === 'activo', fn ($query) => $query->where('activo', true))
            ->when($estado === 'inactivo', fn ($query) => $query->where('activo', false))
            ->orderBy('apellidos')
            ->orderBy('nombres')
            ->paginate(12)
            ->withQueryString();

        return Inertia::render('Pacientes/Index', [
            'pacientes' => $pacientes,
            'filters' => [
                'search' => $search,
                'estado' => $estado,
            ],
            'stats' => [
                'total' => PacienteEloquentModel::count(),
                'activos' => PacienteEloquentModel::where('activo', true)->count(),
                'conRecetas' => PacienteEloquentModel::whereHas('recetas')->count(),
                'nuevosMes' => PacienteEloquentModel::where('created_at', '>=', now()->startOfMonth())->count(),
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Pacientes/Create');
    }

    public function store(StorePacienteRequest $request): RedirectResponse
    {
        $paciente = PacienteEloquentModel::create($request->validated());

        return redirect()
            ->route('pacientes.show', $paciente)
            ->with('success', 'Paciente registrado correctamente.');
    }

    public function show(Request $request, PacienteEloquentModel $paciente): Response
    {
        $user = $request->user();
        $relations = ['ventas' => fn ($query) => $query->latest('fecha')->limit(10)];

        if ($user->canDo('viewRecetas')) {
            $relations['recetas'] = fn ($query) => $query->with('optometrista')->latest('fecha')->limit(20);
        }

        $paciente->load($relations);

        return Inertia::render('Pacientes/Show', [
            'paciente' => $paciente,
            'puedeVerRecetas' => $user->canDo('viewRecetas'),
            'puedeVerVentas' => $user->canDo('viewSales'),
        ]);
    }

    public function edit(PacienteEloquentModel $paciente): Response
    {
        return Inertia::render('Pacientes/Edit', [
            'paciente' => $paciente,
        ]);
    }

    public function update(UpdatePacienteRequest $request, PacienteEloquentModel $paciente): RedirectResponse
    {
        $paciente->update($request->validated());

        return redirect()
            ->route('pacientes.show', $paciente)
            ->with('success', 'Datos del paciente actualizados.');
    }

    public function destroy(PacienteEloquentModel $paciente): RedirectResponse
    {
        $paciente->delete();

        return redirect()
            ->route('pacientes.index')
            ->with('success', 'Paciente eliminado del registro.');
    }

    public function ultimaReceta(Request $request, PacienteEloquentModel $paciente): JsonResponse
    {
        abort_unless($request->user()->canDo('viewLatestReceta'), 403);

        $receta = $paciente->recetas()
            ->with('optometrista')
            ->latest('fecha')
            ->latest('id')
            ->first();

        if (! $receta) {
            return response()->json(['receta' => null]);
        }

        return response()->json([
            'receta' => [
                'id' => $receta->id,
                'fecha' => $receta->fecha?->format('d/m/Y'),
                'optometrista' => $receta->optometrista?->name,
                'od_esfera' => $receta->od_esfera,
                'od_cilindro' => $receta->od_cilindro,
                'od_eje' => $receta->od_eje,
                'od_adicion' => $receta->od_adicion,
                'od_av' => $receta->od_av,
                'oi_esfera' => $receta->oi_esfera,
                'oi_cilindro' => $receta->oi_cilindro,
                'oi_eje' => $receta->oi_eje,
                'oi_adicion' => $receta->oi_adicion,
                'oi_av' => $receta->oi_av,
                'dip' => $receta->dip,
                'observaciones' => $receta->observaciones,
            ],
        ]);
    }
}
