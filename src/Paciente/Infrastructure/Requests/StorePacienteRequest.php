<?php

namespace Src\Paciente\Infrastructure\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePacienteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->canDo('createPatients') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'nombres' => ['required', 'string', 'max:100'],
            'apellidos' => ['required', 'string', 'max:100'],
            'tipo_documento' => ['required', Rule::in(['DNI', 'CE', 'Pasaporte'])],
            'numero_documento' => ['required', 'string', 'max:30', 'unique:pacientes,numero_documento'],
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

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nombres.required' => 'Ingresa los nombres del paciente.',
            'apellidos.required' => 'Ingresa los apellidos del paciente.',
            'numero_documento.required' => 'Ingresa el número de documento.',
            'numero_documento.unique' => 'Ya existe un paciente con ese documento.',
            'fecha_nacimiento.before' => 'La fecha de nacimiento debe ser anterior a hoy.',
            'email.email' => 'El correo electrónico no es válido.',
        ];
    }
}
