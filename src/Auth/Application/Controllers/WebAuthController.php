<?php

namespace Src\Auth\Application\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use Src\Auth\Infrastructure\Models\UserEloquentModel;
use Src\Auth\Infrastructure\Requests\LoginRequest;

class WebAuthController extends Controller
{
    public function showLoginForm(): Response
    {
        return Inertia::render('Auth/Login', [
            'demoAccounts' => app()->environment('local') ? $this->existingDemoAccounts() : [],
        ]);
    }

    /**
     * Listado de accesos de prueba que realmente existen en la base de datos.
     *
     * @return array<int, array{role: string, email: string}>
     */
    private function existingDemoAccounts(): array
    {
        $accounts = [
            ['role' => 'Programador', 'email' => 'programador@optica.com'],
            ['role' => 'Dueño', 'email' => 'dueno@optica.com'],
            ['role' => 'Vendedor', 'email' => 'vendedor@optica.com'],
            ['role' => 'Optometrista', 'email' => 'optometrista@optica.com'],
        ];

        $existentes = UserEloquentModel::query()
            ->whereIn('email', array_column($accounts, 'email'))
            ->pluck('email')
            ->all();

        return array_values(array_filter(
            $accounts,
            fn (array $account): bool => in_array($account['email'], $existentes, true),
        ));
    }

    public function login(LoginRequest $request): RedirectResponse
    {
        $credentials = $request->only('email', 'password');

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => 'El correo o la contraseña no son correctos.',
            ]);
        }

        if (! $request->user()->activo) {
            Auth::logout();

            throw ValidationException::withMessages([
                'email' => 'Tu cuenta está desactivada. Contacta al administrador.',
            ]);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
