<x-layouts.app title="Dashboard"
  class="font-display bg-background-light dark:bg-background-dark text-slate-800 dark:text-slate-200 antialiased min-h-screen">

  {{-- Sidebar --}}
  <x-nav.sidebar active="dashboard" />

  {{-- ── Main Content ────────────────────────────────────────── --}}
  <main class="lg:ml-64 p-4 lg:p-8 space-y-8">

    @include('dashboard._header')

    {{-- Flash messages --}}
    @if (session('success'))
      <div class="p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-900/30 rounded-xl flex items-center space-x-3">
        <span class="material-icons text-green-500">check_circle</span>
        <p class="text-sm text-green-700 dark:text-green-400">{{ session('success') }}</p>
      </div>
    @endif

    @include('dashboard._stats')

    {{-- Main grid: Activity table + Quick actions panel --}}
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
      @include('dashboard._activity')
      @include('dashboard._sidebar-panel')
    </div>

  </main>

  <script>
    function toggleSidebar() {
      document.getElementById('sidebar').classList.toggle('-translate-x-full');
      document.getElementById('sidebar-overlay').classList.toggle('hidden');
    }
  </script>

</x-layouts.app>