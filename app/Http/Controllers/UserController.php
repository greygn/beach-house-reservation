<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    // GET /admin/users
    public function index(): JsonResponse
    {
        return response()->json(User::paginate());
    }

    // GET /admin/users/{user}
    public function show(User $user): JsonResponse
    {
        return response()->json($user);
    }

    // PUT/PATCH /admin/users/{user}
    public function update(Request $request, User $user): JsonResponse
    {
        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
            'phone' => 'sometimes|string|max:20',
            'password' => 'sometimes|string|min:8',
            'is_admin' => 'sometimes|boolean',
        ]);

        if (array_key_exists('is_admin', $data)) {
            if ($user->is($request->user()) && ! $data['is_admin']) {
                return response()->json(['message' => 'Нельзя снять права администратора с самого себя.'], 422);
            }
            $user->is_admin = $data['is_admin'];
            unset($data['is_admin']);
        }

        $user->fill($data)->save();

        return response()->json($user);
    }

    // DELETE /admin/users/{user}
    public function destroy(Request $request, User $user): JsonResponse
    {
        if ($user->is($request->user())) {
            return response()->json(['message' => 'Нельзя удалить самого себя.'], 409);
        }

        if ($user->bookings()->active()->where('time_end', '>', now())->exists()) {
            return response()->json(['message' => 'У пользователя есть активные или будущие брони.'], 409);
        }

        $user->delete();

        return response()->json(null, 204);
    }
}
