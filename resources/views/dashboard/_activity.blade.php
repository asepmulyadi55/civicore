{{-- Recent Activity Table (real data from DB, batch-grouped) --}}
<div
  class="xl:col-span-2 bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden flex flex-col">
  <div class="p-6 border-b border-slate-200 dark:border-slate-800 flex justify-between items-center">
    <h2 class="text-lg font-bold text-slate-900 dark:text-white">{{ __('app.recent_activity') }}</h2>
    <a href="{{ route('payments.index') }}" class="text-sm font-semibold text-primary hover:underline">{{ __('app.view_all') }}</a>
  </div>
  <div class="overflow-x-auto">
    <table class="w-full text-left">
      <thead>
        <tr class="bg-slate-50 dark:bg-slate-800/50 text-slate-500 text-xs uppercase tracking-wider font-bold">
          <th class="px-6 py-4">{{ __('app.table_resident') }}</th>
          <th class="px-6 py-4">{{ __('app.table_months') }}</th>
          <th class="px-6 py-4">{{ __('app.table_unit_block') }}</th>
          <th class="px-6 py-4">{{ __('app.table_date') }}</th>
          <th class="px-6 py-4 text-right">{{ __('app.table_status') }}</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
        @forelse($recentActivity as $activity)
          @php
            $isMulti = ($activity->month_count ?? 1) > 1;
            $allMonths = $activity->all_months ?? collect([$activity->payment_month]);
            $monthLabels = $allMonths->map(fn($m) => \Carbon\Carbon::parse($m)->format('M Y'))->implode(', ');
          @endphp
          <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/20 transition-colors">
            <td class="px-6 py-4">
              <div class="flex items-center space-x-3">
                <div class="w-8 h-8 rounded-full bg-primary/10 flex items-center justify-center flex-shrink-0">
                  <span class="material-icons text-primary text-sm">person</span>
                </div>
                <span class="text-sm font-semibold truncate max-w-[140px]">{{ $activity->resident->fullname }}</span>
              </div>
            </td>
            <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-400">
              {{ $monthLabels }}
              @if($isMulti)
                <span class="ml-1 text-[10px] font-bold uppercase tracking-widest px-1.5 py-0.5 bg-amber-100 dark:bg-amber-900/30 text-amber-600 rounded">{{ $activity->month_count }} {{ __('app.months_count') }}</span>
              @endif
            </td>
            <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-400 min-w-[140px] whitespace-nowrap">
              {{ $activity->resident->block?->name ?? '—' }} · {{ $activity->resident->unit_number }}
            </td>
            <td class="px-6 py-4 text-sm text-slate-500 min-w-[140px] whitespace-nowrap">
              {{ $activity->updated_at->format('M d, g:i A') }}
            </td>
            <td class="px-6 py-4 text-right">
              <span class="px-2.5 py-1 rounded-full text-xs font-bold {{ $activity->status->badgeClass() }}">
                {{ $activity->status->label() }}
              </span>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="5" class="px-6 py-12 text-center text-slate-400 text-sm">
              <span class="material-icons text-3xl block mb-2 text-slate-300 dark:text-slate-600">receipt_long</span>
              {{ __('app.no_recent_activity') }}
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>