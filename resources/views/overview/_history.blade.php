{{-- Payment history grid (current year + previous year) + footer notice --}}
@php
  $appName = \App\Models\Setting::get('app_name', config('app.name', 'Dwipapuri'));
@endphp
<div class="space-y-6">

  {{-- Section Header --}}
  <div class="border-b border-slate-200 dark:border-slate-800 pb-4 space-y-3">

    {{-- Legend — mobile only (above title) --}}
    <div class="flex sm:hidden items-center gap-4 text-xs flex-wrap">
      <div class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-primary"></span><span class="text-slate-500">{{ __('app.status_paid') }}</span></div>
      <div class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-amber-400"></span><span class="text-slate-500">{{ __('app.status_pending') }}</span></div>
      <div class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-rose-400"></span><span class="text-slate-500">{{ __('app.status_rejected') }}</span></div>
      <div class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-slate-200 dark:bg-slate-700"></span><span class="text-slate-500">{{ __('app.status_upcoming') }}</span></div>
    </div>

    {{-- Title + Year tabs + Legend (desktop: inline right) --}}
    <div class="flex items-center gap-4">
      <h3 class="text-lg font-bold">{{ __('app.overview_payment_history') }}</h3>
      <div class="flex bg-slate-100 dark:bg-slate-800 p-1 rounded-lg text-sm font-bold">
        <button type="button" id="tab-year-current" onclick="switchYearTab('current')"
          class="px-4 py-1.5 rounded-md transition-all bg-white dark:bg-slate-700 shadow-sm text-slate-900 dark:text-white">{{ $currentYear }}</button>
        <button type="button" id="tab-year-previous" onclick="switchYearTab('previous')"
          class="px-4 py-1.5 rounded-md transition-all text-slate-400 hover:text-slate-600 dark:hover:text-slate-300">{{ $previousYear }}</button>
      </div>

      {{-- Legend — desktop only (right side) --}}
      <div class="hidden sm:flex items-center gap-4 text-xs flex-wrap ml-auto">
        <div class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-primary"></span><span class="text-slate-500">{{ __('app.status_paid') }}</span></div>
        <div class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-amber-400"></span><span class="text-slate-500">{{ __('app.status_pending') }}</span></div>
        <div class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-rose-400"></span><span class="text-slate-500">{{ __('app.status_rejected') }}</span></div>
        <div class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-slate-200 dark:bg-slate-700"></span><span class="text-slate-500">{{ __('app.status_upcoming') }}</span></div>
      </div>
    </div>
  </div>

  {{-- Current Year Grid --}}
  <div id="panel-year-current" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 gap-4">
    @foreach(range(1, 12) as $month)
      @php
        $date   = \Carbon\Carbon::create($currentYear, $month, 1);
        $record = $currentRecords->get($month);

        if ($record) {
          $status = $record->status instanceof \App\Enums\PaymentStatus
                      ? $record->status->value : $record->status;
        } elseif ($date->gt(now()->startOfMonth())) {
          $status = 'upcoming';
        } else {
          $status = 'unpaid';
        }

        $borderColor = match($status) {
          'approved' => 'border-primary',
          'pending'  => 'border-amber-400',
          'rejected' => 'border-rose-400',
          'unpaid'   => 'border-rose-400',
          default    => 'border-slate-200 dark:border-slate-700',
        };
        $bgColor = match($status) {
          'approved' => 'bg-white dark:bg-slate-900',
          'upcoming' => 'bg-slate-50 dark:bg-slate-800/50',
          default    => 'bg-white dark:bg-slate-900',
        };

        $feeAmount = $record?->amount ?? $currentFee;
        // Use locale-aware month name
        $monthLabel = $date->locale(app()->getLocale())->isoFormat('MMMM YYYY');
      @endphp

      <div class="{{ $bgColor }} border-l-4 {{ $borderColor }} p-4 rounded-lg shadow-sm border-r border-t border-b border-slate-200 dark:border-slate-800 {{ $status === 'upcoming' ? 'opacity-60' : '' }}">
        <p class="text-xs font-bold text-slate-400 uppercase">{{ $monthLabel }}</p>
        <p class="text-lg font-bold mt-1 {{ $status === 'upcoming' ? 'text-slate-400' : '' }}">
          {{ $currency }} {{ number_format($feeAmount) }}
        </p>
        <div class="mt-3 flex items-center justify-between">
          @switch($status)
            @case('approved')
              <span class="text-[10px] font-bold text-primary uppercase bg-primary/10 px-2 py-0.5 rounded">{{ __('app.status_paid') }}</span>
              @if($record?->reference_number)
                <span class="text-[10px] text-slate-400">Ref: #{{ $record->reference_number }}</span>
              @endif
              @break
            @case('pending')
              <span class="text-[10px] font-bold text-amber-600 dark:text-amber-400 uppercase bg-amber-100 dark:bg-amber-900/30 px-2 py-0.5 rounded">{{ __('app.status_pending') }}</span>
              <span class="text-[10px] text-slate-400">{{ __('app.overview_under_review') }}</span>
              @break
            @case('rejected')
              <span class="text-[10px] font-bold text-rose-600 dark:text-rose-400 uppercase bg-rose-100 dark:bg-rose-900/30 px-2 py-0.5 rounded">{{ __('app.status_rejected') }}</span>
              @break
            @case('unpaid')
              <span class="text-[10px] font-bold text-rose-600 dark:text-rose-400 uppercase bg-rose-100 dark:bg-rose-900/30 px-2 py-0.5 rounded">{{ __('app.status_overdue') }}</span>
              @break
            @default
              <span class="text-[10px] font-bold text-slate-400 uppercase bg-slate-200 dark:bg-slate-700 px-2 py-0.5 rounded">{{ __('app.status_upcoming') }}</span>
              <span class="text-[10px] text-slate-400">—</span>
          @endswitch
        </div>
      </div>
    @endforeach
  </div>

  {{-- Previous Year Panel (hidden by default) --}}
  <div id="panel-year-previous" class="hidden">
    @if($previousRecords->isEmpty())
      <p class="text-sm text-slate-400 italic">{{ __('app.overview_no_prev_records', ['year' => $previousYear]) }}</p>
    @else
      <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 gap-4 opacity-70">
        @foreach($previousRecords->sortBy(fn($r) => $r->payment_month) as $record)
          @php
            $date       = \Carbon\Carbon::parse($record->payment_month);
            $monthLabel = $date->locale(app()->getLocale())->isoFormat('MMMM YYYY');
            $recStatus  = $record->status instanceof \App\Enums\PaymentStatus
                            ? $record->status->value : $record->status;
          @endphp
          <div class="bg-white dark:bg-slate-900 border-l-4 {{ $recStatus === 'approved' ? 'border-primary' : 'border-rose-400' }} p-4 rounded-lg border border-slate-200 dark:border-slate-800">
            <p class="text-xs font-bold text-slate-400 uppercase">{{ $monthLabel }}</p>
            <p class="text-lg font-bold mt-1">{{ $currency }} {{ number_format($record->amount) }}</p>
            <div class="mt-3 flex items-center justify-between">
              @if($recStatus === 'approved')
                <span class="text-[10px] font-bold text-primary uppercase bg-primary/10 px-2 py-0.5 rounded">{{ __('app.status_paid') }}</span>
                @if($record->reference_number)
                  <span class="text-[10px] text-slate-400">Ref: #{{ $record->reference_number }}</span>
                @endif
              @else
                <span class="text-[10px] font-bold text-rose-600 uppercase bg-rose-100 px-2 py-0.5 rounded">{{ __('app.status_' . $recStatus, [], null) ?: ucfirst($recStatus) }}</span>
              @endif
            </div>
          </div>
        @endforeach
      </div>
    @endif
  </div>

