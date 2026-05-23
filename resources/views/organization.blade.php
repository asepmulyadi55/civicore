{{-- organization.blade.php — Orchestrator --}}
<x-layouts.app :title="__('app.org_title')">

  <x-nav.sidebar active="organization" />

  <div class="lg:pl-64 min-h-screen bg-background-light dark:bg-background-dark flex flex-col">
    @include('organization._header')

    <div class="flex-1 p-4 lg:p-8 space-y-6">

      {{-- Flash messages --}}
      @if(session('success'))
        <div class="flex items-center gap-3 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 text-emerald-700 dark:text-emerald-400 px-4 py-3 rounded-lg text-sm font-medium">
          <span class="material-icons text-base">check_circle</span>
          {{ session('success') }}
        </div>
      @endif
      @if(session('error'))
        <div class="flex items-center gap-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-400 px-4 py-3 rounded-lg text-sm font-medium">
          <span class="material-icons text-base">error</span>
          {{ session('error') }}
        </div>
      @endif
      @if($errors->any())
        <div class="flex items-start gap-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-400 px-4 py-3 rounded-lg text-sm font-medium">
          <span class="material-icons text-base mt-0.5">error</span>
          <ul class="list-disc pl-4 space-y-0.5">
            @foreach($errors->all() as $e)
              <li>{{ $e }}</li>
            @endforeach
          </ul>
        </div>
      @endif

      {{-- Period selector --}}
      @if($periods->count() > 1)
        <div class="flex items-center gap-3 flex-wrap">
          <span class="text-sm font-semibold text-slate-500 dark:text-slate-400">{{ __('app.org_period') }}:</span>
          <div class="flex flex-wrap gap-2">
            @foreach($periods as $period)
              <a href="{{ route('organization.index', ['period_id' => $period->id]) }}"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold border transition-all
                  {{ $selectedPeriod?->id === $period->id
                      ? 'bg-primary text-white border-primary dark:bg-secondary dark:border-secondary dark:text-primary'
                      : 'bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:border-primary/50 dark:hover:border-secondary/50' }}">
                {{ $period->name }}
                @if($period->is_active)
                  <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 inline-block"></span>
                @endif
              </a>
            @endforeach
          </div>
        </div>
      @endif

      {{-- Org chart --}}
      @include('organization._tree')

      {{-- Admin: period management panel --}}
      @if(auth()->user()->can('organization.create') || auth()->user()->can('organization.edit') || auth()->user()->can('organization.delete'))
        @include('organization._periods_panel')
      @endif

    </div>
  </div>

  @include('organization._modals')

</x-layouts.app>
