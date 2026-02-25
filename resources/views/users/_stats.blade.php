{{-- User Stats --}}
<div class="grid grid-cols-1 sm:grid-cols-3 gap-6">

  <div class="bg-white dark:bg-slate-900 p-6 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm">
    <div class="flex items-center justify-between mb-4">
      <div class="w-10 h-10 rounded-lg bg-primary/10 flex items-center justify-center text-primary">
        <span class="material-icons">group</span>
      </div>
      <span class="text-xs font-bold text-slate-400 uppercase tracking-widest">Total Users</span>
    </div>
    <div class="text-2xl font-bold text-slate-900 dark:text-white">{{ number_format($totalUsers) }}</div>
    <p class="text-sm text-slate-500 mt-1">Platform Users</p>
  </div>

  <div class="bg-white dark:bg-slate-900 p-6 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm">
    <div class="flex items-center justify-between mb-4">
      <div
        class="w-10 h-10 rounded-lg bg-emerald-100 dark:bg-emerald-500/10 flex items-center justify-center text-emerald-600">
        <span class="material-icons">verified_user</span>
      </div>
      <span class="text-xs font-bold text-slate-400 uppercase tracking-widest">Active</span>
    </div>
    <div class="text-2xl font-bold text-slate-900 dark:text-white">{{ number_format($activeUsers) }}</div>
    <p class="text-sm text-slate-500 mt-1">Active Users</p>
  </div>

  <div class="bg-white dark:bg-slate-900 p-6 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm">
    <div class="flex items-center justify-between mb-4">
      <div
        class="w-10 h-10 rounded-lg bg-amber-100 dark:bg-amber-500/10 flex items-center justify-center text-amber-600">
        <span class="material-icons">pending_actions</span>
      </div>
      <span class="text-xs font-bold text-slate-400 uppercase tracking-widest">Pending</span>
    </div>
    <div class="text-2xl font-bold text-slate-900 dark:text-white">{{ number_format($pendingUsers) }}</div>
    <p class="text-sm text-slate-500 mt-1">Awaiting Approval</p>
  </div>

</div>