{{-- Resident-facing sidebar (not the shared admin x-nav.sidebar) --}}
<aside id="resident-sidebar"
  class="fixed left-0 top-0 h-full w-64 bg-white dark:bg-slate-900 border-r border-slate-200 dark:border-slate-800 z-50 flex flex-col">

  {{-- Logo --}}
  <div class="p-6 border-b border-slate-200 dark:border-slate-800">
    <div class="flex items-center gap-2 text-primary font-bold text-2xl tracking-tight">
      <span class="material-icons">account_balance_wallet</span>
      <span>CiviCore</span>
    </div>
  </div>

  {{-- Nav --}}
  <nav class="flex-1 px-4 py-4 space-y-1">
    <a class="flex items-center gap-3 px-4 py-3 bg-primary text-white rounded-lg" href="{{ route('my-overview') }}">
      <span class="material-icons text-xl">dashboard</span>
      <span class="font-medium">Overview</span>
    </a>
  </nav>
  <x-nav.user-footer />

</aside>