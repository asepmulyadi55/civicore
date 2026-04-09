{{-- Blocks Management Page — Orchestrator --}}
<x-layouts.app :title="__('app.nav_blocks')"
  class="font-display bg-background-light dark:bg-background-dark text-slate-800 dark:text-slate-200 antialiased min-h-screen">

  <x-nav.sidebar active="blocks" />
  <x-modals.block-form />

  <div class="lg:pl-64 min-h-screen bg-background-light dark:bg-background-dark flex flex-col">
    @include('blocks._header')
    <main class="flex-1 p-6 lg:p-8 space-y-6">

      @include('blocks._grid')
    </main>
  </div>

  @include('blocks._delete_modal')

</x-layouts.app>