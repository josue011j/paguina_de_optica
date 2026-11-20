<?php

namespace Src\Usuario\Infrastructure\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Src\Auth\Domain\Enums\Role;

class UpdateUsuarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->canDo('manageUsers') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $usuarioId = $this->route('usuario')?->id;

        return [
            'name' => ['required', 'string', 'max:120'],
            'email' => [
                'required',
                'string',
                'email',
                'max:150',
                Rule::unique('users', 'email')->ignore($usuarioId),
            ],
            'telefono' => ['nullable', 'string', 'max:30'],
            'role' => ['required', Rule::in(Role::values())],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'activo' => ['boolean'],
        ];
    }
}
