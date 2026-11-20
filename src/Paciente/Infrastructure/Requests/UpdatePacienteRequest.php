<?php

namespace Src\Paciente\Infrastructure\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePacienteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->canDo('editPatients') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $pacienteId = $this->route('paciente')?->id;

        return [
            'nombres' => ['required', 'string', 'max:100'],
            'apellidos' => ['required', 'string', 'max:100'],
            'tipo_documento' => ['required', Rule::in(['DNI', 'CE', 'Pasaporte'])],
            'numero_documento' => [
                'required',
                'string',
                'max:30',
                Rule::unique('pacientes', 'numero_documento')->ignore($pacienteId),
            ],
            'fecha_nacimiento' => ['nullable', 'date', 'before:today'],
            'sexo' => ['nullable', Rule::in(['Masculino', 'Femenino', 'Otro'])],
            'telefono' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:150'],
            'direccion' => ['nullable', 'string', 'max:200'],
            'alergias' => ['nullable', 'string', 'max:500'],
            'observaciones' => ['nullable', 'string', 'max:1000'],
            'activo' => ['boolean'],
        ];
    }
}
