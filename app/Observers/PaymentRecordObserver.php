<?php

namespace App\Observers;

use App\Models\PaymentRecord;
use Illuminate\Support\Facades\Cache;

class PaymentRecordObserver
{
    /**
     * Flush all caches that depend on payment records.
     * Called on created, updated, and deleted events.
     */
    private function flush(PaymentRecord $record): void
    {
        // Main app dashboard stats (scoped by block or global)
        $blockId = $record->householder?->block_id ?? 'all';
        Cache::forget("dashboard:stats:{$blockId}");
        Cache::forget('dashboard:stats:all');

        // Finance dashboard — current month + nearby months may all be affected
        // Flush the current month and surrounding months (±1) to be safe
        $month = (int) ($record->payment_month ? \Carbon\Carbon::parse($record->payment_month)->month : now()->month);
        $year  = (int) ($record->payment_month ? \Carbon\Carbon::parse($record->payment_month)->year  : now()->year);
        Cache::forget("finance:dashboard:{$month}:{$year}");

        // Prev/next months in case the dashboard was open on a different month
        $prev = \Carbon\Carbon::create($year, $month, 1)->subMonth();
        $next = \Carbon\Carbon::create($year, $month, 1)->addMonth();
        Cache::forget("finance:dashboard:{$prev->month}:{$prev->year}");
        Cache::forget("finance:dashboard:{$next->month}:{$next->year}");

        // Reports summary stats — flush all blocks + global for this year
        Cache::forget("reports:summary:{$year}:all");
        Cache::forget("reports:summary:{$year}:{$blockId}");
    }

    public function created(PaymentRecord $record): void  { $this->flush($record); }
    public function updated(PaymentRecord $record): void  { $this->flush($record); }
    public function deleted(PaymentRecord $record): void  { $this->flush($record); }
}
