<?php

namespace Src\Venta\Domain\Enums;

enum EstadoVenta: string
{
    case Pendiente = 'pendiente';
    case EnProceso = 'en_proceso';
    case Entregado = 'entregado';
    case Cancelado = 'cancelado';

    public function label(): string
    {
        return match ($this) {
            self::Pendiente => 'Pendiente',
            self::EnProceso => 'En proceso',
            self::Entregado => 'Entregado',
            self::Cancelado => 'Cancelado',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pendiente => 'amber',
            self::EnProceso => 'sky',
            self::Entregado => 'emerald',
            self::Cancelado => 'rose',
        };
    }

    public function isCancelado(): bool
    {
        return $this === self::Cancelado;
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        $options = [];

        foreach (self::cases() as $estado) {
            $options[$estado->value] = $estado->label();
        }

        return $options;
    }

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_map(static fn (self $estado): string => $estado->value, self::cases());
    }
}
