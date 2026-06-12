{{-- Payments Page Header --}}
<header
  class="h-16 bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between px-6 lg:px-8">
  <div class="flex items-center gap-4">
    <button class="lg:hidden p-2 rounded-lg border border-slate-200 dark:border-slate-800" onclick="toggleSidebar()">
      <span class="material-icons text-slate-500">menu</span>
    </button>
    <h1 class="text-xl font-bold text-slate-900 dark:text-white">{{ __('app.payment_management') }}</h1>
    <span
      class="hidden sm:inline px-2 py-1 text-xs font-semibold bg-primary/10 text-primary rounded-lg">{{ $payments->total() }} Records</span>
  </div>
  <div class="flex items-center gap-3">
    @if(auth()->user()->isAdmin())
      {{-- Import Excel --}}
      <button onclick="document.getElementById('importModal').classList.remove('hidden')"
        class="hidden lg:flex items-center gap-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300 hover:text-emerald-600 dark:hover:text-emerald-400 hover:border-emerald-500 px-4 py-2 rounded-lg font-semibold transition-all shadow-sm text-sm">
        <span class="material-icons text-sm">file_upload</span>
        <span>Import Excel</span>
      </button>
    @endif

    @if(auth()->user()->can('payments.create'))
      {{-- Record Payment --}}
      <button onclick="openCreateModal()"
        class="flex items-center gap-2 bg-primary hover:bg-primary/90 text-white px-4 py-2 rounded-lg font-semibold transition-all shadow-sm shadow-primary/20 text-sm">
        <span class="material-icons text-sm">add</span>
        <span class="hidden sm:inline">{{ __('app.record_payment') }}</span>
      </button>
    @endif
    {{-- Dark mode --}}
    <button
      class="p-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg hover:border-primary/50 transition-all"
      onclick="toggleDark()" title="Toggle dark mode">
      <span class="material-icons text-slate-500 text-[20px]">dark_mode</span>
    </button>
  </div>
</header>