</div>

<script>
  function switchYearTab(tab) {
    const isCurrent = tab === 'current';

    document.getElementById('panel-year-current').classList.toggle('hidden', !isCurrent);
    document.getElementById('panel-year-previous').classList.toggle('hidden', isCurrent);

    const activeClass  = ['bg-white', 'dark:bg-slate-700', 'shadow-sm', 'text-slate-900', 'dark:text-white'];
    const inactiveClass = ['text-slate-400', 'hover:text-slate-600', 'dark:hover:text-slate-300'];

    const btnCurrent  = document.getElementById('tab-year-current');
    const btnPrevious = document.getElementById('tab-year-previous');

    if (isCurrent) {
      btnCurrent.classList.add(...activeClass);
      btnCurrent.classList.remove(...inactiveClass);
      btnPrevious.classList.remove(...activeClass);
      btnPrevious.classList.add(...inactiveClass);
    } else {
      btnPrevious.classList.add(...activeClass);
      btnPrevious.classList.remove(...inactiveClass);
      btnCurrent.classList.remove(...activeClass);
      btnCurrent.classList.add(...inactiveClass);
    }
  }
</script>

{{-- Transparency Notice --}}
<footer class="bg-slate-100 dark:bg-slate-800/40 p-6 rounded-xl border border-dashed border-slate-300 dark:border-slate-700">
  <div class="flex items-start gap-4">
    <span class="material-icons text-primary mt-1">info</span>
    <div>
      <p class="font-bold text-sm">{{ __('app.overview_notice_title') }}</p>
      <p class="text-sm text-slate-500 mt-1">{{ __('app.overview_notice_body', ['name' => $appName]) }}</p>
    </div>
  </div>
</footer>
