<?php

namespace Src\Auth\Application\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use Src\Auth\Infrastructure\Requests\LoginRequest;

class WebAuthController extends Controller
{
    public function showLoginForm(): Response
    {
        return Inertia::render('Auth/Login', [
            'demoAccounts' => app()->environment('local') ? [
                ['role' => 'Programador', 'email' => 'programador@optica.com'],
                ['role' => 'Dueño', 'email' => 'dueno@optica.com'],
                ['role' => 'Vendedor', 'email' => 'vendedor@optica.com'],
                ['role' => 'Optometrista', 'email' => 'optometrista@optica.com'],
            ] : [],
        ]);
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
