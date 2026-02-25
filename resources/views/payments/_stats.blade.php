{{-- Payment Summary Stats --}}
<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
  <div class="bg-white dark:bg-slate-900 p-6 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm">
    <div class="flex items-center justify-between">
      <span class="text-slate-500 font-medium">Total Pending</span>
      <div class="p-2 bg-amber-100 text-amber-600 rounded-lg">
        <span class="material-icons">pending_actions</span>
      </div>
    </div>
    <div class="mt-4">
      <h3 class="text-2xl font-bold">{{ $currency }} {{ number_format($pendingTotal) }}</h3>
      <p class="text-xs text-slate-400 mt-1">{{ $pendingCount }} payments awaiting review</p>
    </div>
  </div>

  <div class="bg-white dark:bg-slate-900 p-6 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm">
    <div class="flex items-center justify-between">
      <span class="text-slate-500 font-medium">Collected (This Month)</span>
      <div class="p-2 bg-emerald-100 text-emerald-600 rounded-lg">
        <span class="material-icons">payments</span>
      </div>
    </div>
    <div class="mt-4">
      <h3 class="text-2xl font-bold">{{ $currency }} {{ number_format($collectedMonth) }}</h3>
      <p class="text-xs text-emerald-500 mt-1">{{ now()->format('F Y') }}</p>
    </div>
  </div>

  <div class="bg-white dark:bg-slate-900 p-6 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm">
    <div class="flex items-center justify-between">
      <span class="text-slate-500 font-medium">Unpaid This Month</span>
      <div class="p-2 bg-rose-100 text-rose-600 rounded-lg">
        <span class="material-icons">warning</span>
      </div>
    </div>
    <div class="mt-4">
      <h3 class="text-2xl font-bold">{{ $unpaidCount }}</h3>
      <p class="text-xs text-slate-400 mt-1">Residents without approved payment</p>
    </div>
  </div>
</div>