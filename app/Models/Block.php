<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Block extends Model
{
    protected $fillable = ['name', 'description', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Residents who live in this block
    public function residents(): HasMany
    {
        return $this->hasMany(Resident::class);
    }

    // Users (block coordinators) assigned to this block
    public function coordinators(): HasMany
    {
        return $this->hasMany(User::class);
    }

    // Only active blocks
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
