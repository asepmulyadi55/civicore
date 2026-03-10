{{-- reports.blade.php — Orchestrator --}}
<x-layouts.app title="{{ __('app.nav_reports') }}"
  class="font-display bg-background-light dark:bg-background-dark text-slate-800 dark:text-slate-200 antialiased min-h-screen">

  <x-nav.sidebar active="reports" />

  <main class="lg:ml-64 flex flex-col h-screen overflow-hidden">

    @include('reports._header')

    <div class="flex-1 overflow-y-auto p-6 lg:p-8 space-y-6">
      @include('reports._filters')
      @include('reports._stats')
      @include('reports._table')

      {{-- Footer --}}
      <footer class="mt-8 pt-8 border-t border-slate-200 dark:border-slate-800">
        <p class="text-xs text-slate-400 font-medium">
          &copy; {{ now()->year }} CiviCore Community Management. {{ __('app.generated') }}
          {{ now()->format('M d, Y \a\t h:i A') }}.
        </p>
      </footer>
    </div>

  </main>

</x-layouts.app>