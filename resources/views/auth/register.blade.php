<x-layouts.app title="Register Account"
  class="bg-background-light dark:bg-background-dark min-h-screen flex flex-col transition-colors duration-200">

  <main class="flex-grow flex items-center justify-center px-4 py-12">
    <div class="w-full max-w-lg">
      <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center p-3 bg-primary/10 rounded-xl mb-4">
          <span class="material-icons text-primary text-4xl">domain</span>
        </div>
        <h1 class="text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">Dwipapuri</h1>
        <p class="text-slate-500 dark:text-slate-400 mt-2 font-medium">Internal Admin Dashboard</p>
      </div>
      <div
        class="bg-white dark:bg-slate-900/50 dark:border dark:border-slate-800 shadow-xl shadow-slate-200/50 dark:shadow-none rounded-xl overflow-hidden">
        <div class="p-8">
          <div class="mb-6">
            <h2 class="text-xl font-bold text-slate-800 dark:text-white">Register Account</h2>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Join the community management system</p>
          </div>

          @if (session('success'))
            <div
              class="mb-4 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-900/30 rounded-lg">
              <p class="text-sm text-green-700 dark:text-green-400">{{ session('success') }}</p>
            </div>
          @endif

          <form action="{{ route('register') }}" id="register-form" class="space-y-4" method="POST" novalidate>
            @csrf

            {{-- Full Name --}}
            <div>
              <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5" for="fullname">
                Full Name
              </label>
              <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <span class="material-icons text-slate-400 text-sm">person_outline</span>
                </span>
                <input
                  class="block w-full pl-10 pr-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white placeholder:text-slate-400 focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200 outline-none @error('fullname') border-red-500 dark:border-red-500 @enderror"
                  id="fullname" name="fullname" placeholder="John Doe" type="text" value="{{ old('fullname') }}"
                  oninput="clearRegErr('err-reg-fullname')" />
              </div>
              <p id="err-reg-fullname" class="hidden mt-1.5 text-sm text-red-600 dark:text-red-400"></p>
              @error('fullname')
                <p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
              @enderror
            </div>

            {{-- Email --}}
            <div>
              <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5" for="email">
                Email Address
              </label>
              <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <span class="material-icons text-slate-400 text-sm">mail_outline</span>
                </span>
                <input
                  class="block w-full pl-10 pr-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white placeholder:text-slate-400 focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200 outline-none @error('email') border-red-500 dark:border-red-500 @enderror"
                  id="email" name="email" placeholder="john@example.com" type="email" value="{{ old('email') }}"
                  oninput="clearRegErr('err-reg-email')" />
              </div>
              <p id="err-reg-email" class="hidden mt-1.5 text-sm text-red-600 dark:text-red-400"></p>
              @error('email')
                <p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
              @enderror
            </div>

            {{-- Username --}}
            <div>
              <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5" for="username">
                Username
              </label>
              <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <span class="material-icons text-slate-400 text-sm">alternate_email</span>
                </span>
                <input
                  class="block w-full pl-10 pr-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white placeholder:text-slate-400 focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200 outline-none @error('username') border-red-500 dark:border-red-500 @enderror"
                  id="username" name="username" placeholder="johndoe_admin" type="text" value="{{ old('username') }}"
                  oninput="clearRegErr('err-reg-username')" />
              </div>
              <p id="err-reg-username" class="hidden mt-1.5 text-sm text-red-600 dark:text-red-400"></p>
              @error('username')
                <p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
              @enderror
            </div>

            {{-- Password --}}
            <div>
              <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5" for="password">
                Password
              </label>
              <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <span class="material-icons text-slate-400 text-sm">lock_outline</span>
                </span>
                <input
                  class="block w-full pl-10 pr-10 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white placeholder:text-slate-400 focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200 outline-none [&::-ms-reveal]:hidden [&::-webkit-contacts-auto-fill-button]:hidden @error('password') border-red-500 dark:border-red-500 @enderror"
                  id="password" name="password" placeholder="••••••••" type="password"
                  oninput="clearRegErr('err-reg-password'); checkPasswordStrength(this.value)" />
                <button
                  class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-200"
                  type="button" onclick="togglePassword()">
                  <span class="material-icons text-sm" id="toggleIcon">visibility</span>
                </button>
              </div>
              {{-- Live password requirements --}}
              <div id="pw-requirements" class="hidden mt-2 space-y-1">
                <p id="req-length" class="flex items-center gap-1.5 text-xs text-slate-400"><span
                    class="material-icons text-sm">radio_button_unchecked</span> At least 8 characters</p>
                <p id="req-upper" class="flex items-center gap-1.5 text-xs text-slate-400"><span
                    class="material-icons text-sm">radio_button_unchecked</span> One uppercase letter</p>
                <p id="req-lower" class="flex items-center gap-1.5 text-xs text-slate-400"><span
                    class="material-icons text-sm">radio_button_unchecked</span> One lowercase letter</p>
                <p id="req-number" class="flex items-center gap-1.5 text-xs text-slate-400"><span
                    class="material-icons text-sm">radio_button_unchecked</span> One number</p>
                <p id="req-symbol" class="flex items-center gap-1.5 text-xs text-slate-400"><span
                    class="material-icons text-sm">radio_button_unchecked</span> One special character</p>
              </div>
              <p id="err-reg-password" class="hidden mt-1.5 text-sm text-red-600 dark:text-red-400"></p>
              @error('password')
                <p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
              @enderror
            </div>

            {{-- Confirm Password --}}
            <div>
              <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5"
                for="password_confirmation">
                Confirm Password
              </label>
              <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <span class="material-icons text-slate-400 text-sm">lock_outline</span>
                </span>
                <input
                  class="block w-full pl-10 pr-10 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white placeholder:text-slate-400 focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200 outline-none [&::-ms-reveal]:hidden [&::-webkit-contacts-auto-fill-button]:hidden"
                  id="password_confirmation" name="password_confirmation" placeholder="••••••••" type="password"
                  oninput="clearRegErr('err-reg-confirm')" />
                <button
                  class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-200"
                  type="button" onclick="toggleConfirmPassword()">
                  <span class="material-icons text-sm" id="toggleConfirmIcon">visibility</span>
                </button>
              </div>
              <p id="err-reg-confirm" class="hidden mt-1.5 text-sm text-red-600 dark:text-red-400"></p>
            </div>

            <button
              class="w-full bg-primary hover:bg-primary/90 text-white font-bold py-3 px-4 rounded-lg shadow-lg shadow-primary/20 transition-all duration-200 flex items-center justify-center space-x-2 mt-2"
              type="submit">
              <span>Register Account</span>
            </button>
          </form>

          <div class="relative my-6">
            <div class="absolute inset-0 flex items-center">
              <div class="w-full border-t border-slate-200 dark:border-slate-800"></div>
            </div>
            <div class="relative flex justify-center text-xs uppercase">
              <span class="bg-white dark:bg-slate-900 px-4 text-slate-500 font-semibold tracking-wider">Or continue
                with</span>
            </div>
          </div>

          <a href="{{ route('auth.google.register') }}"
            class="w-full flex items-center justify-center space-x-3 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 py-3 px-4 rounded-lg transition-all duration-200 group">
            <svg width="20" height="20" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
              <path
                d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"
                fill="#4285F4" />
              <path
                d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"
                fill="#34A853" />
              <path
                d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l3.66-2.84z"
                fill="#FBBC05" />
              <path
                d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"
                fill="#EA4335" />
            </svg>
            <span class="text-sm font-bold text-slate-700 dark:text-slate-200">Sign up with Google</span>
          </a>

          <div
            class="mt-6 p-4 bg-amber-50 dark:bg-amber-900/20 border border-amber-100 dark:border-amber-900/30 rounded-lg">
            <div class="flex space-x-3">
              <span class="material-icons text-amber-500 text-lg">info</span>
              <p class="text-xs text-amber-700 dark:text-amber-400 leading-relaxed font-medium">
                Note: Your account will require Admin activation before you can access the dashboard.
              </p>
            </div>
          </div>
        </div>

        <div class="p-6 bg-slate-50 dark:bg-slate-800/50 border-t border-slate-100 dark:border-slate-800 text-center">
          <p class="text-sm text-slate-600 dark:text-slate-400">
            Already have an account?
            <a class="text-primary font-bold hover:underline" href="{{ route('login') }}">Back to Login</a>
          </p>
        </div>
      </div>

      <div class="mt-8 text-center text-xs text-slate-400 dark:text-slate-500 uppercase tracking-widest font-medium">
        <p>© {{ now()->year }} Dwipapuri Management System • v2.4.0</p>
        <div class="mt-2 space-x-4">
          <a class="hover:text-primary transition-colors" href="#">Security</a>
          <a class="hover:text-primary transition-colors" href="#">Privacy</a>
          <a class="hover:text-primary transition-colors" href="#">Support</a>
        </div>
      </div>
    </div>
  </main>

  <div class="fixed bottom-6 right-6">
    <button
      class="p-3 rounded-full bg-white dark:bg-slate-800 shadow-lg border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:text-primary transition-all duration-200"
      onclick="toggleDark()">
      <span class="material-icons">dark_mode</span>
    </button>
  </div>

  <script>
    function togglePassword() {
      const passwordField = document.getElementById('password');
      const toggleIcon = document.getElementById('toggleIcon');
      if (passwordField.type === 'password') {
        passwordField.type = 'text';
        toggleIcon.textContent = 'visibility_off';
      } else {
        passwordField.type = 'password';
        toggleIcon.textContent = 'visibility';
      }
    }
    function toggleConfirmPassword() {
      const field = document.getElementById('password_confirmation');
      const icon = document.getElementById('toggleConfirmIcon');
      if (field.type === 'password') {
        field.type = 'text';
        icon.textContent = 'visibility_off';
      } else {
        field.type = 'password';
        icon.textContent = 'visibility';
      }
    }

    function isPasswordValid(pw) {
      return pw.length >= 8 && /[A-Z]/.test(pw) && /[a-z]/.test(pw) && /[0-9]/.test(pw) && /[^A-Za-z0-9]/.test(pw);
    }

    function checkPasswordStrength(pw) {
      const box = document.getElementById('pw-requirements');
      if (!pw) { box.classList.add('hidden'); return; }
      box.classList.remove('hidden');
      function setReq(id, passed) {
        const el = document.getElementById(id);
        const icon = el.querySelector('.material-icons');
        if (passed) {
          el.classList.replace('text-slate-400', 'text-emerald-500');
          icon.textContent = 'check_circle';
        } else {
          el.classList.replace('text-emerald-500', 'text-slate-400');
          icon.textContent = 'radio_button_unchecked';
        }
      }
      setReq('req-length', pw.length >= 8);
      setReq('req-upper', /[A-Z]/.test(pw));
      setReq('req-lower', /[a-z]/.test(pw));
      setReq('req-number', /[0-9]/.test(pw));
      setReq('req-symbol', /[^A-Za-z0-9]/.test(pw));
    }

    function clearRegErr(id) {
      const el = document.getElementById(id);
      if (el) el.classList.add('hidden');
    }
    function showRegErr(id, msg) {
      const el = document.getElementById(id);
      if (el) { el.textContent = msg; el.classList.remove('hidden'); }
    }

    document.getElementById('register-form').addEventListener('submit', function (e) {
      let valid = true;
      const fullname = document.getElementById('fullname').value.trim();
      const email = document.getElementById('email').value.trim();
      const username = document.getElementById('username').value.trim();
      const password = document.getElementById('password').value;
      const confirm = document.getElementById('password_confirmation').value;

      if (!fullname) {
        showRegErr('err-reg-fullname', 'Please enter your full name.');
        valid = false;
      } else clearRegErr('err-reg-fullname');

      if (!email) {
        showRegErr('err-reg-email', 'Please enter your email address.');
        valid = false;
      } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        showRegErr('err-reg-email', 'Please enter a valid email address.');
        valid = false;
      } else clearRegErr('err-reg-email');

      if (!username) {
        showRegErr('err-reg-username', 'Please choose a username.');
        valid = false;
      } else clearRegErr('err-reg-username');

      if (!password) {
        showRegErr('err-reg-password', 'Please enter a password.');
        valid = false;
      } else if (!isPasswordValid(password)) {
        showRegErr('err-reg-password', 'Password does not meet all requirements.');
        valid = false;
      } else clearRegErr('err-reg-password');

      if (!confirm) {
        showRegErr('err-reg-confirm', 'Please confirm your password.');
        valid = false;
      } else if (confirm !== password) {
        showRegErr('err-reg-confirm', 'Passwords do not match.');
        valid = false;
      } else clearRegErr('err-reg-confirm');

      if (!valid) e.preventDefault();
    });
  </script>

</x-layouts.app>