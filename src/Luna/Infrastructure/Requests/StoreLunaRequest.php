<?php

namespace Src\Luna\Infrastructure\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Src\Luna\Domain\Enums\TipoLuna;

class StoreLunaRequest extends FormRequest
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
            'nombre' => ['required', 'string', 'max:80'],
            'tipo' => ['required', Rule::in(array_column(TipoLuna::cases(), 'value'))],
            'precio' => ['required', 'numeric', 'min:0', 'max:99999'],
            'descripcion' => ['nullable', 'string', 'max:255'],
            'activo' => ['boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nombre.required' => 'Ingresa el nombre del tipo de luna.',
            'tipo.required' => 'Selecciona el tipo de luna.',
            'precio.required' => 'Ingresa el precio de la luna.',
        ];
    }
}
