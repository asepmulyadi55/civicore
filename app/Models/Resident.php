<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Resident extends Model
{
    use HasUuids;
    protected $fillable = [
        'user_id',
        'block_id',
        'unit_number',
        'fullname',
        'phone',
        'email',
        'is_active',
        'family_card_number',
        'house_status',
        'notes',
    ];

    protected $casts = [
        'is_active'          => 'boolean',
        'family_card_number' => 'encrypted',
    ];

    // Optional linked user account
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function block(): BelongsTo
    {
        return $this->belongsTo(Block::class);
    }

    public function familyMembers(): HasMany
    {
        return $this->hasMany(FamilyMember::class)->orderByDesc('is_head')->orderBy('fullname');
    }

    /**
     * Returns the head-of-family member.
     * Uses the in-memory collection when already eager-loaded to avoid extra queries.
     */
    public function headOfFamily(): ?FamilyMember
    {
        return $this->relationLoaded('familyMembers')
            ? $this->familyMembers->firstWhere('is_head', true)
            : $this->familyMembers()->where('is_head', true)->first();
    }

    /**
     * Display name: head of family name, or fallback to fullname.
     */
    public function displayName(): string
    {
        return $this->headOfFamily()?->fullname ?? $this->fullname;
    }

    public function paymentRecords(): HasMany
    {
        return $this->hasMany(PaymentRecord::class)->orderBy('payment_month');
    }

    public function feeHistories(): HasMany
    {
        return $this->hasMany(FeeHistory::class)->orderByDesc('effective_from');
    }

    /**
     * Get the currently active monthly fee for this resident.
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
