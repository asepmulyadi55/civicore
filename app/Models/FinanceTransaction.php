<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class FinanceTransaction extends Model
{
    use HasUuids;

    protected $fillable = [
        'type',
        'category',
        'amount',
        'description',
        'notes',
        'transaction_date',
        'report_month',
        'report_year',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'amount'           => 'decimal:2',
            'transaction_date' => 'date',
            'report_month'     => 'integer',
            'report_year'      => 'integer',
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

    // ── Scopes ───────────────────────────────────────────────────────────────

    public function scopeIncome(Builder $query): Builder
    {
        return $query->where('type', 'income');
    }

    public function scopeExpense(Builder $query): Builder
    {
        return $query->where('type', 'expense');
    }

    public function scopeForMonth(Builder $query, int $year, int $month): Builder
    {
        return $query->where('report_year', $year)->where('report_month', $month);
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    /**
     * Return array of distinct categories used in transactions.
     */
    public static function distinctCategories(): array
    {
        return static::whereNotNull('category')
            ->distinct()
            ->orderBy('category')
            ->pluck('category')
            ->toArray();
    }

    /**
     * Badge classes for display.
     */
    public function typeBadge(): array
    {
        return match ($this->type) {
            'income'  => ['label' => 'Income',  'class' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400'],
            'expense' => ['label' => 'Expense', 'class' => 'bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-400'],
            default   => ['label' => ucfirst($this->type), 'class' => 'bg-slate-100 text-slate-600'],
        };
    }
}
