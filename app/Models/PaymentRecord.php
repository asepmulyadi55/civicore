<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentRecord extends Model
{
    protected $fillable = [
        'resident_id',
        'payment_month',
        'amount',
        'payment_method_id',
        'proof_path',
        'status',
        'rejection_reason',
        'notes',
        'submitted_by',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'payment_month' => 'date',
        'amount' => 'decimal:2',
        'approved_at' => 'datetime',
    ];

    // ── Relationships ───────────────────────────────────────────

    public function resident(): BelongsTo
    {
        return $this->belongsTo(Resident::class);
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function submittedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // ── Status Scopes ───────────────────────────────────────────

    public function scopeUnpaid($query)
    {
        return $query->where('status', 'unpaid');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    // ── Helpers ─────────────────────────────────────────────────

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }
    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }
    public function isUnpaid(): bool
    {
        return $this->status === 'unpaid';
    }

    /**
     * Status badge config for use in Blade views.
     */
    public function statusBadge(): array
    {
        return match ($this->status) {
            'approved' => ['label' => 'Approved', 'class' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400'],
            'pending' => ['label' => 'Pending', 'class' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400'],
            'rejected' => ['label' => 'Rejected', 'class' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400'],
            default => ['label' => 'Unpaid', 'class' => 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-400'],
        };
    }
}
