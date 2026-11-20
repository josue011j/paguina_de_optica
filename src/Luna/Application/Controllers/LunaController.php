<?php

namespace Src\Luna\Application\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Src\Luna\Domain\Enums\TipoLuna;
use Src\Luna\Infrastructure\Models\LunaEloquentModel;
use Src\Luna\Infrastructure\Requests\StoreLunaRequest;
use Src\Luna\Infrastructure\Requests\UpdateLunaRequest;

class LunaController extends Controller
{
    public function index(Request $request): Response
    {
        $search = trim((string) $request->input('search'));
        $tipo = (string) $request->input('tipo');
        $estado = (string) $request->input('estado');

        $lunas = LunaEloquentModel::query()
            ->withCount('ventas')
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('nombre', 'like', "%{$search}%")
                        ->orWhere('descripcion', 'like', "%{$search}%");
                });
            })
            ->when($tipo !== '', fn ($query) => $query->where('tipo', $tipo))
            ->when($estado === 'activo', fn ($query) => $query->where('activo', true))
            ->when($estado === 'inactivo', fn ($query) => $query->where('activo', false))
            ->orderBy('tipo')
            ->orderBy('nombre')
            ->paginate(12)
            ->withQueryString();

        return Inertia::render('Lunas/Index', [
            'lunas' => $lunas,
            'filters' => [
                'search' => $search,
                'tipo' => $tipo,
                'estado' => $estado,
            ],
            'tipos' => TipoLuna::options(),
            'stats' => [
                'total' => LunaEloquentModel::count(),
                'activas' => LunaEloquentModel::where('activo', true)->count(),
                'precioPromedio' => round((float) LunaEloquentModel::avg('precio'), 2),
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Lunas/Create', [
            'tipos' => TipoLuna::options(),
        ]);
    }

    public function store(StoreLunaRequest $request): RedirectResponse
    {
        LunaEloquentModel::create($request->validated());

        return redirect()
            ->route('lunas.index')
            ->with('success', 'Tipo de luna registrado.');
    }

    public function edit(LunaEloquentModel $luna): Response
    {
        return Inertia::render('Lunas/Edit', [
            'luna' => $luna,
            'tipos' => TipoLuna::options(),
        ]);
    }

    public function update(UpdateLunaRequest $request, LunaEloquentModel $luna): RedirectResponse
    {
        $luna->update($request->validated());

        return redirect()
            ->route('lunas.index')
            ->with('success', 'Tipo de luna actualizado.');
    }

    public function destroy(LunaEloquentModel $luna): RedirectResponse
    {
        if ($luna->ventas()->exists()) {
            return redirect()
                ->back()
                ->with('error', 'No se puede eliminar un tipo de luna con ventas registradas. Puedes marcarlo como inactivo.');
        }

        $luna->delete();

        return redirect()
            ->route('lunas.index')
            ->with('success', 'Tipo de luna eliminado.');
    }
}
