<?php

namespace Src\Marco\Infrastructure\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMarcoRequest extends FormRequest
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
        $marcoId = $this->route('marco')?->id;

        return [
            'codigo' => [
                'required',
                'string',
                'max:40',
                Rule::unique('marcos', 'codigo')->ignore($marcoId),
            ],
            'marca' => ['required', 'string', 'max:80'],
            'modelo' => ['required', 'string', 'max:80'],
            'color' => ['nullable', 'string', 'max:60'],
            'material' => ['nullable', 'string', 'max:60'],
            'forma' => ['nullable', 'string', 'max:60'],
            'talla' => ['nullable', 'string', 'max:40'],
            'precio' => ['required', 'numeric', 'min:0', 'max:99999'],
            'stock' => ['required', 'integer', 'min:0', 'max:100000'],
            'stock_minimo' => ['required', 'integer', 'min:0', 'max:10000'],
            'descripcion' => ['nullable', 'string', 'max:255'],
            'foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'eliminar_foto' => ['boolean'],
            'activo' => ['boolean'],
        ];
    }
}
