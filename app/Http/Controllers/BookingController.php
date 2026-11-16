<?php

namespace App\Http\Controllers;

use App\Enums\BookingStatus;
use App\Models\Addon;
use App\Models\Booking;
use App\Models\House;
use App\Models\Service;
use App\Models\User;
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
            ->with(['house', 'addons', 'services.service', 'review'])
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

        $bookings = Booking::with(['user', 'house', 'addons', 'services.service', 'review'])
            ->filter($filters)
            ->orderBy('time_start')
            ->paginate();

        return response()->json($bookings);
    }

    // GET /bookings/{booking}
    public function show(Request $request, Booking $booking): JsonResponse
    {
        abort_unless($booking->user_id === $request->user()->id || $request->user()->is_admin, 403);

        return response()->json($booking->load(['house', 'addons', 'services.service', 'review']));
    }

    // POST /bookings
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'house_id' => ['required', Rule::exists('houses', 'id')->whereNull('deleted_at')],
            'time_start' => 'required|date|after:now',
            'time_end' => 'required|date|after:time_start',
            'guests' => 'required|integer|min:1',
            'services' => 'sometimes|array',
            'services.*.service_id' => ['required', Rule::exists('services', 'id')->whereNull('deleted_at')],
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
                    'time_start' => 'Домик уже забронирован на этот период.',
                ]);
            }

            if ($data['guests'] > $house->capacity) {
                throw ValidationException::withMessages([
                    'guests' => "Вместимость домика: {$house->capacity} чел.",
                ]);
            }

            $hours = (int) ceil($from->diffInMinutes($to, true) / 60);
            $services = Service::whereIn('id', collect($data['services'] ?? [])->pluck('service_id'))->get()->keyBy('id');
            $servicesPrice = collect($data['services'] ?? [])->sum(fn ($s) => $services[$s['service_id']]->price_hour * $hours);

            $addons = Addon::whereIn('id', $requested->keys())->orderBy('id')->lockForUpdate()->get();
            $addonsPrice = 0;

            foreach ($addons as $addon) {
                $qty = $requested[$addon->id];
                $available = $addon->availableBetween($from, $to);

                if ($qty > $available) {
                    throw ValidationException::withMessages([
                        'addons' => "Аддон «{$addon->addon_name}» недоступен на этот период (доступно: {$available}).",
                    ]);
                }

                $addonsPrice += $addon->price_hour * $hours * $qty;
            }

            $booking = Booking::create([
                'user_id' => $request->user()->id,
                'house_id' => $house->id,
                'time_start' => $from,
                'time_end' => $to,
                'guests' => $data['guests'],
                'status' => BookingStatus::Pending,
                'total_price' => round($this->housePrice($house, $hours) + $addonsPrice + $servicesPrice, 2),
            ]);

            foreach ($data['services'] ?? [] as $s) {
                $booking->services()->create([
                    'service_id' => $s['service_id'],
                    'time_start' => $from,
                    'time_end' => $to,
                    'price' => $services[$s['service_id']]->price_hour * $hours,
                ]);
            }

            $booking->addons()->attach(
                $requested->map(fn ($qty) => ['quantity' => $qty])->all()
            );

            return $booking;
        });

        return response()->json($booking->load(['house', 'addons', 'services.service']), 201);
    }

    // PATCH /admin/bookings/{booking}/approve: assignments = [booking_service_id => staff user_id]
    public function approve(Request $request, Booking $booking): JsonResponse
    {
        $data = $request->validate([
            'assignments' => 'sometimes|array',
            'assignments.*' => ['required', Rule::exists('users', 'id')->where('is_staff', true)],
        ]);

        DB::transaction(function () use ($booking, $data) {
            $house = House::lockForUpdate()->findOrFail($booking->house_id);
            $booking = Booking::lockForUpdate()->findOrFail($booking->id);

            if ($booking->status !== BookingStatus::Pending || $booking->time_end->isPast()) {
                throw ValidationException::withMessages(['booking' => 'Подтвердить можно только ожидающую бронь.']);
            }

            $overlap = $house->bookings()
                ->active()
                ->where('time_start', '<', $booking->time_end)
                ->where('time_end', '>', $booking->time_start)
                ->exists();

            if ($overlap) {
                throw ValidationException::withMessages(['booking' => 'Домик уже занят на этот период.']);
            }

            foreach ($booking->addons as $addon) {
                $available = $addon->availableBetween($booking->time_start, $booking->time_end);

                if ($addon->pivot->quantity > $available) {
                    throw ValidationException::withMessages([
                        'booking' => "Аддон «{$addon->addon_name}» недоступен на этот период (доступно: {$available}).",
                    ]);
                }
            }

            $booking->update(['status' => BookingStatus::Active]);

            foreach ($booking->services as $slot) {
                $staff = User::find($data['assignments'][$slot->id] ?? null);

                if (! $staff || ! $slot->canBeTakenBy($staff)) {
                    throw ValidationException::withMessages([
                        'assignments' => "Назначьте на услугу «{$slot->service->name}» свободного сотрудника, чья смена покрывает весь период брони.",
                    ]);
                }

                $slot->update(['user_id' => $staff->id]);
            }
        });

        return response()->json($booking->refresh()->load(['house', 'addons', 'services.service', 'services.staff']));
    }

    // POST /bookings/{booking}/pay: stub, card data is validated and discarded.
    public function pay(Request $request, Booking $booking): JsonResponse
    {
        abort_unless($booking->user_id === $request->user()->id, 403);

        if ($booking->status !== BookingStatus::Active) {
            return response()->json(['message' => 'Оплатить можно только подтверждённую бронь.'], 409);
        }

        if ($booking->paid_at) {
            return response()->json(['message' => 'Бронь уже оплачена.'], 409);
        }

        $request->validate([
            'card_number' => ['required', 'regex:/^\d{4}( ?\d{4}){3}$/'],
            'card_holder' => 'required|string|max:100',
            'expiry' => ['required', 'regex:/^(0[1-9]|1[0-2])\/\d{2}$/'],
            'cvc' => ['required', 'regex:/^\d{3}$/'],
        ]);

        $booking->update(['paid_at' => now()]);

        return response()->json($booking);
    }

    // PATCH /bookings/{booking}/cancel (owner) and /admin/bookings/{booking}/cancel (admin).
    public function cancel(Request $request, Booking $booking): JsonResponse
    {
        abort_unless($booking->user_id === $request->user()->id || $request->user()->is_admin, 403);

        if ($booking->status === BookingStatus::Cancelled) {
            return response()->json(['message' => 'Бронь уже отменена.'], 409);
        }

        if ($booking->time_end->isPast()) {
            return response()->json(['message' => 'Завершённую бронь нельзя отменить.'], 409);
        }

        $booking->update(['status' => BookingStatus::Cancelled]);

        return response()->json($booking->load(['house', 'addons']));
    }

    private function housePrice(House $house, int $hours): float
    {
        $days = intdiv($hours, 24);
        $rest = $hours % 24;

        return $days * $house->price_day + $rest * $house->price_hour;
    }
}
