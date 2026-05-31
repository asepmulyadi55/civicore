{{-- finance/_dashboard.blade.php --}}
@php
  $fmt = fn($n) => number_format((float)$n, 0, ',', '.');
  $selectedPeriodLabel = \Carbon\Carbon::create($selectedYear, $selectedMonth)->format('F Y');
@endphp

{{-- Period selector --}}
<div class="flex items-center gap-3">
  <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">{{ __('app.fin_viewing_period') }}</span>
  <form method="GET" action="{{ route('finance.index') }}" class="flex items-center gap-2">
    <input type="hidden" name="tab" value="dashboard">
    <div class="relative">
      <select name="dash_month" onchange="this.form.submit()"
        class="appearance-none pl-3 pr-8 py-2 text-sm font-medium rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-primary/30 cursor-pointer">
        @foreach(range(1,12) as $m)
          <option value="{{ $m }}" {{ $m == $selectedMonth ? 'selected' : '' }}>
            {{ \Carbon\Carbon::create(null, $m)->format('F') }}
          </option>
        @endforeach
      </select>
      <span class="pointer-events-none absolute right-2 top-1/2 -translate-y-1/2 material-icons text-slate-400 text-[15px]">expand_more</span>
    </div>
    <div class="relative">
      <select name="dash_year" onchange="this.form.submit()"
        class="appearance-none pl-3 pr-8 py-2 text-sm font-medium rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-primary/30 cursor-pointer">
        @foreach(range(now()->year + 1, 2020) as $y)
          <option value="{{ $y }}" {{ $y == $selectedYear ? 'selected' : '' }}>{{ $y }}</option>
        @endforeach
      </select>
      <span class="pointer-events-none absolute right-2 top-1/2 -translate-y-1/2 material-icons text-slate-400 text-[15px]">expand_more</span>
    </div>
  </form>
  @if($selectedMonth != $currentMonth || $selectedYear != $currentYear)
    <a href="{{ route('finance.index', ['tab' => 'dashboard']) }}"
      class="text-xs text-primary hover:underline font-medium">{{ __('app.fin_back_to_current') }}</a>
  @endif
</div>

