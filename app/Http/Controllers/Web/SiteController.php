<?php

namespace App\Http\Controllers\Web;

use App\Enums\BookingStatus;
use App\Http\Controllers\Controller;
use App\Models\Addon;
use App\Models\Booking;
use App\Models\House;
use App\Models\HouseType;
use App\Models\Review;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;

class SiteController extends Controller
{
    public function houses(Request $request): View
    {
        $houses = House::with('houseType')
            ->when($request->query('type'), fn ($q, $id) => $q->where('house_type_id', $id))
            ->orderBy('name')
            ->get();

        return view('site.houses', [
            'houses' => $houses,
            'types' => HouseType::orderBy('name')->get(),
            'type' => $request->query('type'),
        ]);
    }

    public function house(House $house): View
    {
        $reviews = Review::published()
            ->whereHas('booking', fn ($q) => $q->where('house_id', $house->id))
            ->with('booking.user:id,name')
            ->latest('review_date')
            ->get();

        return view('site.house', [
            'house' => $house->load('houseType'),
            'addons' => Addon::orderBy('addon_name')->get(),
            'services' => Service::orderBy('name')->get(),
            'reviews' => $reviews,
        ]);
    }

    public function pay(Request $request, Booking $booking): View
    {
        abort_unless($booking->user_id === $request->user()->id, 403);

        return view('site.pay', ['booking' => $booking->load('house')]);
    }

    public function staff(Request $request): View
    {
        $user = $request->user();

        return view('site.staff', [
            'shifts' => $user->shifts()->where('time_end', '>', now())->orderBy('time_start')->get(),
            'assignments' => $user->assignments()
                ->whereHas('booking', fn ($q) => $q->active())
                ->where('time_end', '>', now())
                ->with(['service', 'booking.house'])
                ->orderBy('time_start')
                ->get(),
        ]);
    }

    public function bookings(Request $request): View
    {
        $bookings = $request->user()->bookings()
            ->with(['house', 'addons', 'services.service', 'review.status'])
            ->latest('time_start')
            ->get();

        return view('site.bookings', ['bookings' => $bookings, 'cancelled' => BookingStatus::Cancelled, 'active' => BookingStatus::Active]);
    }
}
