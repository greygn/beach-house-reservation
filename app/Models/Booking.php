<?php

namespace App\Models;

use App\Enums\BookingStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

#[Fillable(['user_id', 'house_id', 'time_start', 'time_end', 'total_price', 'status'])]
class Booking extends Model
{

    public function addons(): BelongsToMany
    {
        return $this->belongsToMany(Addon::class, 'bookings_addons')
            ->withTrashed()
            ->withPivot('quantity');
    }
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function house(): BelongsTo
    {
        return $this->belongsTo(House::class)->withTrashed();
    }

    public function review(): HasOne{
        return $this->hasOne(Review::class);
    }

    // Admin filters: house, status, bookings overlapping a date range.
    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['house_id'] ?? null, fn ($q, $id) => $q->where('house_id', $id))
            ->when($filters['status'] ?? null, fn ($q, $s) => $q->where('status', $s))
            ->when($filters['date_from'] ?? null, fn ($q, $d) => $q->where('time_end', '>', Carbon::parse($d)->startOfDay()))
            ->when($filters['date_to'] ?? null, fn ($q, $d) => $q->where('time_start', '<', Carbon::parse($d)->endOfDay()));
    }

    // Cancelled bookings hold neither the house nor addon stock.
    public function scopeActive(Builder $query): Builder
    {
        return $query->where($query->getModel()->qualifyColumn('status'), BookingStatus::Active->value);
    }

    protected function casts(): array
    {
        return [
            'total_price' => 'decimal:2',
            'status' => BookingStatus::class,
            'time_start' => 'datetime',
            'time_end'   => 'datetime',
        ];
    }
}
