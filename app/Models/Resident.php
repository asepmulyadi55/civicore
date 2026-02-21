<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Resident extends Model
{
    protected $fillable = [
        'user_id',
        'block_id',
        'unit_number',
        'fullname',
        'phone',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
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

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
