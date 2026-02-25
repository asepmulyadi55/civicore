{{-- Reports Page Header --}}
<header class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-8">
  <div>
    <nav class="flex items-center gap-2 text-xs font-medium text-slate-400 mb-2 uppercase tracking-wider">
      <span>Dashboard</span>
      <span class="material-icons text-xs">chevron_right</span>
      <span class="text-primary">Reports</span>
    </nav>
    <h1 class="text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">Yearly Financial Report</h1>
    <p class="text-slate-500 dark:text-slate-400 mt-1">Payment tracking for {{ $year }} by resident and block.</p>
  </div>
  <div class="flex items-center gap-3">
    <button onclick="window.print()"
      class="flex items-center gap-2 px-6 py-2.5 bg-primary text-white rounded-lg text-sm font-bold hover:bg-primary/90 transition-all shadow-lg shadow-primary/20">
      <span class="material-icons text-lg">print</span>
      Print Report
    </button>
  </div>
</header>