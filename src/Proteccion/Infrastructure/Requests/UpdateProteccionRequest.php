<?php

namespace Src\Proteccion\Infrastructure\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProteccionRequest extends FormRequest
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
        $proteccionId = $this->route('proteccion')?->id;

        return [
            'nombre' => [
                'required',
                'string',
                'max:80',
                Rule::unique('protecciones', 'nombre')->ignore($proteccionId),
            ],
            'descripcion' => ['nullable', 'string', 'max:255'],
            'precio' => ['required', 'numeric', 'min:0', 'max:99999'],
            'activo' => ['boolean'],
        ];
    }
}
