<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['house_type_id', 'picture', 'name', 'capacity', 'price_hour', 'price_day'])]
class House extends Model
{
    use SoftDeletes;

    protected $appends = ['picture_url'];

    // Public URL of the uploaded file (php artisan storage:link).
    protected function pictureUrl(): Attribute
    {
        return Attribute::get(fn () => $this->picture ? Storage::disk('public')->url($this->picture) : null);
    }

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
