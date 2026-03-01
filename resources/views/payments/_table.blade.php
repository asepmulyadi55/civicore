{{-- Payments Table --}}
<div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
  <div class="overflow-x-auto">
    <table class="w-full text-left border-collapse">
      <thead>
        <tr class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-800">
          <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Resident</th>
          <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Block</th>
          <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Month(s)</th>
          <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Amount</th>
          <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Status</th>
          <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider text-right">Actions</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
        @forelse($payments as $payment)
          @php
            $initials = collect(explode(' ', $payment->resident->fullname))->map(fn($w) => strtoupper($w[0]))->take(2)->implode('');
            $isMulti  = ($payment->month_count ?? 1) > 1;
            $allMonths = $payment->all_months ?? collect([$payment->payment_month]);
            $monthLabels = $allMonths->map(fn($m) => \Carbon\Carbon::parse($m)->format('F Y'))->implode(', ');
            // CSV of YYYY-MM for JS (without -01 day suffix)
            $monthsForJs = $allMonths->map(fn($m) => \Carbon\Carbon::parse($m)->format('Y-m'))->implode(',');
            // first month YYYY-MM for backward compat
            $firstMonth  = \Carbon\Carbon::parse($allMonths->first())->format('Y-m');
          @endphp
          <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors">
            <td class="px-6 py-4">
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center text-primary font-bold text-sm">{{ $initials }}</div>
                <div>
                  <p class="font-semibold text-sm">{{ $payment->resident->fullname }}</p>
                  <p class="text-xs text-slate-500">Unit {{ $payment->resident->unit_number }}</p>
                </div>
              </div>
            </td>
            <td class="px-6 py-4 text-sm font-medium">{{ $payment->resident->block?->name ?? '—' }}</td>
            <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-400">
              {{ $monthLabels }}
              @if($isMulti)
                <span class="ml-1 text-[10px] font-bold uppercase tracking-widest px-1.5 py-0.5 bg-amber-100 dark:bg-amber-900/30 text-amber-600 rounded">{{ $payment->month_count }} Months</span>
              @endif
            </td>
            <td class="px-6 py-4 text-sm font-bold">{{ $currency }} {{ number_format($payment->total_amount ?? $payment->amount) }}</td>
            <td class="px-6 py-4">
              @switch($payment->status)
                @case('approved')
                  <span class="px-3 py-1 bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 rounded-full text-xs font-bold uppercase">Approved</span>
                  @break
                @case('pending')
                  <span class="px-3 py-1 bg-amber-100 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 rounded-full text-xs font-bold uppercase">Pending</span>
                  @break
                @case('rejected')
                  <span class="px-3 py-1 bg-rose-100 dark:bg-rose-900/30 text-rose-600 dark:text-rose-400 rounded-full text-xs font-bold uppercase">Rejected</span>
                  @break
                @default
                  <span class="px-3 py-1 bg-slate-100 dark:bg-slate-800 text-slate-500 rounded-full text-xs font-bold uppercase">Unpaid</span>
              @endswitch
            </td>
            <td class="px-6 py-4">
              <div class="flex items-center justify-end gap-1">

                {{-- View Proof --}}
                @if($payment->proof_path)
                  <button
                    onclick="openProofModal('{{ asset('storage/' . $payment->proof_path) }}')"
                    title="View payment proof"
                    class="p-1.5 text-slate-400 hover:text-indigo-500 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 rounded-lg transition-colors">
                    <span class="material-icons text-lg">receipt_long</span>
                  </button>
                @else
                  <span class="p-1.5 text-slate-200 dark:text-slate-700 cursor-not-allowed" title="No proof uploaded">
                    <span class="material-icons text-lg">receipt_long</span>
                  </span>
                @endif

                {{-- Edit button --}}
                <button
                  onclick="openEditModal(
                    {{ $payment->id }},
                    {{ $payment->resident_id }},
                    '{{ addslashes($payment->resident->fullname) }}',
                    '{{ $payment->resident->unit_number }}',
                    '{{ $monthsForJs }}',
                    {{ $payment->amount }},
                    {{ $payment->payment_method_id ?? 'null' }},
                    '{{ $payment->status }}',
                    '{{ addslashes($payment->rejection_reason ?? '') }}',
                    '{{ addslashes($payment->notes ?? '') }}',
                    '{{ $payment->proof_path ? asset('storage/' . $payment->proof_path) : '' }}'
                  )"
                  title="Edit payment"
                  class="p-1.5 text-slate-400 hover:text-primary hover:bg-primary/5 rounded-lg transition-colors">
                  <span class="material-icons text-lg">edit</span>
                </button>

                {{-- Review / Approve / Reject --}}
                @if($canApprove)
                  @if($payment->status === 'pending')
                    <button onclick="openReviewModal(
                        {{ $payment->id }},
                        '{{ addslashes($payment->resident->fullname) }}',
                        '{{ $payment->resident->unit_number }}',
                        '{{ $currency }} {{ number_format($payment->total_amount ?? $payment->amount) }}',
                        '{{ $monthLabels }}',
                        '{{ addslashes($payment->notes ?? '') }}',
                        {{ $isMulti ? "'{$payment->batch_id}'" : 'null' }}
                      )"
                      class="text-primary hover:text-primary/80 font-bold text-xs uppercase tracking-widest px-3 py-1 border border-primary/20 rounded-lg hover:bg-primary/5 transition-all">
                      Review
                    </button>
                  @elseif($payment->status === 'approved')
                    <span class="text-xs text-slate-400">Approved {{ $payment->approved_at?->format('d M') }}</span>
                  @elseif($payment->status === 'rejected')
                    <span class="text-xs text-rose-400" title="{{ $payment->rejection_reason }}">Rejected</span>
                  @endif
                @else
                  {{-- Coordinator: no approve/reject --}}
                  @if($payment->status === 'approved')
                    <span class="text-xs text-slate-400">Approved {{ $payment->approved_at?->format('d M') }}</span>
                  @elseif($payment->status === 'rejected')
                    <span class="text-xs text-rose-400" title="{{ $payment->rejection_reason }}">Rejected</span>
                  @endif
                @endif

                {{-- Delete: admin only, not for approved --}}
                @if(auth()->user()->isAdmin())
                  @if($payment->status !== 'approved')
                    <button type="button"
                      onclick="openPaymentDeleteModal({{ $payment->id }}, '{{ addslashes($payment->resident->fullname) }}')"
                      title="Delete payment"
                      class="p-1.5 text-slate-400 hover:text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-900/20 rounded-lg transition-colors">
                      <span class="material-icons text-lg">delete_outline</span>
                    </button>
                  @else
                    <span title="Approved payments cannot be deleted"
                      class="p-1.5 text-slate-300 dark:text-slate-600 cursor-not-allowed inline-flex">
                      <span class="material-icons text-lg">lock_outline</span>
                    </span>
                  @endif
                @endif

              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="6" class="px-6 py-16 text-center">
              <span class="material-icons text-4xl text-slate-300 dark:text-slate-600 block mb-2">receipt_long</span>
              <p class="text-slate-500 font-medium">No payments found</p>
              <p class="text-slate-400 text-sm mt-1">Try adjusting your filters</p>
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  {{-- Pagination --}}
  @if($payments->hasPages())
    <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-800 flex items-center justify-between">
      <p class="text-sm text-slate-500">Showing {{ $payments->firstItem() }}–{{ $payments->lastItem() }} of {{ $payments->total() }}</p>
      {{ $payments->links() }}
    </div>
  @endif
</div>
