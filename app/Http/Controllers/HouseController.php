<?php

namespace App\Http\Controllers;

use App\Models\House;
use App\Http\Controllers\Concerns\HandlesPictures;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HouseController extends Controller
{
    use HandlesPictures;

    // GET /houses?house_type_id=
    public function index(Request $request): JsonResponse
    {
        $houses = House::with('houseType')
            ->when($request->query('house_type_id'), fn ($q, $id) => $q->where('house_type_id', $id))
            ->paginate();

        return response()->json($houses);
    }

    // GET /houses/{house}
    public function show(House $house): JsonResponse
    {
        return response()->json($house->load('houseType'));
    }

    // POST /admin/houses
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'house_type_id' => 'required|exists:house_types,id',
            'name' => 'required|string|max:255',
            ...$this->pictureRules(),
            'capacity' => 'required|integer|min:1|max:65535',
            'price_hour' => 'required|numeric|min:0',
            'price_day' => 'required|numeric|min:0',
        ]);

        return response()->json(House::create($this->applyPicture($request, 'houses', null, $data))->load('houseType'), 201);
    }

    // PUT/PATCH /admin/houses/{house}
    public function update(Request $request, House $house): JsonResponse
    {
        $data = $request->validate([
            'house_type_id' => 'sometimes|exists:house_types,id',
            'name' => 'sometimes|string|max:255',
            ...$this->pictureRules(),
            'capacity' => 'sometimes|integer|min:1|max:65535',
            'price_hour' => 'sometimes|numeric|min:0',
            'price_day' => 'sometimes|numeric|min:0',
        ]);

        $house->update($this->applyPicture($request, 'houses', $house->picture, $data));

        return response()->json($house->load('houseType'));
    }

    // DELETE /admin/houses/{house}
    public function destroy(House $house): JsonResponse
    {
        if ($house->bookings()->active()->where('time_end', '>', now())->exists()) {
            return response()->json(['message' => 'У домика есть активные или будущие брони.'], 409);
        }

        $house->delete();

        return response()->json(null, 204);
    }
}
