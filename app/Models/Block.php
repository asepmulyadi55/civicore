<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
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
        return $this->hasMany(Unit::class)->orderByRaw('LENGTH(unit_number), unit_number');
    }

    // Residents who live in this block (denormalized FK)
    public function residents(): HasMany
    {
        return $this->hasMany(Resident::class);
    }

    // Users (block coordinators) assigned to this block
    public function coordinators(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
