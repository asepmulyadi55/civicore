{{-- residents.blade.php — Orchestrator --}}
<x-layouts.app title="Residents">

  <x-nav.sidebar active="residents" />

  <div class="lg:pl-64 min-h-screen bg-background-light dark:bg-background-dark flex flex-col">
    @include('residents._header')

    <div class="flex-1 p-6 lg:p-8 space-y-6">
      @include('residents._filters')
      @include('residents._table')
    </div>
  </div>

  @include('residents._drawer')

</x-layouts.app>