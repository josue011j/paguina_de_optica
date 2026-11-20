<?php

namespace Src\Auth\Domain\Enums;

enum Role: string
{
    case Programador = 'programador';
    case Dueno = 'dueno';
    case Vendedor = 'vendedor';
    case Optometrista = 'optometrista';

    public function label(): string
    {
        return match ($this) {
            self::Programador => 'Programador',
            self::Dueno => 'Dueño',
            self::Vendedor => 'Vendedor',
            self::Optometrista => 'Optometrista',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Programador => 'Acceso total al sistema, usuarios y configuración.',
            self::Dueno => 'Acceso total a la operación, catálogo y reportes.',
            self::Vendedor => 'Registra ventas y elige las protecciones del cliente.',
            self::Optometrista => 'Gestiona pacientes y las medidas de las lunas.',
        };
    }

    public function isAdmin(): bool
    {
        return in_array($this, [self::Programador, self::Dueno], true);
    }

    /**
     * @return array<string, bool>
     */
    public function permissions(): array
    {
        $admin = $this->isAdmin();

        return [
            'manageUsers' => $admin,
            'manageCatalog' => $admin,
            'viewCatalog' => true,
            'viewPatients' => true,
            'createPatients' => true,
            'editPatients' => $admin || $this === self::Optometrista,
            'destroyPatients' => $admin,
            'manageRecetas' => $admin || $this === self::Optometrista,
            'viewRecetas' => $admin || $this === self::Optometrista,
            'viewLatestReceta' => true,
            'manageSales' => $admin || $this === self::Vendedor,
            'viewSales' => $admin || $this === self::Vendedor,
            'viewReports' => $admin,
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_map(static fn (self $role): string => $role->value, self::cases());
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        $options = [];

        foreach (self::cases() as $role) {
            $options[$role->value] = $role->label();
        }

        return $options;
    }
}
