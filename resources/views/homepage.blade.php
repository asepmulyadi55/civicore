{{-- Homepage CMS Page � Orchestrator --}}
<x-layouts.app :title="__('app.nav_homepage')"
  class="font-display bg-background-light dark:bg-background-dark text-slate-800 dark:text-slate-200 antialiased min-h-screen">

  <x-nav.sidebar active="homepage" />

  <div class="lg:pl-64 min-h-screen bg-background-light dark:bg-background-dark flex flex-col">

    @include('homepage._header')

    <main class="flex-1 p-6 lg:p-8 space-y-6">

      {{-- Flash Messages --}}
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
            <span class="material-icons text-rose-500">warning</span>
            <p class="text-sm font-semibold text-rose-700 dark:text-rose-400">Please fix the following errors:</p>
          </div>
          <ul class="list-disc list-inside text-sm text-rose-600 dark:text-rose-400 space-y-1 ml-7">
            @foreach($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
        </div>
      @endif

      {{-- ── Tab Navigation ──────────────────────────────────────── --}}
      <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
          <nav class="flex border-b border-slate-100 dark:border-slate-800 min-w-max" id="hp-tab-nav" aria-label="Homepage sections">

            <button type="button" id="hp-tab-btn-featured" onclick="switchHpTab('featured')"
              class="hp-tab-btn flex items-center gap-2 px-5 py-4 text-sm font-semibold whitespace-nowrap border-b-2 transition-all
                     border-primary text-primary">
              <span class="material-icons text-[18px]">star</span>
              {{ __('app.hp_section_featured') }}
            </button>

            <button type="button" id="hp-tab-btn-events" onclick="switchHpTab('events')"
              class="hp-tab-btn flex items-center gap-2 px-5 py-4 text-sm font-semibold whitespace-nowrap border-b-2 transition-all
                     border-transparent text-slate-500 hover:text-slate-800 dark:hover:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800/40">
              <span class="material-icons text-[18px]">event</span>
              {{ __('app.hp_section_events') }}
              <span class="px-1.5 py-0.5 text-[10px] font-bold bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 rounded-full">{{ $totalEvents }}</span>
            </button>

            <button type="button" id="hp-tab-btn-moments" onclick="switchHpTab('moments')"
              class="hp-tab-btn flex items-center gap-2 px-5 py-4 text-sm font-semibold whitespace-nowrap border-b-2 transition-all
                     border-transparent text-slate-500 hover:text-slate-800 dark:hover:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800/40">
              <span class="material-icons text-[18px]">photo_library</span>
              {{ __('app.hp_section_moments') }}
            </button>

            <button type="button" id="hp-tab-btn-buletin" onclick="switchHpTab('buletin')"
              class="hp-tab-btn flex items-center gap-2 px-5 py-4 text-sm font-semibold whitespace-nowrap border-b-2 transition-all
                     border-transparent text-slate-500 hover:text-slate-800 dark:hover:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800/40">
              <span class="material-icons text-[18px]">article</span>
              {{ __('app.hp_section_buletin') }}
              <span class="px-1.5 py-0.5 text-[10px] font-bold bg-sky-100 dark:bg-sky-900/30 text-sky-700 dark:text-sky-400 rounded-full">{{ $totalBuletin }}</span>
            </button>

            <button type="button" id="hp-tab-btn-about" onclick="switchHpTab('about')"
              class="hp-tab-btn flex items-center gap-2 px-5 py-4 text-sm font-semibold whitespace-nowrap border-b-2 transition-all
                     border-transparent text-slate-500 hover:text-slate-800 dark:hover:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800/40">
              <span class="material-icons text-[18px]">info</span>
              {{ __('app.hp_section_about') }}
            </button>

            <button type="button" id="hp-tab-btn-footer" onclick="switchHpTab('footer')"
              class="hp-tab-btn flex items-center gap-2 px-5 py-4 text-sm font-semibold whitespace-nowrap border-b-2 transition-all
                     border-transparent text-slate-500 hover:text-slate-800 dark:hover:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800/40">
              <span class="material-icons text-[18px]">web_asset</span>
              {{ __('app.hp_section_footer') }}
            </button>

          </nav>
        </div>
      </div>

      {{-- ── Tab Panels ───────────────────────────────────────────── --}}
      <div id="hp-tab-featured" class="hp-tab-panel">
        @include('homepage._featured')
      </div>
      <div id="hp-tab-events" class="hp-tab-panel hidden">
        @include('homepage._events')
      </div>
      <div id="hp-tab-buletin" class="hp-tab-panel hidden">
        @include('homepage._buletin')
      </div>
      <div id="hp-tab-moments" class="hp-tab-panel hidden">
        @include('homepage._memorable_moments')
      </div>
      <div id="hp-tab-about" class="hp-tab-panel hidden">
        @include('homepage._about')
      </div>
      <div id="hp-tab-footer" class="hp-tab-panel hidden">
        @include('homepage._footer')
      </div>

    </main>

    <script>
    function switchHpTab(tab, updateHistory) {
      document.querySelectorAll('.hp-tab-panel').forEach(function(p) { p.classList.add('hidden'); });
      document.querySelectorAll('.hp-tab-btn').forEach(function(b) {
        b.classList.remove('border-primary', 'text-primary');
        b.classList.add('border-transparent', 'text-slate-500');
      });
      var panel = document.getElementById('hp-tab-' + tab);
      if (panel) panel.classList.remove('hidden');
      var btn = document.getElementById('hp-tab-btn-' + tab);
      if (btn) {
        btn.classList.remove('border-transparent', 'text-slate-500');
        btn.classList.add('border-primary', 'text-primary');
      }
      if (updateHistory !== false) history.replaceState(null, null, location.pathname + location.search + '#' + tab);
    }
    (function () {
      var validTabs = ['featured', 'events', 'buletin', 'moments', 'about', 'footer'];
      var stored    = sessionStorage.getItem('hp_active_tab');
      var hash      = location.hash.slice(1);
      sessionStorage.removeItem('hp_active_tab');
      var active = (stored && validTabs.indexOf(stored) !== -1) ? stored
                 : (validTabs.indexOf(hash) !== -1             ? hash
                 : 'featured');
      switchHpTab(active, false);

      // Remember active tab across form submissions
      document.querySelectorAll('.hp-tab-panel').forEach(function(panel) {
        panel.querySelectorAll('form').forEach(function(form) {
          form.addEventListener('submit', function() {
            sessionStorage.setItem('hp_active_tab', panel.id.replace('hp-tab-', ''));
          });
        });
      });
    })();
    </script>
  </div>

  @include('homepage._modals')

</x-layouts.app>
