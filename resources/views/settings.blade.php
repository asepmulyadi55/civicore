{{-- Settings Page --}}
<x-layouts.app title="Settings"
  class="font-display bg-background-light dark:bg-background-dark text-slate-800 dark:text-slate-200 antialiased min-h-screen">

  <x-nav.sidebar active="settings" />

  <div class="lg:pl-64 min-h-screen bg-background-light dark:bg-background-dark flex flex-col">

    {{-- Header --}}
    <header
      class="h-16 bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between px-6 lg:px-8">
      <div class="flex items-center gap-4">
        <button class="lg:hidden p-2 rounded-lg border border-slate-200 dark:border-slate-800"
          onclick="toggleSidebar()">
          <span class="material-icons text-slate-500">menu</span>
        </button>
        <h1 class="text-xl font-bold text-slate-900 dark:text-white">Settings</h1>
      </div>
      <button
        class="p-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg hover:border-primary/50 transition-all"
        onclick="toggleDark()" title="Toggle dark mode">
        <span class="material-icons text-slate-500 text-[20px]">dark_mode</span>
      </button>
    </header>

    {{-- Body --}}
    <main class="flex-1 p-6 lg:p-8">

      {{-- Flash --}}
      @if(session('success'))
        <div
          class="mb-6 p-4 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 rounded-xl flex items-center gap-3">
          <span class="material-icons text-emerald-500">check_circle</span>
          <p class="text-sm text-emerald-700 dark:text-emerald-400">{{ session('success') }}</p>
        </div>
      @endif
      @if(session('error'))
        <div class="mb-6 p-4 bg-rose-50 dark:bg-rose-900/20 border border-rose-200 rounded-xl flex items-center gap-3">
          <span class="material-icons text-rose-500">error</span>
          <p class="text-sm text-rose-700 dark:text-rose-400">{{ session('error') }}</p>
        </div>
      @endif

      <form method="POST" action="{{ route('settings.update') }}" class="space-y-6">
        @csrf

        {{-- Tab Navigation --}}
        <div
          class="flex gap-1 overflow-x-auto bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-1 rounded-xl">
          @php
            $tabs = [
              ['id' => 'community', 'icon' => 'apartment', 'label' => 'Community'],
              ['id' => 'locale', 'icon' => 'language', 'label' => 'Locale'],
              ['id' => 'financial', 'icon' => 'account_balance', 'label' => 'Financial'],
              ['id' => 'notifications', 'icon' => 'notifications', 'label' => 'Notifications'],
              ['id' => 'security', 'icon' => 'security', 'label' => 'Security'],
            ];
          @endphp

          @foreach($tabs as $tab)
            <button type="button" onclick="switchTab('{{ $tab['id'] }}')" id="tab-btn-{{ $tab['id'] }}"
              class="settings-tab-btn flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-semibold whitespace-nowrap transition-all text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200">
              <span class="material-icons text-base">{{ $tab['icon'] }}</span>
              {{ $tab['label'] }}
            </button>
          @endforeach
        </div>

        {{-- ── COMMUNITY tab ─────────────────────────────────────────── --}}
        <div id="tab-community" class="settings-tab space-y-6">
          <div
            class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center gap-3">
              <div class="w-9 h-9 rounded-lg bg-primary/10 flex items-center justify-center">
                <span class="material-icons text-primary text-lg">apartment</span>
              </div>
              <div>
                <h2 class="font-bold text-slate-900 dark:text-white">Community Information</h2>
                <p class="text-xs text-slate-500">Basic details about your residential community</p>
              </div>
            </div>
            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">

              <div>
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Application
                  Name</label>
                <input type="text" name="app_name" value="{{ old('app_name', $all['app_name']->value ?? 'CiviCore') }}"
                  class="w-full px-3 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none transition-all">
                @error('app_name') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
              </div>

              <div>
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Support
                  Email</label>
                <input type="email" name="support_email"
                  value="{{ old('support_email', $all['support_email']->value ?? '') }}"
                  class="w-full px-3 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none transition-all">
                @error('support_email') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
              </div>

              <div class="md:col-span-2">
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Community
                  Name</label>
                <input type="text" name="community_name"
                  value="{{ old('community_name', $all['community_name']->value ?? '') }}"
                  class="w-full px-3 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none transition-all">
                @error('community_name') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
              </div>

              <div class="md:col-span-2">
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Address</label>
                <textarea name="community_address" rows="2"
                  class="w-full px-3 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none transition-all resize-none">{{ old('community_address', $all['community_address']->value ?? '') }}</textarea>
                @error('community_address') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
              </div>

              <div>
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Contact
                  Phone</label>
                <input type="text" name="community_phone"
                  value="{{ old('community_phone', $all['community_phone']->value ?? '') }}"
                  placeholder="+62 21 xxx xxxx"
                  class="w-full px-3 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none transition-all">
                @error('community_phone') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
              </div>

            </div>
          </div>
        </div>

        {{-- ── LOCALE tab ────────────────────────────────────────────── --}}
        <div id="tab-locale" class="settings-tab hidden space-y-6">
          <div
            class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center gap-3">
              <div class="w-9 h-9 rounded-lg bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center">
                <span class="material-icons text-indigo-500 text-lg">language</span>
              </div>
              <div>
                <h2 class="font-bold text-slate-900 dark:text-white">Locale & Display</h2>
                <p class="text-xs text-slate-500">Language, date format, and currency preferences</p>
              </div>
            </div>
            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">

              <div>
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Language</label>
                <select name="app_language"
                  class="w-full px-3 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none transition-all">
                  @foreach(['en' => '🇬🇧 English', 'id' => '🇮🇩 Indonesian (Bahasa)'] as $code => $label)
                    <option value="{{ $code }}" @selected(old('app_language', $all['app_language']->value ?? 'en') === $code)>{{ $label }}</option>
                  @endforeach
                </select>
                <p class="text-xs text-slate-400 mt-1">Full translation support coming soon.</p>
                @error('app_language') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
              </div>

              <div>
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Date Format</label>
                <select name="date_format"
                  class="w-full px-3 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none transition-all">
                  @foreach(['DD/MM/YYYY' => 'DD/MM/YYYY (e.g. 04/03/2026)', 'MM/DD/YYYY' => 'MM/DD/YYYY (e.g. 03/04/2026)', 'YYYY-MM-DD' => 'YYYY-MM-DD (ISO 8601)'] as $fmt => $desc)
                    <option value="{{ $fmt }}" @selected(old('date_format', $all['date_format']->value ?? 'DD/MM/YYYY') === $fmt)>{{ $desc }}</option>
                  @endforeach
                </select>
                @error('date_format') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
              </div>

              <div>
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Currency
                  Symbol</label>
                <input type="text" name="currency_symbol"
                  value="{{ old('currency_symbol', $all['currency_symbol']->value ?? 'Rp') }}" placeholder="Rp"
                  class="w-full px-3 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none transition-all">
                <p class="text-xs text-slate-400 mt-1">Displayed before amounts (e.g. Rp, $, €)</p>
                @error('currency_symbol') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
              </div>

              <div>
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Currency
                  Code</label>
                <input type="text" name="currency_code"
                  value="{{ old('currency_code', $all['currency_code']->value ?? 'IDR') }}" placeholder="IDR"
                  class="w-full px-3 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none transition-all">
                <p class="text-xs text-slate-400 mt-1">ISO 4217 code (e.g. IDR, USD, EUR)</p>
                @error('currency_code') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
              </div>

            </div>
          </div>
        </div>

        {{-- ── FINANCIAL tab ─────────────────────────────────────────── --}}
        <div id="tab-financial" class="settings-tab hidden space-y-6">
          <div
            class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center gap-3">
              <div class="w-9 h-9 rounded-lg bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center">
                <span class="material-icons text-emerald-500 text-lg">account_balance</span>
              </div>
              <div>
                <h2 class="font-bold text-slate-900 dark:text-white">Financial Defaults</h2>
                <p class="text-xs text-slate-500">Default fee amounts, due dates, and grace periods</p>
              </div>
            </div>
            <div class="p-6 grid grid-cols-1 md:grid-cols-3 gap-6">

              <div>
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Default Monthly
                  Fee</label>
                <div class="relative">
                  <span
                    class="absolute left-3 top-1/2 -translate-y-1/2 text-sm text-slate-400 font-medium">{{ $all['currency_symbol']->value ?? 'Rp' }}</span>
                  <input type="number" name="default_fee_amount" min="0" step="1000"
                    value="{{ old('default_fee_amount', $all['default_fee_amount']->value ?? '0') }}"
                    class="w-full pl-10 pr-3 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none transition-all">
                </div>
                <p class="text-xs text-slate-400 mt-1">Pre-filled when creating new fee histories</p>
                @error('default_fee_amount') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
              </div>

              <div>
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Payment Due
                  Day</label>
                <div class="relative">
                  <input type="number" name="default_due_day" min="1" max="28"
                    value="{{ old('default_due_day', $all['default_due_day']->value ?? '5') }}"
                    class="w-full px-3 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none transition-all">
                </div>
                <p class="text-xs text-slate-400 mt-1">Day of month (1–28) payments are due</p>
                @error('default_due_day') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
              </div>

              <div>
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Grace Period
                  (days)</label>
                <div class="relative">
                  <input type="number" name="late_payment_grace_days" min="0" max="60"
                    value="{{ old('late_payment_grace_days', $all['late_payment_grace_days']->value ?? '7') }}"
                    class="w-full px-3 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none transition-all">
                </div>
                <p class="text-xs text-slate-400 mt-1">Days after due date before marked overdue</p>
                @error('late_payment_grace_days') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
              </div>

            </div>
          </div>
        </div>

        {{-- ── NOTIFICATIONS tab ─────────────────────────────────────── --}}
        <div id="tab-notifications" class="settings-tab hidden space-y-6">
          <div
            class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center gap-3">
              <div class="w-9 h-9 rounded-lg bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center">
                <span class="material-icons text-amber-500 text-lg">notifications</span>
              </div>
              <div>
                <h2 class="font-bold text-slate-900 dark:text-white">Email Notifications</h2>
                <p class="text-xs text-slate-500">Choose which events trigger an email notification</p>
              </div>
            </div>

            <div
              class="px-2 py-2 mx-4 my-4 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg flex items-start gap-2">
              <span class="material-icons text-amber-500 text-sm mt-0.5">info</span>
              <p class="text-xs text-amber-700 dark:text-amber-400">
                Email delivery requires a configured mail driver. Toggles are stored now and enforced once the mailer is
                set up.
              </p>
            </div>

            <div class="p-6 space-y-4">
              @php
                $notificationSettings = [
                  'notify_payment_approved' => [
                    'label' => 'Payment Approved',
                    'desc' => 'Notify the resident when their payment is approved by the treasurer',
                    'icon' => 'check_circle',
                    'color' => 'text-emerald-500',
                    'bg' => 'bg-emerald-100 dark:bg-emerald-900/30',
                  ],
                  'notify_payment_rejected' => [
                    'label' => 'Payment Rejected',
                    'desc' => 'Notify the resident when their payment is rejected with a reason',
                    'icon' => 'cancel',
                    'color' => 'text-rose-500',
                    'bg' => 'bg-rose-100 dark:bg-rose-900/30',
                  ],
                  'notify_new_resident' => [
                    'label' => 'New Resident Registration',
                    'desc' => 'Notify the admin when a new resident account registers and needs approval',
                    'icon' => 'person_add',
                    'color' => 'text-primary',
                    'bg' => 'bg-primary/10',
                  ],
                ];
              @endphp

              @foreach($notificationSettings as $key => $cfg)
                @php $checked = old($key, $all[$key]->value ?? '1') === '1'; @endphp
                <label for="{{ $key }}"
                  class="flex items-center justify-between p-4 rounded-xl border {{ $checked ? 'border-primary/30 bg-primary/5' : 'border-slate-200 dark:border-slate-700' }} cursor-pointer hover:border-primary/40 transition-all group">
                  <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg {{ $cfg['bg'] }} flex items-center justify-center flex-shrink-0">
                      <span class="material-icons {{ $cfg['color'] }} text-lg">{{ $cfg['icon'] }}</span>
                    </div>
                    <div>
                      <p class="text-sm font-semibold text-slate-800 dark:text-slate-200">{{ $cfg['label'] }}</p>
                      <p class="text-xs text-slate-500">{{ $cfg['desc'] }}</p>
                    </div>
                  </div>
                  <div class="relative flex-shrink-0 ml-4">
                    <input type="checkbox" id="{{ $key }}" name="{{ $key }}" value="1" class="sr-only peer" {{ $checked ? 'checked' : '' }}>
                    <div
                      class="w-11 h-6 bg-slate-200 dark:bg-slate-700 peer-checked:bg-primary rounded-full transition-colors">
                    </div>
                    <div
                      class="absolute top-0.5 left-0.5 w-5 h-5 bg-white rounded-full shadow transition-transform peer-checked:translate-x-5">
                    </div>
                  </div>
                </label>
              @endforeach
            </div>
          </div>
        </div>

        {{-- ── SECURITY tab ──────────────────────────────────────────── --}}
        <div id="tab-security" class="settings-tab hidden space-y-6">
          <div
            class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center gap-3">
              <div class="w-9 h-9 rounded-lg bg-slate-100 dark:bg-slate-800 flex items-center justify-center">
                <span class="material-icons text-slate-500 text-lg">security</span>
              </div>
              <div>
                <h2 class="font-bold text-slate-900 dark:text-white">Security</h2>
                <p class="text-xs text-slate-500">Session management and authentication policies</p>
              </div>
            </div>
            <div class="p-6 space-y-6">

              <div>
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Session
                  Timeout</label>
                <div class="flex items-center gap-3">
                  <input type="number" name="session_timeout_minutes" min="15" max="1440"
                    value="{{ old('session_timeout_minutes', $all['session_timeout_minutes']->value ?? '120') }}"
                    class="w-36 px-3 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none transition-all">
                  <span class="text-sm text-slate-500">minutes</span>
                </div>
                <p class="text-xs text-slate-400 mt-1">Inactive users are logged out after this duration (15–1440 min)
                </p>
                @error('session_timeout_minutes') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
              </div>

              {{-- 2FA toggle --}}
              @php $twofa = old('require_2fa_admin', $all['require_2fa_admin']->value ?? '0') === '1'; @endphp
              <div
                class="p-4 rounded-xl border {{ $twofa ? 'border-primary/30 bg-primary/5' : 'border-slate-200 dark:border-slate-700' }} transition-all">
                <label for="require_2fa_admin" class="flex items-center justify-between cursor-pointer">
                  <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-slate-100 dark:bg-slate-800 flex items-center justify-center">
                      <span class="material-icons text-slate-500 text-lg">phonelink_lock</span>
                    </div>
                    <div>
                      <p class="text-sm font-semibold text-slate-800 dark:text-slate-200">Require 2FA for Admin &
                        Treasurer</p>
                      <p class="text-xs text-slate-500">Stored now — enforcement requires 2FA package setup</p>
                    </div>
                  </div>
                  <div class="relative flex-shrink-0 ml-4">
                    <input type="checkbox" id="require_2fa_admin" name="require_2fa_admin" value="1"
                      class="sr-only peer" {{ $twofa ? 'checked' : '' }}>
                    <div
                      class="w-11 h-6 bg-slate-200 dark:bg-slate-700 peer-checked:bg-primary rounded-full transition-colors">
                    </div>
                    <div
                      class="absolute top-0.5 left-0.5 w-5 h-5 bg-white rounded-full shadow transition-transform peer-checked:translate-x-5">
                    </div>
                  </div>
                </label>
              </div>

            </div>
          </div>
        </div>

        {{-- Save Button --}}
        <div class="flex justify-end">
          <button type="submit"
            class="flex items-center gap-2 bg-primary hover:bg-primary/90 text-white px-6 py-2.5 rounded-lg font-semibold transition-all shadow-sm shadow-primary/20">
            <span class="material-icons text-sm">save</span>
            Save Settings
          </button>
        </div>

      </form>
    </main>
  </div>

  <script>
    const tabIds = ['community', 'locale', 'financial', 'notifications', 'security'];

    function switchTab(active) {
      tabIds.forEach(id => {
        const panel = document.getElementById('tab-' + id);
        const btn = document.getElementById('tab-btn-' + id);
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

    // Restore last active tab, default to 'community'
    const savedTab = sessionStorage.getItem('settingsTab') || 'community';
    switchTab(savedTab);

    // Visual feedback on toggle labels
    document.querySelectorAll('input[type="checkbox"].sr-only').forEach(cb => {
      cb.addEventListener('change', function () {
        const label = this.closest('label') || this.closest('[class*="rounded-xl"]');
        if (!label) return;
        if (this.checked) {
          label.classList.add('border-primary/30', 'bg-primary/5');
          label.classList.remove('border-slate-200', 'dark:border-slate-700');
        } else {
          label.classList.remove('border-primary/30', 'bg-primary/5');
          label.classList.add('border-slate-200', 'dark:border-slate-700');
        }
      });
    });
  </script>

</x-layouts.app>