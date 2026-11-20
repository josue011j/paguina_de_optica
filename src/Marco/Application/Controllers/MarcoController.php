<?php

namespace Src\Marco\Application\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Src\Marco\Infrastructure\Models\MarcoEloquentModel;
use Src\Marco\Infrastructure\Requests\StoreMarcoRequest;
use Src\Marco\Infrastructure\Requests\UpdateMarcoRequest;

class MarcoController extends Controller
{
    public function index(Request $request): Response
    {
        $search = trim((string) $request->input('search'));
        $marca = (string) $request->input('marca');
        $estado = (string) $request->input('estado');

        $marcos = MarcoEloquentModel::query()
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('codigo', 'like', "%{$search}%")
                        ->orWhere('marca', 'like', "%{$search}%")
                        ->orWhere('modelo', 'like', "%{$search}%")
                        ->orWhere('color', 'like', "%{$search}%");
                });
            })
            ->when($marca !== '', fn ($query) => $query->where('marca', $marca))
            ->when($estado === 'activo', fn ($query) => $query->where('activo', true))
            ->when($estado === 'inactivo', fn ($query) => $query->where('activo', false))
            ->when($estado === 'bajo', fn ($query) => $query->whereColumn('stock', '<=', 'stock_minimo'))
            ->when($estado === 'agotado', fn ($query) => $query->where('stock', 0))
            ->orderBy('marca')
            ->orderBy('modelo')
            ->paginate(12)
            ->withQueryString();

        return Inertia::render('Marcos/Index', [
            'marcos' => $marcos,
            'filters' => [
                'search' => $search,
                'marca' => $marca,
                'estado' => $estado,
            ],
            'marcas' => MarcoEloquentModel::query()
                ->select('marca')
                ->distinct()
                ->orderBy('marca')
                ->pluck('marca'),
            'stats' => [
                'total' => MarcoEloquentModel::count(),
                'activos' => MarcoEloquentModel::where('activo', true)->count(),
                'stock' => (int) MarcoEloquentModel::sum('stock'),
                'bajos' => MarcoEloquentModel::whereColumn('stock', '<=', 'stock_minimo')->count(),
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Marcos/Create');
    }

    public function store(StoreMarcoRequest $request): RedirectResponse
    {
        $data = $request->safe()->except(['foto', 'eliminar_foto']);

        if ($request->hasFile('foto')) {
            $data['foto'] = $request->file('foto')->store('marcos', 'public');
        }

        MarcoEloquentModel::create($data);

        return redirect()
            ->route('marcos.index')
            ->with('success', 'Marco agregado al inventario.');
    }

    public function edit(MarcoEloquentModel $marco): Response
    {
        return Inertia::render('Marcos/Edit', [
            'marco' => $marco,
        ]);
    }

    public function update(UpdateMarcoRequest $request, MarcoEloquentModel $marco): RedirectResponse
    {
        $data = $request->safe()->except(['foto', 'eliminar_foto']);
        $fotoAnterior = $marco->foto;

        if ($request->hasFile('foto')) {
            $data['foto'] = $request->file('foto')->store('marcos', 'public');
        } elseif ($request->boolean('eliminar_foto')) {
            $data['foto'] = null;
        }

        $marco->update($data);

        if (($fotoAnterior !== null) && ($fotoAnterior !== $marco->foto)) {
            Storage::disk('public')->delete($fotoAnterior);
        }

        return redirect()
            ->route('marcos.index')
            ->with('success', 'Marco actualizado correctamente.');
    }

    public function destroy(MarcoEloquentModel $marco): RedirectResponse
    {
        if ($marco->ventas()->exists()) {
            return redirect()
                ->back()
                ->with('error', 'No se puede eliminar un marco con ventas registradas. Puedes marcarlo como inactivo.');
        }

        if ($marco->foto) {
            Storage::disk('public')->delete($marco->foto);
        }

        $marco->delete();

        return redirect()
            ->route('marcos.index')
            ->with('success', 'Marco eliminado del inventario.');
    }
}
