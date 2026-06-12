<?php

namespace App\Observers;

use App\Models\FinanceReport;
use Illuminate\Support\Facades\Cache;

class FinanceReportObserver
{
    private function flush(FinanceReport $report): void
    {
        $month = (int) $report->month;
        $year  = (int) $report->year;

        // Flush dashboard — the current balance comes from the latest approved report
        Cache::forget("finance:dashboard:{$month}:{$year}");

        // Also flush surrounding months since the closing balance chain propagates
        $prev = \Carbon\Carbon::create($year, $month, 1)->subMonth();
        $next = \Carbon\Carbon::create($year, $month, 1)->addMonth();
        Cache::forget("finance:dashboard:{$prev->month}:{$prev->year}");
        Cache::forget("finance:dashboard:{$next->month}:{$next->year}");
    }

    public function created(FinanceReport $report): void  { $this->flush($report); }
    public function updated(FinanceReport $report): void  { $this->flush($report); }
    public function deleted(FinanceReport $report): void  { $this->flush($report); }
}
