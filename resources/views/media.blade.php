{{-- Media Manager Page — Orchestrator --}}
<x-layouts.app :title="__('app.media_manager')"
  class="font-display bg-background-light dark:bg-background-dark text-slate-800 dark:text-slate-200 antialiased min-h-screen">

  <x-nav.sidebar active="media" />

  <main class="lg:ml-64 flex flex-col h-screen overflow-hidden">

    @include('media._header')

    <div class="flex-1 overflow-y-auto p-6 lg:p-8 space-y-6">
      @include('media._filters')
      @include('media._grid')
    </div>

  </main>

  @include('media._modals')

</x-layouts.app>
