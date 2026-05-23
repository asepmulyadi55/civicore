<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class FinanceReport extends Model
{
    use HasUuids;

    protected $fillable = [
        'month',
        'year',
        'opening_balance',
        'total_income',
        'total_expense',
        'closing_balance',
        'status',
        'notes',
        'submitted_by',
        'submitted_at',
        'approved_by',
        'approved_at',
        'revised_by',
        'revised_at',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'opening_balance' => 'decimal:2',
            'total_income'    => 'decimal:2',
            'total_expense'   => 'decimal:2',
            'closing_balance' => 'decimal:2',
            'submitted_at'    => 'datetime',
            'approved_at'     => 'datetime',
            'revised_at'      => 'datetime',
            'month'           => 'integer',
            'year'            => 'integer',
        ];
    }

    // ── Relationships ────────────────────────────────────────────────────────

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function submittedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function revisedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'revised_by');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(FinanceTransaction::class, 'report_month', 'month')
            ->where('report_year', $this->year);
    }

    // ── Scopes ───────────────────────────────────────────────────────────────

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', 'approved');
    }

    public function scopeForMonth(Builder $query, int $year, int $month): Builder
    {
        return $query->where('year', $year)->where('month', $month);
    }

    // ── Business Logic ───────────────────────────────────────────────────────

    /**
     * Whether this report is locked (approved and not in revised state).
     */
    public function isLocked(): bool
    {
        return $this->status === 'approved';
    }

    /**
     * Whether a given user can modify this report's transactions.
     */
    public function canEdit(User $user): bool
    {
        if ($user->can('finance.approve')) {
            return true; // Admin / Chairman can always edit
        }
        return !$this->isLocked();
    }

    /**
     * Recalculate income, expense, and closing balance from transactions + approved payments.
     * Does NOT save — caller must call save() after.
     */
    public function recalculate(): static
    {
        $manualIncome = FinanceTransaction::where('type', 'income')
            ->where('report_month', $this->month)
            ->where('report_year', $this->year)
            ->sum('amount');

        $paymentIncome = PaymentRecord::where('status', 'approved')
            ->whereYear('payment_month', $this->year)
            ->whereMonth('payment_month', $this->month)
            ->sum('amount');

        $totalExpense = FinanceTransaction::where('type', 'expense')
            ->where('report_month', $this->month)
            ->where('report_year', $this->year)
            ->sum('amount');

        $this->total_income   = (float) $manualIncome + (float) $paymentIncome;
        $this->total_expense  = (float) $totalExpense;
        $this->closing_balance = (float) $this->opening_balance + $this->total_income - $this->total_expense;

        return $this;
    }

    /**
     * Status badge for display.
     */
    public function statusBadge(): array
    {
        return match ($this->status) {
            'draft'     => ['label' => __('app.fin_status_draft'),     'class' => 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300'],
            'submitted' => ['label' => __('app.fin_status_submitted'), 'class' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400'],
            'approved'  => ['label' => __('app.fin_status_approved'),  'class' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400'],
            'revised'   => ['label' => __('app.fin_status_revised'),   'class' => 'bg-sky-100 text-sky-700 dark:bg-sky-900/30 dark:text-sky-400'],
            default     => ['label' => ucfirst($this->status), 'class' => 'bg-slate-100 text-slate-600'],
        };
    }

    /**
     * Short month-year label, e.g. "May 2025".
     */
    public function periodLabel(): string
    {
        return \Carbon\Carbon::create($this->year, $this->month, 1)->format('F Y');
    }
}
