<?php

namespace Src\Venta\Infrastructure\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Src\Venta\Domain\Enums\EstadoVenta;
use Src\Venta\Domain\Enums\MetodoPago;

class StoreVentaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->canDo('manageSales') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $estadosPermitidos = array_values(array_diff(EstadoVenta::values(), [EstadoVenta::Cancelado->value]));

        return [
            'paciente_id' => ['required', 'exists:pacientes,id'],
            'receta_id' => ['nullable', 'exists:recetas,id'],
            'marco_id' => ['nullable', 'exists:marcos,id'],
            'luna_id' => ['required', 'exists:lunas,id'],
            'protecciones' => ['nullable', 'array'],
            'protecciones.*' => ['integer', 'distinct', 'exists:protecciones,id'],
            'cantidad' => ['required', 'integer', 'between:1,10'],
            'descuento' => ['nullable', 'numeric', 'min:0', 'max:99999'],
            'metodo_pago' => ['required', Rule::enum(MetodoPago::class)],
            'estado' => ['required', Rule::in($estadosPermitidos)],
            'fecha' => ['required', 'date', 'before_or_equal:today'],
            'notas' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'paciente_id.required' => 'Selecciona el paciente.',
            'luna_id.required' => 'Selecciona el tipo de luna.',
            'cantidad.required' => 'Indica la cantidad.',
            'metodo_pago.required' => 'Selecciona el método de pago.',
            'fecha.required' => 'Indica la fecha de la venta.',
        ];
    }
}
