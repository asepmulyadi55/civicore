{{-- finance/_header.blade.php --}}
<header class="flex-shrink-0 bg-white dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700 px-6 lg:px-8 py-4">
  <div class="flex items-center justify-between">
    <div class="flex items-center gap-4">
      <button class="lg:hidden p-2 rounded-lg border border-slate-200 dark:border-slate-700" onclick="toggleSidebar()">
        <span class="material-icons text-slate-500">menu</span>
      </button>
      <div>
        <h1 class="text-xl font-bold text-slate-800 dark:text-slate-100">
          {{ __('app.fin_title') }}
        </h1>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">
          {{ now()->format('F Y') }}
        </p>
      </div>
    </div>

    <div class="flex items-center gap-2">
      @if($canManage)
        @if($tab === 'transactions')
          <button type="button" onclick="openAddTransactionModal()"
            class="inline-flex items-center gap-2 px-4 py-2 bg-primary text-white text-sm font-medium rounded-lg hover:opacity-90 transition-opacity shadow-sm">
            <span class="material-icons text-[18px]">add</span>
            {{ __('app.fin_add_transaction') }}
          </button>
        @elseif($tab === 'reports')
          <button type="button" onclick="openGenerateReportModal()"
            class="inline-flex items-center gap-2 px-4 py-2 bg-primary text-white text-sm font-medium rounded-lg hover:opacity-90 transition-opacity shadow-sm">
            <span class="material-icons text-[18px]">add_chart</span>
            {{ __('app.fin_generate_report') }}
          </button>
        @else
          <button type="button" onclick="openAddTransactionModal()"
            class="inline-flex items-center gap-2 px-4 py-2 bg-primary text-white text-sm font-medium rounded-lg hover:opacity-90 transition-opacity shadow-sm">
            <span class="material-icons text-[18px]">add</span>
            {{ __('app.fin_add_transaction') }}
          </button>
        @endif
      @endif

      {{-- Dark mode toggle --}}
      <button onclick="toggleDark()" title="Toggle dark mode"
        class="p-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg hover:border-primary/50 transition-all">
        <span class="material-icons text-slate-500 dark:text-slate-400 text-[20px]">dark_mode</span>
      </button>
    </div>
  </div>
</header>
