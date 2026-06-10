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
      <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden mb-6">
        <div class="overflow-x-auto">
          <nav class="flex border-b border-slate-100 dark:border-slate-800 min-w-max" aria-label="Finance sections">
            @if($canManage)
              @foreach([
                'dashboard'    => ['icon' => 'dashboard',      'label' => __('app.fin_tab_dashboard')],
                'transactions' => ['icon' => 'receipt_long',   'label' => __('app.fin_tab_transactions')],
                'reports'      => ['icon' => 'summarize',      'label' => __('app.fin_tab_reports')],
              ] as $tabKey => $tabInfo)
                <button type="button" id="tab-btn-{{ $tabKey }}" onclick="switchTab('{{ $tabKey }}')"
                  class="finance-tab-btn flex items-center gap-2 px-5 py-4 text-sm font-semibold whitespace-nowrap border-b-2 transition-all
                         border-transparent text-slate-500 hover:text-slate-800 dark:hover:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800/40">
                  <span class="material-icons text-[18px]">{{ $tabInfo['icon'] }}</span>
                  {{ $tabInfo['label'] }}
                </button>
              @endforeach
            @else
              {{-- Residents: only Monthly Reports tab --}}
              <button type="button" id="tab-btn-reports" onclick="switchTab('reports')"
                 class="finance-tab-btn flex items-center gap-2 px-5 py-4 text-sm font-semibold whitespace-nowrap border-b-2 transition-all
                        border-transparent text-slate-500 hover:text-slate-800 dark:hover:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800/40">
                <span class="material-icons text-[18px]">summarize</span>
                {{ __('app.fin_tab_reports') }}
              </button>
            @endif
          </nav>
        </div>
      </div>

      {{-- Tab content --}}
      <div id="tab-dashboard" class="hidden">
        @if($canManage)
          @include('finance._dashboard')
        @endif
      </div>
      <div id="tab-transactions" class="hidden">
        @if($canManage)
          @include('finance._transactions')
        @endif
      </div>
      <div id="tab-reports" class="hidden">
        @include('finance._reports')
      </div>

    </div>
  </main>

  @include('finance._modals')

  <script>
    const tabIds = ['dashboard', 'transactions', 'reports'];
    const canManage = {{ $canManage ? 'true' : 'false' }};

    function switchTab(active) {
      tabIds.forEach(function(id) {
        const panel = document.getElementById('tab-' + id);
        const btn   = document.getElementById('tab-btn-' + id);
        if (!panel || !btn) { return; }
        if (id === active) {
          panel.classList.remove('hidden');
          btn.classList.add('border-primary', 'text-primary');
          btn.classList.remove('border-transparent', 'text-slate-500');
        } else {
          panel.classList.add('hidden');
          btn.classList.remove('border-primary', 'text-primary');
          btn.classList.add('border-transparent', 'text-slate-500');
        }
      });

      // Update header buttons visibility
      const txBtn = document.getElementById('header-btn-transactions');
      const rptBtn = document.getElementById('header-btn-reports');
      if (txBtn) txBtn.classList.toggle('hidden', active === 'reports');
      if (rptBtn) rptBtn.classList.toggle('hidden', active !== 'reports');

      sessionStorage.setItem('financeTab', active);
      
      const url = new URL(window.location);
      url.searchParams.set('tab', active);
      window.history.replaceState({}, '', url);
    }

    // Initialize tab
    const urlParams = new URLSearchParams(window.location.search);
    const urlTab = urlParams.get('tab');
    let defaultTab = canManage ? 'dashboard' : 'reports';
    const savedTab = urlTab || sessionStorage.getItem('financeTab') || defaultTab;
    switchTab(savedTab);
  </script>

</x-layouts.app>
