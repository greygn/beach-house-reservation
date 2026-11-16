<?php

namespace Tests\Feature;

use App\Models\House;
use App\Models\HouseType;
use App\Models\Service;
use App\Models\Shift;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class BookingApprovalTest extends TestCase
{
    use RefreshDatabase;

    public function test_booking_needs_approval_staff_assignment_and_payment(): void
    {
        $admin = User::factory()->create();
        $admin->is_admin = true;
        $admin->save();

        $type = HouseType::create(['name' => 'Type']);
        $house = House::create(['house_type_id' => $type->id, 'name' => 'H', 'capacity' => 4, 'price_hour' => 10, 'price_day' => 100]);
        $massage = Service::create(['name' => 'Массаж', 'price_hour' => 50]);

        Sanctum::actingAs($admin);
        $staffId = $this->postJson('/api/admin/staff', [
            'name' => 'Masseur', 'email' => 'm@example.com', 'phone' => '1', 'password' => 'password1',
        ])->assertCreated()->json('id');
        $this->postJson('/api/admin/shifts', [
            'user_id' => $staffId, 'time_start' => now()->addDay()->setTime(8, 0)->toDateTimeString(), 'time_end' => now()->addDay()->setTime(20, 0)->toDateTimeString(),
        ])->assertCreated();

        $guest = User::factory()->create();
        Sanctum::actingAs($guest);
        $day = now()->addDay();
        $bookingId = $this->postJson('/api/bookings', [
            'house_id' => $house->id,
            'time_start' => $day->copy()->setTime(9, 0)->toDateTimeString(),
            'time_end' => $day->copy()->setTime(18, 0)->toDateTimeString(),
            'guests' => 2,
            'services' => [['service_id' => $massage->id]],
        ])->assertCreated()->assertJsonPath('status', 'pending')->assertJsonPath('total_price', '540.00')->json('id');

        $this->postJson("/api/bookings/{$bookingId}/pay", [])->assertStatus(409);

        Sanctum::actingAs($admin);
        $slot = \App\Models\BookingService::first();
        $this->patchJson("/api/admin/bookings/{$bookingId}/approve", [])->assertStatus(422);
        $this->patchJson("/api/admin/bookings/{$bookingId}/approve", ['assignments' => [$slot->id => $staffId]])
            ->assertOk()->assertJsonPath('status', 'active');

        Sanctum::actingAs($guest);
        $this->postJson("/api/bookings/{$bookingId}/pay", [
            'card_number' => '4242 4242 4242 4242', 'card_holder' => 'A B', 'expiry' => '08/29', 'cvc' => '123',
        ])->assertOk();

        Sanctum::actingAs(User::find($staffId));
        $this->getJson('/api/staff/schedule')->assertOk()->assertJsonCount(1, 'assignments')->assertJsonPath('assignments.0.booking.guests', 2);
        $this->getJson('/api/admin/staff')->assertForbidden();
    }
}
