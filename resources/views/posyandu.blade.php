{{-- posyandu.blade.php — Orchestrator --}}
<x-layouts.app :title="__('app.posyandu_title')"
  class="font-display bg-background-light dark:bg-background-dark text-slate-800 dark:text-slate-200 antialiased min-h-screen">

  <x-nav.sidebar active="posyandu" />

  <main class="lg:ml-64 flex flex-col min-h-screen">

    @include('posyandu._header')

    <div class="flex-1 p-6 lg:p-8 space-y-6">
      @include('posyandu._filters')
      @include('posyandu._table')
    </div>

  </main>

</x-layouts.app>