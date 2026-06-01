{{-- finance/_reports.blade.php --}}
@php
  $fmt = fn($n) => number_format((float)$n, 0, ',', '.');
  $currentFilterYear = request('rpt_year');
@endphp

{{-- Year filter --}}
<form method="GET" action="{{ route('finance.index') }}" class="flex gap-3 items-end">
  <input type="hidden" name="tab" value="reports">
  <div class="flex flex-col gap-1">
    <label class="text-xs font-medium text-slate-500 dark:text-slate-400">{{ __('app.fin_filter_year') }}</label>
    <div class="relative overflow-hidden">
      <select name="rpt_year"
        class="appearance-none text-sm rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-700 dark:text-slate-300 pl-3 pr-8 py-2 focus:outline-none focus:ring-2 focus:ring-primary/30 w-full">
        <option value="">{{ __('app.fin_all_years') }}</option>
        @foreach(range(now()->year, 2020) as $y)
          <option value="{{ $y }}" {{ $currentFilterYear == $y ? 'selected' : '' }}>{{ $y }}</option>
        @endforeach
      </select>
      <span class="pointer-events-none absolute right-2 top-1/2 -translate-y-1/2 material-icons text-slate-400 text-[15px] bg-white dark:bg-slate-700 rounded">expand_more</span>
    </div>
  </div>
  <button type="submit"
    class="px-4 py-2 text-sm font-medium bg-primary text-white rounded-lg hover:opacity-90 transition-opacity">
    {{ __('app.btn_search') }}
  </button>
  @if($currentFilterYear)
    <a href="{{ route('finance.index', ['tab' => 'reports']) }}"
       class="px-4 py-2 text-sm font-medium text-slate-500 dark:text-slate-400 bg-slate-100 dark:bg-slate-700 rounded-lg hover:opacity-80">
      {{ __('app.btn_clear') }}
    </a>
  @endif
</form>

