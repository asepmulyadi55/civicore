<?php

namespace App\Models;

use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentRecord extends Model
{
    use HasUuids;
    protected $fillable = [
        'householder_id',
        'householder_name',
        'block_id',
        'unit_number',
        'batch_id',
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

    protected function casts(): array
    {
        return [
            'payment_month' => 'date',
            'amount' => 'decimal:2',
            'approved_at' => 'datetime',
            'status' => PaymentStatus::class,
        ];
    }

    // ── Relationships ───────────────────────────────────────────

    public function householder(): BelongsTo
    {
        return $this->belongsTo(Householder::class, 'householder_id');
    }

    public function block(): BelongsTo
    {
        return $this->belongsTo(Block::class, 'block_id');
    }

    /**
     * Returns the householder's display name.
     * Falls back to the stored snapshot name if the householder has been deleted.
     */
    public function residentDisplayName(): string
    {
        return $this->householder?->fullname
            ?? $this->householder_name
            ?? '(Deleted)';
    }

    /**
     * Returns the block name. Prioritizes the live householder's block, 
     * falling back to the snapshotted block if the householder is deleted.
     */
    public function blockDisplayName(): string
    {
        return $this->householder?->block?->name
            ?? $this->block?->name
            ?? '—';
    }

    /**
     * Returns the unit number. Prioritizes the live householder's unit,
     * falling back to the snapshotted unit if the householder is deleted.
     */
    public function unitDisplayNumber(): string
    {
        return $this->householder?->unit_number
            ?? $this->unit_number
            ?? '—';
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

    // ── Status Helpers ──────────────────────────────────────────

    public function isUnpaid(): bool
    {
        return $this->status === PaymentStatus::Unpaid;
    }

    public function isPending(): bool
    {
        return $this->status === PaymentStatus::Pending;
    }

    public function isApproved(): bool
    {
        return $this->status === PaymentStatus::Approved;
    }

    public function isRejected(): bool
    {
        return $this->status === PaymentStatus::Rejected;
    }

    /**
     * Status badge config for use in Blade views.
     * Returns ['label' => string, 'class' => string].
     */
    public function statusBadge(): array
    {
        return match ($this->status) {
            PaymentStatus::Approved => [
                'label' => 'Approved',
                'class' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
            ],
            PaymentStatus::Pending => [
                'label' => 'Pending',
                'class' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
            ],
            PaymentStatus::Rejected => [
                'label' => 'Rejected',
                'class' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
            ],
            default => [
                'label' => 'Unpaid',
                'class' => 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-400',
            ],
        };
    }
}
