<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'description'])]
class Status extends Model
{
    public function reviews(): HasMany{
        return $this->hasMany(Review::class);
    }
}
