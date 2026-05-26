<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrganizationPeriod extends Model
{
    use HasUuids;

    protected $fillable = ['name', 'start_year', 'end_year', 'is_active'];

    protected $casts = [
        'is_active'   => 'boolean',
        'start_year'  => 'integer',
        'end_year'    => 'integer',
    ];

    public function positions(): HasMany
    {
        return $this->hasMany(OrganizationPosition::class)->orderBy('sort_order');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
