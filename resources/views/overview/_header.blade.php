{{-- Top bar header for the resident overview page --}}
<header
  class="h-16 bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between px-4 lg:px-8">
  <div class="flex items-center gap-3">
    <button class="lg:hidden p-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg"
      onclick="toggleSidebar()">
      <span class="material-icons text-slate-500">menu</span>
    </button>
    <h1 class="text-xl font-bold">{{ $pageTitle ?? __('app.nav_overview') }}</h1>
  </div>
  <div class="flex items-center gap-4">
    @if($showSync ?? true)
    <div class="text-xs text-slate-400 text-right hidden sm:block">
      <p>{{ __('app.overview_last_sync') }}</p>
      <p class="font-medium">{{ now()->format('M d, Y • h:i A') }}</p>
    </div>
    <button onclick="window.location.reload()" class="p-2 text-slate-400 hover:text-primary transition-colors"
      title="Refresh">
      <span class="material-icons">refresh</span>
    </button>
    @endif
    <button onclick="toggleDark()" class="p-2 text-slate-400 hover:text-primary transition-colors" title="Toggle dark mode">
      <span class="material-icons">dark_mode</span>
    </button>
  </div>
</header>