{{-- finance.blade.php — Finance module orchestrator --}}
<x-layouts.app :title="__('app.fin_title')"
  class="font-display bg-background-light dark:bg-background-dark text-slate-800 dark:text-slate-200 antialiased min-h-screen">

  <x-nav.sidebar active="finance" />

  <main class="lg:ml-64 flex flex-col h-screen overflow-hidden">

    @include('finance._header')

    <div class="flex-1 overflow-y-auto p-6 lg:p-8 space-y-6">

      {{-- Flash messages --}}
      @if(session('success'))
        <div class="p-4 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 rounded-xl flex items-center gap-3">
          <span class="material-icons text-emerald-500">check_circle</span>
          <p class="text-sm text-emerald-700 dark:text-emerald-400">{{ session('success') }}</p>
        </div>
      @endif
      @if(session('error'))
        <div class="p-4 bg-rose-50 dark:bg-rose-900/20 border border-rose-200 dark:border-rose-800 rounded-xl flex items-center gap-3">
          <span class="material-icons text-rose-500">error</span>
          <p class="text-sm text-rose-700 dark:text-rose-400">{{ session('error') }}</p>
        </div>
      @endif
      @if($errors->any())
        <div class="p-4 bg-rose-50 dark:bg-rose-900/20 border border-rose-200 dark:border-rose-800 rounded-xl">
          <div class="flex items-center gap-3 mb-2">
            <span class="material-icons text-rose-500">error_outline</span>
            <p class="text-sm font-medium text-rose-700 dark:text-rose-400">{{ $errors->first() }}</p>
          </div>
        </div>
      @endif

      {{-- Tab navigation --}}
      <div class="flex gap-1 border-b border-slate-200 dark:border-slate-700">
        @can('finance.create')
          @foreach([
            'dashboard'    => ['icon' => 'dashboard',      'label' => __('app.fin_tab_dashboard')],
            'transactions' => ['icon' => 'receipt_long',   'label' => __('app.fin_tab_transactions')],
            'reports'      => ['icon' => 'summarize',      'label' => __('app.fin_tab_reports')],
          ] as $tabKey => $tabInfo)
            <a href="{{ route('finance.index', ['tab' => $tabKey]) }}"
               class="flex items-center gap-2 px-4 py-2.5 text-sm font-medium rounded-t-lg border-b-2 transition-colors
                 {{ $tab === $tabKey
                   ? 'border-primary text-primary dark:text-emerald-400 dark:border-emerald-400 bg-white dark:bg-slate-800/50'
                   : 'border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300 hover:border-slate-300 dark:hover:border-slate-600' }}">
              <span class="material-icons text-[18px]">{{ $tabInfo['icon'] }}</span>
              {{ $tabInfo['label'] }}
            </a>
          @endforeach
        @else
          {{-- Residents: only Monthly Reports tab --}}
          <a href="{{ route('finance.index', ['tab' => 'reports']) }}"
             class="flex items-center gap-2 px-4 py-2.5 text-sm font-medium rounded-t-lg border-b-2 transition-colors
               border-primary text-primary dark:text-emerald-400 dark:border-emerald-400 bg-white dark:bg-slate-800/50">
            <span class="material-icons text-[18px]">summarize</span>
            {{ __('app.fin_tab_reports') }}
          </a>
        @endcan
      </div>

      {{-- Tab content --}}
      @if($tab === 'transactions')
        @include('finance._transactions')
      @elseif($tab === 'reports')
        @include('finance._reports')
      @else
        @include('finance._dashboard')
      @endif

    </div>
  </main>

  @include('finance._modals')

</x-layouts.app>
