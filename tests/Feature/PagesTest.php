<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\House;
use App\Models\HouseType;
use App\Models\Status;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();

        foreach (Status::SYSTEM as $name) {
            Status::create(['name' => $name]);
        }
    }

    private function user(bool $admin = false): User
    {
        $user = User::factory()->create();
        $user->is_admin = $admin;
        $user->save();

        return $user;
    }

    public function test_public_pages_render(): void
    {
        $type = HouseType::create(['name' => 'Bungalow']);
        $house = House::create([
            'house_type_id' => $type->id, 'name' => 'H1', 'capacity' => 4, 'price_hour' => 10, 'price_day' => 100,
        ]);

        $this->get('/')->assertOk()->assertSee('H1');
        $this->get("/houses/{$house->id}")->assertOk()->assertSee('войдите');
        $this->get('/login')->assertOk();
        $this->get('/register')->assertOk();
    }

    public function test_user_pages_require_login(): void
    {
        $this->get('/bookings')->assertRedirect('/login');

        $user = $this->user();
        $type = HouseType::create(['name' => 'Bungalow']);
        $house = House::create([
            'house_type_id' => $type->id, 'name' => 'H1', 'capacity' => 4, 'price_hour' => 10, 'price_day' => 100,
        ]);
        Booking::create([
            'user_id' => $user->id, 'house_id' => $house->id,
            'time_start' => now()->subDays(2), 'time_end' => now()->subDay(), 'total_price' => 100,
        ]);

        $this->actingAs($user)->get('/bookings')->assertOk()->assertSee('Отправить отзыв');
        $this->actingAs($user)->get("/houses/{$house->id}")->assertOk()->assertSee('Забронировать');
    }

    public function test_admin_pages_are_admin_only(): void
    {
        $paths = ['/admin', '/admin/reviews', '/admin/houses', '/admin/house-types', '/admin/addons', '/admin/statuses', '/admin/users'];

        foreach ($paths as $path) {
            $this->actingAs($this->user())->get($path)->assertForbidden();
        }

        $admin = $this->user(true);
        foreach ($paths as $path) {
            $this->actingAs($admin)->get($path)->assertOk();
        }

        $this->actingAs($admin)->get('/admin?status=cancelled&date_from=2026-01-01')->assertOk();
    }
}
