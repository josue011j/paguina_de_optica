<?php

namespace Src\Usuario\Application\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Src\Auth\Domain\Enums\Role;
use Src\Auth\Infrastructure\Models\UserEloquentModel;
use Src\Usuario\Infrastructure\Requests\StoreUsuarioRequest;
use Src\Usuario\Infrastructure\Requests\UpdateUsuarioRequest;

class UsuarioController extends Controller
{
    public function index(Request $request): Response
    {
        $search = trim((string) $request->input('search'));
        $role = (string) $request->input('role');

        $usuarios = UserEloquentModel::query()
            ->withCount(['recetas', 'ventas'])
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('telefono', 'like', "%{$search}%");
                });
            })
            ->when($role !== '', fn ($query) => $query->where('role', $role))
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        $stats = [
            'total' => UserEloquentModel::count(),
            'activos' => UserEloquentModel::where('activo', true)->count(),
        ];

        foreach (Role::cases() as $case) {
            $stats[$case->value] = UserEloquentModel::where('role', $case->value)->count();
        }

        return Inertia::render('Usuarios/Index', [
            'usuarios' => $usuarios,
            'filters' => [
                'search' => $search,
                'role' => $role,
            ],
            'roles' => Role::options(),
            'stats' => $stats,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Usuarios/Create', [
            'roles' => Role::options(),
            'descripciones' => $this->roleDescriptions(),
        ]);
    }

    public function store(StoreUsuarioRequest $request): RedirectResponse
    {
        UserEloquentModel::create($request->validated());

        return redirect()
            ->route('usuarios.index')
            ->with('success', 'Usuario creado correctamente.');
    }

    public function edit(UserEloquentModel $usuario): Response
    {
        return Inertia::render('Usuarios/Edit', [
            'usuario' => $usuario->only(['id', 'name', 'email', 'telefono', 'role', 'activo']),
            'roles' => Role::options(),
            'descripciones' => $this->roleDescriptions(),
        ]);
    }

    public function update(UpdateUsuarioRequest $request, UserEloquentModel $usuario): RedirectResponse
    {
        $data = $request->validated();
        $role = Role::from($data['role']);
        $esUnoMismo = $usuario->id === $request->user()->id;
        $dejaDeSerAdmin = $usuario->isAdmin() && ! $role->isAdmin();
        $seDesactiva = $usuario->activo && ! (bool) ($data['activo'] ?? false);

        if ($esUnoMismo && ($dejaDeSerAdmin || $seDesactiva)) {
            return back()->with('error', 'No puedes quitarte el rol de administrador ni desactivar tu propia cuenta.');
        }

        if ($usuario->isAdmin() && ($dejaDeSerAdmin || $seDesactiva) && $this->activeAdminsExcept($usuario) === 0) {
            return back()->with('error', 'Debe existir al menos un administrador activo en el sistema.');
        }

        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        }

        $usuario->update($data);

        return redirect()
            ->route('usuarios.index')
            ->with('success', 'Usuario actualizado correctamente.');
    }

    public function destroy(Request $request, UserEloquentModel $usuario): RedirectResponse
    {
        if ($usuario->id === $request->user()->id) {
            return back()->with('error', 'No puedes eliminar tu propia cuenta.');
        }

        if ($usuario->isAdmin() && $this->activeAdminsExcept($usuario) === 0) {
            return back()->with('error', 'Debe existir al menos un administrador activo en el sistema.');
        }

        if ($usuario->recetas()->exists() || $usuario->ventas()->exists()) {
            return back()->with('error', 'El usuario tiene recetas o ventas registradas. Desactívalo en lugar de eliminarlo.');
        }

        $usuario->delete();

        return redirect()
            ->route('usuarios.index')
            ->with('success', 'Usuario eliminado.');
    }

    private function activeAdminsExcept(UserEloquentModel $usuario): int
    {
        return UserEloquentModel::query()
            ->where('activo', true)
            ->whereIn('role', [Role::Programador->value, Role::Dueno->value])
            ->whereKeyNot($usuario->id)
            ->count();
    }

    /**
     * @return array<string, string>
     */
    private function roleDescriptions(): array
    {
        $descriptions = [];

        foreach (Role::cases() as $role) {
            $descriptions[$role->value] = $role->description();
        }

        return $descriptions;
    }
}
