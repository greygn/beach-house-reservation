<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StaffController extends Controller
{
    // GET /admin/staff
    public function index(): JsonResponse
    {
        return response()->json(User::where('is_staff', true)->with('shifts')->paginate());
    }

    // POST /admin/staff
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'phone' => 'required|string|max:20',
            'password' => 'required|string|min:8',
        ]);

        $staff = new User($data);
        $staff->is_staff = true;
        $staff->save();

        return response()->json($staff, 201);
    }

    // GET /staff/schedule: own shifts and assigned services of confirmed bookings
    public function schedule(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'shifts' => $user->shifts()->where('time_end', '>', now())->orderBy('time_start')->get(),
            'assignments' => $user->assignments()
                ->whereHas('booking', fn ($q) => $q->active())
                ->where('time_end', '>', now())
                ->with(['service', 'booking.house'])
                ->orderBy('time_start')
                ->get(),
        ]);
    }
}
