{{-- meetings.blade.php — Orchestrator --}}
<x-layouts.app :title="__('app.meeting_title')">

  <x-nav.sidebar active="meetings" />

  <div class="lg:pl-64 min-h-screen bg-background-light dark:bg-background-dark flex flex-col">
    @include('meetings._header')

    <div class="flex-1 p-6 lg:p-8 space-y-6">

      {{-- Flash messages --}}
      @if(session('success'))
        <div class="flex items-center gap-3 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 text-emerald-700 dark:text-emerald-400 px-4 py-3 rounded-xl text-sm font-medium">
          <span class="material-icons text-base">check_circle</span>
          {{ session('success') }}
        </div>
      @endif
      @if(session('error'))
        <div class="flex items-center gap-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-400 px-4 py-3 rounded-xl text-sm font-medium">
          <span class="material-icons text-base">error</span>
          {{ session('error') }}
        </div>
      @endif
      @if($errors->any())
        <div class="flex items-start gap-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-400 px-4 py-3 rounded-xl text-sm font-medium">
          <span class="material-icons text-base mt-0.5">error</span>
          <ul class="list-disc pl-4 space-y-0.5">
            @foreach($errors->all() as $e) <li>{{ $e }}</li> @endforeach
          </ul>
        </div>
      @endif

      {{-- Filters --}}
      @include('meetings._filters')

      {{-- Meeting list --}}
      @include('meetings._list')

    </div>
  </div>

  @include('meetings._modals')

</x-layouts.app>
