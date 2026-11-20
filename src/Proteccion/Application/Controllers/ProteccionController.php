<?php

namespace Src\Proteccion\Application\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Src\Proteccion\Infrastructure\Models\ProteccionEloquentModel;
use Src\Proteccion\Infrastructure\Requests\StoreProteccionRequest;
use Src\Proteccion\Infrastructure\Requests\UpdateProteccionRequest;

class ProteccionController extends Controller
{
    public function index(Request $request): Response
    {
        $search = trim((string) $request->input('search'));
        $estado = (string) $request->input('estado');

        $protecciones = ProteccionEloquentModel::query()
            ->withCount('ventas')
            ->when($search !== '', fn ($query) => $query->where('nombre', 'like', "%{$search}%"))
            ->when($estado === 'activo', fn ($query) => $query->where('activo', true))
            ->when($estado === 'inactivo', fn ($query) => $query->where('activo', false))
            ->orderBy('nombre')
            ->paginate(12)
            ->withQueryString();

        return Inertia::render('Protecciones/Index', [
            'protecciones' => $protecciones,
            'filters' => [
                'search' => $search,
                'estado' => $estado,
            ],
            'stats' => [
                'total' => ProteccionEloquentModel::count(),
                'activas' => ProteccionEloquentModel::where('activo', true)->count(),
                'precioPromedio' => round((float) ProteccionEloquentModel::avg('precio'), 2),
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Protecciones/Create');
    }

    public function store(StoreProteccionRequest $request): RedirectResponse
    {
        ProteccionEloquentModel::create($request->validated());

        return redirect()
            ->route('protecciones.index')
            ->with('success', 'Protección registrada correctamente.');
    }

    public function edit(ProteccionEloquentModel $proteccion): Response
    {
        return Inertia::render('Protecciones/Edit', [
            'proteccion' => $proteccion,
        ]);
    }

    public function update(UpdateProteccionRequest $request, ProteccionEloquentModel $proteccion): RedirectResponse
    {
        $proteccion->update($request->validated());

        return redirect()
            ->route('protecciones.index')
            ->with('success', 'Protección actualizada correctamente.');
    }

    public function destroy(ProteccionEloquentModel $proteccion): RedirectResponse
    {
        if ($proteccion->ventas()->exists()) {
            return redirect()
                ->back()
                ->with('error', 'No se puede eliminar una protección usada en ventas. Puedes marcarla como inactiva.');
        }

        $proteccion->delete();

        return redirect()
            ->route('protecciones.index')
            ->with('success', 'Protección eliminada.');
    }
}
