<?php

namespace Src\Proteccion\Infrastructure\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProteccionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->canDo('manageCatalog') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:80', 'unique:protecciones,nombre'],
            'descripcion' => ['nullable', 'string', 'max:255'],
            'precio' => ['required', 'numeric', 'min:0', 'max:99999'],
            'activo' => ['boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nombre.required' => 'Ingresa el nombre de la protección.',
            'nombre.unique' => 'Ya existe una protección con ese nombre.',
            'precio.required' => 'Ingresa el precio de la protección.',
        ];
    }
}
