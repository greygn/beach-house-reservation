<?php

namespace Tests\Feature;

use App\Models\Addon;
use App\Models\House;
use App\Models\HouseType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PicturesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        $admin = User::factory()->create();
        $admin->is_admin = true;
        $admin->save();
        Sanctum::actingAs($admin);
    }

    // 1x1 PNG: UploadedFile::fake()->image() needs the GD extension.
    private function png(string $name): UploadedFile
    {
        $bytes = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==');

        return UploadedFile::fake()->createWithContent($name, $bytes);
    }

    private function housePayload(array $extra = []): array
    {
        return $extra + [
            'house_type_id' => HouseType::firstOrCreate(['name' => 'Bungalow'])->id,
            'name' => 'H1',
            'capacity' => 4,
            'price_hour' => 10,
            'price_day' => 100,
        ];
    }

    public function test_house_picture_is_uploaded_replaced_and_removed(): void
    {
        $response = $this->post('/api/admin/houses', $this->housePayload([
            'picture' => $this->png('a.png'),
        ]), ['Accept' => 'application/json'])->assertCreated();

        $house = House::find($response->json('id'));
        Storage::disk('public')->assertExists($house->picture);
        $first = $house->picture;

        // PATCH with a file goes as POST + _method (PHP does not parse multipart PATCH).
        $this->post("/api/admin/houses/{$house->id}", [
            '_method' => 'PATCH',
            'picture' => $this->png('b.png'),
        ], ['Accept' => 'application/json'])->assertOk();

        $house->refresh();
        Storage::disk('public')->assertMissing($first);
        Storage::disk('public')->assertExists($house->picture);
        $second = $house->picture;

        // Update without a file keeps the picture.
        $this->patchJson("/api/admin/houses/{$house->id}", ['name' => 'H2'])->assertOk();
        $this->assertSame($second, $house->fresh()->picture);

        $this->post("/api/admin/houses/{$house->id}", [
            '_method' => 'PATCH',
            'remove_picture' => '1',
        ], ['Accept' => 'application/json'])->assertOk();

        $this->assertNull($house->fresh()->picture);
        Storage::disk('public')->assertMissing($second);
    }

    public function test_non_image_is_rejected(): void
    {
        $this->post('/api/admin/houses', $this->housePayload([
            'picture' => UploadedFile::fake()->create('doc.pdf', 10, 'application/pdf'),
        ]), ['Accept' => 'application/json'])->assertUnprocessable();
    }

    public function test_addon_picture_is_uploaded(): void
    {
        $response = $this->post('/api/admin/addons', [
            'addon_name' => 'Umbrella',
            'quantity' => 2,
            'price_hour' => 5,
            'picture' => $this->png('u.png'),
        ], ['Accept' => 'application/json'])->assertCreated();

        Storage::disk('public')->assertExists(Addon::find($response->json('id'))->picture);
        $this->assertNotNull($response->json('picture_url'));
    }
}
