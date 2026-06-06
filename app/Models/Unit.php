<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Unit extends Model
{
    use HasUuids;

    protected $fillable = [
        'block_id',
        'unit_number',
        'house_status',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public static array $houseStatuses = [
        'owner_occupied' => 'Owner Occupied',
        'vacant'         => 'Vacant',
        'rented'         => 'Rented',
    ];

    public function block(): BelongsTo
    {
        return $this->belongsTo(Block::class);
    }

    public function householder(): HasOne
    {
        return $this->hasOne(Householder::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function isOccupied(): bool
    {
        return $this->householder()->exists();
    }
}
