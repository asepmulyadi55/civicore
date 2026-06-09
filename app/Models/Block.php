<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Block extends Model
{
    use HasUuids;
    protected $fillable = ['name', 'description', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Units that belong to this block
    public function units(): HasMany
    {
        return $this->hasMany(Unit::class)->orderByRaw("
            LEFT(unit_number, LOCATE('-', unit_number) - 1),
            CAST(SUBSTRING(unit_number, LOCATE('-', unit_number) + 1) AS UNSIGNED),
            unit_number
        ");
    }

    // Householders who live in this block (denormalized FK)
    public function householders(): HasMany
    {
        return $this->hasMany(Householder::class);
    }

    // Users (block coordinators) assigned to this block via pivot
    public function coordinators(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'block_user');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}

