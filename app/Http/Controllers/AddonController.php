<?php

namespace App\Http\Controllers;

use App\Models\Addon;
use App\Http\Controllers\Concerns\HandlesPictures;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class AddonController extends Controller
{
    use HandlesPictures;

    // GET /addons
    public function index(): JsonResponse
    {
        return response()->json(Addon::all());
    }

    // GET /addons/availability?time_start=&time_end=
    public function availability(Request $request): JsonResponse
    {
        $data = $request->validate([
            'time_start' => 'required|date',
            'time_end' => 'required|date|after:time_start',
        ]);

        $from = Carbon::parse($data['time_start']);
        $to = Carbon::parse($data['time_end']);

        $addons = Addon::all()->map(fn (Addon $addon) => [
            'id' => $addon->id,
            'addon_name' => $addon->addon_name,
            'price_hour' => $addon->price_hour,
            'quantity' => $addon->quantity,
            'available' => $addon->availableBetween($from, $to),
        ]);

        return response()->json($addons);
    }

    // GET /admin/addons/{addon}
    public function show(Addon $addon): JsonResponse
    {
        return response()->json($addon);
    }

    // POST /admin/addons
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'addon_name' => 'required|string|max:255',
            ...$this->pictureRules(),
            'quantity' => 'required|integer|min:0',
            'price_hour' => 'required|numeric|min:0',
        ]);

        return response()->json(Addon::create($this->applyPicture($request, 'addons', null, $data)), 201);
    }

    // PUT/PATCH /admin/addons/{addon}
    public function update(Request $request, Addon $addon): JsonResponse
    {
        $data = $request->validate([
            'addon_name' => 'sometimes|string|max:255',
            ...$this->pictureRules(),
            'quantity' => 'sometimes|integer|min:0',
            'price_hour' => 'sometimes|numeric|min:0',
        ]);

        // Quantity cannot drop below the peak already promised to unfinished bookings.
        if (isset($data['quantity']) && $data['quantity'] < $addon->quantity) {
            $reserved = $addon->peakReservedFuture();

            if ($data['quantity'] < $reserved) {
                return response()->json([
                    'message' => "Quantity cannot be below reserved amount ({$reserved}).",
                ], 422);
            }
        }

        $addon->update($this->applyPicture($request, 'addons', $addon->picture, $data));

        return response()->json($addon);
    }

    // DELETE /admin/addons/{addon}
    public function destroy(Addon $addon): JsonResponse
    {
        if ($addon->hasFutureBookings()) {
            return response()->json(['message' => 'Addon is used in active or future bookings.'], 409);
        }

        // Soft delete: past bookings keep their addon.
        $addon->delete();

        return response()->json(null, 204);
    }
}
