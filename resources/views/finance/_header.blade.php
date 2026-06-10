{{-- finance/_header.blade.php --}}
<header class="h-16 bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between px-6 lg:px-8">
  <div class="flex items-center gap-4">
    <button class="lg:hidden p-2 rounded-lg border border-slate-200 dark:border-slate-800" onclick="toggleSidebar()">
      <span class="material-icons text-slate-500">menu</span>
    </button>
    <div>
      <h1 class="text-xl font-bold text-slate-900 dark:text-white">
        {{ __('app.fin_title') }}
      </h1>
      <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">
        {{ now()->format('F Y') }}
      </p>
    </div>
  </div>

  <div class="flex items-center gap-3">
    @if($canManage)
      <button type="button" id="header-btn-transactions" onclick="openAddTransactionModal()"
        class="hidden flex items-center gap-2 bg-primary hover:bg-primary/90 text-white px-4 py-2 rounded-lg font-semibold transition-all shadow-sm shadow-primary/20 text-sm">
        <span class="material-icons text-sm">add</span>
        <span class="hidden sm:inline">{{ __('app.fin_add_transaction') }}</span>
      </button>

      <button type="button" id="header-btn-reports" onclick="openGenerateReportModal()"
        class="hidden flex items-center gap-2 bg-primary hover:bg-primary/90 text-white px-4 py-2 rounded-lg font-semibold transition-all shadow-sm shadow-primary/20 text-sm">
        <span class="material-icons text-sm">add_chart</span>
        <span class="hidden sm:inline">{{ __('app.fin_generate_report') }}</span>
      </button>
    @endif

    {{-- Dark mode toggle --}}
    <button onclick="toggleDark()" title="Toggle dark mode"
      class="p-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg hover:border-primary/50 transition-all">
      <span class="material-icons text-slate-500 text-[20px]">dark_mode</span>
    </button>
  </div>
</header>
