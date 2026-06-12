{{-- Payments Table --}}
<form id="bulk-delete-payments-form" action="{{ route('payments.bulk-destroy') }}" method="POST">
  @csrf
  @method('DELETE')

  {{-- Bulk Action Bar --}}
  <div id="bulk-action-bar-payments" class="hidden mb-4 p-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl flex items-center justify-between shadow-sm transition-all">
    <div class="flex items-center gap-3">
      <span class="text-sm font-semibold text-slate-700 dark:text-slate-300 ml-2">
        <span id="selected-count-payments">0</span> {{ __('app.select_all') ?? 'selected' }}
      </span>
    </div>
    <button type="button" onclick="confirmBulkDelete(event, 'bulk-delete-payments-form', 'Are you sure you want to delete the selected payments?')" class="flex items-center gap-1.5 px-3 py-1.5 bg-rose-500 hover:bg-rose-600 text-white text-xs font-bold rounded-lg transition-colors">
      <span class="material-icons text-sm">delete</span> Delete Selected
    </button>
  </div>

<div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
  <div class="overflow-x-auto">
    <table class="w-full text-left border-collapse">
      <thead>
        <tr class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-800">
          @if(auth()->user()->isAdmin())
          <th class="w-12 px-6 py-4 text-center">
            <input type="checkbox" id="select-all-payments" class="w-4 h-4 rounded border-slate-300 text-primary focus:ring-primary/30 bg-white dark:bg-slate-800 cursor-pointer" onchange="toggleAllPayments(this)">
          </th>
          @endif
          <x-ui.sort-th column="resident" :label="__('app.table_resident')" />
          <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">{{ __('app.table_block') }}</th>
          <x-ui.sort-th column="month" :label="__('app.table_months')" />
          <x-ui.sort-th column="amount" :label="__('app.table_amount')" />
          <x-ui.sort-th column="status" :label="__('app.table_status')" />
          <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">{{ __('app.table_recorded') }}</th>
          <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider text-right">{{ __('app.table_actions') }}</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
        @forelse($payments as $payment)
          @php
            $initials = collect(preg_split('/\s+/', trim($payment->householder->fullname ?? '')))->filter()->map(fn($w) => strtoupper($w[0]))->take(2)->implode('') ?: '?';
            $isMulti  = ($payment->month_count ?? 1) > 1;
            $allMonths = $payment->all_months ?? collect([$payment->payment_month]);
            $monthLabels = $allMonths->map(fn($m) => \Carbon\Carbon::parse($m)->format('F Y'))->implode(', ');
            // CSV of YYYY-MM for JS (without -01 day suffix)
            $monthsForJs = $allMonths->map(fn($m) => \Carbon\Carbon::parse($m)->format('Y-m'))->implode(',');
            // first month YYYY-MM for backward compat
            $firstMonth  = \Carbon\Carbon::parse($allMonths->first())->format('Y-m');
          @endphp
          <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors">
            @if(auth()->user()->isAdmin())
            <td class="w-12 px-6 py-4 text-center">
              <input type="checkbox" name="ids[]" value="{{ $payment->id }}" class="payment-checkbox w-4 h-4 rounded border-slate-300 text-primary focus:ring-primary/30 bg-white dark:bg-slate-800 cursor-pointer" onchange="updateBulkActionBarPayments()">
            </td>
            @endif
            <td class="px-6 py-4">
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center text-primary font-bold text-sm">{{ $initials }}</div>
                <div>
                  <p class="font-semibold text-sm">{{ $payment->householder->fullname }}</p>
                  <p class="text-xs text-slate-500">Unit {{ $payment->householder->unit_number }}</p>
                </div>
              </div>
            </td>
            <td class="px-6 py-4 text-sm font-medium">{{ $payment->householder->block?->name ?? '—' }}</td>
            <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-400">
              {{ $monthLabels }}
              @if($isMulti)
                <span class="ml-1 text-[10px] font-bold uppercase tracking-widest px-1.5 py-0.5 bg-amber-100 dark:bg-amber-900/30 text-amber-600 rounded">{{ $payment->month_count }} {{ __('app.months_count') }}</span>
              @endif
            </td>
            <td class="px-6 py-4 text-sm font-bold">{{ $currency }} {{ number_format($payment->total_amount ?? $payment->amount) }}</td>
            <td class="px-6 py-4">
              @php $statusValue = $payment->status instanceof \App\Enums\PaymentStatus ? $payment->status->value : $payment->status; @endphp
              @switch($statusValue)
                @case('approved')
                  <span class="px-3 py-1 bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 rounded-full text-xs font-bold uppercase">{{ __('app.status_approved') }}</span>
                  @break
                @case('pending')
                  <span class="px-3 py-1 bg-amber-100 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 rounded-full text-xs font-bold uppercase">{{ __('app.status_pending') }}</span>
                  @break
                @case('rejected')
                  <span class="px-3 py-1 bg-rose-100 dark:bg-rose-900/30 text-rose-600 dark:text-rose-400 rounded-full text-xs font-bold uppercase">{{ __('app.status_rejected') }}</span>
                  @break
                @default
                  <span class="px-3 py-1 bg-slate-100 dark:bg-slate-800 text-slate-500 rounded-full text-xs font-bold uppercase">{{ __('app.status_unpaid') }}</span>
              @endswitch
            </td>
            {{-- Recorded date --}}
            <td class="px-6 py-4 text-sm text-slate-500 dark:text-slate-400 whitespace-nowrap">
              <div class="flex flex-col">
                <span class="font-medium text-slate-700 dark:text-slate-300">{{ $payment->created_at->format('d M Y') }}</span>
                <span class="text-xs text-slate-400">{{ $payment->created_at->format('H:i') }}</span>
              </div>
            </td>
            <td class="px-6 py-4">
              <div class="flex items-center justify-end gap-1">

                {{-- View Proof --}}
                @if($payment->proof_path)
                  <button
                    onclick="openProofModal('{{ route('private.file', ['path' => $payment->proof_path]) }}')"
                    title="View payment proof"
                    class="p-1.5 text-slate-400 hover:text-indigo-500 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 rounded-lg transition-colors">
                    <span class="material-icons text-lg">receipt_long</span>
                  </button>
                @else
                  <span class="p-1.5 text-slate-200 dark:text-slate-700 cursor-not-allowed" title="No proof uploaded">
                    <span class="material-icons text-lg">receipt_long</span>
                  </span>
                @endif

                {{-- Edit button: hidden for block coordinators on approved payments --}}
                @if($canEditApproved || $statusValue !== 'approved')
                  <button
                    onclick="openEditModal(
                      '{{ $payment->id }}',
                      '{{ $payment->householder_id }}',
                      '{{ addslashes($payment->householder->fullname) }}',
                      '{{ $payment->householder->unit_number }}',
                      '{{ $monthsForJs }}',
                      {{ $payment->amount }},
                      {{ $payment->payment_method_id ? "'{$payment->payment_method_id}'" : 'null' }},
                      '{{ $payment->status instanceof \App\Enums\PaymentStatus ? $payment->status->value : $payment->status }}',
                      '{{ addslashes($payment->rejection_reason ?? '') }}',
                      '{{ addslashes($payment->notes ?? '') }}',
                      '{{ $payment->proof_path ? route('private.file', ['path' => $payment->proof_path]) : '' }}'
                    )"
                    title="Edit payment"
                    class="p-1.5 text-slate-400 hover:text-primary hover:bg-primary/5 rounded-lg transition-colors">
                    <span class="material-icons text-lg">edit</span>
                  </button>
                @else
                  <span class="p-1.5 text-slate-200 dark:text-slate-700 cursor-not-allowed inline-flex" title="Approved payments can only be edited by Admin or Treasurer">
                    <span class="material-icons text-lg">lock_outline</span>
                  </span>
                @endif

                {{-- Review / Approve / Reject --}}
                @if($canApprove)
                  @if($statusValue === 'pending')
                    <button onclick="openReviewModal(
                        '{{ $payment->id }}',
                        '{{ addslashes($payment->householder->fullname) }}',
                        '{{ $payment->householder->unit_number }}',
                        '{{ $currency }} {{ number_format($payment->total_amount ?? $payment->amount) }}',
                        '{{ $monthLabels }}',
                        '{{ addslashes($payment->notes ?? '') }}',
                        {{ $isMulti ? "'{$payment->batch_id}'" : 'null' }}
                      )"
                      class="text-amber-600 dark:text-amber-400 border border-amber-500/40 dark:border-amber-500/30 bg-amber-50/60 dark:bg-amber-500/10 hover:bg-amber-500 hover:border-amber-500 hover:text-white dark:hover:bg-amber-500 dark:hover:border-amber-500 dark:hover:text-white font-semibold text-xs px-3 py-1.5 rounded-lg transition-all">
                      {{ __('app.review_payment') }}
                    </button>
                  @elseif($statusValue === 'approved')
                    <span class="text-xs text-slate-400">{{ __('app.status_approved') }} {{ $payment->approved_at?->format('d M') }}</span>
                  @elseif($statusValue === 'rejected')
                    <span class="text-xs text-rose-400" title="{{ $payment->rejection_reason }}">{{ __('app.status_rejected') }}</span>
                  @endif
                @else
                  {{-- Coordinator: no approve/reject --}}
                  @if($statusValue === 'approved')
                    <span class="text-xs text-slate-400">{{ __('app.status_approved') }} {{ $payment->approved_at?->format('d M') }}</span>
                  @elseif($statusValue === 'rejected')
                    <span class="text-xs text-rose-400" title="{{ $payment->rejection_reason }}">{{ __('app.status_rejected') }}</span>
                  @endif
                @endif

                {{-- Delete: admin only, not for approved --}}
                @if(auth()->user()->isAdmin())
                  @if($statusValue !== 'approved')
                    <button type="button"
                      onclick="openPaymentDeleteModal('{{ $payment->id }}', '{{ addslashes($payment->householder->fullname) }}')"
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
              <p class="text-slate-500 font-medium">{{ __('app.no_payments_found') }}</p>
              <p class="text-slate-400 text-sm mt-1">{{ __('app.try_adjusting_filters') }}</p>
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  {{-- Pagination --}}
  @if($payments->hasPages())
    <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-800 flex flex-col sm:flex-row items-center sm:justify-between gap-3">
      <p class="text-sm text-slate-500 text-center sm:text-left">{{ __('app.showing') }} {{ $payments->firstItem() }}–{{ $payments->lastItem() }} {{ __('app.of') }} {{ $payments->total() }}</p>
      <div class="flex items-center gap-1">
        {{-- Previous --}}
        @if ($payments->onFirstPage())
          <button class="p-2 rounded-lg border border-slate-200 dark:border-slate-700 text-slate-300 dark:text-slate-600 cursor-not-allowed" disabled>
            <span class="material-icons text-sm">chevron_left</span>
          </button>
        @else
          <a href="{{ $payments->previousPageUrl() }}" class="p-2 rounded-lg border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
            <span class="material-icons text-sm">chevron_left</span>
          </a>
        @endif

        {{-- Page numbers (show at most 5 around current page) --}}
        @php
          $lastPage    = $payments->lastPage();
          $currentPage = $payments->currentPage();
          $window      = 2; // pages either side of current
          $start       = max(1, $currentPage - $window);
          $end         = min($lastPage, $currentPage + $window);
          // Always show first and last page with ellipsis if needed
        @endphp

        @if ($start > 1)
          <a href="{{ $payments->url(1) }}" class="px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 text-sm text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">1</a>
          @if ($start > 2)
            <span class="px-1 text-slate-400 text-sm">…</span>
          @endif
        @endif

        @for ($p = $start; $p <= $end; $p++)
          @if ($p === $currentPage)
            <span class="px-3 py-1.5 rounded-lg bg-primary text-white text-sm font-semibold">{{ $p }}</span>
          @else
            <a href="{{ $payments->url($p) }}" class="px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 text-sm text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">{{ $p }}</a>
          @endif
        @endfor

        @if ($end < $lastPage)
          @if ($end < $lastPage - 1)
            <span class="px-1 text-slate-400 text-sm">…</span>
          @endif
          <a href="{{ $payments->url($lastPage) }}" class="px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 text-sm text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">{{ $lastPage }}</a>
        @endif

        {{-- Next --}}
        @if ($payments->hasMorePages())
          <a href="{{ $payments->nextPageUrl() }}" class="p-2 rounded-lg border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
            <span class="material-icons text-sm">chevron_right</span>
          </a>
        @else
          <button class="p-2 rounded-lg border border-slate-200 dark:border-slate-700 text-slate-300 dark:text-slate-600 cursor-not-allowed" disabled>
            <span class="material-icons text-sm">chevron_right</span>
          </button>
        @endif
      </div>
    </div>
  @endif
</div>
</form>

<script>
  function toggleAllPayments(source) {
    const checkboxes = document.querySelectorAll('.payment-checkbox');
    checkboxes.forEach(cb => { cb.checked = source.checked; });
    updateBulkActionBarPayments();
  }

  function updateBulkActionBarPayments() {
    const checkboxes = document.querySelectorAll('.payment-checkbox');
    const checkedCount = Array.from(checkboxes).filter(cb => cb.checked).length;
    const actionBar = document.getElementById('bulk-action-bar-payments');
    const selectAll = document.getElementById('select-all-payments');
    const countLabel = document.getElementById('selected-count-payments');

    if (checkedCount > 0) {
      actionBar.classList.remove('hidden');
      actionBar.classList.add('flex');
    } else {
      actionBar.classList.add('hidden');
      actionBar.classList.remove('flex');
    }

    if (countLabel) countLabel.textContent = checkedCount;
    if (selectAll) selectAll.checked = (checkedCount === checkboxes.length && checkboxes.length > 0);
  }
</script>

