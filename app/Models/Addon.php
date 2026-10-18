<?php

namespace App\Models;

use App\Enums\BookingStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['picture', 'addon_name', 'quantity', 'price_hour'])]
class Addon extends Model
{
    use SoftDeletes;

    protected $appends = ['picture_url'];

    // Public URL of the uploaded file (php artisan storage:link).
    protected function pictureUrl(): Attribute
    {
        return Attribute::get(fn () => $this->picture ? Storage::disk('public')->url($this->picture) : null);
    }

    public function bookings(): BelongsToMany
    {
        return $this->belongsToMany(Booking::class, 'bookings_addons')
            ->withPivot('quantity');
    }
    /**
     * Max number of units reserved at the same moment inside [$from, $to).
     * Sweep line over booking intervals clamped to the window.
     */
    public function peakReserved(Carbon $from, Carbon $to, ?int $exceptBookingId = null): int
    {
        $rows = DB::table('bookings_addons')
            ->join('bookings', 'bookings.id', '=', 'bookings_addons.booking_id')
            ->where('bookings_addons.addon_id', $this->id)
            ->where('bookings.time_start', '<', $to)
            ->where('bookings.time_end', '>', $from)
            ->where('bookings.status', BookingStatus::Active->value)
            ->when($exceptBookingId, fn ($q) => $q->where('bookings.id', '!=', $exceptBookingId))
            ->get(['bookings.time_start', 'bookings.time_end', 'bookings_addons.quantity']);

        $events = [];
        foreach ($rows as $row) {
            $start = max(Carbon::parse($row->time_start)->getTimestamp(), $from->getTimestamp());
            $end = min(Carbon::parse($row->time_end)->getTimestamp(), $to->getTimestamp());
            $events[] = [$start, 1, (int) $row->quantity];
            $events[] = [$end, 0, -(int) $row->quantity];
        }

        // At equal time, ends (0) go before starts (1): back-to-back bookings do not overlap.
        usort($events, fn ($a, $b) => [$a[0], $a[1]] <=> [$b[0], $b[1]]);

        $current = $peak = 0;
        foreach ($events as [, , $delta]) {
            $current += $delta;
            $peak = max($peak, $current);
        }

        return $peak;
    }

    /** Units free for the whole of [$from, $to). */
    public function availableBetween(Carbon $from, Carbon $to): int
    {
        return max(0, $this->quantity - $this->peakReserved($from, $to));
    }

    /** Max units reserved at once by any booking that has not ended yet. */
    public function peakReservedFuture(): int
    {
        return $this->peakReserved(now(), Carbon::create(9999, 12, 31));
    }

    public function hasFutureBookings(): bool
    {
        return $this->bookings()->active()->where('time_end', '>', now())->exists();
    }

    protected function casts(): array
    {
        return [
            'price_hour' => 'decimal:2',
        ];
    }
}
