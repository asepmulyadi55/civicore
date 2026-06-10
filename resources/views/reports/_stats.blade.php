{{-- Report Summary Stats --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">

  {{-- Collection Rate --}}
  <div class="bg-white dark:bg-slate-900 p-4 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm">
    <p class="text-xs font-bold text-slate-400 uppercase tracking-wide">{{ __('app.collection_rate') }}</p>
    <div class="flex items-end justify-between mt-1">
      <h3 class="text-xl font-bold text-slate-900 dark:text-white">{{ $collectionRate }}%</h3>
      <span class="text-{{ $collectionRate >= 80 ? 'emerald' : 'amber' }}-500 text-xs font-bold flex items-center">
        <span class="material-icons text-sm">{{ $collectionRate >= 80 ? 'trending_up' : 'trending_flat' }}</span>
      </span>
    </div>
    <div class="w-full bg-slate-100 dark:bg-slate-800 h-1.5 rounded-full mt-3">
      <div class="bg-emerald-500 h-1.5 rounded-full transition-all" style="width: {{ $collectionRate }}%"></div>
    </div>
  </div>

  {{-- Active Residents --}}
  <div class="bg-white dark:bg-slate-900 p-4 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm">
    <p class="text-xs font-bold text-slate-400 uppercase tracking-wide">{{ __('app.active_residents') }}</p>
    <h3 class="text-xl font-bold text-slate-900 dark:text-white mt-1">{{ $totalResidents }}</h3>
    <p class="text-xs text-slate-500 mt-2">{{ __('app.included_in_report') }}</p>
  </div>

  {{-- Paid Months --}}
  <div class="bg-white dark:bg-slate-900 p-4 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm">
    <p class="text-xs font-bold text-slate-400 uppercase tracking-wide">{{ __('app.paid_months') }}</p>
    <h3 class="text-xl font-bold text-emerald-600 dark:text-emerald-400 mt-1">{{ number_format($paidCount) }}</h3>
    <p class="text-xs text-slate-500 mt-2">{{ __('app.confirmed_payments') }}</p>
  </div>

  {{-- Outstanding --}}
  <div class="bg-white dark:bg-slate-900 p-4 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm">
    <p class="text-xs font-bold text-slate-400 uppercase tracking-wide">{{ __('app.outstanding') }}</p>
    <h3 class="text-xl font-bold text-red-500 mt-1">{{ number_format($unpaidCount) }}</h3>
    <p class="text-xs text-slate-500 mt-2">{{ __('app.months_unpaid_pending') }}</p>
  </div>

</div>