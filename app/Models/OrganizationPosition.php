<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrganizationPosition extends Model
{
    use HasUuids;

    protected $fillable = [
        'organization_period_id',
        'parent_id',
        'householder_id',
        'resident_id',
        'position_name',
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function period(): BelongsTo
    {
        return $this->belongsTo(OrganizationPeriod::class, 'organization_period_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(OrganizationPosition::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(OrganizationPosition::class, 'parent_id')->orderBy('sort_order');
    }

    public function householder(): BelongsTo
    {
        return $this->belongsTo(Householder::class, 'householder_id');
    }

    public function resident(): BelongsTo
    {
        return $this->belongsTo(Resident::class, 'resident_id');
    }

    // ── Display helpers ───────────────────────────────────────────────────────

    /**
     * Full name of the assigned person (householder head-of-family or resident).
     */
    public function personName(): string
    {
        if ($this->householder_id && $this->relationLoaded('householder') && $this->householder) {
            return $this->householder->displayName();
        }
        if ($this->resident_id && $this->relationLoaded('resident') && $this->resident) {
            return $this->resident->fullname;
        }
        return '';
    }

    /**
     * Authenticated photo URL of the assigned person.
     */
    public function personPhotoUrl(): ?string
    {
        if ($this->householder_id && $this->relationLoaded('householder') && $this->householder) {
            return $this->householder->photoUrl();
        }
        if ($this->resident_id && $this->relationLoaded('resident') && $this->resident) {
            return $this->resident->photoUrl();
        }
        return null;
    }

    /**
     * Block · Unit string for the assigned person.
     */
    public function personLocation(): string
    {
        $householder = null;
        if ($this->householder_id && $this->relationLoaded('householder')) {
            $householder = $this->householder;
        } elseif ($this->resident_id && $this->relationLoaded('resident') && $this->resident) {
            $householder = $this->resident->relationLoaded('householder') ? $this->resident->householder : null;
        }
        if (!$householder) return '';
        $block = $householder->block?->name ?? '';
        $unit  = $householder->unit_number ?? '';
        return $block . ($unit ? ' · ' . $unit : '');
    }

    /**
     * Phone number (householders only; residents typically don't have separate phone).
     */
    public function personPhone(): string
    {
        if ($this->householder_id && $this->relationLoaded('householder') && $this->householder) {
            return $this->householder->phone ?? '';
        }
        return '';
    }

    /**
     * Two-letter initials for avatar fallback.
     */
    public function personInitials(): string
    {
        $name = $this->personName();
        if (!$name) return '?';
        return collect(explode(' ', $name))
            ->map(fn($w) => strtoupper($w[0] ?? ''))
            ->take(2)
            ->implode('');
    }
}
