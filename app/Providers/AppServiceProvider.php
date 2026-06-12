<?php

namespace App\Providers;

use App\Models\FinanceReport;
use App\Models\FinanceTransaction;
use App\Models\PaymentRecord;
use App\Models\PropertyListing;
use App\Observers\FinanceReportObserver;
use App\Observers\FinanceTransactionObserver;
use App\Observers\PaymentRecordObserver;
use App\Observers\PropertyListingObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // ── Cache-invalidation observers ─────────────────────────────────────
        PaymentRecord::observe(PaymentRecordObserver::class);
        FinanceTransaction::observe(FinanceTransactionObserver::class);
        FinanceReport::observe(FinanceReportObserver::class);
        PropertyListing::observe(PropertyListingObserver::class);
    }
}
