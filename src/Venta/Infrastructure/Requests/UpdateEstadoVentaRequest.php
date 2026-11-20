<?php

namespace Src\Venta\Infrastructure\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Src\Venta\Domain\Enums\EstadoVenta;

class UpdateEstadoVentaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->canDo('viewSales') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'estado' => ['required', Rule::enum(EstadoVenta::class)],
        ];
    }
}
