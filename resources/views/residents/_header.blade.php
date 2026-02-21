{{-- residents/_header.blade.php --}}
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
  <div>
    <h2 class="text-2xl font-bold text-slate-900 dark:text-white">Resident Directory</h2>
    <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">
      Manage <span class="font-semibold text-slate-700 dark:text-slate-200">{{ $activeCount }}</span>
      active residents across <span
        class="font-semibold text-slate-700 dark:text-slate-200">{{ $blocks->count() }}</span> blocks.
    </p>
  </div>

  <div class="flex items-center gap-3">
    {{-- Dark mode toggle --}}
    <button class="p-2 rounded-lg text-slate-500 hover:text-primary hover:bg-primary/5 transition-colors"
      onclick="document.documentElement.classList.toggle('dark')" title="Toggle dark mode">
      <span class="material-icons text-xl">dark_mode</span>
    </button>

    {{-- Add Resident --}}
    <button id="btn-add-resident" onclick="openResidentDrawer()"
      class="flex items-center gap-2 bg-primary hover:bg-primary/90 text-white px-4 py-2.5 rounded-lg font-semibold text-sm transition-all shadow-lg shadow-primary/20">
      <span class="material-icons text-lg">add</span>
      Add New Resident
    </button>
  </div>
</div>