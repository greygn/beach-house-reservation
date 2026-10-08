<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['picture', 'addon_name', 'quantity', 'price_hour'])]
class Addon extends Model
{
    public function bookings(): BelongsToMany
    {
        return $this->belongsToMany(Booking::class)
            ->withPivot('quantity');
    }
    protected function casts(): array
    {
        return [
            'price_hour' => 'decimal:2',
        ];
    }
}