{{-- Summary cards --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4">

  {{-- Current Balance --}}
  <div class="col-span-2 lg:col-span-1 bg-white dark:bg-slate-800 rounded-xl p-5 border border-slate-200 dark:border-slate-700 shadow-sm">
    <div class="flex items-center justify-between mb-3">
      <span class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wide">{{ __('app.fin_current_balance') }}</span>
      <span class="material-icons text-emerald-500 text-[20px]">account_balance_wallet</span>
    </div>
    <p class="text-2xl font-bold text-slate-800 dark:text-slate-100">
      {{ $currency }} {{ $fmt($currentBalance) }}
    </p>
    <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">{{ __('app.fin_from_last_approved') }}</p>
  </div>

  {{-- Monthly Income --}}
  <div class="bg-white dark:bg-slate-800 rounded-xl p-5 border border-slate-200 dark:border-slate-700 shadow-sm">
    <div class="flex items-center justify-between mb-3">
      <span class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wide">{{ __('app.fin_month_income') }}</span>
      <span class="material-icons text-sky-500 text-[20px]">trending_up</span>
    </div>
    <p class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">
      {{ $currency }} {{ $fmt($monthIncome) }}
    </p>
    <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">{{ $selectedPeriodLabel }}</p>
  </div>

  {{-- Monthly Expense --}}
  <div class="bg-white dark:bg-slate-800 rounded-xl p-5 border border-slate-200 dark:border-slate-700 shadow-sm">
    <div class="flex items-center justify-between mb-3">
      <span class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wide">{{ __('app.fin_month_expense') }}</span>
      <span class="material-icons text-rose-500 text-[20px]">trending_down</span>
    </div>
    <p class="text-2xl font-bold text-rose-600 dark:text-rose-400">
      {{ $currency }} {{ $fmt($monthExpense) }}
    </p>
    <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">{{ $selectedPeriodLabel }}</p>
  </div>

  {{-- Pending Payments --}}
  <div class="bg-white dark:bg-slate-800 rounded-xl p-5 border border-slate-200 dark:border-slate-700 shadow-sm">
    <div class="flex items-center justify-between mb-3">
      <span class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wide">{{ __('app.fin_pending_payments') }}</span>
      <span class="material-icons text-amber-500 text-[20px]">pending_actions</span>
    </div>
    <p class="text-2xl font-bold text-amber-600 dark:text-amber-400">
      {{ $currency }} {{ $fmt($pendingPaymentsTotal) }}
    </p>
    <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">{{ $pendingPaymentsCount }} {{ Str::lower(__('app.payments_awaiting_review')) }}</p>
  </div>

</div>

{{-- Chart + Pending Approvals --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

  {{-- Monthly trend chart --}}
  <div class="lg:col-span-2 bg-white dark:bg-slate-800 rounded-xl p-5 border border-slate-200 dark:border-slate-700 shadow-sm">
    <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-300 mb-4">{{ __('app.fin_monthly_trend') }}</h3>

    <div class="flex items-end gap-3" style="height: 120px;">
      @foreach($trend as $t)
        @php
          $incH = $maxTrend > 0 ? max(4, (int) round($t['income'] / $maxTrend * 100)) : 4;
          $expH = $maxTrend > 0 ? max(4, (int) round($t['expense'] / $maxTrend * 100)) : 4;
        @endphp
        <div class="flex-1 flex flex-col items-center gap-1">
          <div class="w-full flex gap-0.5 items-end justify-center" style="height: 100px;">
            <div class="flex-1 rounded-t bg-emerald-400 dark:bg-emerald-500 transition-all"
                 style="height: {{ $incH }}%;"
                 title="{{ __('app.fin_income_label') }}: {{ $currency }} {{ number_format($t['income'], 0, ',', '.') }}"></div>
            <div class="flex-1 rounded-t bg-rose-400 dark:bg-rose-500 transition-all"
                 style="height: {{ $expH }}%;"
                 title="{{ __('app.fin_expense_label') }}: {{ $currency }} {{ number_format($t['expense'], 0, ',', '.') }}"></div>
          </div>
          <span class="text-[10px] text-slate-400 dark:text-slate-500 font-medium">{{ $t['label'] }}</span>
        </div>
      @endforeach
    </div>

    <div class="flex items-center gap-4 mt-4 pt-3 border-t border-slate-100 dark:border-slate-700">
      <div class="flex items-center gap-1.5">
        <span class="w-3 h-3 rounded-sm bg-emerald-400"></span>
        <span class="text-xs text-slate-500 dark:text-slate-400">{{ __('app.fin_income_label') }}</span>
      </div>
      <div class="flex items-center gap-1.5">
        <span class="w-3 h-3 rounded-sm bg-rose-400"></span>
        <span class="text-xs text-slate-500 dark:text-slate-400">{{ __('app.fin_expense_label') }}</span>
      </div>
    </div>
  </div>

  {{-- Pending approvals --}}
  <div class="bg-white dark:bg-slate-800 rounded-xl p-5 border border-slate-200 dark:border-slate-700 shadow-sm">
    <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-300 mb-4">{{ __('app.fin_pending_approvals') }}</h3>

    @forelse($pendingReports as $pr)
      <div class="flex items-center justify-between py-2.5 border-b border-slate-100 dark:border-slate-700 last:border-0">
        <div>
          <p class="text-sm font-medium text-slate-700 dark:text-slate-300">{{ $pr->periodLabel() }}</p>
          <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">
            {{ __('app.fin_submitted_by') }}: {{ $pr->submittedBy?->name ?? '—' }}
          </p>
        </div>
        @if($canApprove)
          <form method="POST" action="{{ route('finance.reports.approve', $pr) }}">
            @csrf @method('PATCH')
            <button type="submit"
              class="text-xs px-2.5 py-1 bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400 rounded-lg hover:opacity-80 font-medium">
              {{ __('app.fin_approve_report') }}
            </button>
          </form>
        @endif
      </div>
    @empty
      <p class="text-sm text-slate-400 dark:text-slate-500 text-center py-6">{{ __('app.fin_no_pending_reports') }}</p>
    @endforelse
  </div>
</div>

{{-- Recent transactions --}}
<div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
  <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100 dark:border-slate-700">
    <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-300">{{ __('app.fin_recent_transactions') }}</h3>
    <a href="{{ route('finance.index', ['tab' => 'transactions']) }}"
       class="text-xs text-primary dark:text-emerald-400 hover:underline font-medium">{{ __('app.view_all') }}</a>
  </div>

  @if($recentTransactions->isEmpty())
    <div class="text-center py-10 text-slate-400 dark:text-slate-500 text-sm">
      <span class="material-icons text-3xl block mb-2">receipt_long</span>
      {{ __('app.fin_no_transactions') }}
    </div>
  @else
    <div class="divide-y divide-slate-100 dark:divide-slate-700">
      @foreach($recentTransactions as $tx)
        @php $badge = $tx->typeBadge(); @endphp
        <div class="flex items-center gap-4 px-5 py-3 hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
          <div class="flex-shrink-0 w-9 h-9 rounded-full flex items-center justify-center
            {{ $tx->type === 'income' ? 'bg-emerald-100 dark:bg-emerald-900/30' : 'bg-rose-100 dark:bg-rose-900/30' }}">
            <span class="material-icons text-[18px] {{ $tx->type === 'income' ? 'text-emerald-500' : 'text-rose-500' }}">
              {{ $tx->type === 'income' ? 'arrow_downward' : 'arrow_upward' }}
            </span>
          </div>
          <div class="flex-1 min-w-0">
            <p class="text-sm font-medium text-slate-700 dark:text-slate-300 truncate">{{ $tx->description }}</p>
            <p class="text-xs text-slate-400 dark:text-slate-500">
              {{ $tx->category ? $tx->category . ' · ' : '' }}{{ $tx->transaction_date->format('d M Y') }}
            </p>
          </div>
          <div class="text-right flex-shrink-0">
            <p class="text-sm font-semibold {{ $tx->type === 'income' ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
              {{ $tx->type === 'income' ? '+' : '-' }}{{ $currency }} {{ number_format($tx->amount, 0, ',', '.') }}
            </p>
            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium {{ $badge['class'] }}">
              {{ $badge['label'] }}
            </span>
          </div>
        </div>
      @endforeach
    </div>
  @endif
</div>
