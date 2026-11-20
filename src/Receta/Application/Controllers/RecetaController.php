<?php

namespace Src\Receta\Application\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Src\Paciente\Infrastructure\Models\PacienteEloquentModel;
use Src\Receta\Infrastructure\Models\RecetaEloquentModel;
use Src\Receta\Infrastructure\Requests\StoreRecetaRequest;
use Src\Receta\Infrastructure\Requests\UpdateRecetaRequest;

class RecetaController extends Controller
{
    public function index(Request $request): Response
    {
        $search = trim((string) $request->input('search'));
        $desde = (string) $request->input('desde');
        $hasta = (string) $request->input('hasta');

        $recetas = RecetaEloquentModel::query()
            ->with(['paciente', 'optometrista'])
            ->when($search !== '', function ($query) use ($search): void {
                $query->whereHas('paciente', function ($query) use ($search): void {
                    $query->where('nombres', 'like', "%{$search}%")
                        ->orWhere('apellidos', 'like', "%{$search}%")
                        ->orWhere('numero_documento', 'like', "%{$search}%");
                });
            })
            ->when($desde !== '', fn ($query) => $query->whereDate('fecha', '>=', $desde))
            ->when($hasta !== '', fn ($query) => $query->whereDate('fecha', '<=', $hasta))
            ->latest('fecha')
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Recetas/Index', [
            'recetas' => $recetas,
            'filters' => [
                'search' => $search,
                'desde' => $desde,
                'hasta' => $hasta,
            ],
            'stats' => [
                'total' => RecetaEloquentModel::count(),
                'hoy' => RecetaEloquentModel::whereDate('fecha', now()->toDateString())->count(),
                'mes' => RecetaEloquentModel::where('fecha', '>=', now()->startOfMonth())->count(),
                'pacientes' => RecetaEloquentModel::distinct('paciente_id')->count('paciente_id'),
            ],
        ]);
    }

    public function create(Request $request): Response
    {
        return Inertia::render('Recetas/Create', [
            'pacientes' => $this->pacientesOptions(),
            'pacienteSeleccionado' => $request->integer('paciente') ?: null,
        ]);
    }

    public function store(StoreRecetaRequest $request): RedirectResponse
    {
        $receta = RecetaEloquentModel::create([
            ...$request->validated(),
            'optometrista_id' => $request->user()->id,
        ]);

        return redirect()
            ->route('recetas.show', $receta)
            ->with('success', 'Receta registrada correctamente.');
    }

    public function show(RecetaEloquentModel $receta): Response
    {
        $receta->load(['paciente', 'optometrista']);

        return Inertia::render('Recetas/Show', [
            'receta' => $receta,
        ]);
    }

    public function edit(RecetaEloquentModel $receta): Response
    {
        return Inertia::render('Recetas/Edit', [
            'receta' => $receta,
            'pacientes' => $this->pacientesOptions(),
        ]);
    }

    public function update(UpdateRecetaRequest $request, RecetaEloquentModel $receta): RedirectResponse
    {
        $receta->update($request->validated());

        return redirect()
            ->route('recetas.show', $receta)
            ->with('success', 'Receta actualizada correctamente.');
    }

    public function destroy(RecetaEloquentModel $receta): RedirectResponse
    {
        if ($receta->ventas()->exists()) {
            return redirect()
                ->back()
                ->with('error', 'No se puede eliminar una receta asociada a una venta.');
        }

        $pacienteId = $receta->paciente_id;
        $receta->delete();

        return redirect()
            ->route('pacientes.show', $pacienteId)
            ->with('success', 'Receta eliminada.');
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function pacientesOptions(): array
    {
        return PacienteEloquentModel::query()
            ->where('activo', true)
            ->orderBy('apellidos')
            ->orderBy('nombres')
            ->get(['id', 'nombres', 'apellidos', 'numero_documento'])
            ->map(fn (PacienteEloquentModel $paciente): array => [
                'id' => $paciente->id,
                'nombre_completo' => $paciente->nombre_completo,
                'numero_documento' => $paciente->numero_documento,
            ])
            ->all();
    }
}
