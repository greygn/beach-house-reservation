<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable(['user_id', 'house_id', 'time_start', 'time_end', 'total_price'])]
class Booking extends Model
{

    public function addons(): BelongsToMany
    {
        return $this->belongsToMany(Addon::class)
            ->withPivot('quantity');
    }
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function house(): BelongsTo
    {
        return $this->belongsTo(House::class);
    }

    public function review(): HasOne{
        return $this->hasOne(Review::class);
    }

    protected function casts(): array
    {
        return [
            'total_price' => 'decimal:2',
            'check_in'  => 'datetime',
            'check_out' => 'datetime',
        ];
    }
}
