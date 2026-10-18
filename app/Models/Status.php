<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'description'])]
class Status extends Model
{
    public const PENDING = 'На проверке';
    public const PUBLISHED = 'Опубликован';
    public const DELETED = 'Удалён';

    public const SYSTEM = [self::PENDING, self::PUBLISHED, self::DELETED];

    public function reviews(): HasMany{
        return $this->hasMany(Review::class);
    }
}
