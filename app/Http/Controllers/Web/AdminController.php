<?php

namespace App\Http\Controllers\Web;

use App\Enums\BookingStatus;
use App\Http\Controllers\Controller;
use App\Models\Addon;
use App\Models\Booking;
use App\Models\House;
use App\Models\HouseType;
use App\Models\Review;
use App\Models\Status;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    public function bookings(Request $request): View
    {
        $filters = $request->validate([
            'house_id' => 'nullable|integer',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            'status' => ['nullable', Rule::enum(BookingStatus::class)],
        ]);

        return view('admin.bookings', [
            'bookings' => Booking::with(['user', 'house', 'addons', 'review'])
                ->filter($filters)
                ->orderByDesc('time_start')
                ->paginate(30)
                ->withQueryString(),
            'houses' => House::orderBy('name')->get(),
            'filters' => $filters,
            'statuses' => BookingStatus::cases(),
        ]);
    }

    public function reviews(Request $request): View
    {
        return view('admin.reviews', [
            'reviews' => Review::with(['status', 'booking.user', 'booking.house'])
                ->when($request->query('status_id'), fn ($q, $id) => $q->where('status_id', $id))
                ->latest('review_date')
                ->paginate(30)
                ->withQueryString(),
            'statuses' => Status::orderBy('id')->get(),
            'statusId' => $request->query('status_id'),
        ]);
    }

    public function houses(): View
    {
        return view('admin.houses', [
            'houses' => House::orderBy('name')->get(),
            'types' => HouseType::orderBy('name')->get(),
        ]);
    }

    public function houseTypes(): View
    {
        return view('admin.house-types', ['types' => HouseType::orderBy('name')->get()]);
    }

    public function addons(): View
    {
        return view('admin.addons', ['addons' => Addon::orderBy('addon_name')->get()]);
    }

    public function statuses(): View
    {
        return view('admin.statuses', [
            'statuses' => Status::orderBy('id')->get(),
            'system' => Status::SYSTEM,
        ]);
    }

    public function users(): View
    {
        return view('admin.users', ['users' => User::orderBy('id')->paginate(30)]);
    }
}
