<?php

namespace App\Http\Controllers;

use App\Enums\BookingStatus;
use App\Models\Addon;
use App\Models\Booking;
use App\Models\House;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class BookingController extends Controller
{
    // GET /bookings (own)
    public function index(Request $request): JsonResponse
    {
        $bookings = $request->user()->bookings()
            ->with(['house', 'addons', 'review'])
            ->latest('time_start')
            ->paginate();

        return response()->json($bookings);
    }

    // GET /admin/bookings?house_id=&date_from=&date_to=
    public function adminIndex(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'house_id' => 'sometimes|integer',
            'date_from' => 'sometimes|date',
            'date_to' => 'sometimes|date|after_or_equal:date_from',
            'status' => ['sometimes', Rule::enum(BookingStatus::class)],
        ]);

        $bookings = Booking::with(['user', 'house', 'addons', 'review'])
            ->filter($filters)
            ->orderBy('time_start')
            ->paginate();

        return response()->json($bookings);
    }

    // GET /bookings/{booking}
    public function show(Request $request, Booking $booking): JsonResponse
    {
        abort_unless($booking->user_id === $request->user()->id || $request->user()->is_admin, 403);

        return response()->json($booking->load(['house', 'addons', 'review']));
    }

    // POST /bookings
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'house_id' => ['required', Rule::exists('houses', 'id')->whereNull('deleted_at')],
            'time_start' => 'required|date|after:now',
            'time_end' => 'required|date|after:time_start',
            'addons' => 'sometimes|array',
            'addons.*.addon_id' => ['required', 'distinct', Rule::exists('addons', 'id')->whereNull('deleted_at')],
            'addons.*.quantity' => 'sometimes|integer|min:1',
        ]);

        $from = Carbon::parse($data['time_start']);
        $to = Carbon::parse($data['time_end']);
        $requested = collect($data['addons'] ?? [])
            ->mapWithKeys(fn ($a) => [(int) $a['addon_id'] => (int) ($a['quantity'] ?? 1)]);

        $booking = DB::transaction(function () use ($request, $data, $from, $to, $requested) {
            // Lock house row: concurrent bookings of one house are serialized.
            $house = House::lockForUpdate()->findOrFail($data['house_id']);

            $overlap = $house->bookings()
                ->active()
                ->where('time_start', '<', $to)
                ->where('time_end', '>', $from)
                ->exists();

            if ($overlap) {
                throw ValidationException::withMessages([
                    'time_start' => 'House is already booked for this period.',
                ]);
            }

            // Lock addons in id order (no deadlocks), then check stock for the period.
            $addons = Addon::whereIn('id', $requested->keys())->orderBy('id')->lockForUpdate()->get();
            $addonsPrice = 0;
            $hours = (int) ceil($from->diffInMinutes($to, true) / 60);

            foreach ($addons as $addon) {
                $qty = $requested[$addon->id];
                $available = $addon->availableBetween($from, $to);

                if ($qty > $available) {
                    throw ValidationException::withMessages([
                        'addons' => "Addon \"{$addon->addon_name}\" unavailable for this period (available: {$available}).",
                    ]);
                }

                $addonsPrice += $addon->price_hour * $hours * $qty;
            }

            $booking = Booking::create([
                'user_id' => $request->user()->id,
                'house_id' => $house->id,
                'time_start' => $from,
                'time_end' => $to,
                'total_price' => round($this->housePrice($house, $hours) + $addonsPrice, 2),
            ]);

            $booking->addons()->attach(
                $requested->map(fn ($qty) => ['quantity' => $qty])->all()
            );

            return $booking;
        });

        return response()->json($booking->load(['house', 'addons']), 201);
    }

    // PATCH /bookings/{booking}/cancel (owner) and /admin/bookings/{booking}/cancel (admin).
    // Row stays, status changes.
    public function cancel(Request $request, Booking $booking): JsonResponse
    {
        abort_unless($booking->user_id === $request->user()->id || $request->user()->is_admin, 403);

        if ($booking->status === BookingStatus::Cancelled) {
            return response()->json(['message' => 'Booking is already cancelled.'], 409);
        }

        if ($booking->time_end->isPast()) {
            return response()->json(['message' => 'Finished booking cannot be cancelled.'], 409);
        }

        $booking->update(['status' => BookingStatus::Cancelled]);

        return response()->json($booking->load(['house', 'addons']));
    }

    // Full days at day price; leftover hours at hour price, capped by day price.
    private function housePrice(House $house, int $hours): float
    {
        $days = intdiv($hours, 24);
        $rest = $hours % 24;

        return $days * $house->price_day + min($rest * $house->price_hour, $house->price_day);
    }
}
