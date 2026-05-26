{{-- Residents Page Header --}}
<header
  class="h-16 bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between px-6 lg:px-8">
  <div class="flex items-center gap-4">
    <button class="lg:hidden p-2 rounded-lg border border-slate-200 dark:border-slate-800" onclick="toggleSidebar()">
      <span class="material-icons text-slate-500">menu</span>
    </button>
    <h1 class="text-xl font-bold text-slate-900 dark:text-white">{{ __('app.resident_directory') }}</h1>
    <span
      class="hidden sm:inline px-2 py-1 text-xs font-semibold bg-primary/10 text-primary rounded-lg">{{ $totalCount }} Residents</span>
  </div>
  <div class="flex items-center gap-3">
    {{-- Import from Excel --}}
    @if(auth()->user()->can('residents.create'))
    <button onclick="document.getElementById('modal-import-residents').classList.remove('hidden'); document.getElementById('modal-import-residents').classList.add('flex')"
      class="flex items-center gap-2 border border-primary/30 hover:bg-primary/5 text-primary dark:border-secondary/30 dark:hover:bg-secondary/5 dark:text-secondary px-4 py-2 rounded-lg font-semibold transition-all text-sm">
      <span class="material-icons text-sm">upload_file</span>
      <span class="hidden sm:inline">Import Excel</span>
    </button>
    {{-- Add Resident --}}
    <button id="btn-add-resident" onclick="openResidentDrawer()"
      class="flex items-center gap-2 bg-primary hover:bg-primary/90 text-white px-4 py-2 rounded-lg font-semibold transition-all shadow-sm shadow-primary/20 text-sm">
      <span class="material-icons text-sm">add</span>
      <span class="hidden sm:inline">{{ __('app.add_new_resident') }}</span>
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