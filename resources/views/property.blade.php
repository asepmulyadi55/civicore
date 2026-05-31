{{-- property.blade.php — Orchestrator --}}
<x-layouts.app title="{{ __('app.nav_property') }}"
  class="font-display bg-background-light dark:bg-background-dark text-slate-800 dark:text-slate-200 antialiased min-h-screen">

  <x-nav.sidebar active="property" />

  <main class="lg:ml-64 flex flex-col min-h-screen">

    @include('property._header')

    <div class="flex-1 p-6 lg:p-8 space-y-6">

      {{-- Flash Messages --}}
      @if(session('success'))
        <div class="p-4 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 rounded-xl flex items-center gap-3">
          <span class="material-icons text-emerald-500">check_circle</span>
          <p class="text-sm text-emerald-700 dark:text-emerald-400">{{ session('success') }}</p>
        </div>
      @endif
      @if($errors->any())
        <div class="p-4 bg-rose-50 dark:bg-rose-900/20 border border-rose-200 dark:border-rose-800 rounded-xl flex items-start gap-3">
          <span class="material-icons text-rose-500 mt-0.5">error</span>
          <ul class="text-sm text-rose-700 dark:text-rose-400 list-disc list-inside space-y-0.5">
            @foreach($errors->all() as $err) <li>{{ $err }}</li> @endforeach
          </ul>
        </div>
      @endif

      @include('property._filters')
      @include('property._table')

    </div>

  </main>

  @include('property._modals')

</x-layouts.app>
