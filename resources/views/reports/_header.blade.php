{{-- Reports Page Header --}}
<header
  class="h-16 bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between px-6 lg:px-8 no-print">
  <div class="flex items-center gap-4">
    <button class="lg:hidden p-2 rounded-lg border border-slate-200 dark:border-slate-800" onclick="toggleSidebar()">
      <span class="material-icons text-slate-500">menu</span>
    </button>
    <h1 class="text-xl font-bold text-slate-900 dark:text-white">Reports</h1>
    <span
      class="hidden sm:inline px-2 py-1 text-xs font-semibold bg-blue-100 dark:bg-blue-500/10 text-blue-700 dark:text-blue-400 rounded-lg uppercase">Yearly
      Block Report</span>
  </div>
  <div class="flex items-center gap-3">
    <button
      class="hidden sm:flex items-center gap-2 px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-50 transition-all shadow-sm">
      <span class="material-icons text-[18px] text-slate-400">file_download</span>
      Export CSV
    </button>
    <button onclick="window.print()"
      class="flex items-center gap-2 px-4 py-2 bg-primary hover:bg-primary/90 text-white rounded-lg text-sm font-bold transition-all shadow-sm shadow-primary/20">
      <span class="material-icons text-[18px]">print</span>
      <span class="hidden sm:inline">Print Report</span>
    </button>
    <button
      class="p-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg hover:border-primary/50 transition-all"
      onclick="document.documentElement.classList.toggle('dark')" title="Toggle dark mode">
      <span class="material-icons text-slate-500 text-[20px]">dark_mode</span>
    </button>
  </div>
</header>