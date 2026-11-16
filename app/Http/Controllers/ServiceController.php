<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    // GET /services
    public function index(): JsonResponse
    {
        return response()->json(Service::orderBy('name')->get());
    }

    // GET /admin/services/{service}
    public function show(Service $service): JsonResponse
    {
        return response()->json($service);
    }

    // POST /admin/services
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'price_hour' => 'required|numeric|min:0',
        ]);

        return response()->json(Service::create($data), 201);
    }

    // PUT/PATCH /admin/services/{service}
    public function update(Request $request, Service $service): JsonResponse
    {
        $service->update($request->validate([
            'name' => 'sometimes|string|max:255',
            'price_hour' => 'sometimes|numeric|min:0',
        ]));

        return response()->json($service);
    }

    // DELETE /admin/services/{service}
    public function destroy(Service $service): JsonResponse
    {
        if ($service->hasFutureBookings()) {
            return response()->json(['message' => 'Услуга используется в ожидающих или подтверждённых будущих бронях.'], 409);
        }

        $service->delete();

        return response()->json(null, 204);
    }
}
