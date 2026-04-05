{{-- Settings / Profile Page — accessible to all roles --}}
<x-layouts.app :title="__('app.settings_title')"
  class="font-display bg-background-light dark:bg-background-dark text-slate-800 dark:text-slate-200 antialiased min-h-screen">

  <x-nav.sidebar active="settings" />

  <div class="lg:pl-64 min-h-screen bg-background-light dark:bg-background-dark flex flex-col">

    {{-- Header --}}
    <header class="h-16 bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between px-6 lg:px-8">
      <div class="flex items-center gap-4">
        <button class="lg:hidden p-2 rounded-lg border border-slate-200 dark:border-slate-800" onclick="toggleSidebar()">
          <span class="material-icons text-slate-500">menu</span>
        </button>
        <h1 class="text-xl font-bold text-slate-900 dark:text-white">{{ __('app.settings_title') }}</h1>
      </div>
      <button class="p-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg hover:border-primary/50 transition-all"
        onclick="toggleDark()" title="Toggle dark mode">
        <span class="material-icons text-slate-500 text-[20px]">dark_mode</span>
      </button>
    </header>

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
          ['id' => 'profile',  'icon' => 'person', 'label' => __('app.settings_tab_profile')],
          ['id' => 'password', 'icon' => 'lock',   'label' => __('app.settings_tab_password')],
        ];
        if (auth()->user()->isAdmin()) {
          $tabs[] = ['id' => 'security', 'icon' => 'security', 'label' => __('app.settings_tab_security')];
          $tabs[] = ['id' => 'memo', 'icon' => 'sticky_note_2', 'label' => 'Admin Memo'];
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

      {{-- ══ PROFILE TAB ══════════════════════════════════════════════════════ --}}
      <div id="tab-profile">
        <form method="POST" action="{{ route('settings.profile') }}" enctype="multipart/form-data" class="space-y-6">
          @csrf

          {{-- Photo --}}
          <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-6">
            <h2 class="text-sm font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide mb-4">
              {{ __('app.settings_photo') }}
            </h2>
            <div class="flex items-center gap-5">
              <div class="w-20 h-20 rounded-2xl overflow-hidden flex-shrink-0 bg-primary/10 border-2 border-white dark:border-slate-800 shadow-md">
                <img id="avatar-preview" src="{{ $user->avatarUrl() }}" alt="{{ $user->name }}"
                  class="w-full h-full object-cover">
              </div>
              <div class="flex-1">
                <label for="avatar-upload"
                  class="inline-flex items-center gap-2 cursor-pointer px-4 py-2 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-lg text-sm font-semibold transition-all border border-slate-200 dark:border-slate-700">
                  <span class="material-icons text-sm">upload</span>
                  {{ __('app.settings_upload_photo') }}
                </label>
                <input id="avatar-upload" type="file" name="avatar" accept="image/*" class="hidden"
                  onchange="previewAvatar(event)">
                <p class="text-xs text-slate-400 mt-2">{{ __('app.settings_photo_hint') }}</p>
                @error('avatar') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
              </div>
            </div>
          </div>

          {{-- Identity --}}
          <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-6 space-y-5">
            <h2 class="text-sm font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide">
              {{ __('app.settings_identity') }}
            </h2>

            <div>
              <label for="input-name" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                {{ __('app.settings_full_name') }}
              </label>
              <input type="text" name="name" id="input-name"
                value="{{ old('name', $user->name) }}"
                class="w-full px-3 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none transition-all">
              @error('name') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
              <label for="input-email" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                {{ __('app.settings_email') }}
              </label>
              <input type="email" id="input-email" value="{{ $user->email }}" disabled
                class="w-full px-3 py-2.5 bg-slate-100 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 rounded-lg text-sm text-slate-400 cursor-not-allowed">
              <p class="text-xs text-slate-400 mt-1">{{ __('app.settings_email_readonly') }}</p>
            </div>

            <div>
              <label for="input-username" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                {{ __('app.settings_username') }}
              </label>
              <input type="text" id="input-username" value="{{ $user->username }}" disabled
                class="w-full px-3 py-2.5 bg-slate-100 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 rounded-lg text-sm text-slate-400 cursor-not-allowed">
            </div>
          </div>

          {{-- Language --}}
          <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-6">
            <h2 class="text-sm font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide mb-4">
              {{ __('app.settings_language') }}
            </h2>
            <div class="grid grid-cols-2 gap-3">
              @foreach(['en' => ['flag' => '🇬🇧', 'label' => 'English'], 'id' => ['flag' => '🇮🇩', 'label' => 'Indonesian']] as $langCode => $lang)
                @php $isActive = (old('language', $user->language ?? 'en') === $langCode); @endphp
                <label for="lang-{{ $langCode }}"
                  class="flex items-center gap-3 p-4 rounded-xl border-2 cursor-pointer transition-all {{ $isActive ? 'border-primary bg-primary/5' : 'border-slate-200 dark:border-slate-700 hover:border-primary/40' }}">
                  <input type="radio" name="language" id="lang-{{ $langCode }}" value="{{ $langCode }}"
                    class="sr-only" {{ $isActive ? 'checked' : '' }}
                    onchange="updateLangCards()">
                  <span class="text-2xl">{{ $lang['flag'] }}</span>
                  <span class="font-semibold text-sm text-slate-800 dark:text-slate-200">{{ $lang['label'] }}</span>
                  @if($isActive)
                    <span class="ml-auto material-icons text-primary text-base">check_circle</span>
                  @else
                    <span class="ml-auto material-icons text-slate-300 text-base">radio_button_unchecked</span>
                  @endif
                </label>
              @endforeach
            </div>
            @error('language') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
            <p class="text-xs text-slate-400 mt-3">{{ __('app.settings_lang_hint') }}</p>
          </div>

          <div class="flex justify-end">
            <button type="submit"
              class="flex items-center gap-2 bg-primary hover:bg-primary/90 text-white px-6 py-2.5 rounded-lg font-semibold transition-all shadow-sm shadow-primary/20 text-sm">
              <span class="material-icons text-sm">save</span>
              {{ __('app.settings_save_profile') }}
            </button>
          </div>
        </form>
      </div>

      {{-- ══ PASSWORD TAB ═════════════════════════════════════════════════════ --}}
      <div id="tab-password" class="hidden space-y-6">

        <form method="POST" action="{{ route('settings.password') }}"
          class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-6 space-y-5">
          @csrf
          <div class="flex items-center gap-3 mb-2">
            <div class="w-9 h-9 rounded-lg bg-primary/10 flex items-center justify-center">
              <span class="material-icons text-primary text-lg">lock</span>
            </div>
            <div>
              <h2 class="font-bold text-slate-900 dark:text-white">{{ __('app.settings_change_password') }}</h2>
              <p class="text-xs text-slate-500">{{ __('app.settings_current_password') }}</p>
            </div>
          </div>

          @php
            $inputBase = 'w-full px-3 py-2.5 bg-slate-50 dark:bg-slate-800 border rounded-lg text-sm focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none transition-all';
            $inputError = 'border-rose-400';
            $inputNormal = 'border-slate-200 dark:border-slate-700';
          @endphp

          <div>
            <label for="current_password" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
              {{ __('app.settings_current_password') }}
            </label>
            <input type="password" name="current_password" id="current_password" autocomplete="current-password"
              class="{{ $inputBase }} {{ $errors->has('current_password') ? $inputError : $inputNormal }}">
            @error('current_password') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
          </div>

          <div>
            <label for="new_password" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
              {{ __('app.settings_new_password') }}
            </label>
            <input type="password" name="password" id="new_password" autocomplete="new-password"
              class="{{ $inputBase }} {{ $errors->has('password') ? $inputError : $inputNormal }}"
              oninput="checkPasswordStrengthSettings(this.value)">
            <div id="sp-requirements" class="hidden mt-2 space-y-1">
              <p id="sp-req-length" class="flex items-center gap-1.5 text-xs text-slate-400"><span class="material-icons text-sm">radio_button_unchecked</span> At least 8 characters</p>
              <p id="sp-req-upper"  class="flex items-center gap-1.5 text-xs text-slate-400"><span class="material-icons text-sm">radio_button_unchecked</span> One uppercase letter</p>
              <p id="sp-req-lower"  class="flex items-center gap-1.5 text-xs text-slate-400"><span class="material-icons text-sm">radio_button_unchecked</span> One lowercase letter</p>
              <p id="sp-req-number" class="flex items-center gap-1.5 text-xs text-slate-400"><span class="material-icons text-sm">radio_button_unchecked</span> One number</p>
              <p id="sp-req-symbol" class="flex items-center gap-1.5 text-xs text-slate-400"><span class="material-icons text-sm">radio_button_unchecked</span> One special character</p>
            </div>
            @error('password') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
            <p class="text-xs text-slate-400 mt-1">{{ __('app.settings_password_hint') }}</p>
          </div>

          <div>
            <label for="password_confirmation" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
              {{ __('app.settings_confirm_password') }}
            </label>
            <input type="password" name="password_confirmation" id="password_confirmation" autocomplete="new-password"
              class="{{ $inputBase }} {{ $inputNormal }}">
          </div>

          <div class="flex justify-end pt-1">
            <button type="submit"
              class="flex items-center gap-2 bg-primary hover:bg-primary/90 text-white px-6 py-2.5 rounded-lg font-semibold transition-all shadow-sm shadow-primary/20 text-sm">
              <span class="material-icons text-sm">lock_reset</span>
              {{ __('app.settings_change_password') }}
            </button>
          </div>
        </form>

        {{-- Reset link --}}
        <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-6">
          <div class="flex items-start gap-4">
            <div class="w-9 h-9 rounded-lg bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center flex-shrink-0">
              <span class="material-icons text-amber-500 text-lg">email</span>
            </div>
            <div class="flex-1">
              <h3 class="font-bold text-slate-900 dark:text-white text-sm">{{ __('app.settings_forgot_password') }}</h3>
              <p class="text-xs text-slate-500 mt-0.5">
                {{ __('app.settings_reset_link_desc') }}
                <span class="font-semibold text-slate-700 dark:text-slate-300">{{ $user->email }}</span>
              </p>
            </div>
            <form method="POST" action="{{ route('settings.reset-link') }}">
              @csrf
              <button type="submit"
                class="flex items-center gap-1.5 px-4 py-2 border border-amber-400 dark:border-amber-600 text-amber-700 dark:text-amber-400 hover:bg-amber-50 dark:hover:bg-amber-900/20 rounded-lg text-xs font-semibold transition-all whitespace-nowrap">
                <span class="material-icons text-sm">send</span>
                {{ __('app.settings_send_reset') }}
              </button>
            </form>
          </div>
        </div>

      </div>{{-- /tab-password --}}

      {{-- ══ SECURITY TAB (admin only) ════════════════════════════════════════ --}}
      @if(auth()->user()->isAdmin())
        <div id="tab-security" class="hidden space-y-6">
          <form method="POST" action="{{ route('settings.security') }}"
            class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-6 space-y-5">
            @csrf
            <div class="flex items-center gap-3 mb-2">
              <div class="w-9 h-9 rounded-lg bg-slate-100 dark:bg-slate-800 flex items-center justify-center">
                <span class="material-icons text-slate-500 text-lg">security</span>
              </div>
              <div>
                <h2 class="font-bold text-slate-900 dark:text-white">{{ __('app.settings_security') }}</h2>
                <p class="text-xs text-slate-500">Admin-only session control</p>
              </div>
            </div>

            <div>
              <label for="session_timeout_minutes" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                {{ __('app.settings_session_timeout') }}
              </label>
              <div class="flex items-center gap-3">
                <input type="number" name="session_timeout_minutes" id="session_timeout_minutes"
                  min="5" max="120" step="5"
                  value="{{ old('session_timeout_minutes', \App\Models\Setting::get('session_timeout_minutes', 30)) }}"
                  class="w-32 px-3 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none transition-all">
                <span class="text-sm text-slate-500">minutes</span>
              </div>
              <p class="text-xs text-slate-400 mt-1">{{ __('app.settings_session_hint') }}</p>
              @error('session_timeout_minutes') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="flex justify-end pt-1">
              <button type="submit"
                class="flex items-center gap-2 bg-primary hover:bg-primary/90 text-white px-6 py-2.5 rounded-lg font-semibold transition-all shadow-sm shadow-primary/20 text-sm">
                <span class="material-icons text-sm">save</span>
                {{ __('app.settings_save_security') }}
              </button>
            </div>
          </form>
        </div>
      @endif

      {{-- ══ ADMIN MEMO TAB (admin only) ══════════════════════════════════════ --}}
      @if(auth()->user()->isAdmin())
        <div id="tab-memo" class="hidden space-y-6">
          <form method="POST" action="{{ route('settings.memo') }}"
            class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-6 space-y-5">
            @csrf
            <div class="flex items-center gap-3 mb-2">
              <div class="w-9 h-9 rounded-lg bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center">
                <span class="material-icons text-amber-500 text-lg">sticky_note_2</span>
              </div>
              <div>
                <h2 class="font-bold text-slate-900 dark:text-white">Admin Memo</h2>
                <p class="text-xs text-slate-500">Shown on the dashboard sidebar and on each resident's Overview page.</p>
              </div>
            </div>

            <div>
              <textarea name="admin_memo" rows="6"
                placeholder="Write a memo or announcement..."
                class="w-full px-3 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none transition-all resize-y"
                >{{ old('admin_memo', \App\Models\Setting::get('admin_memo', '')) }}</textarea>
              <p class="text-xs text-slate-400 mt-1">Max 1,000 characters. Leave blank to hide the notice.</p>
              @error('admin_memo') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="flex justify-end pt-1">
              <button type="submit"
                class="flex items-center gap-2 bg-primary hover:bg-primary/90 text-white px-6 py-2.5 rounded-lg font-semibold transition-all shadow-sm shadow-primary/20 text-sm">
                <span class="material-icons text-sm">save</span>
                Save Memo
              </button>
            </div>
          </form>
        </div>
      @endif

    </main>
  </div>

  <script>
    const tabIds = ['profile', 'password', 'security', 'memo'];

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
          card.classList.add('border-primary', 'bg-primary/5');
          card.classList.remove('border-slate-200', 'hover:border-primary/40');
          if (icon) {
            icon.textContent = 'check_circle';
            icon.classList.replace('text-slate-300', 'text-primary');
          }
        } else {
          card.classList.remove('border-primary', 'bg-primary/5');
          card.classList.add('border-slate-200', 'hover:border-primary/40');
          if (icon) {
            icon.textContent = 'radio_button_unchecked';
            icon.classList.replace('text-primary', 'text-slate-300');
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
        if (passed) { el.classList.replace('text-slate-400','text-emerald-500'); icon.textContent='check_circle'; }
        else { el.classList.replace('text-emerald-500','text-slate-400'); icon.textContent='radio_button_unchecked'; }
      }
      setReq('sp-req-length', pw.length >= 8);
      setReq('sp-req-upper',  /[A-Z]/.test(pw));
      setReq('sp-req-lower',  /[a-z]/.test(pw));
      setReq('sp-req-number', /[0-9]/.test(pw));
      setReq('sp-req-symbol', /[^A-Za-z0-9]/.test(pw));
    }
  </script>

</x-layouts.app>