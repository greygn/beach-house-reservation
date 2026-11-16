<?php

namespace App\Models;

use App\Enums\BookingStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['name', 'price_hour'])]
class Service extends Model
{
    use SoftDeletes;

    public function hasFutureBookings(): bool
    {
        return BookingService::where('service_id', $this->id)
            ->where('time_end', '>', now())
            ->whereHas('booking', fn ($q) => $q->whereIn('status', [BookingStatus::Pending->value, BookingStatus::Active->value]))
            ->exists();
    }

    protected function casts(): array
    {
        return [
            'price_hour' => 'decimal:2',
        ];
    }
}
