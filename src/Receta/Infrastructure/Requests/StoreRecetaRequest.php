<?php

namespace Src\Receta\Infrastructure\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRecetaRequest extends FormRequest
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

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'paciente_id.required' => 'Selecciona el paciente.',
            'paciente_id.exists' => 'El paciente seleccionado no existe.',
            'fecha.required' => 'Ingresa la fecha del examen.',
            'fecha.before_or_equal' => 'La fecha no puede ser futura.',
            'od_esfera.between' => 'La esfera OD debe estar entre -30 y 30.',
            'oi_esfera.between' => 'La esfera OI debe estar entre -30 y 30.',
            'od_eje.between' => 'El eje OD debe estar entre 0 y 180.',
            'oi_eje.between' => 'El eje OI debe estar entre 0 y 180.',
        ];
    }
}
