<?php

namespace App\Http\Controllers;

use App\Models\Status;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StatusController extends Controller
{
    // GET /admin/statuses
    public function index(): JsonResponse
    {
        return response()->json(Status::all());
    }

    // GET /admin/statuses/{status}
    public function show(Status $status): JsonResponse
    {
        return response()->json($status);
    }

    // POST /admin/statuses
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:statuses,name',
            'description' => 'nullable|string',
        ]);

        return response()->json(Status::create($data), 201);
    }

    // PUT/PATCH /admin/statuses/{status}
    public function update(Request $request, Status $status): JsonResponse
    {
        $data = $request->validate([
            'name' => 'sometimes|string|max:255|unique:statuses,name,' . $status->id,
            'description' => 'nullable|string',
        ]);

        if (isset($data['name']) && in_array($status->name, Status::SYSTEM, true) && $data['name'] !== $status->name) {
            return response()->json(['message' => 'Системный статус нельзя переименовать.'], 409);
        }

        $status->update($data);

        return response()->json($status);
    }

    // DELETE /admin/statuses/{status}
    public function destroy(Status $status): JsonResponse
    {
        if (in_array($status->name, Status::SYSTEM, true)) {
            return response()->json(['message' => 'Системный статус нельзя удалить.'], 409);
        }

        if ($status->reviews()->exists()) {
            return response()->json(['message' => 'Статус используется в отзывах.'], 409);
        }

        $status->delete();

        return response()->json(null, 204);
    }
}
