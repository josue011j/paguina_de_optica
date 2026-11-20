<?php

namespace Src\Receta\Infrastructure\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRecetaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->canDo('manageRecetas') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'paciente_id' => ['required', 'exists:pacientes,id'],
            'fecha' => ['required', 'date', 'before_or_equal:today'],
            'motivo_consulta' => ['nullable', 'string', 'max:150'],

            'od_esfera' => ['nullable', 'numeric', 'between:-30,30'],
            'od_cilindro' => ['nullable', 'numeric', 'between:-15,15'],
            'od_eje' => ['nullable', 'integer', 'between:0,180'],
            'od_adicion' => ['nullable', 'numeric', 'between:0,10'],
            'od_av' => ['nullable', 'string', 'max:15'],

            'oi_esfera' => ['nullable', 'numeric', 'between:-30,30'],
            'oi_cilindro' => ['nullable', 'numeric', 'between:-15,15'],
            'oi_eje' => ['nullable', 'integer', 'between:0,180'],
            'oi_adicion' => ['nullable', 'numeric', 'between:0,10'],
            'oi_av' => ['nullable', 'string', 'max:15'],

            'dip' => ['nullable', 'numeric', 'between:30,90'],
            'observaciones' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
