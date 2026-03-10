{{-- Top bar header for the resident overview page --}}
<header
  class="h-16 bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between px-8">
  <h1 class="text-xl font-bold">Resident Personal Overview</h1>
  <div class="flex items-center gap-4">
    <div class="text-xs text-slate-400 text-right">
      <p>Last Sync</p>
      <p class="font-medium">{{ now()->format('M d, Y • h:i A') }}</p>
    </div>
    <button onclick="window.location.reload()" class="p-2 text-slate-400 hover:text-primary transition-colors"
      title="Refresh">
      <span class="material-icons">refresh</span>
    </button>
  </div>
</header>