{{-- Settings Page — Orchestrator --}}
<x-layouts.app :title="__('app.settings_title')"
  class="font-display bg-background-light dark:bg-background-dark text-slate-800 dark:text-slate-200 antialiased min-h-screen">

  <x-nav.sidebar active="settings" />

  <div class="lg:pl-64 min-h-screen bg-background-light dark:bg-background-dark flex flex-col">

    @include('settings._header')

    <main class="flex-1 p-6 lg:p-8 max-w-3xl w-full">

      {{-- Flash --}}
      @if(session('success'))
        <div class="mb-6 p-4 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 rounded-xl flex items-center gap-3">
          <span class="material-icons text-emerald-500">check_circle</span>
          <p class="text-sm text-emerald-700 dark:text-emerald-400">{{ session('success') }}</p>
        </div>
      @endif

      {{-- Tab Nav --}}
      @php
        $tabs = [
          ['id' => 'profile',  'icon' => 'person',        'label' => __('app.settings_tab_profile')],
          ['id' => 'password', 'icon' => 'lock',          'label' => __('app.settings_tab_password')],
        ];
        if (auth()->user()->isAdmin()) {
          $tabs[] = ['id' => 'security', 'icon' => 'security',      'label' => __('app.settings_tab_security')];
          $tabs[] = ['id' => 'memo',     'icon' => 'sticky_note_2',    'label' => 'Admin Memo'];
          $tabs[] = ['id' => 'posyandu', 'icon' => 'health_and_safety','label' => 'Posyandu'];
        }
      @endphp

      <div class="grid grid-cols-2 sm:flex gap-1 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-1 rounded-xl mb-6">
        @foreach($tabs as $tab)
          <button type="button" id="tab-btn-{{ $tab['id'] }}" onclick="switchTab('{{ $tab['id'] }}')"
            class="settings-tab-btn flex items-center justify-center gap-1.5 px-3 sm:px-5 py-2 rounded-lg text-sm font-semibold transition-all text-slate-500 dark:text-slate-400">
            <span class="material-icons text-base">{{ $tab['icon'] }}</span>
            {{ $tab['label'] }}
          </button>
        @endforeach
      </div>

      @include('settings._profile')
      @include('settings._password')
      @if(auth()->user()->isAdmin())
        @include('settings._security')
        @include('settings._memo')
        @include('settings._posyandu')
      @endif

    </main>
  </div>

  <script>
    const tabIds = ['profile', 'password', 'security', 'memo', 'posyandu'];

    function switchTab(active) {
      tabIds.forEach(function(id) {
        const panel = document.getElementById('tab-' + id);
        const btn   = document.getElementById('tab-btn-' + id);
        if (!panel || !btn) { return; }
        if (id === active) {
          panel.classList.remove('hidden');
          btn.classList.add('bg-primary/10', 'text-primary');
          btn.classList.remove('text-slate-500', 'dark:text-slate-400');
        } else {
          panel.classList.add('hidden');
          btn.classList.remove('bg-primary/10', 'text-primary');
          btn.classList.add('text-slate-500', 'dark:text-slate-400');
        }
      });
      sessionStorage.setItem('settingsTab', active);
    }

    const hasPasswordErrors = {{ ($errors->has('current_password') || $errors->has('password')) ? 'true' : 'false' }};
    const savedTab = hasPasswordErrors ? 'password' : (sessionStorage.getItem('settingsTab') || 'profile');
    switchTab(savedTab);

    function previewAvatar(event) {
      const file = event.target.files[0];
      if (!file) { return; }
      const reader = new FileReader();
      reader.onload = function(e) {
        document.getElementById('avatar-preview').src = e.target.result;
      };
      reader.readAsDataURL(file);
    }

    function updateLangCards() {
      document.querySelectorAll('input[name="language"]').forEach(function(radio) {
        const card = radio.closest('label');
        const icon = card.querySelector('.material-icons.ml-auto');
        if (radio.checked) {
          card.classList.add('border-primary', 'bg-primary/5', 'dark:border-secondary', 'dark:bg-secondary/10');
          card.classList.remove('border-slate-200', 'dark:border-slate-700', 'hover:border-primary/40', 'dark:hover:border-secondary/40');
          if (icon) {
            icon.textContent = 'check_circle';
            icon.classList.remove('text-slate-300');
            icon.classList.add('text-secondary');
          }
        } else {
          card.classList.remove('border-primary', 'bg-primary/5', 'dark:border-secondary', 'dark:bg-secondary/10');
          card.classList.add('border-slate-200', 'dark:border-slate-700', 'hover:border-primary/40', 'dark:hover:border-secondary/40');
          if (icon) {
            icon.textContent = 'radio_button_unchecked';
            icon.classList.remove('text-secondary');
            icon.classList.add('text-slate-300');
          }
        }
      });
    }

    function checkPasswordStrengthSettings(pw) {
      const box = document.getElementById('sp-requirements');
      if (!pw) { box.classList.add('hidden'); return; }
      box.classList.remove('hidden');
      function setReq(id, passed) {
        const el = document.getElementById(id);
        const icon = el.querySelector('.material-icons');
        if (passed) { el.classList.replace('text-slate-400', 'text-emerald-500'); icon.textContent = 'check_circle'; }
        else { el.classList.replace('text-emerald-500', 'text-slate-400'); icon.textContent = 'radio_button_unchecked'; }
      }
      setReq('sp-req-length', pw.length >= 8);
      setReq('sp-req-upper',  /[A-Z]/.test(pw));
      setReq('sp-req-lower',  /[a-z]/.test(pw));
      setReq('sp-req-number', /[0-9]/.test(pw));
      setReq('sp-req-symbol', /[^A-Za-z0-9]/.test(pw));
    }
  </script>

</x-layouts.app>
