{{-- Resident Personal Overview --}}
<x-layouts.app title="My Overview"
  class="font-display bg-background-light dark:bg-background-dark text-slate-800 dark:text-slate-200 antialiased min-h-screen">

  {{-- Resident Sidebar --}}
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

  {{-- Main content --}}
  <main class="lg:ml-64 flex flex-col min-h-screen">

    {{-- Top bar --}}
    <header class="h-16 bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between px-8">
      <h1 class="text-xl font-bold">Resident Personal Overview</h1>
      <div class="flex items-center gap-4">
        <div class="text-xs text-slate-400 text-right">
          <p>Last Sync</p>
          <p class="font-medium">{{ now()->format('M d, Y • h:i A') }}</p>
        </div>
        <button onclick="window.location.reload()" class="p-2 text-slate-400 hover:text-primary transition-colors" title="Refresh">
          <span class="material-icons">refresh</span>
        </button>
      </div>
    </header>

    <div class="p-8 space-y-8 max-w-7xl mx-auto w-full">

      {{-- Flash --}}
      @if(session('success'))
        <div class="p-4 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-900/30 rounded-xl flex items-center gap-3">
          <span class="material-icons text-emerald-500">check_circle</span>
          <p class="text-sm text-emerald-700 dark:text-emerald-400">{{ session('success') }}</p>
        </div>
      @endif

      @if (!$resident)
        {{-- No resident record linked --}}
        <div class="text-center py-24">
          <span class="material-icons text-5xl text-slate-300 dark:text-slate-600 block mb-4">person_off</span>
          <h2 class="text-xl font-bold text-slate-700 dark:text-slate-300">No Resident Profile Found</h2>
          <p class="text-slate-500 mt-2">Your account is not yet linked to a resident record. Please contact management.</p>
        </div>
      @else
        {{-- ── Summary Cards ─────────────────────────────────────────── --}}
        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-6">

          {{-- Identity Card --}}
          <div class="col-span-1 md:col-span-2 bg-white dark:bg-slate-900 p-6 rounded-xl border border-slate-200 dark:border-slate-800 flex items-center justify-between shadow-sm">
            <div class="flex items-center gap-4">
              <div class="w-16 h-16 bg-primary/10 text-primary rounded-xl flex items-center justify-center">
                <span class="material-icons text-3xl">person</span>
              </div>
              <div>
                <h2 class="text-lg font-bold">{{ $resident->fullname }}</h2>
                <p class="text-slate-500 text-sm">Unit: <span class="font-mono font-bold">{{ $resident->block?->name }} - {{ $resident->unit_number }}</span></p>
                <div class="mt-1 flex items-center gap-2">
                  @if($resident->is_active)
                    <span class="px-2 py-0.5 bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400 text-[10px] font-bold uppercase rounded">Active</span>
                  @else
                    <span class="px-2 py-0.5 bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-400 text-[10px] font-bold uppercase rounded">Inactive</span>
                  @endif
                  <span class="text-xs text-slate-400">• Resident since {{ $resident->created_at->format('M Y') }}</span>
                </div>
              </div>
            </div>
          </div>

          {{-- Active Fee Card --}}
          <div class="bg-primary text-white p-6 rounded-xl shadow-lg flex flex-col justify-between">
            <span class="text-sm font-medium opacity-80 uppercase tracking-wider">Active Monthly Fee</span>
            <div>
              <span class="text-3xl font-extrabold">{{ $currency }} {{ number_format($currentFee) }}</span>
              <p class="text-xs opacity-75 mt-1">Due on the {{ $dueDayLabel }}{{ (int)$dueDayLabel === 1 ? 'st' : ((int)$dueDayLabel === 2 ? 'nd' : ((int)$dueDayLabel === 3 ? 'rd' : 'th')) }} of every month</p>
            </div>
          </div>

          {{-- Total Paid This Year --}}
          <div class="bg-white dark:bg-slate-900 p-6 rounded-xl border border-slate-200 dark:border-slate-800 flex flex-col justify-between shadow-sm">
            <span class="text-sm font-medium text-slate-500 uppercase tracking-wider">Total Paid {{ $currentYear }}</span>
            <div>
              <span class="text-2xl font-bold">{{ $currency }} {{ number_format($totalPaidYear) }}</span>
              <div class="flex items-center gap-1 text-emerald-500 text-xs font-bold mt-1">
                <span class="material-icons text-xs">check_circle</span>
                <span>{{ $paidMonthsYear }} of 12 Months</span>
              </div>
            </div>
          </div>
        </div>

        {{-- ── Payment History ────────────────────────────────────────── --}}
        <div class="space-y-6">

          {{-- Section Header --}}
          <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-4">
            <div class="flex items-center gap-4">
              <h3 class="text-lg font-bold">Payment History</h3>
              <div class="flex bg-slate-100 dark:bg-slate-800 p-1 rounded-lg text-sm font-bold">
                <span class="px-4 py-1.5 bg-white dark:bg-slate-700 shadow-sm rounded-md">{{ $currentYear }}</span>
                <span class="px-4 py-1.5 text-slate-400">{{ $previousYear }}</span>
              </div>
            </div>
            <div class="flex items-center gap-4 text-xs">
              <div class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-primary"></span><span class="text-slate-500">Paid</span></div>
              <div class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-amber-400"></span><span class="text-slate-500">Pending</span></div>
              <div class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-rose-400"></span><span class="text-slate-500">Rejected</span></div>
              <div class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-slate-200 dark:bg-slate-700"></span><span class="text-slate-500">Upcoming</span></div>
            </div>
          </div>

          {{-- Current Year Grid --}}
          <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 gap-4">
            @foreach(range(1, 12) as $month)
              @php
                $date   = \Carbon\Carbon::create($currentYear, $month, 1);
                $record = $currentRecords->get($month);
                $isPast = $date->lt(now()->startOfMonth());
                $isCurrent = $date->equalTo(now()->startOfMonth());

                if ($record) {
                  $status = $record->status; // approved, pending, rejected
                } elseif ($date->gt(now()->startOfMonth())) {
                  $status = 'upcoming';
                } else {
                  $status = 'unpaid';
                }

                $borderColor = match($status) {
                  'approved'  => 'border-primary',
                  'pending'   => 'border-amber-400',
                  'rejected'  => 'border-rose-400',
                  'unpaid'    => 'border-rose-400',
                  default     => 'border-slate-200 dark:border-slate-700',
                };
                $bgColor = match($status) {
                  'approved' => 'bg-white dark:bg-slate-900',
                  'upcoming' => 'bg-slate-50 dark:bg-slate-800/50',
                  default    => 'bg-white dark:bg-slate-900',
                };

                $feeAmount = $record?->amount ?? $currentFee;
              @endphp

              <div class="{{ $bgColor }} border-l-4 {{ $borderColor }} p-4 rounded-lg shadow-sm border-r border-t border-b border-slate-200 dark:border-slate-800 {{ $status === 'upcoming' ? 'opacity-60' : '' }}">
                <p class="text-xs font-bold text-slate-400 uppercase">{{ $date->format('F Y') }}</p>
                <p class="text-lg font-bold mt-1 {{ $status === 'upcoming' ? 'text-slate-400' : '' }}">
                  {{ $currency }} {{ number_format($feeAmount) }}
                </p>
                <div class="mt-3 flex items-center justify-between">
                  @switch($status)
                    @case('approved')
                      <span class="text-[10px] font-bold text-primary uppercase bg-primary/10 px-2 py-0.5 rounded">Paid</span>
                      @if($record?->reference_number)
                        <span class="text-[10px] text-slate-400">Ref: #{{ $record->reference_number }}</span>
                      @endif
                      @break
                    @case('pending')
                      <span class="text-[10px] font-bold text-amber-600 dark:text-amber-400 uppercase bg-amber-100 dark:bg-amber-900/30 px-2 py-0.5 rounded">Pending</span>
                      <span class="text-[10px] text-slate-400">Under review</span>
                      @break
                    @case('rejected')
                      <span class="text-[10px] font-bold text-rose-600 dark:text-rose-400 uppercase bg-rose-100 dark:bg-rose-900/30 px-2 py-0.5 rounded">Rejected</span>
                      @break
                    @case('unpaid')
                      <span class="text-[10px] font-bold text-rose-600 dark:text-rose-400 uppercase bg-rose-100 dark:bg-rose-900/30 px-2 py-0.5 rounded">Overdue</span>
                      @break
                    @default
                      <span class="text-[10px] font-bold text-slate-400 uppercase bg-slate-200 dark:bg-slate-700 px-2 py-0.5 rounded">Upcoming</span>
                      <span class="text-[10px] text-slate-400">—</span>
                  @endswitch
                </div>
              </div>
            @endforeach
          </div>

          {{-- Previous Year Section --}}
          <div class="mt-12">
            <div class="flex items-center gap-4 border-b border-slate-200 dark:border-slate-800 pb-4 mb-6">
              <h3 class="text-lg font-bold text-slate-500">Previous Year: {{ $previousYear }}</h3>
              <span class="text-xs bg-primary/10 text-primary px-2 py-1 rounded font-bold uppercase tracking-tighter">Completed</span>
            </div>

            @if($previousRecords->isEmpty())
              <p class="text-sm text-slate-400 italic">No payment records for {{ $previousYear }}.</p>
            @else
              <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 gap-4 opacity-70">
                @foreach($previousRecords->sortByDesc('payment_month') as $record)
                  @php
                    $date = \Carbon\Carbon::parse($record->payment_month);
                  @endphp
                  <div class="bg-white dark:bg-slate-900 border-l-4 {{ $record->status === 'approved' ? 'border-primary' : 'border-rose-400' }} p-4 rounded-lg border border-slate-200 dark:border-slate-800">
                    <p class="text-xs font-bold text-slate-400 uppercase">{{ $date->format('F Y') }}</p>
                    <p class="text-lg font-bold mt-1">{{ $currency }} {{ number_format($record->amount) }}</p>
                    <div class="mt-3 flex items-center justify-between">
                      @if($record->status === 'approved')
                        <span class="text-[10px] font-bold text-primary uppercase bg-primary/10 px-2 py-0.5 rounded">Paid</span>
                        @if($record->reference_number)
                          <span class="text-[10px] text-slate-400">Ref: #{{ $record->reference_number }}</span>
                        @endif
                      @else
                        <span class="text-[10px] font-bold text-rose-600 uppercase bg-rose-100 px-2 py-0.5 rounded">{{ ucfirst($record->status) }}</span>
                      @endif
                    </div>
                  </div>
                @endforeach
              </div>
            @endif
          </div>
        </div>

        {{-- ── Transparency Notice ─────────────────────────────────────── --}}
        <footer class="bg-slate-100 dark:bg-slate-800/40 p-6 rounded-xl border border-dashed border-slate-300 dark:border-slate-700">
          <div class="flex items-start gap-4">
            <span class="material-icons text-primary mt-1">info</span>
            <div>
              <p class="font-bold text-sm">Transparency & Accuracy Notice</p>
              <p class="text-sm text-slate-500 mt-1">This dashboard is a read-only representation of your payment ledger as maintained by CiviCore Management.
                If you notice any discrepancies, please contact the management office directly.</p>
            </div>
          </div>
        </footer>
      @endif

    </div>
  </main>

</x-layouts.app>
