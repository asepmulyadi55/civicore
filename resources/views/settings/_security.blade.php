{{-- Security tab (admin only) --}}
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
