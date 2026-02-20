{{-- Report Summary Cards --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-4">

  {{-- Collection Rate --}}
  <div class="bg-white dark:bg-slate-900 p-4 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm">
    <p class="text-xs font-bold text-slate-400 uppercase tracking-wide">Block Collection</p>
    <div class="flex items-end justify-between mt-1">
      <h3 class="text-xl font-bold text-slate-900 dark:text-white">82%</h3>
      <span class="text-emerald-500 text-xs font-bold flex items-center">
        <span class="material-icons text-sm">trending_up</span>+3%
      </span>
    </div>
    <div class="w-full bg-slate-100 dark:bg-slate-800 h-1.5 rounded-full mt-3">
      <div class="bg-emerald-500 h-1.5 rounded-full" style="width: 82%"></div>
    </div>
  </div>

  {{-- Total Residents --}}
  <div class="bg-white dark:bg-slate-900 p-4 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm">
    <p class="text-xs font-bold text-slate-400 uppercase tracking-wide">Total Residents</p>
    <h3 class="text-xl font-bold text-slate-900 dark:text-white mt-1">124</h3>
    <p class="text-xs text-slate-500 mt-2">Active in Block A</p>
  </div>

  {{-- Paid Months --}}
  <div class="bg-white dark:bg-slate-900 p-4 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm">
    <p class="text-xs font-bold text-slate-400 uppercase tracking-wide">Paid Months</p>
    <h3 class="text-xl font-bold text-emerald-600 dark:text-emerald-400 mt-1">1,342</h3>
    <p class="text-xs text-slate-500 mt-2">Confirmed payments</p>
  </div>

  {{-- Outstanding --}}
  <div class="bg-white dark:bg-slate-900 p-4 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm">
    <p class="text-xs font-bold text-slate-400 uppercase tracking-wide">Outstanding</p>
    <h3 class="text-xl font-bold text-red-500 mt-1">146</h3>
    <p class="text-xs text-slate-500 mt-2">Months unpaid</p>
  </div>

</div>