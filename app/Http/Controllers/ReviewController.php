<?php

namespace App\Http\Controllers;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\House;
use App\Models\Review;
use App\Models\Status;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    // GET /houses/{house}/reviews (published only)
    public function indexByHouse(House $house): JsonResponse
    {
        $reviews = Review::whereHas('booking', fn ($q) => $q->where('house_id', $house->id))
            ->published()
            ->with('booking.user:id,name')
            ->latest('review_date')
            ->paginate();

        return response()->json($reviews);
    }

    // GET /admin/reviews?status_id=
    public function index(Request $request): JsonResponse
    {
        $reviews = Review::with(['status', 'booking.user', 'booking.house'])
            ->when($request->query('status_id'), fn ($q, $id) => $q->where('status_id', $id))
            ->latest('review_date')
            ->paginate();

        return response()->json($reviews);
    }

    // POST /bookings/{booking}/review
    public function store(Request $request, Booking $booking): JsonResponse
    {
        abort_unless($booking->user_id === $request->user()->id, 403);

        if ($booking->status === BookingStatus::Cancelled) {
            return response()->json(['message' => 'На отменённую бронь нельзя оставить отзыв.'], 422);
        }

        if ($booking->time_end->isFuture()) {
            return response()->json(['message' => 'Отзыв можно оставить только после окончания брони.'], 422);
        }

        if ($booking->review()->exists()) {
            return response()->json(['message' => 'На эту бронь уже есть отзыв.'], 422);
        }

        $data = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'text' => 'nullable|string|max:5000',
        ]);

        $review = $booking->review()->create($data + [
            'status_id' => Status::where('name', Status::PENDING)->firstOrFail()->id,
            'review_date' => now(),
        ]);

        return response()->json($review->load('status'), 201);
    }

    // PATCH /admin/reviews/{review}/status
    public function updateStatus(Request $request, Review $review): JsonResponse
    {
        $data = $request->validate([
            'status_id' => 'required|exists:statuses,id',
        ]);

        $review->update($data);

        return response()->json($review->load('status'));
    }
}
