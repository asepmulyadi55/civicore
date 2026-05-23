{{-- Yearly Payment Heatmap Table --}}
<div
  class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden flex flex-col">
  <div class="overflow-x-auto custom-scrollbar">
    <table class="w-full text-left border-collapse min-w-[1100px]">
      <thead>
        <tr class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-800">
          <th
            class="sticky left-0 z-20 bg-slate-50 dark:bg-slate-800 px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider border-r border-slate-200 dark:border-slate-800 w-40">
            {{ __('app.unit_and_resident') }}
          </th>
          @foreach($months as $m)
            <th class="px-3 py-4 text-center text-xs font-bold text-slate-500 uppercase tracking-wider">{{ $m['label'] }}
            </th>
          @endforeach
          <th class="px-6 py-4 text-right text-xs font-bold text-primary uppercase tracking-wider">
            {{ __('app.annual_total') }}
          </th>
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
                  <span class="text-[10px] text-primary/70 font-medium mt-0.5 dark:text-white">{{ $resident->block->name }}</span>
                @endif
              </div>
            </td>
            @foreach($months as $m)
              @php
                $record = $yearRecords->get($m['num']);
                $today = \Carbon\Carbon::create($year, $m['num'], 1);
                $isFuture = $today->gt(now()->startOfMonth());
                $recordStatus = $record
                  ? ($record->status instanceof \App\Enums\PaymentStatus ? $record->status->value : (string) $record->status)
                  : null;
              @endphp
              <td class="p-1">
                @if($recordStatus === 'approved')
                  <div
                    class="bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600 dark:text-emerald-400 py-3 rounded text-center">
                    <span
                      class="text-[10px] font-bold block">{{ \Carbon\Carbon::parse($record->paid_at ?? $record->updated_at)->format('d/m') }}</span>
                    <span class="material-icons text-sm">check_circle</span>
                  </div>
                @elseif($recordStatus === 'pending')
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
              <p class="text-slate-500 font-medium">{{ __('app.no_residents_found') }}</p>
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  {{-- Legend + Pagination --}}
  <div
    class="p-4 flex flex-col sm:flex-row items-center sm:justify-between border-t border-slate-200 dark:border-slate-800 gap-4">
    <div class="flex items-center gap-6">
      <div class="flex items-center gap-2">
        <div class="w-3 h-3 bg-emerald-500 rounded-sm"></div>
        <span class="text-xs font-medium text-slate-500">{{ __('app.legend_paid') }}</span>
      </div>
      <div class="flex items-center gap-2">
        <div class="w-3 h-3 bg-amber-400 rounded-sm"></div>
        <span class="text-xs font-medium text-slate-500">{{ __('app.legend_pending') }}</span>
      </div>
      <div class="flex items-center gap-2">
        <div class="w-3 h-3 bg-red-400 rounded-sm"></div>
        <span class="text-xs font-medium text-slate-500">{{ __('app.legend_unpaid') }}</span>
      </div>
      <div class="flex items-center gap-2">
        <div class="w-3 h-3 bg-slate-200 dark:bg-slate-700 rounded-sm"></div>
        <span class="text-xs font-medium text-slate-500">{{ __('app.legend_future') }}</span>
      </div>
    </div>
    @if($residents->hasPages())
      <div class="flex items-center gap-1">
        @if ($residents->onFirstPage())
          <button class="p-2 rounded-lg border border-slate-200 dark:border-slate-700 text-slate-300 dark:text-slate-600 cursor-not-allowed" disabled>
            <span class="material-icons text-sm">chevron_left</span>
          </button>
        @else
          <a href="{{ $residents->previousPageUrl() }}" class="p-2 rounded-lg border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
            <span class="material-icons text-sm">chevron_left</span>
          </a>
        @endif

        @php
          $lastPage    = $residents->lastPage();
          $currentPage = $residents->currentPage();
          $start       = max(1, $currentPage - 2);
          $end         = min($lastPage, $currentPage + 2);
        @endphp

        @if ($start > 1)
          <a href="{{ $residents->url(1) }}" class="px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 text-sm text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">1</a>
          @if ($start > 2)
            <span class="px-1 text-slate-400 text-sm">&hellip;</span>
          @endif
        @endif

        @for ($p = $start; $p <= $end; $p++)
          @if ($p === $currentPage)
            <span class="px-3 py-1.5 rounded-lg bg-primary text-white text-sm font-semibold">{{ $p }}</span>
          @else
            <a href="{{ $residents->url($p) }}" class="px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 text-sm text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">{{ $p }}</a>
          @endif
        @endfor

        @if ($end < $lastPage)
          @if ($end < $lastPage - 1)
            <span class="px-1 text-slate-400 text-sm">&hellip;</span>
          @endif
          <a href="{{ $residents->url($lastPage) }}" class="px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 text-sm text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">{{ $lastPage }}</a>
        @endif

        @if ($residents->hasMorePages())
          <a href="{{ $residents->nextPageUrl() }}" class="p-2 rounded-lg border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
            <span class="material-icons text-sm">chevron_right</span>
          </a>
        @else
          <button class="p-2 rounded-lg border border-slate-200 dark:border-slate-700 text-slate-300 dark:text-slate-600 cursor-not-allowed" disabled>
            <span class="material-icons text-sm">chevron_right</span>
          </button>
        @endif
      </div>
    @endif
  </div>
</div>