{{-- Reports table --}}
<div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
  @if($reports->isEmpty())
    <div class="text-center py-16 text-slate-400 dark:text-slate-500">
      <span class="material-icons text-4xl block mb-3">summarize</span>
      <p class="text-sm">{{ __('app.fin_no_reports') }}</p>
      @if($canManage)
        <button type="button" onclick="openGenerateReportModal()"
          class="mt-4 inline-flex items-center gap-2 text-sm text-primary dark:text-emerald-400 hover:underline">
          <span class="material-icons text-[16px]">add_chart</span>
          {{ __('app.fin_generate_report') }}
        </button>
      @endif
    </div>
  @else
    <div class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead class="bg-slate-50 dark:bg-slate-700/50">
          <tr class="text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">
            <th class="px-4 py-3">{{ __('app.fin_period') }}</th>
            <th class="px-4 py-3 text-right">{{ __('app.fin_opening_balance') }}</th>
            <th class="px-4 py-3 text-right">{{ __('app.fin_total_income') }}</th>
            <th class="px-4 py-3 text-right">{{ __('app.fin_total_expense') }}</th>
            <th class="px-4 py-3 text-right">{{ __('app.fin_closing_balance') }}</th>
            <th class="px-4 py-3">{{ __('app.status') }}</th>
            <th class="px-4 py-3 text-right">{{ __('app.table_actions') }}</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
          @foreach($reports as $report)
            @php $badge = $report->statusBadge(); @endphp
            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors" id="report-row-{{ $report->id }}">
              <td class="px-4 py-3">
                <p class="font-semibold text-slate-700 dark:text-slate-300">{{ $report->periodLabel() }}</p>
                @if($report->submitted_at)
                  <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">
                    {{ __('app.fin_submitted_by') }}: {{ $report->submittedBy?->name ?? '—' }}
                  </p>
                @endif
                @if($report->approved_at)
                  <p class="text-xs text-slate-400 dark:text-slate-500">
                    {{ __('app.fin_approved_by') }}: {{ $report->approvedBy?->name ?? '—' }}
                  </p>
                @endif
                @if($report->status === 'rejected' && $report->rejection_notes)
                  <div class="mt-1.5 flex items-start gap-1.5 p-2 bg-rose-50 dark:bg-rose-900/20 rounded-lg border border-rose-200 dark:border-rose-800">
                    <span class="material-icons text-rose-500 text-[14px] mt-0.5 flex-shrink-0">info</span>
                    <p class="text-xs text-rose-700 dark:text-rose-400">{{ $report->rejection_notes }}</p>
                  </div>
                @endif
              </td>
              <td class="px-4 py-3 text-right text-slate-600 dark:text-slate-400 whitespace-nowrap">
                {{ $currency }} {{ $fmt($report->opening_balance) }}
              </td>
              <td class="px-4 py-3 text-right font-medium text-emerald-600 dark:text-emerald-400 whitespace-nowrap">
                {{ $currency }} {{ $fmt($report->total_income) }}
              </td>
              <td class="px-4 py-3 text-right font-medium text-rose-600 dark:text-rose-400 whitespace-nowrap">
                {{ $currency }} {{ $fmt($report->total_expense) }}
              </td>
              <td class="px-4 py-3 text-right font-bold
                {{ $report->closing_balance >= 0 ? 'text-slate-800 dark:text-slate-100' : 'text-rose-600 dark:text-rose-400' }}
                whitespace-nowrap">
                {{ $currency }} {{ $fmt($report->closing_balance) }}
              </td>
              <td class="px-4 py-3">
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $badge['class'] }}">
                  {{ $badge['label'] }}
                </span>
              </td>
              <td class="px-4 py-3 text-right">
                <div class="flex items-center justify-end gap-1 flex-wrap">

                  {{-- Refresh (recalculate) — draft / revised / rejected only --}}
                  @if($canManage && in_array($report->status, ['draft', 'revised', 'rejected']))
                    <form method="POST" action="{{ route('finance.reports.generate') }}" class="inline">
                      @csrf
                      <input type="hidden" name="month" value="{{ $report->month }}">
                      <input type="hidden" name="year"  value="{{ $report->year }}">
                      <button type="submit"
                        class="p-1.5 rounded-lg text-slate-400 hover:text-sky-600 hover:bg-sky-50 dark:hover:bg-sky-900/20 transition-colors"
                        title="{{ __('app.fin_generate_report') }}">
                        <span class="material-icons text-[18px]">refresh</span>
                      </button>
                    </form>
                  @endif

                  {{-- Opening balance edit --}}
                  @if($canManage && !$report->isLocked())
                    <button type="button"
                      onclick="openOpeningBalanceModal({{ json_encode(['id' => $report->id, 'period' => $report->periodLabel(), 'opening_balance' => $report->opening_balance]) }})"
                      class="p-1.5 rounded-lg text-slate-400 hover:text-amber-600 hover:bg-amber-50 dark:hover:bg-amber-900/20 transition-colors"
                      title="{{ __('app.fin_opening_balance') }}">
                      <span class="material-icons text-[18px]">edit_note</span>
                    </button>
                  @endif

                  {{-- Submit --}}
                  @if($canManage && in_array($report->status, ['draft', 'revised', 'rejected']))
                    <form method="POST" action="{{ route('finance.reports.submit', $report) }}" class="inline">
                      @csrf @method('PATCH')
                      <button type="submit"
                        class="p-1.5 rounded-lg text-slate-400 hover:text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-900/20 transition-colors"
                        title="{{ __('app.fin_submit_report') }}">
                        <span class="material-icons text-[18px]">send</span>
                      </button>
                    </form>
                  @endif

                  {{-- Approve --}}
                  @if($canApprove && $report->status === 'submitted')
                    <form method="POST" action="{{ route('finance.reports.approve', $report) }}" class="inline">
                      @csrf @method('PATCH')
                      <button type="submit"
                        class="p-1.5 rounded-lg text-slate-400 hover:text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-900/20 transition-colors"
                        title="{{ __('app.fin_approve_report') }}">
                        <span class="material-icons text-[18px]">check_circle</span>
                      </button>
                    </form>
                  @endif

                  {{-- Reject --}}
                  @if($canApprove && $report->status === 'submitted')
                    <button type="button"
                      onclick="openRejectReportModal('{{ $report->id }}', {{ json_encode($report->periodLabel()) }})"
                      class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-900/20 transition-colors"
                      title="{{ __('app.fin_reject_report') }}">
                      <span class="material-icons text-[18px]">cancel</span>
                    </button>
                  @endif

                  {{-- Revise --}}
                  @if($canApprove && $report->status === 'approved')
                    <button type="button"
                      onclick="openReviseReportModal('{{ $report->id }}', {{ json_encode($report->periodLabel()) }})"
                      class="p-1.5 rounded-lg text-slate-400 hover:text-amber-600 hover:bg-amber-50 dark:hover:bg-amber-900/20 transition-colors"
                      title="{{ __('app.fin_revise_report') }}">
                      <span class="material-icons text-[18px]">undo</span>
                    </button>
                  @endif

                  {{-- Export --}}
                  @if(in_array($report->status, ['approved', 'submitted']))
                    <a href="{{ route('finance.reports.export', $report) }}"
                      class="p-1.5 rounded-lg text-slate-400 hover:text-violet-600 hover:bg-violet-50 dark:hover:bg-violet-900/20 transition-colors"
                      title="{{ __('app.fin_export_report') }}">
                      <span class="material-icons text-[18px]">download</span>
                    </a>
                  @endif

                  {{-- Delete (draft / revised / rejected only) --}}
                  @if($canManage && in_array($report->status, ['draft', 'revised', 'rejected']))
                    <button type="button"
                      onclick="openDeleteReportModal('{{ $report->id }}', {{ json_encode($report->periodLabel()) }})"
                      class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-900/20 transition-colors"
                      title="Delete report">
                      <span class="material-icons text-[18px]">delete</span>
                    </button>
                  @endif

                </div>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>

    @if($reports->hasPages())
      <div class="px-4 py-3 border-t border-slate-100 dark:border-slate-700">
        {{ $reports->links() }}
      </div>
    @endif
  @endif
</div>
