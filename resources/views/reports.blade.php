{{-- Yearly Block Report --}}
<x-layouts.app title="Reports"
  class="font-display bg-background-light dark:bg-background-dark text-slate-800 dark:text-slate-200 antialiased min-h-screen">

  <x-nav.sidebar active="reports" />

  <main class="lg:ml-64 p-4 lg:p-8">

    {{-- Header --}}
    <header class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-8">
      <div>
        <nav class="flex items-center gap-2 text-xs font-medium text-slate-400 mb-2 uppercase tracking-wider">
          <span>Dashboard</span>
          <span class="material-icons text-xs">chevron_right</span>
          <span class="text-primary">Reports</span>
        </nav>
        <h1 class="text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">Yearly Financial Report</h1>
        <p class="text-slate-500 dark:text-slate-400 mt-1">Payment tracking for {{ $year }} by resident and block.</p>
      </div>
      <div class="flex items-center gap-3">
        <button onclick="window.print()"
          class="flex items-center gap-2 px-6 py-2.5 bg-primary text-white rounded-lg text-sm font-bold hover:bg-primary/90 transition-all shadow-lg shadow-primary/20">
          <span class="material-icons text-lg">print</span>
          Print Report
        </button>
      </div>
    </header>

    {{-- Filters --}}
    <form method="GET" action="{{ route('reports.index') }}"
      class="bg-white dark:bg-slate-900 p-4 rounded-xl border border-slate-200 dark:border-slate-800 mb-6 shadow-sm">
      <div class="grid grid-cols-1 md:grid-cols-4 lg:grid-cols-5 gap-4">
        <div class="space-y-1.5">
          <label class="text-xs font-bold text-slate-400 uppercase tracking-tight">Year</label>
          <select name="year" onchange="this.form.submit()"
            class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-lg text-sm focus:ring-2 focus:ring-primary px-4 py-2.5">
            @foreach($years as $y)
              <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>{{ $y }}</option>
            @endforeach
          </select>
        </div>
        <div class="space-y-1.5">
          <label class="text-xs font-bold text-slate-400 uppercase tracking-tight">Block</label>
          <select name="block_id" onchange="this.form.submit()"
            class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-lg text-sm focus:ring-2 focus:ring-primary px-4 py-2.5">
            <option value="">All Blocks</option>
            @foreach($blocks as $block)
              <option value="{{ $block->id }}" {{ $blockId == $block->id ? 'selected' : '' }}>{{ $block->name }}</option>
            @endforeach
          </select>
        </div>
        <div class="md:col-span-2 space-y-1.5">
          <label class="text-xs font-bold text-slate-400 uppercase tracking-tight">Search Resident</label>
          <div class="relative">
            <span class="material-icons absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-lg">search</span>
            <input name="search" value="{{ $search }}"
              class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-lg text-sm focus:ring-2 focus:ring-primary pl-10 py-2.5"
              placeholder="Name or unit number..." />
          </div>
        </div>
        <div class="flex items-end gap-2">
          <button type="submit"
            class="flex-1 bg-primary/10 text-primary hover:bg-primary/20 py-2.5 rounded-lg text-sm font-bold transition-colors">
            Apply
          </button>
          @if($search || $blockId)
            <a href="{{ route('reports.index', ['year' => $year]) }}"
              class="py-2.5 px-3 border border-slate-200 dark:border-slate-700 rounded-lg text-sm text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
              <span class="material-icons text-base leading-none">close</span>
            </a>
          @endif
        </div>
      </div>
    </form>

    {{-- Summary Stats --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
      <div class="bg-white dark:bg-slate-900 p-4 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm">
        <p class="text-xs font-bold text-slate-400 uppercase tracking-wide">Collection Rate</p>
        <div class="flex items-end justify-between mt-1">
          <h3 class="text-xl font-bold text-slate-900 dark:text-white">{{ $collectionRate }}%</h3>
          <span class="text-{{ $collectionRate >= 80 ? 'emerald' : 'amber' }}-500 text-xs font-bold flex items-center">
            <span class="material-icons text-sm">{{ $collectionRate >= 80 ? 'trending_up' : 'trending_flat' }}</span>
          </span>
        </div>
        <div class="w-full bg-slate-100 dark:bg-slate-800 h-1.5 rounded-full mt-3">
          <div class="bg-emerald-500 h-1.5 rounded-full transition-all" style="width: {{ $collectionRate }}%"></div>
        </div>
      </div>
      <div class="bg-white dark:bg-slate-900 p-4 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm">
        <p class="text-xs font-bold text-slate-400 uppercase tracking-wide">Active Residents</p>
        <h3 class="text-xl font-bold text-slate-900 dark:text-white mt-1">{{ $totalResidents }}</h3>
        <p class="text-xs text-slate-500 mt-2">Included in report</p>
      </div>
      <div class="bg-white dark:bg-slate-900 p-4 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm">
        <p class="text-xs font-bold text-slate-400 uppercase tracking-wide">Paid Months</p>
        <h3 class="text-xl font-bold text-emerald-600 dark:text-emerald-400 mt-1">{{ number_format($paidCount) }}</h3>
        <p class="text-xs text-slate-500 mt-2">Confirmed payments</p>
      </div>
      <div class="bg-white dark:bg-slate-900 p-4 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm">
        <p class="text-xs font-bold text-slate-400 uppercase tracking-wide">Outstanding</p>
        <h3 class="text-xl font-bold text-red-500 mt-1">{{ number_format($unpaidCount) }}</h3>
        <p class="text-xs text-slate-500 mt-2">Months unpaid/pending</p>
      </div>
    </div>

    {{-- Heatmap Table --}}
    <div
      class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden flex flex-col">
      <div class="overflow-x-auto custom-scrollbar">
        <table class="w-full text-left border-collapse min-w-[1100px]">
          <thead>
            <tr class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-800">
              <th
                class="sticky left-0 z-20 bg-slate-50 dark:bg-slate-800 px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider border-r border-slate-200 dark:border-slate-800 w-40">
                Unit & Resident
              </th>
              @foreach($months as $m)
                <th class="px-3 py-4 text-center text-xs font-bold text-slate-500 uppercase tracking-wider">
                  {{ $m['label'] }}</th>
              @endforeach
              <th class="px-6 py-4 text-right text-xs font-bold text-primary uppercase tracking-wider">Annual Total</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
            @forelse($residents as $resident)
              @php
                $yearRecords = $resident->paymentRecords->keyBy(fn($r) => \Carbon\Carbon::parse($r->payment_month)->month);
                $annualTotal = $resident->paymentRecords->where('status', 'approved')->sum('amount');
              @endphp
              <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors">
                <td
                  class="sticky left-0 z-10 bg-white dark:bg-slate-900 px-6 py-4 border-r border-slate-200 dark:border-slate-800">
                  <div class="flex flex-col">
                    <span class="text-sm font-bold text-slate-900 dark:text-white">{{ $resident->unit_number }}</span>
                    <span class="text-[11px] text-slate-400 font-medium truncate">{{ $resident->fullname }}</span>
                    @if($resident->block)
                      <span class="text-[10px] text-primary/70 font-medium mt-0.5">{{ $resident->block->name }}</span>
                    @endif
                  </div>
                </td>
                @foreach($months as $m)
                  @php
                    $record = $yearRecords->get($m['num']);
                    $today = \Carbon\Carbon::create($year, $m['num'], 1);
                    $isFuture = $today->gt(now()->startOfMonth());
                  @endphp
                  <td class="p-1">
                    @if($record && $record->status === 'approved')
                      <div
                        class="bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600 dark:text-emerald-400 py-3 rounded text-center">
                        <span
                          class="text-[10px] font-bold block">{{ \Carbon\Carbon::parse($record->paid_at ?? $record->updated_at)->format('d/m') }}</span>
                        <span class="material-icons text-sm">check_circle</span>
                      </div>
                    @elseif($record && $record->status === 'pending')
                      <div
                        class="bg-amber-50 dark:bg-amber-900/20 text-amber-500 py-3 rounded text-center flex flex-col items-center justify-center h-full min-h-[44px]">
                        <span class="material-icons text-sm">hourglass_empty</span>
                      </div>
                    @elseif($isFuture)
                      <div
                        class="bg-slate-50 dark:bg-slate-800/40 py-3 rounded text-center flex flex-col items-center justify-center h-full min-h-[44px]">
                        <span class="text-[10px] text-slate-300 dark:text-slate-600">—</span>
                      </div>
                    @else
                      <div
                        class="bg-red-50 dark:bg-red-900/20 text-red-500 py-3 rounded text-center flex flex-col items-center justify-center h-full min-h-[44px]">
                        <span class="material-icons text-sm">priority_high</span>
                      </div>
                    @endif
                  </td>
                @endforeach
                <td class="px-6 py-4 text-right">
                  <span class="text-sm font-bold text-slate-900 dark:text-white">
                    {{ $annualTotal > 0 ? $currency . ' ' . number_format($annualTotal) : '—' }}
                  </span>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="{{ count($months) + 2 }}" class="px-6 py-16 text-center">
                  <span class="material-icons text-4xl text-slate-300 dark:text-slate-600 block mb-2">assessment</span>
                  <p class="text-slate-500 font-medium">No residents found</p>
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      {{-- Legend + Pagination --}}
      <div
        class="p-4 flex flex-col md:flex-row items-center justify-between border-t border-slate-200 dark:border-slate-800 gap-4">
        <div class="flex items-center gap-6">
          <div class="flex items-center gap-2">
            <div class="w-3 h-3 bg-emerald-500 rounded-sm"></div><span
              class="text-xs font-medium text-slate-500">Paid</span>
          </div>
          <div class="flex items-center gap-2">
            <div class="w-3 h-3 bg-amber-400 rounded-sm"></div><span
              class="text-xs font-medium text-slate-500">Pending</span>
          </div>
          <div class="flex items-center gap-2">
            <div class="w-3 h-3 bg-red-400 rounded-sm"></div><span
              class="text-xs font-medium text-slate-500">Unpaid</span>
          </div>
          <div class="flex items-center gap-2">
            <div class="w-3 h-3 bg-slate-200 dark:bg-slate-700 rounded-sm"></div><span
              class="text-xs font-medium text-slate-500">Future</span>
          </div>
        </div>
        @if($residents->hasPages())
          <div>{{ $residents->links() }}</div>
        @endif
      </div>
    </div>

    {{-- Footer --}}
    <footer class="mt-8 pt-8 border-t border-slate-200 dark:border-slate-800">
      <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <p class="text-xs text-slate-400 font-medium">
          © {{ now()->year }} CiviCore Community Management. Generated {{ now()->format('M d, Y \a\t h:i A') }}.
        </p>
      </div>
    </footer>

  </main>

</x-layouts.app>