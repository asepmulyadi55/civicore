<x-layouts.app title="Forgot Password"
  class="bg-background-light dark:bg-background-dark min-h-screen flex flex-col transition-colors duration-200">

  <main class="flex-grow flex items-center justify-center px-4 py-12">
    <div class="w-full max-w-md">

      {{-- Brand --}}
      <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center p-3 bg-primary/10 rounded-xl mb-4">
          <span class="material-icons text-primary text-4xl">apartment</span>
        </div>
        <h1 class="text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">CiviCore</h1>
        <p class="text-slate-500 dark:text-slate-400 mt-2 font-medium">Password Recovery</p>
      </div>

      <div
        class="bg-white dark:bg-slate-900/50 dark:border dark:border-slate-800 shadow-xl shadow-slate-200/50 dark:shadow-none rounded-xl overflow-hidden">
        <div class="p-8">
          <div class="mb-6">
            <h2 class="text-xl font-bold text-slate-800 dark:text-white">Forgot Password?</h2>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Enter your registered email and we'll send a
              reset link.</p>
          </div>

          {{-- Success --}}
          @if(session('success'))
            <div
              class="mb-6 p-4 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-900/30 rounded-lg flex items-start gap-3">
              <span class="material-icons text-emerald-500 mt-0.5 text-lg flex-shrink-0">check_circle</span>
              <p class="text-sm text-emerald-700 dark:text-emerald-400">{{ session('success') }}</p>
            </div>
          @endif

          {{-- Errors --}}
          {{-- server-side errors shown inline below field --}}

          <form id="forgot-form" action="{{ route('password.email') }}" method="POST" class="space-y-5" novalidate>
            @csrf
            <div>
              <label for="email" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                Email Address
              </label>
              <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <span class="material-icons text-slate-400 text-sm">mail</span>
                </span>
                <input id="email" name="email" type="email" value="{{ old('email') }}" placeholder="admin@civicore.com"
                  class="block w-full pl-10 pr-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white placeholder:text-slate-400 focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all outline-none @error('email') border-red-500 @enderror"
                  autofocus oninput="clearFpErr('err-fp-email')" />
              </div>
              <p id="err-fp-email" class="hidden mt-1.5 text-sm text-red-600 dark:text-red-400"></p>
              @error('email')
                <p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
              @enderror
            </div>

            <button type="submit"
              class="w-full bg-primary hover:bg-primary/90 text-white font-bold py-3 px-4 rounded-lg shadow-lg shadow-primary/20 transition-all duration-200 flex items-center justify-center gap-2">
              <span class="material-icons text-lg">send</span>
              <span>Send Reset Link</span>
            </button>
          </form>
        </div>

        <div class="p-6 bg-slate-50 dark:bg-slate-800/50 border-t border-slate-100 dark:border-slate-800 text-center">
          <a href="{{ route('login') }}"
            class="flex items-center justify-center gap-2 text-sm font-bold text-primary hover:text-primary/80 transition-colors">
            <span class="material-icons text-base">arrow_back</span>
            Back to Login
          </a>
        </div>
      </div>

    </div>
  </main>

  <script>
    function clearFpErr(id) {
      const el = document.getElementById(id); if (el) el.classList.add('hidden');
    }
    function showFpErr(id, msg) {
      const el = document.getElementById(id); if (el) { el.textContent = msg; el.classList.remove('hidden'); }
    }
    document.getElementById('forgot-form').addEventListener('submit', function(e) {
      const email = document.getElementById('email').value.trim();
      if (!email) {
        showFpErr('err-fp-email', 'Please enter your email address.');
        e.preventDefault();
      } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        showFpErr('err-fp-email', 'Please enter a valid email address.');
        e.preventDefault();
      } else {
        clearFpErr('err-fp-email');
      }
    });
  </script>

  {{-- Dark mode toggle --}}
  <div class="fixed bottom-6 right-6">
    <button onclick="toggleDark()"
      class="p-3 rounded-full bg-white dark:bg-slate-800 shadow-lg border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:text-primary transition-all duration-200">
      <span class="material-icons">dark_mode</span>
    </button>
  </div>

</x-layouts.app>