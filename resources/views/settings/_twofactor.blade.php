<div id="tab-twofactor" class="hidden animate-fade-in-up">
  <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 p-6 md:p-8">
    <div class="mb-6">
      <h2 class="text-xl font-bold text-slate-900 dark:text-white flex items-center gap-2">
        <span class="material-icons text-primary">verified_user</span>
        Two-Factor Authentication
      </h2>
      <p class="text-slate-500 mt-2">Add additional security to your account using two-factor authentication.</p>
    </div>

    <div class="p-6 bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 rounded-xl mb-6">
      @if(auth()->user()->two_factor_secret)
        <div class="flex items-start gap-4">
          <div class="w-10 h-10 rounded-full bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 flex items-center justify-center flex-shrink-0">
            <span class="material-icons">check_circle</span>
          </div>
          <div>
            <h3 class="text-lg font-bold text-slate-900 dark:text-white">You have enabled two-factor authentication.</h3>
            <p class="text-slate-600 dark:text-slate-400 text-sm mt-1 mb-4">When logging in, you will be prompted for a secure, random token downloaded from your phone's Google Authenticator app.</p>
            
            <div class="mt-4 p-4 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 rounded-xl">
              <p class="text-sm text-emerald-700 dark:text-emerald-400 font-medium">
                <span class="material-icons text-[16px] align-text-bottom mr-1">security</span>
                Two-Factor Authentication is a mandatory security requirement for this application and cannot be disabled.
              </p>
            </div>
          </div>
        </div>
      @else
        <div class="flex items-start gap-4">
          <div class="w-10 h-10 rounded-full bg-slate-200 dark:bg-slate-700 text-slate-500 flex items-center justify-center flex-shrink-0">
            <span class="material-icons">shield_off</span>
          </div>
          <div>
            <h3 class="text-lg font-bold text-slate-900 dark:text-white">You have not enabled two-factor authentication.</h3>
            <p class="text-slate-600 dark:text-slate-400 text-sm mt-1 mb-4">When two-factor authentication is enabled, you will be prompted for a secure, random token during authentication. You may retrieve this token from your phone's Google Authenticator application.</p>
            
            <a href="{{ route('settings.2fa') }}" class="inline-block px-5 py-2.5 bg-slate-900 dark:bg-slate-700 hover:bg-slate-800 dark:hover:bg-slate-600 text-white text-sm font-bold rounded-lg transition-colors shadow-sm">
              Enable Two-Factor Authentication
            </a>
          </div>
        </div>
      @endif
    </div>
  </div>
</div>
