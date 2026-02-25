{{-- Resident Personal Overview — orchestrator --}}
<x-layouts.app title="My Overview"
  class="font-display bg-background-light dark:bg-background-dark text-slate-800 dark:text-slate-200 antialiased min-h-screen">

  @include('my-overview._sidebar')

  <main class="lg:ml-64 flex flex-col min-h-screen">

    @include('my-overview._header')

    <div class="p-8 space-y-8 max-w-7xl mx-auto w-full">

      {{-- Flash --}}
      @if(session('success'))
        <div
          class="p-4 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-900/30 rounded-xl flex items-center gap-3">
          <span class="material-icons text-emerald-500">check_circle</span>
          <p class="text-sm text-emerald-700 dark:text-emerald-400">{{ session('success') }}</p>
        </div>
      @endif

      @if (!$resident)
        {{-- No resident record linked --}}
        <div class="text-center py-24">
          <span class="material-icons text-5xl text-slate-300 dark:text-slate-600 block mb-4">person_off</span>
          <h2 class="text-xl font-bold text-slate-700 dark:text-slate-300">No Resident Profile Found</h2>
          <p class="text-slate-500 mt-2">Your account is not yet linked to a resident record. Please contact management.
          </p>
        </div>
      @else
        @include('my-overview._cards')
        @include('my-overview._history')
      @endif

    </div>
  </main>

</x-layouts.app>