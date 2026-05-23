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
        'resident_id',
        'family_member_id',
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

    public function resident(): BelongsTo
    {
        return $this->belongsTo(Resident::class);
    }

    public function familyMember(): BelongsTo
    {
        return $this->belongsTo(FamilyMember::class);
    }

    // ── Display helpers ───────────────────────────────────────────────────────

    /**
     * Full name of the assigned person (resident head-of-family or family member).
     */
    public function personName(): string
    {
        if ($this->resident_id && $this->relationLoaded('resident') && $this->resident) {
            return $this->resident->displayName();
        }
        if ($this->family_member_id && $this->relationLoaded('familyMember') && $this->familyMember) {
            return $this->familyMember->fullname;
        }
        return '';
    }

    /**
     * Authenticated photo URL of the assigned person.
     */
    public function personPhotoUrl(): ?string
    {
        if ($this->resident_id && $this->relationLoaded('resident') && $this->resident) {
            return $this->resident->photoUrl();
        }
        if ($this->family_member_id && $this->relationLoaded('familyMember') && $this->familyMember) {
            return $this->familyMember->photoUrl();
        }
        return null;
    }

    /**
     * Block · Unit string for the assigned person.
     */
    public function personLocation(): string
    {
        $resident = null;
        if ($this->resident_id && $this->relationLoaded('resident')) {
            $resident = $this->resident;
        } elseif ($this->family_member_id && $this->relationLoaded('familyMember') && $this->familyMember) {
            $resident = $this->familyMember->relationLoaded('resident') ? $this->familyMember->resident : null;
        }
        if (!$resident) return '';
        $block = $resident->block?->name ?? '';
        $unit  = $resident->unit_number ?? '';
        return $block . ($unit ? ' · ' . $unit : '');
    }

    /**
     * Phone number (residents only; family members typically don't have separate phone).
     */
    public function personPhone(): string
    {
        if ($this->resident_id && $this->relationLoaded('resident') && $this->resident) {
            return $this->resident->phone ?? '';
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
