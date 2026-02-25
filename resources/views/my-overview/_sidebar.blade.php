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

  {{-- User footer --}}
  <div class="p-4 border-t border-slate-200 dark:border-slate-800">
    <div class="flex items-center gap-3 px-2">
      <div class="w-9 h-9 rounded-full bg-primary/10 text-primary flex items-center justify-center font-bold text-sm">
        {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
      </div>
      <div class="flex-1 min-w-0">
        <p class="text-sm font-bold truncate uppercase">{{ auth()->user()->name }}</p>
        @if ($resident)
          <p class="text-xs text-slate-400">{{ $resident->block?->name }} · {{ $resident->unit_number }}</p>
        @endif
      </div>
      <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="text-slate-400 hover:text-slate-600 transition-colors" title="Logout">
          <span class="material-icons text-lg">logout</span>
        </button>
      </form>
    </div>
  </div>
</aside>