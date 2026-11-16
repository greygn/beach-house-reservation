<?php

namespace App\Models;

use App\Enums\BookingStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['booking_id', 'service_id', 'user_id', 'time_start', 'time_end', 'price'])]
class BookingService extends Model
{
    protected $table = 'bookings_services';

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class)->withTrashed();
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Can $user take this slot: shift covers it, no other confirmed job overlaps (at any house).
    public function canBeTakenBy(User $user): bool
    {
        return $user->is_staff
            && $user->shifts()->where('time_start', '<=', $this->time_start)->where('time_end', '>=', $this->time_end)->exists()
            && ! $user->assignments()
                ->where('id', '!=', $this->id)
                ->where('time_start', '<', $this->time_end)
                ->where('time_end', '>', $this->time_start)
                ->whereHas('booking', fn ($q) => $q->active())
                ->exists();
    }

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'time_start' => 'datetime',
            'time_end' => 'datetime',
        ];
    }
}
