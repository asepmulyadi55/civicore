{{-- users.blade.php — Orchestrator --}}
<x-layouts.app title="User Management"
  class="font-display bg-background-light dark:bg-background-dark text-slate-800 dark:text-slate-200 antialiased min-h-screen">

  <x-nav.sidebar active="users" />

  {{-- Create / Edit modals (Blade components) --}}
  <x-modals.create-user />
  <x-modals.edit-user />

  <main class="lg:ml-64 flex flex-col min-h-screen">

    @include('users._header')

    <div class="flex-1 p-6 lg:p-8 space-y-6">

      {{-- Flash messages --}}
      @if(session('success'))
        <div
          class="p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-900/30 rounded-xl flex items-center space-x-3">
          <span class="material-icons text-green-500">check_circle</span>
          <p class="text-sm text-green-700 dark:text-green-400">{{ session('success') }}</p>
        </div>
      @endif
      @if(session('error'))
        <div
          class="p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-900/30 rounded-xl flex items-center space-x-3">
          <span class="material-icons text-red-500">error</span>
          <p class="text-sm text-red-700 dark:text-red-400">{{ session('error') }}</p>
        </div>
      @endif

      @include('users._stats')
      @include('users._filters')
      @include('users._table')

    </div>
  </main>

  {{-- User confirm modal + shared JS helpers (component, consistent with other modals) --}}
  <x-modals.user-actions />

</x-layouts.app>