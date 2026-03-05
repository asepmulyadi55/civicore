{{-- Dashboard Page Header --}}
<header class="flex flex-col md:flex-row md:items-center justify-between gap-4">
  <div class="flex items-center space-x-4">
    <button class="lg:hidden p-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg"
      onclick="toggleSidebar()">
      <span class="material-icons text-slate-500">menu</span>
    </button>
    <div>
      <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white">{{ __('app.dashboard_overview') }}</h1>
      <p class="text-slate-500 text-sm">{{ __('app.dashboard_welcome', ['name' => Auth::user()->name]) }}</p>
    </div>
  </div>
  <div class="flex items-center space-x-4">
    <div class="relative flex-1 md:w-64">
      <span class="material-icons absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">search</span>
      <input
        class="w-full pl-10 pr-4 py-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/50 transition-all text-sm"
        placeholder="{{ __('app.search_data') }}" type="text" />
    </div>
    <button
      class="relative p-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg hover:border-primary/50 transition-all">
      <span class="material-icons text-slate-500">notifications</span>
      <span
        class="absolute top-2 right-2.5 w-2 h-2 bg-red-500 rounded-full border-2 border-white dark:border-slate-900"></span>
    </button>
    <button
      class="p-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg hover:border-primary/50 transition-all"
      onclick="toggleDark()" title="Toggle dark mode">
      <span class="material-icons text-slate-500">dark_mode</span>
    </button>
  </div>
</header>