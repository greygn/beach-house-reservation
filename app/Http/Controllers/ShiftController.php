<?php

namespace App\Http\Controllers;

use App\Models\Shift;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ShiftController extends Controller
{
    // POST /admin/shifts
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'user_id' => ['required', Rule::exists('users', 'id')->where('is_staff', true)],
            'time_start' => 'required|date',
            'time_end' => 'required|date|after:time_start',
        ]);

        $overlap = Shift::where('user_id', $data['user_id'])
            ->where('time_start', '<', $data['time_end'])
            ->where('time_end', '>', $data['time_start'])
            ->exists();

        if ($overlap) {
            throw ValidationException::withMessages(['time_start' => 'Смена пересекается с другой сменой сотрудника.']);
        }

        return response()->json(Shift::create($data), 201);
    }

    // DELETE /admin/shifts/{shift}
    public function destroy(Shift $shift): JsonResponse
    {
        $assigned = $shift->user->assignments()
            ->where('time_start', '>=', $shift->time_start)
            ->where('time_end', '<=', $shift->time_end)
            ->where('time_end', '>', now())
            ->whereHas('booking', fn ($q) => $q->active())
            ->exists();

        if ($assigned) {
            return response()->json(['message' => 'В смене есть назначенные услуги.'], 409);
        }

        $shift->delete();

        return response()->json(null, 204);
    }
}
