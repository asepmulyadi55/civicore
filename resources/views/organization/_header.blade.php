{{-- organization/_header.blade.php --}}
<header class="h-16 bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between px-4 lg:px-8">
  <div class="flex items-center gap-4">
    <button class="lg:hidden p-2 rounded-lg border border-slate-200 dark:border-slate-800" onclick="toggleSidebar()">
      <span class="material-icons text-slate-500">menu</span>
    </button>
    <div>
      <h1 class="text-xl font-bold text-slate-900 dark:text-white leading-tight">{{ __('app.org_title') }}</h1>
      @if($selectedPeriod)
        <p class="text-xs text-slate-400 hidden sm:block">{{ $selectedPeriod->name }}
          @if($selectedPeriod->is_active)
            <span class="inline-flex items-center gap-1 text-emerald-600 dark:text-emerald-400 font-semibold ml-1">
              <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 inline-block"></span>{{ __('app.org_active_badge') }}
            </span>
          @endif
        </p>
      @endif
    </div>
  </div>

  <div class="flex items-center gap-2 sm:gap-3">
    {{-- Add position (admin) --}}
    @if($selectedPeriod && auth()->user()->can('organization.create'))
      <button onclick="openOrgPositionModal()"
        class="flex items-center gap-2 bg-primary hover:bg-primary/90 text-white px-3 sm:px-4 py-2 rounded-lg font-semibold transition-all shadow-sm shadow-primary/20 text-sm">
        <span class="material-icons text-sm">add</span>
        <span class="hidden sm:inline">{{ __('app.org_add_position') }}</span>
      </button>
    @endif

    {{-- Manage periods (admin) --}}
    @if(auth()->user()->can('organization.create'))
      <button onclick="openOrgPeriodModal()"
        class="flex items-center gap-2 border border-primary/30 hover:bg-primary/5 text-primary dark:border-secondary/30 dark:hover:bg-secondary/5 dark:text-secondary px-3 sm:px-4 py-2 rounded-lg font-semibold transition-all text-sm">
        <span class="material-icons text-sm">date_range</span>
        <span class="hidden sm:inline">{{ __('app.org_add_period') }}</span>
      </button>
    @endif

    {{-- Dark mode --}}
    <button class="p-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg hover:border-primary/50 transition-all"
      onclick="toggleDark()" title="Toggle dark mode">
      <span class="material-icons text-slate-500 text-[20px]">dark_mode</span>
    </button>
  </div>
</header>
