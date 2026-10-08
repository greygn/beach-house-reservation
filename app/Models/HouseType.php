<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'description'])]
class HouseType extends Model
{
    public function houses(): HasMany
    {
        return $this->hasMany(House::class);
    }
}
