<?php

namespace Tests\Feature;

use App\Models\Addon;
use App\Models\Booking;
use App\Models\House;
use App\Models\HouseType;
use App\Models\Status;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class BookingRulesTest extends TestCase
{
    use RefreshDatabase;

    private House $house;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (Status::SYSTEM as $name) {
            Status::create(['name' => $name]);
        }

        $type = HouseType::create(['name' => 'Bungalow']);
        $this->house = House::create([
            'house_type_id' => $type->id, 'name' => 'H1', 'capacity' => 4,
            'price_hour' => 10, 'price_day' => 100,
        ]);
    }

    private function user(bool $admin = false): User
    {
        $user = User::create([
            'name' => 'U', 'email' => uniqid() . '@x.test', 'phone' => '1', 'password' => 'password',
        ]);
        $user->is_admin = $admin;
        $user->save();

        return $user;
    }

    private function book(User $user, string $start, string $end, array $addons = [])
    {
        Sanctum::actingAs($user);

        return $this->postJson('/api/bookings', [
            'house_id' => $this->house->id,
            'time_start' => now()->addDays(1)->setTimeFromTimeString($start)->toDateTimeString(),
            'time_end' => now()->addDays(1)->setTimeFromTimeString($end)->toDateTimeString(),
            'addons' => $addons,
        ]);
    }

    public function test_overlapping_booking_is_rejected_but_adjacent_is_allowed(): void
    {
        $this->book($this->user(), '10:00', '12:00')->assertCreated();
        $this->book($this->user(), '11:00', '13:00')->assertUnprocessable();
        $this->book($this->user(), '12:00', '14:00')->assertCreated();
    }

    public function test_addon_stock_is_enforced_per_period(): void
    {
        $addon = Addon::create(['addon_name' => 'A', 'quantity' => 2, 'price_hour' => 5]);
        $payload = [['addon_id' => $addon->id]];

        $house2 = House::create([
            'house_type_id' => $this->house->house_type_id, 'name' => 'H2', 'capacity' => 2,
            'price_hour' => 10, 'price_day' => 100,
        ]);

        $this->book($this->user(), '10:00', '12:00', $payload)->assertCreated();
        // second house, same period, second addon unit
        $this->house = $house2;
        $this->book($this->user(), '10:00', '12:00', $payload)->assertCreated();
        // third: none left in that period
        $this->book($this->user(), '11:00', '13:00', $payload)->assertUnprocessable();
        // later period is free again
        $this->book($this->user(), '14:00', '16:00', $payload)->assertCreated();
    }

    public function test_price_includes_addons(): void
    {
        $addon = Addon::create(['addon_name' => 'A', 'quantity' => 1, 'price_hour' => 5]);

        $this->book($this->user(), '10:00', '12:00', [['addon_id' => $addon->id]])
            ->assertCreated()
            ->assertJsonPath('total_price', '30.00'); // 2h * 10 + 2h * 5
    }

    public function test_addon_quantity_cannot_drop_below_reserved(): void
    {
        $addon = Addon::create(['addon_name' => 'A', 'quantity' => 2, 'price_hour' => 5]);
        $this->book($this->user(), '10:00', '12:00', [['addon_id' => $addon->id]])->assertCreated();

        Sanctum::actingAs($this->user(true));
        $this->patchJson("/api/admin/addons/{$addon->id}", ['quantity' => 1])->assertOk();
        $this->patchJson("/api/admin/addons/{$addon->id}", ['quantity' => 0])->assertUnprocessable();
    }

    public function test_delete_house_and_addon_blocked_by_future_booking(): void
    {
        $addon = Addon::create(['addon_name' => 'A', 'quantity' => 2, 'price_hour' => 5]);
        $this->book($this->user(), '10:00', '12:00', [['addon_id' => $addon->id]])->assertCreated();

        Sanctum::actingAs($this->user(true));
        $this->deleteJson("/api/admin/houses/{$this->house->id}")->assertStatus(409);
        $this->deleteJson("/api/admin/addons/{$addon->id}")->assertStatus(409);
    }

    public function test_review_requires_finished_own_booking_and_admin_moderates(): void
    {
        $owner = $this->user();
        $booking = Booking::create([
            'user_id' => $owner->id, 'house_id' => $this->house->id,
            'time_start' => now()->subDays(2), 'time_end' => now()->subDays(1), 'total_price' => 100,
        ]);
        $future = Booking::create([
            'user_id' => $owner->id, 'house_id' => $this->house->id,
            'time_start' => now()->addDays(2), 'time_end' => now()->addDays(3), 'total_price' => 100,
        ]);

        Sanctum::actingAs($this->user());
        $this->postJson("/api/bookings/{$booking->id}/review", ['rating' => 5])->assertForbidden();

        Sanctum::actingAs($owner);
        $this->postJson("/api/bookings/{$future->id}/review", ['rating' => 5])->assertUnprocessable();
        $this->postJson("/api/bookings/{$booking->id}/review", ['rating' => 5])->assertCreated();
        $this->postJson("/api/bookings/{$booking->id}/review", ['rating' => 4])->assertUnprocessable();

        $this->getJson("/api/houses/{$this->house->id}/reviews")->assertJsonCount(0, 'data');

        Sanctum::actingAs($owner);
        $this->patchJson('/api/admin/reviews/1/status', ['status_id' => 2])->assertForbidden();

        Sanctum::actingAs($this->user(true));
        $published = Status::where('name', Status::PUBLISHED)->first();
        $this->patchJson('/api/admin/reviews/1/status', ['status_id' => $published->id])->assertOk();
        $this->getJson("/api/houses/{$this->house->id}/reviews")->assertJsonCount(1, 'data');
    }

    public function test_cancel_changes_status_and_frees_house_and_addons(): void
    {
        $addon = Addon::create(['addon_name' => 'A', 'quantity' => 1, 'price_hour' => 5]);
        $payload = [['addon_id' => $addon->id]];

        $id = $this->book($this->user(), '10:00', '12:00', $payload)->assertCreated()->json('id');
        $this->book($this->user(), '10:00', '12:00')->assertUnprocessable();

        // someone else's booking
        Sanctum::actingAs($this->user());
        $this->patchJson("/api/bookings/{$id}/cancel")->assertForbidden();
        $this->patchJson("/api/admin/bookings/{$id}/cancel")->assertForbidden();

        // owner cancels own booking
        Sanctum::actingAs(Booking::find($id)->user);
        $this->patchJson("/api/bookings/{$id}/cancel")->assertOk()->assertJsonPath('status', 'cancelled');
        $this->patchJson("/api/bookings/{$id}/cancel")->assertStatus(409);
        $this->assertDatabaseHas('bookings', ['id' => $id, 'status' => 'cancelled']);

        $this->book($this->user(), '10:00', '12:00', $payload)->assertCreated();
    }

    public function test_admin_booking_filters(): void
    {
        $this->book($this->user(), '10:00', '12:00')->assertCreated();
        $admin = $this->user(true);
        Sanctum::actingAs($admin);

        $date = now()->addDay()->toDateString();
        $this->getJson("/api/admin/bookings?house_id={$this->house->id}&date_from={$date}&date_to={$date}")
            ->assertJsonCount(1, 'data');
        $this->getJson('/api/admin/bookings?house_id=999')->assertJsonCount(0, 'data');
        $this->getJson('/api/admin/bookings?date_from=' . now()->addDays(5)->toDateString())->assertJsonCount(0, 'data');
    }
}
