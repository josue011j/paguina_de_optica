<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Src\Auth\Domain\Enums\Role;
use Src\Auth\Infrastructure\Models\UserEloquentModel;
use Src\Luna\Domain\Enums\TipoLuna;
use Src\Luna\Infrastructure\Models\LunaEloquentModel;
use Src\Marco\Infrastructure\Models\MarcoEloquentModel;
use Src\Paciente\Infrastructure\Models\PacienteEloquentModel;
use Src\Proteccion\Infrastructure\Models\ProteccionEloquentModel;
use Src\Receta\Infrastructure\Models\RecetaEloquentModel;
use Src\Venta\Application\Services\VentaService;
use Src\Venta\Domain\Enums\EstadoVenta;
use Src\Venta\Domain\Enums\MetodoPago;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedUsers();
        $lunas = $this->seedLunas();
        $protecciones = $this->seedProtecciones();
        $marcos = $this->seedMarcos();
        $pacientes = $this->seedPacientes();
        $recetas = $this->seedRecetas($pacientes);
        $this->seedVentas($pacientes, $marcos, $lunas, $protecciones, $recetas);

        $this->command?->info('Datos de demostración creados.');
        $this->command?->info('Acceso: programador@optica.com / password');
    }

    private function seedUsers(): void
    {
        UserEloquentModel::create([
            'name' => 'Josué Programador',
            'email' => 'programador@optica.com',
            'password' => 'password',
            'role' => Role::Programador->value,
            'telefono' => '999111222',
            'activo' => true,
        ]);

        UserEloquentModel::create([
            'name' => 'María Fernández',
            'email' => 'dueno@optica.com',
            'password' => 'password',
            'role' => Role::Dueno->value,
            'telefono' => '999222333',
            'activo' => true,
        ]);

        UserEloquentModel::create([
            'name' => 'Carlos Ramírez',
            'email' => 'vendedor@optica.com',
            'password' => 'password',
            'role' => Role::Vendedor->value,
            'telefono' => '999333444',
            'activo' => true,
        ]);

        UserEloquentModel::create([
            'name' => 'Dra. Ana Torres',
            'email' => 'optometrista@optica.com',
            'password' => 'password',
            'role' => Role::Optometrista->value,
            'telefono' => '999444555',
            'activo' => true,
        ]);
    }

    /**
     * @return array<string, LunaEloquentModel>
     */
    private function seedLunas(): array
    {
        $data = [
            'monofocal' => ['nombre' => 'Monofocal estándar', 'tipo' => TipoLuna::Monofocal, 'precio' => 150, 'descripcion' => 'Lente de una sola distancia para visión lejana o cercana.'],
            'monofocal_premium' => ['nombre' => 'Monofocal premium', 'tipo' => TipoLuna::Monofocal, 'precio' => 240, 'descripcion' => 'Mayor nitidez y campo visual.'],
            'bifocal' => ['nombre' => 'Bifocal', 'tipo' => TipoLuna::Bifocal, 'precio' => 280, 'descripcion' => 'Dos zonas de visión: lejos y cerca.'],
            'progresivo' => ['nombre' => 'Progresiva', 'tipo' => TipoLuna::Progresivo, 'precio' => 450, 'descripcion' => 'Visión continua sin líneas visibles.'],
            'ocupacional' => ['nombre' => 'Ocupacional', 'tipo' => TipoLuna::Ocupacional, 'precio' => 340, 'descripcion' => 'Ideal para trabajo en computadora.'],
        ];

        $lunas = [];

        foreach ($data as $key => $item) {
            $lunas[$key] = LunaEloquentModel::create([
                'nombre' => $item['nombre'],
                'tipo' => $item['tipo']->value,
                'precio' => $item['precio'],
                'descripcion' => $item['descripcion'],
                'activo' => true,
            ]);
        }

        return $lunas;
    }

    /**
     * @return array<string, ProteccionEloquentModel>
     */
    private function seedProtecciones(): array
    {
        $data = [
            'antirreflejo' => ['nombre' => 'Antirreflejo', 'precio' => 80, 'descripcion' => 'Reduce reflejos y mejora la claridad nocturna.'],
            'luz_azul' => ['nombre' => 'Filtro luz azul', 'precio' => 110, 'descripcion' => 'Protege la vista del uso prolongado de pantallas.'],
            'fotocromatico' => ['nombre' => 'Fotocromático', 'precio' => 180, 'descripcion' => 'Los lentes se oscurecen con la luz solar.'],
            'polarizado' => ['nombre' => 'Polarizado', 'precio' => 150, 'descripcion' => 'Elimina el deslumbramiento.'],
            'endurecido' => ['nombre' => 'Endurecido', 'precio' => 60, 'descripcion' => 'Mayor resistencia a rayaduras.'],
            'hidrofobico' => ['nombre' => 'Hidrofóbico', 'precio' => 70, 'descripcion' => 'Repele el agua y facilita la limpieza.'],
        ];

        $protecciones = [];

        foreach ($data as $key => $item) {
            $protecciones[$key] = ProteccionEloquentModel::create([
                'nombre' => $item['nombre'],
                'precio' => $item['precio'],
                'descripcion' => $item['descripcion'],
                'activo' => true,
            ]);
        }

        return $protecciones;
    }

    /**
     * @return array<int, MarcoEloquentModel>
     */
    private function seedMarcos(): array
    {
        $data = [
            ['M-1001', 'Ray-Ban', 'RB2140 Wayfarer', 'Negro', 'Acetato', 'Rectangular', 'Adulto', 499, 6],
            ['M-1002', 'Ray-Ban', 'RB3025 Aviador', 'Dorado', 'Metal', 'Aviador', 'Adulto', 549, 3],
            ['M-1003', 'Oakley', 'Holbrook', 'Negro mate', 'O-Matter', 'Cuadrada', 'Adulto', 620, 1],
            ['M-1004', 'Vogue', 'VO5315', 'Carey', 'Acetato', 'Redonda', 'Adulto', 289, 8],
            ['M-1005', 'Carrera', '1132', 'Azul', 'Metal', 'Rectangular', 'Adulto', 459, 4],
            ['M-1006', 'Nike', '7171', 'Negro', 'TR90', 'Deportiva', 'Adulto', 390, 5],
            ['M-1007', 'Prada', 'PR 14WV', 'Negro', 'Acetato', 'Cuadrada', 'Adulto', 890, 2],
            ['M-1008', 'Tommy Hilfiger', 'TH 1798', 'Azul', 'Acetato', 'Rectangular', 'Adulto', 379, 7],
            ['M-1009', 'Ferragamo', 'SF2934', 'Havana', 'Acetato', 'Redonda', 'Adulto', 750, 1],
            ['M-1010', 'Guess', 'GU50125', 'Rosado', 'Acetato', 'Cuadrada', 'Adulto', 320, 6],
            ['M-1011', 'Miraflex', 'Mini', 'Azul', 'TR90', 'Redonda', 'Niño', 260, 9],
            ['M-1012', 'Nano Vista', 'Beta', 'Verde', 'TR90', 'Redonda', 'Niño', 240, 0],
            ['M-1013', 'Police', 'VPLF05', 'Negro', 'Metal', 'Rectangular', 'Adulto', 350, 5],
            ['M-1014', 'Adidas', 'SP0070', 'Negro', 'TR90', 'Deportiva', 'Adulto', 420, 3],
        ];

        $marcos = [];

        foreach ($data as $index => [$codigo, $marca, $modelo, $color, $material, $forma, $talla, $precio, $stock]) {
            $marcos[$index + 1] = MarcoEloquentModel::create([
                'codigo' => $codigo,
                'marca' => $marca,
                'modelo' => $modelo,
                'color' => $color,
                'material' => $material,
                'forma' => $forma,
                'talla' => $talla,
                'precio' => $precio,
                'stock' => $stock,
                'stock_minimo' => 2,
                'activo' => true,
            ]);
        }

        return $marcos;
    }

    /**
     * @return array<int, PacienteEloquentModel>
     */
    private function seedPacientes(): array
    {
        $data = [
            ['Lucía', 'Gonzales Ríos', '45871236', '1992-04-18', 'Femenino', '987654321', 'lucia.gonzales@example.com', 'Av. Los Álamos 245, Lima'],
            ['Jorge', 'Mendoza Salas', '41236587', '1985-11-02', 'Masculino', '986512347', 'jorge.mendoza@example.com', 'Jr. Puno 118, Lima'],
            ['Valeria', 'Castro Núñez', '73215489', '2001-07-25', 'Femenino', '955123789', 'valeria.castro@example.com', 'Calle Las Begonias 340, Surco'],
            ['Diego', 'Herrera Ponce', '09874561', '1978-02-14', 'Masculino', '944852136', 'diego.herrera@example.com', 'Av. Brasil 1290, Jesús María'],
            ['Rosa', 'Quispe Huamán', '10245789', '1966-09-30', 'Femenino', '933741258', null, 'Av. Grau 560, Callao'],
            ['Mateo', 'Rojas Delgado', '75412369', '2014-06-12', 'Masculino', '922614875', null, 'Av. Universitaria 1850, San Miguel'],
            ['Camila', 'Torres Vega', '72145896', '1998-12-05', 'Femenino', '911258746', 'camila.torres@example.com', 'Calle Cantuarias 210, Miraflores'],
            ['Andrés', 'Paredes Luna', '45128796', '1959-03-21', 'Masculino', '900147258', null, 'Av. Arequipa 3120, San Isidro'],
        ];

        $pacientes = [];

        foreach ($data as $index => [$nombres, $apellidos, $documento, $fecha, $sexo, $telefono, $email, $direccion]) {
            $pacientes[$index + 1] = PacienteEloquentModel::create([
                'nombres' => $nombres,
                'apellidos' => $apellidos,
                'tipo_documento' => 'DNI',
                'numero_documento' => $documento,
                'fecha_nacimiento' => $fecha,
                'sexo' => $sexo,
                'telefono' => $telefono,
                'email' => $email,
                'direccion' => $direccion,
                'activo' => true,
            ]);
        }

        return $pacientes;
    }

    /**
     * @param  array<int, PacienteEloquentModel>  $pacientes
     * @return array<int, RecetaEloquentModel>
     */
    private function seedRecetas(array $pacientes): array
    {
        $optometrista = UserEloquentModel::where('email', 'optometrista@optica.com')->firstOrFail();

        $data = [
            [1, '-1.25', '-0.50', 90, '20/25', '-1.00', '-0.75', 85, '20/25', 62.5, 'Uso de pantallas 8 horas al día.'],
            [2, '-2.50', '-1.25', 180, '20/40', '-2.25', '-1.00', 175, '20/30', 64.0, 'Se recomienda progresivo.'],
            [3, '-0.75', null, null, '20/20', '-1.00', '-0.25', 100, '20/25', 60.0, 'Primera evaluación.'],
            [5, '1.25', '-1.50', 95, '20/50', '1.00', '-1.75', 90, '20/40', 58.0, 'Paciente con presbicia.'],
            [6, '-3.00', '-0.50', 10, '20/80', '-3.25', '-0.75', 5, '20/80', 54.0, 'Control pediátrico.'],
        ];

        $recetas = [];

        foreach ($data as $index => [$pacienteKey, $odEsfera, $odCilindro, $odEje, $odAv, $oiEsfera, $oiCilindro, $oiEje, $oiAv, $dip, $observaciones]) {
            $recetas[$index + 1] = RecetaEloquentModel::create([
                'paciente_id' => $pacientes[$pacienteKey]->id,
                'optometrista_id' => $optometrista->id,
                'fecha' => now()->subDays(20 - ($index * 3))->toDateString(),
                'motivo_consulta' => 'Evaluación visual de control',
                'od_esfera' => $odEsfera,
                'od_cilindro' => $odCilindro,
                'od_eje' => $odEje,
                'od_adicion' => null,
                'od_av' => $odAv,
                'oi_esfera' => $oiEsfera,
                'oi_cilindro' => $oiCilindro,
                'oi_eje' => $oiEje,
                'oi_adicion' => null,
                'oi_av' => $oiAv,
                'dip' => $dip,
                'observaciones' => $observaciones,
            ]);
        }

        return $recetas;
    }

    /**
     * @param  array<int, PacienteEloquentModel>  $pacientes
     * @param  array<int, MarcoEloquentModel>  $marcos
     * @param  array<string, LunaEloquentModel>  $lunas
     * @param  array<string, ProteccionEloquentModel>  $protecciones
     * @param  array<int, RecetaEloquentModel>  $recetas
     */
    private function seedVentas(
        array $pacientes,
        array $marcos,
        array $lunas,
        array $protecciones,
        array $recetas,
    ): void {
        $vendedor = UserEloquentModel::where('email', 'vendedor@optica.com')->firstOrFail();
        $service = app(VentaService::class);

        $ventas = [
            [1, 1, 1, 'progresivo', ['antirreflejo', 'luz_azul'], 0, EstadoVenta::Pendiente, MetodoPago::Efectivo, 0],
            [2, 2, 0, 'monofocal', ['antirreflejo'], 30, EstadoVenta::Entregado, MetodoPago::Tarjeta, 0],
            [3, 4, 0, 'bifocal', ['fotocromatico'], 0, EstadoVenta::EnProceso, MetodoPago::YapePlin, 1],
            [5, 5, 4, 'ocupacional', ['polarizado', 'hidrofobico'], 0, EstadoVenta::Entregado, MetodoPago::Transferencia, 2],
            [6, 7, 5, 'progresivo', ['antirreflejo', 'hidrofobico'], 50, EstadoVenta::Pendiente, MetodoPago::Tarjeta, 4],
            [7, 10, 0, 'monofocal', ['endurecido'], 0, EstadoVenta::Entregado, MetodoPago::Efectivo, 6],
            [8, 13, 0, 'monofocal_premium', ['antirreflejo', 'luz_azul'], 0, EstadoVenta::Entregado, MetodoPago::YapePlin, 8],
        ];

        foreach ($ventas as [$pacienteKey, $marcoKey, $recetaKey, $lunaKey, $proteccionKeys, $descuento, $estado, $metodo, $diasAtras]) {
            $service->registrar([
                'paciente_id' => $pacientes[$pacienteKey]->id,
                'receta_id' => $recetaKey > 0 ? $recetas[$recetaKey]->id : null,
                'marco_id' => $marcos[$marcoKey]->id,
                'luna_id' => $lunas[$lunaKey]->id,
                'protecciones' => array_map(
                    fn (string $key): int => $protecciones[$key]->id,
                    $proteccionKeys,
                ),
                'cantidad' => 1,
                'descuento' => $descuento,
                'metodo_pago' => $metodo->value,
                'estado' => $estado->value,
                'fecha' => now()->subDays($diasAtras)->toDateString(),
                'notas' => null,
            ], $vendedor);
        }
    }
}
