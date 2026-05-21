<x-layouts.app title="Reset Password"
  class="bg-background-light dark:bg-background-dark min-h-screen flex flex-col transition-colors duration-200">

  <main class="flex-grow flex items-center justify-center px-4 py-12">
    <div class="w-full max-w-md">

      {{-- Brand --}}
      <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center p-3 bg-primary/10 rounded-xl mb-4">
          <span class="material-icons text-primary text-4xl">apartment</span>
        </div>
        <h1 class="text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">Dwipapuri</h1>
        <p class="text-slate-500 dark:text-slate-400 mt-2 font-medium">Set New Password</p>
      </div>

      <div
        class="bg-white dark:bg-slate-900/50 dark:border dark:border-slate-800 shadow-xl shadow-slate-200/50 dark:shadow-none rounded-xl overflow-hidden">
        <div class="p-8">
          <div class="mb-6">
            <h2 class="text-xl font-bold text-slate-800 dark:text-white">Create New Password</h2>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Choose a strong password of at least 8
              characters.</p>
          </div>

          {{-- Top banner for all errors (invalid token, password too short, mismatch, etc.) --}}
          @if ($errors->any())
            <div class="mb-4 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-900/30 rounded-lg">
              <p class="text-sm text-red-700 dark:text-red-400">{{ $errors->first() }}</p>
            </div>
          @endif

          <form action="{{ route('password.update') }}" method="POST" class="space-y-5" novalidate>
            @csrf

            {{-- Hidden fields —”- email & token are from the reset link, not editable --}}
            <input type="hidden" name="token" value="{{ $token }}" />
            <input type="hidden" name="email" value="{{ $email ?? old('email') }}" />

            <div>
              <label for="password" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                New Password
              </label>
              <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <span class="material-icons text-slate-400 text-sm">lock_outline</span>
                </span>
                <input id="password" name="password" type="password" placeholder="••••••••" minlength="8"
                  class="block w-full pl-10 pr-10 py-2.5 bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white placeholder:text-slate-400 focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all outline-none [&::-ms-reveal]:hidden @error('password') border-red-500 @enderror"
                  oninput="checkPasswordStrengthReset(this.value)" required />
                <button type="button" onclick="toggleField('password','toggleIcon1')"
                  class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600">
                  <span class="material-icons text-sm" id="toggleIcon1">visibility</span>
                </button>
              </div>
              <div id="rp-requirements" class="hidden mt-2 space-y-1">
                <p id="rp-req-length" class="flex items-center gap-1.5 text-xs text-slate-400"><span class="material-icons text-sm">radio_button_unchecked</span> At least 8 characters</p>
                <p id="rp-req-upper"  class="flex items-center gap-1.5 text-xs text-slate-400"><span class="material-icons text-sm">radio_button_unchecked</span> One uppercase letter</p>
                <p id="rp-req-lower"  class="flex items-center gap-1.5 text-xs text-slate-400"><span class="material-icons text-sm">radio_button_unchecked</span> One lowercase letter</p>
                <p id="rp-req-number" class="flex items-center gap-1.5 text-xs text-slate-400"><span class="material-icons text-sm">radio_button_unchecked</span> One number</p>
                <p id="rp-req-symbol" class="flex items-center gap-1.5 text-xs text-slate-400"><span class="material-icons text-sm">radio_button_unchecked</span> One special character</p>
              </div>
            </div>

            <div>
              <label for="password_confirmation"
                class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                Confirm New Password
              </label>
              <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <span class="material-icons text-slate-400 text-sm">lock_outline</span>
                </span>
                <input id="password_confirmation" name="password_confirmation" type="password" placeholder="••••••••"
                  class="block w-full pl-10 pr-10 py-2.5 bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white placeholder:text-slate-400 focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all outline-none [&::-ms-reveal]:hidden"
                  required />
                <button type="button" onclick="toggleField('password_confirmation','toggleIcon2')"
                  class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600">
                  <span class="material-icons text-sm" id="toggleIcon2">visibility</span>
                </button>
              </div>
            </div>

            <button type="submit"
              class="w-full bg-primary hover:bg-primary/90 text-white font-bold py-3 px-4 rounded-lg shadow-lg shadow-primary/20 transition-all duration-200 flex items-center justify-center gap-2">
              <span class="material-icons text-lg">lock_reset</span>
              <span>Reset Password</span>
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

  <div class="fixed bottom-6 right-6">
    <button onclick="toggleDark()"
      class="p-3 rounded-full bg-white dark:bg-slate-800 shadow-lg border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:text-primary transition-all duration-200">
      <span class="material-icons">dark_mode</span>
    </button>
  </div>

  <script>
    function toggleField(fieldId, iconId) {
      const field = document.getElementById(fieldId);
      const icon = document.getElementById(iconId);
      if (field.type === 'password') {
        field.type = 'text';
        icon.textContent = 'visibility_off';
      } else {
        field.type = 'password';
        icon.textContent = 'visibility';
      }
    }
    function checkPasswordStrengthReset(pw) {
      const box = document.getElementById('rp-requirements');
      if (!pw) { box.classList.add('hidden'); return; }
      box.classList.remove('hidden');
      function setReq(id, passed) {
        const el = document.getElementById(id);
        const icon = el.querySelector('.material-icons');
        if (passed) { el.classList.replace('text-slate-400','text-emerald-500'); icon.textContent='check_circle'; }
        else { el.classList.replace('text-emerald-500','text-slate-400'); icon.textContent='radio_button_unchecked'; }
      }
      setReq('rp-req-length', pw.length >= 8);
      setReq('rp-req-upper',  /[A-Z]/.test(pw));
      setReq('rp-req-lower',  /[a-z]/.test(pw));
      setReq('rp-req-number', /[0-9]/.test(pw));
      setReq('rp-req-symbol', /[^A-Za-z0-9]/.test(pw));
    }
  </script>

</x-layouts.app>