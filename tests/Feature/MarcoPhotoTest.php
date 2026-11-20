<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Src\Auth\Infrastructure\Models\UserEloquentModel;
use Src\Marco\Infrastructure\Models\MarcoEloquentModel;
use Tests\TestCase;

class MarcoPhotoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
    }

    /**
     * @return array<string, mixed>
     */
    private function marcoPayload(array $overrides = []): array
    {
        return [
            'codigo' => 'M-9001',
            'marca' => 'Ray-Ban',
            'modelo' => 'RB2140',
            'color' => 'Negro',
            'material' => 'Acetato',
            'forma' => 'Rectangular',
            'talla' => 'Adulto',
            'precio' => 499,
            'stock' => 5,
            'stock_minimo' => 2,
            'descripcion' => 'Marco de prueba',
            'activo' => true,
            ...$overrides,
        ];
    }

    private function foto(string $name = 'marco.png'): UploadedFile
    {
        $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==');

        return UploadedFile::fake()->createWithContent($name, $png);
    }

    public function test_admin_can_upload_a_photo_when_creating_a_marco(): void
    {
        $admin = UserEloquentModel::factory()->dueno()->create();

        $this->actingAs($admin)
            ->post(route('marcos.store'), $this->marcoPayload([
                'foto' => $this->foto('marco.png'),
            ]))
            ->assertRedirect(route('marcos.index'));

        $marco = MarcoEloquentModel::query()->firstOrFail();

        $this->assertNotNull($marco->foto);
        Storage::disk('public')->assertExists($marco->foto);
        $this->assertStringContainsString('/storage/', (string) $marco->foto_url);
    }

    public function test_replacing_the_photo_deletes_the_previous_file(): void
    {
        $admin = UserEloquentModel::factory()->dueno()->create();

        $this->actingAs($admin)->post(route('marcos.store'), $this->marcoPayload([
            'foto' => $this->foto('original.png'),
        ]));

        $marco = MarcoEloquentModel::query()->firstOrFail();
        $fotoAnterior = $marco->foto;

        $this->actingAs($admin)->put(route('marcos.update', $marco), $this->marcoPayload([
            'foto' => $this->foto('nueva.png'),
        ]))->assertRedirect(route('marcos.index'));

        $marco->refresh();

        $this->assertNotSame($fotoAnterior, $marco->foto);
        Storage::disk('public')->assertMissing($fotoAnterior);
        Storage::disk('public')->assertExists($marco->foto);
    }

    public function test_admin_can_remove_the_photo(): void
    {
        $admin = UserEloquentModel::factory()->dueno()->create();

        $this->actingAs($admin)->post(route('marcos.store'), $this->marcoPayload([
            'foto' => $this->foto('marco.png'),
        ]));

        $marco = MarcoEloquentModel::query()->firstOrFail();
        $foto = $marco->foto;

        $this->actingAs($admin)->put(route('marcos.update', $marco), $this->marcoPayload([
            'eliminar_foto' => true,
        ]))->assertRedirect(route('marcos.index'));

        $this->assertNull($marco->fresh()->foto);
        Storage::disk('public')->assertMissing($foto);
    }

    public function test_only_images_are_accepted_as_photo(): void
    {
        $admin = UserEloquentModel::factory()->dueno()->create();

        $this->actingAs($admin)
            ->post(route('marcos.store'), $this->marcoPayload([
                'foto' => UploadedFile::fake()->create('documento.pdf', 120, 'application/pdf'),
            ]))
            ->assertSessionHasErrors('foto');

        $this->assertDatabaseCount('marcos', 0);
    }

    public function test_vendedor_cannot_upload_frame_photos(): void
    {
        $vendedor = UserEloquentModel::factory()->vendedor()->create();

        $this->actingAs($vendedor)
            ->post(route('marcos.store'), $this->marcoPayload([
                'foto' => $this->foto('marco.png'),
            ]))
            ->assertForbidden();

        $this->assertDatabaseCount('marcos', 0);
    }
}
