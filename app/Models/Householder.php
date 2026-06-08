<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Householder extends Model
{
    use HasUuids;
    protected $fillable = [
        'user_id',
        'block_id',
        'unit_id',
        'fullname',
        'phone',
        'email',
        'is_active',
        'family_card_number',
        'notes',
        'photo_path',
        'rent_start',
        'rent_end',
    ];

    protected $hidden = [
        // Temporary Data Hardening: completely hide these from UI/forms serialization.
        'family_card_number', 'phone',
    ];

    protected $casts = [
        'is_active'          => 'boolean',
        'family_card_number' => 'encrypted',
        'rent_start'         => 'date',
        'rent_end'           => 'date',
    ];

    // ── Backward-compat virtual accessors ────────────────────────────────────

    /** Returns the unit_number from the linked unit (read-only). */
    public function getUnitNumberAttribute(): ?string
    {
        return $this->unit?->unit_number;
    }

    /** Returns house_status from the linked unit (read-only). */
    public function getHouseStatusAttribute(): ?string
    {
        return $this->unit?->house_status ?? 'owner_occupied';
    }

    // Optional linked user account
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function block(): BelongsTo
    {
        return $this->belongsTo(Block::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function residents(): HasMany
    {
        return $this->hasMany(Resident::class, 'householder_id')->orderByDesc('is_head')->orderBy('fullname');
    }

    /**
     * Returns the head-of-household resident.
     * Uses the in-memory collection when already eager-loaded to avoid extra queries.
     */
    public function headOfFamily(): ?Resident
    {
        return $this->relationLoaded('residents')
            ? $this->residents->firstWhere('is_head', true)
            : $this->residents()->where('is_head', true)->first();
    }

    /**
     * Display name: head-of-household resident name, or fallback to fullname.
     */
    public function displayName(): string
    {
        return $this->headOfFamily()?->fullname ?? $this->fullname;
    }

    public function photoUrl(): ?string
    {
        if (!$this->photo_path) return null;
        return route('private.file', ['path' => $this->photo_path]);
    }

    public function paymentRecords(): HasMany
    {
        return $this->hasMany(PaymentRecord::class, 'householder_id')->orderBy('payment_month');
    }

    public function feeHistories(): HasMany
    {
        return $this->hasMany(FeeHistory::class, 'householder_id')->orderByDesc('effective_from');
    }

    /**
     * Get the currently active monthly fee for this householder.
     * Returns the most recent fee history entry with effective_from <= today.
     */
    public function currentFee(): ?FeeHistory
    {
        return $this->feeHistories()
            ->where('effective_from', '<=', now()->startOfMonth())
            ->first();
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function maskedFamilyCardNumber(): string
    {
        if (!$this->family_card_number) return '—';
        $val = $this->family_card_number;
        return str_repeat('•', max(0, strlen($val) - 4)) . substr($val, -4);
    }
}
