<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['booking_id', 'rating', 'text', 'status_id', 'review_date'])]
class Review extends Model
{
    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class);
    }

    public function booking(): BelongsTo{
        return $this->belongsTo(Booking::class);
    }

    protected function casts(): array
    {
        return [
            'review_date' => 'datetime',
        ];
    }
}
