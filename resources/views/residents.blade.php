{{-- residents.blade.php — Orchestrator --}}
<x-layouts.app :title="__('app.nav_residents')">

  <x-nav.sidebar active="residents" />

  <div class="lg:pl-64 min-h-screen bg-background-light dark:bg-background-dark flex flex-col">
    @include('residents._header')

    <div class="flex-1 p-6 lg:p-8 space-y-6">
      @include('residents._filters')
      @include('residents._table')
    </div>
  </div>

  {{-- Resident form modals — in components/modals/ consistent with other sections --}}
  <x-modals.resident-form :blocks="$blocks" :currency="$currency" />
  @include('residents._import_modal')


  {{-- ── Resident Confirmation Modal (Deactivate / Delete) ────────── --}}
  <div id="resident-confirm-modal"
    class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4"
    onclick="if(event.target===this) closeResidentConfirmModal()">
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-2xl w-full max-w-sm overflow-hidden
      transform transition-all duration-200 scale-95 opacity-0" id="rcm-card">

      {{-- Icon + Message --}}
      <div class="flex flex-col items-center pt-8 pb-5 px-6 text-center">
        <div id="rcm-icon-wrap" class="w-16 h-16 rounded-full flex items-center justify-center mb-4">
          <span id="rcm-icon" class="material-icons text-3xl"></span>
        </div>
        <h3 id="rcm-title" class="text-xl font-bold text-slate-900 dark:text-white mb-2"></h3>
        <p id="rcm-body" class="text-sm text-slate-500 dark:text-slate-400 leading-relaxed"></p>
      </div>

      {{-- Buttons --}}
      <div class="flex gap-3 px-6 pb-6">
        <button onclick="closeResidentConfirmModal()" class="flex-1 px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 text-sm font-semibold
            text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition-all">
          {{ __('app.btn_cancel') }}
        </button>

        {{-- Deactivate form --}}
        <form id="rcm-form-deactivate" method="POST" action="" class="flex-1 hidden">
          @csrf @method('PATCH')
          <button type="submit"
            class="w-full px-4 py-2.5 rounded-xl text-sm font-bold text-white bg-amber-500 hover:bg-amber-600 transition-all">
            {{ __('app.btn_yes_deactivate') }}
          </button>
        </form>

        {{-- Delete form --}}
        <form id="rcm-form-delete" method="POST" action="" class="flex-1 hidden">
          @csrf @method('DELETE')
          <button type="submit"
            class="w-full px-4 py-2.5 rounded-xl text-sm font-bold text-white bg-red-600 hover:bg-red-700 transition-all">
            {{ __('app.btn_yes_delete') }}
          </button>
        </form>
      </div>
    </div>
  </div>

  <script>
    const residentsBaseUrl = "{{ url('/residents') }}";
    const RCM_CONFIGS = {
      deactivate: {
        iconWrap: 'bg-amber-100 dark:bg-amber-900/30',
        icon: 'person_off',
        iconColor: 'text-amber-500',
        title: '{{ __('app.deactivate_title') }}',
        body: (name) => `<strong class="text-slate-800 dark:text-slate-200">${name}</strong> {{ __('app.deactivate_body') }}`,
        form: 'rcm-form-deactivate',
        route: (id) => `${residentsBaseUrl}/${id}/deactivate`,
      },
      delete: {
        iconWrap: 'bg-red-100 dark:bg-red-900/30',
        icon: 'delete_forever',
        iconColor: 'text-red-600',
        title: '{{ __('app.delete_title') }}',
        body: (name) => `<strong class="text-slate-800 dark:text-slate-200">${name}</strong> {!! __('app.delete_body') !!}`,
        form: 'rcm-form-delete',
        route: (id) => `${residentsBaseUrl}/${id}`,
      },
    };

    function openResidentConfirmModal(action, residentId, residentName) {
      const cfg = RCM_CONFIGS[action];

      document.getElementById('rcm-icon-wrap').className =
        `w-16 h-16 rounded-full flex items-center justify-center mb-4 ${cfg.iconWrap}`;
      const iconEl = document.getElementById('rcm-icon');
      iconEl.textContent = cfg.icon;
      iconEl.className = `material-icons text-3xl ${cfg.iconColor}`;
      document.getElementById('rcm-title').textContent = cfg.title;
      document.getElementById('rcm-body').innerHTML = cfg.body(residentName);

      // Show only the relevant form
      ['deactivate', 'delete'].forEach(a => {
        document.getElementById(`rcm-form-${a}`).classList.toggle('hidden', a !== action);
      });
      document.getElementById(`rcm-form-${action}`).action = cfg.route(residentId);

      const modal = document.getElementById('resident-confirm-modal');
      const card = document.getElementById('rcm-card');
      modal.classList.remove('hidden');
      document.body.classList.add('overflow-hidden');
      // Animate in
      requestAnimationFrame(() => {
        card.classList.remove('scale-95', 'opacity-0');
        card.classList.add('scale-100', 'opacity-100');
      });
    }

    function closeResidentConfirmModal() {
      const modal = document.getElementById('resident-confirm-modal');
      const card = document.getElementById('rcm-card');
      card.classList.remove('scale-100', 'opacity-100');
      card.classList.add('scale-95', 'opacity-0');
      setTimeout(() => {
        modal.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
      }, 150);
    }

    document.addEventListener('keydown', e => {
      if (e.key === 'Escape') closeResidentConfirmModal();
    });
  </script>

</x-layouts.app>