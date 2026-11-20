<?php

namespace Src\Marco\Infrastructure\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMarcoRequest extends FormRequest
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
            'codigo' => ['required', 'string', 'max:40', 'unique:marcos,codigo'],
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
            'activo' => ['boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'codigo.required' => 'Ingresa el código del marco.',
            'codigo.unique' => 'Ya existe un marco con ese código.',
            'marca.required' => 'Ingresa la marca del marco.',
            'modelo.required' => 'Ingresa el modelo del marco.',
            'precio.required' => 'Ingresa el precio de venta.',
            'foto.image' => 'La foto debe ser una imagen válida.',
            'foto.mimes' => 'La foto debe ser JPG, PNG o WebP.',
            'foto.max' => 'La foto no puede superar los 4 MB.',
        ];
    }
}
