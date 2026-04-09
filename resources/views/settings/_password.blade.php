{{-- Password tab --}}
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

</div>
