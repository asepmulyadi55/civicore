<?php

namespace App\Observers;

use App\Models\FinanceTransaction;
use Illuminate\Support\Facades\Cache;

class FinanceTransactionObserver
{
    private function flush(FinanceTransaction $transaction): void
    {
        $month = (int) $transaction->report_month;
        $year  = (int) $transaction->report_year;

        // Flush dashboard for the affected month
        Cache::forget("finance:dashboard:{$month}:{$year}");

        // Also flush surrounding months in case the 6-month trend is affected
        $prev = \Carbon\Carbon::create($year, $month, 1)->subMonth();
        $next = \Carbon\Carbon::create($year, $month, 1)->addMonth();
        Cache::forget("finance:dashboard:{$prev->month}:{$prev->year}");
        Cache::forget("finance:dashboard:{$next->month}:{$next->year}");

        // Categories list may have a new entry
        Cache::forget('finance:categories');
    }

    public function created(FinanceTransaction $transaction): void  { $this->flush($transaction); }
    public function updated(FinanceTransaction $transaction): void  { $this->flush($transaction); }
    public function deleted(FinanceTransaction $transaction): void  { $this->flush($transaction); }
}
