<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['house_type_id', 'picture', 'name', 'capacity', 'price_hour', 'price_day'])]
class House extends Model
{
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function houseType(): BelongsTo
    {
        return $this->belongsTo(HouseType::class);
    }

    protected function casts(): array
    {
        return [
            'price_hour' => 'decimal:2',
            'price_day'  => 'decimal:2',
            ];
    }
}
