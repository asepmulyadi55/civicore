<x-layouts.app title="Two-Factor Authentication" class="bg-background-light dark:bg-background-dark min-h-screen flex flex-col justify-center transition-colors duration-200">
  <main class="flex-grow flex items-center justify-center px-4 py-12">
    <div class="w-full max-w-md bg-white dark:bg-slate-900/50 dark:border dark:border-slate-800 shadow-xl shadow-slate-200/50 dark:shadow-none rounded-xl overflow-hidden p-8">
  <div class="mb-8 text-center">
    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-primary/10 text-primary mb-4">
      <span class="material-icons text-3xl">lock</span>
    </div>
    <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Two-Factor Authentication</h1>
    <p class="text-slate-500 mt-2">Please confirm access to your account by entering the authentication code provided by your authenticator application.</p>
  </div>

  @if(session('error'))
    <div class="mb-6 p-4 bg-rose-50 dark:bg-rose-900/20 border border-rose-200 dark:border-rose-800 rounded-xl flex items-center gap-3">
      <span class="material-icons text-rose-500">error</span>
      <p class="text-sm text-rose-700 dark:text-rose-400">{{ session('error') }}</p>
    </div>
  @endif

  <form method="POST" action="{{ route('two-factor.verify') }}" class="space-y-6">
    @csrf

    <div>
      <label for="code" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Authentication Code</label>
      <div class="relative">
        <span class="material-icons absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">pin</span>
        <input id="code" type="text" name="code" required autofocus autocomplete="one-time-code" inputmode="numeric" pattern="[0-9]*" maxlength="6"
          class="w-full pl-11 pr-4 py-3 bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 rounded-xl text-lg font-mono tracking-[0.25em] text-center focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition-all dark:text-white @error('code') border-rose-400 @enderror"
          placeholder="000000">
      </div>
      @error('code')
        <p class="text-sm text-rose-500 mt-1.5 font-medium">{{ $message }}</p>
      @enderror
    </div>

    <button type="submit"
      class="w-full bg-primary hover:bg-primary/90 text-white font-bold py-3.5 px-4 rounded-xl shadow-lg shadow-primary/25 transition-all transform active:scale-[0.98] flex items-center justify-center gap-2">
      <span>Verify Code</span>
      <span class="material-icons text-sm">arrow_forward</span>
    </button>
  </form>

    <div class="mt-8 text-center">
      <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="text-sm font-semibold text-slate-500 hover:text-primary transition-colors">
          Cancel Login
        </button>
      </form>
    </div>
  </div>
  </main>
</x-layouts.app>
