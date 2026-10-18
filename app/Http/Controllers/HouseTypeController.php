<?php

namespace App\Http\Controllers;

use App\Models\HouseType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HouseTypeController extends Controller
{
    // GET /house-types
    public function index(): JsonResponse
    {
        return response()->json(HouseType::all());
    }

    // GET /admin/house-types/{house_type}
    public function show(HouseType $houseType): JsonResponse
    {
        return response()->json($houseType);
    }

    // POST /admin/house-types
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:house_types,name',
            'description' => 'nullable|string',
        ]);

        return response()->json(HouseType::create($data), 201);
    }

    // PUT/PATCH /admin/house-types/{house_type}
    public function update(Request $request, HouseType $houseType): JsonResponse
    {
        $data = $request->validate([
            'name' => 'sometimes|string|max:255|unique:house_types,name,' . $houseType->id,
            'description' => 'nullable|string',
        ]);

        $houseType->update($data);

        return response()->json($houseType);
    }

    // DELETE /admin/house-types/{house_type}
    public function destroy(HouseType $houseType): JsonResponse
    {
        // FK is restrictOnDelete, so soft-deleted houses block it too.
        if ($houseType->houses()->withTrashed()->exists()) {
            return response()->json(['message' => 'Type is used by houses.'], 409);
        }

        $houseType->delete();

        return response()->json(null, 204);
    }
}
