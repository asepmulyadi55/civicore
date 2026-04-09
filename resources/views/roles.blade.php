<x-layouts.app title="Roles & Permissions"
  class="font-display bg-background-light dark:bg-background-dark text-slate-800 dark:text-slate-200 antialiased min-h-screen">

  <x-nav.sidebar active="roles" />

  <main class="lg:ml-64 flex flex-col h-screen overflow-hidden">
    @include('roles._header')
    <div class="flex-1 overflow-y-auto p-8 space-y-6">
      @include('roles._table')
    </div>
  </main>

  @include('roles._modals')

</x-layouts.app>