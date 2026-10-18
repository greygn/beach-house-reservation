<?php

namespace App\Http\Controllers\Web;

use App\Enums\BookingStatus;
use App\Http\Controllers\Controller;
use App\Models\Addon;
use App\Models\House;
use App\Models\HouseType;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;

// Read-only pages for visitors and users. All writes go through the JSON API.
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
            'reviews' => $reviews,
        ]);
    }

    public function bookings(Request $request): View
    {
        $bookings = $request->user()->bookings()
            ->with(['house', 'addons', 'review.status'])
            ->latest('time_start')
            ->get();

        return view('site.bookings', ['bookings' => $bookings, 'cancelled' => BookingStatus::Cancelled]);
    }
}
