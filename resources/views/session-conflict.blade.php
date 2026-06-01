<x-layouts.app title="Session Conflict"
  class="bg-background-light dark:bg-background-dark min-h-screen flex flex-col transition-colors duration-200">

  <main class="flex-grow flex items-center justify-center px-4 py-12">
    <div class="w-full max-w-md">

      {{-- Icon --}}
      <div class="flex justify-center mb-6">
        <div class="w-16 h-16 rounded-2xl bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center shadow-lg">
          <span class="material-icons text-amber-500 text-3xl">devices</span>
        </div>
      </div>

      <div
        class="bg-white dark:bg-slate-900/50 dark:border dark:border-slate-800 shadow-xl shadow-slate-200/50 dark:shadow-none rounded-xl overflow-hidden">
        <div class="p-8 text-center space-y-4">

          <div>
            <h1 class="text-xl font-bold text-slate-900 dark:text-white">Account Already Logged In</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-2 leading-relaxed">
              This account is currently active in another browser or device.
              You can either <strong class="text-slate-700 dark:text-slate-300">cancel</strong> and go back, or
              <strong class="text-slate-700 dark:text-slate-300">use this device</strong> which will log out the other
              session immediately.
            </p>
          </div>

          @if (session('error'))
            <div
              class="p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-900/30 rounded-lg text-left">
              <p class="text-sm text-red-700 dark:text-red-400">{{ session('error') }}</p>
            </div>
          @endif

          <div class="pt-2 flex flex-col gap-3">

            {{-- Use this device --}}
            <form method="POST" action="{{ route('session.use-this') }}">
              @csrf
              <input type="hidden" name="user_id" value="{{ session('conflict_user_id') }}">
              <button type="submit"
                class="w-full flex items-center justify-center gap-2 bg-primary hover:bg-primary/90 text-white px-6 py-3 rounded-xl font-bold transition-all shadow-md shadow-primary/20">
                <span class="material-icons text-base">login</span>
                Use This Device (Kick Other Session)
              </button>
            </form>

            {{-- Cancel --}}
            <a href="{{ route('login') }}"
              class="w-full flex items-center justify-center gap-2 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800 px-6 py-3 rounded-xl font-semibold transition-all text-sm">
              <span class="material-icons text-base">arrow_back</span>
              Cancel - Go Back to Login
            </a>

          </div>
        </div>

        <div class="p-4 bg-slate-50 dark:bg-slate-800/50 border-t border-slate-100 dark:border-slate-800 text-center">
          <p class="text-xs text-slate-400">Dwipapuri Â· Session Management</p>
        </div>
      </div>

      <div class="fixed bottom-6 right-6">
        <button
          class="p-3 rounded-full bg-white dark:bg-slate-800 shadow-lg border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:text-primary transition-all duration-200"
          onclick="toggleDark()">
          <span class="material-icons">dark_mode</span>
        </button>
      </div>

    </div>
  </main>

</x-layouts.app>