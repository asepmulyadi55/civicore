{{-- Homepage header --}}
<header
  class="h-16 bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between px-6 lg:px-8">
  <div class="flex items-center gap-4">
    <button class="lg:hidden p-2 rounded-lg border border-slate-200 dark:border-slate-800"
      onclick="toggleSidebar()">
      <span class="material-icons text-slate-500">menu</span>
    </button>
    <h1 class="text-xl font-bold text-slate-900 dark:text-white">{{ __('app.nav_homepage') }}</h1>
    <span class="hidden sm:inline px-2.5 py-1 text-xs font-semibold bg-primary/10 text-primary rounded-lg">{{ $totalEvents }} {{ __('app.hp_section_events') }}</span>
  </div>
  <button class="p-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg hover:border-primary/50 transition-all"
    onclick="toggleDark()" title="Toggle dark mode">
    <span class="material-icons text-slate-500 text-[20px]">dark_mode</span>
  </button>
</header>
