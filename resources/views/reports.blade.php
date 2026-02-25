{{-- reports.blade.php — Orchestrator --}}
<x-layouts.app title="Reports"
  class="font-display bg-background-light dark:bg-background-dark text-slate-800 dark:text-slate-200 antialiased min-h-screen">

  <x-nav.sidebar active="reports" />

  <main class="lg:ml-64 p-4 lg:p-8">

    @include('reports._header')
    @include('reports._filters')
    @include('reports._stats')
    @include('reports._table')

    {{-- Footer --}}
    <footer class="mt-8 pt-8 border-t border-slate-200 dark:border-slate-800">
      <p class="text-xs text-slate-400 font-medium">
        &copy; {{ now()->year }} CiviCore Community Management. Generated {{ now()->format('M d, Y \a\t h:i A') }}.
      </p>
    </footer>

  </main>

</x-layouts.app>