<?php

namespace Src\Luna\Domain\Enums;

enum TipoLuna: string
{
    case Monofocal = 'monofocal';
    case Bifocal = 'bifocal';
    case Progresivo = 'progresivo';
    case Ocupacional = 'ocupacional';

    public function label(): string
    {
        return match ($this) {
            self::Monofocal => 'Monofocal',
            self::Bifocal => 'Bifocal',
            self::Progresivo => 'Progresivo',
            self::Ocupacional => 'Ocupacional',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        $options = [];

        foreach (self::cases() as $tipo) {
            $options[$tipo->value] = $tipo->label();
        }

        return $options;
    }
}
