{{-- residents.blade.php — Orchestrator --}}
<x-layouts.app title="Residents">

  <x-nav.sidebar active="residents" />

  <div class="lg:pl-64 min-h-screen bg-background-light dark:bg-background-dark">
    {{-- Mobile header --}}
    <div
      class="lg:hidden h-14 bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800 flex items-center px-4 gap-3">
      <button onclick="toggleSidebar()" class="text-slate-500 hover:text-primary transition-colors">
        <span class="material-icons">menu</span>
      </button>
      <span class="font-bold text-primary">CiviCore</span>
    </div>

    <div class="p-6 max-w-7xl mx-auto">
      @include('residents._header')
      @include('residents._filters')
      @include('residents._table')
    </div>
  </div>

  @include('residents._drawer')

</x-layouts.app>