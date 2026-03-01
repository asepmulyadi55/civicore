{{-- Payments Table --}}
@php
  // Pre-count pending records per batch_id so we can decide when to show batch buttons
  $batchPendingCounts = $payments->getCollection()
    ->filter(fn($p) => $p->batch_id && $p->status === 'pending')
    ->groupBy('batch_id')
    ->map->count();

  // Track which batch_ids we've already seen to detect first vs last row in a batch
  $seenBatches = [];
@endphp
<div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
  <div class="overflow-x-auto">
    <table class="w-full text-left border-collapse">
      <thead>
        <tr class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-800">
          <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Resident</th>
          <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Block</th>
          <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Month</th>
          <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Amount</th>
          <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Status</th>
          <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider text-right">Actions</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
        @forelse($payments as $payment)
          @php
            $initials = collect(explode(' ', $payment->resident->fullname))->map(fn($w) => strtoupper($w[0]))->take(2)->implode('');

            // Determine if this row is part of a multi-pending batch
            $bid = $payment->batch_id;
            $batchPendingCount = $bid ? ($batchPendingCounts[$bid] ?? 0) : 0;
            $isMultiBatch = $batchPendingCount >= 2;

            // Is this the last pending row in the batch? (for showing the Approve All button)
            if ($bid && $isMultiBatch && $payment->status === 'pending') {
              $seenBatches[$bid] = ($seenBatches[$bid] ?? 0) + 1;
              $isBatchLastRow = $seenBatches[$bid] === $batchPendingCount;
              $isBatchFirstRow = $seenBatches[$bid] === 1;
            } else {
              $isBatchLastRow = false;
              $isBatchFirstRow = false;
            }
          @endphp
          <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors {{ $isMultiBatch && $payment->status === 'pending' ? 'bg-amber-50/40 dark:bg-amber-900/5' : '' }}">
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
              {{ \Carbon\Carbon::parse($payment->payment_month)->format('F Y') }}
              @if($isMultiBatch && $payment->status === 'pending' && $isBatchFirstRow)
                <span class="ml-1 text-[10px] font-bold uppercase tracking-widest px-1.5 py-0.5 bg-amber-100 dark:bg-amber-900/30 text-amber-600 rounded">Batch</span>
              @endif
            </td>
            <td class="px-6 py-4 text-sm font-bold">{{ $currency }} {{ number_format($payment->amount) }}</td>
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
                    '{{ addslashes($payment->resident->fullname) }}',
                    '{{ $payment->resident->unit_number }}',
                    '{{ $payment->payment_month->format('Y-m') }}',
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

                {{-- Review / Batch buttons --}}
                @if($canApprove)
                  @if($isMultiBatch && $payment->status === 'pending' && $isBatchLastRow)
                    {{-- Approve All batch --}}
                    <form method="POST" action="{{ route('payments.batch.approve', $bid) }}" class="inline">
                      @csrf
                      <button type="submit"
                        class="text-emerald-600 hover:text-emerald-700 font-bold text-xs uppercase tracking-widest px-3 py-1 border border-emerald-300 dark:border-emerald-700/50 rounded-lg hover:bg-emerald-50 dark:hover:bg-emerald-900/20 transition-all whitespace-nowrap">
                        ✓ All ({{ $batchPendingCount }})
                      </button>
                    </form>
                    {{-- Reject All batch --}}
                    <button
                      onclick="openBatchRejectModal('{{ $bid }}', {{ $batchPendingCount }})"
                      class="text-rose-500 hover:text-rose-600 font-bold text-xs uppercase tracking-widest px-2 py-1 border border-rose-200 dark:border-rose-700/50 rounded-lg hover:bg-rose-50 dark:hover:bg-rose-900/20 transition-all">
                      ✕
                    </button>
                  @elseif($isMultiBatch && $payment->status === 'pending')
                    {{-- Non-last rows in the batch: suppress the individual Review button --}}
                  @elseif($payment->status === 'pending')
                    <button onclick="openReviewModal({{ $payment->id }}, '{{ addslashes($payment->resident->fullname) }}', '{{ $payment->resident->unit_number }}', '{{ $currency }} {{ number_format($payment->amount) }}', '{{ \Carbon\Carbon::parse($payment->payment_month)->format('F Y') }}')"
                      class="text-primary hover:text-primary/80 font-bold text-xs uppercase tracking-widest px-3 py-1 border border-primary/20 rounded-lg hover:bg-primary/5 transition-all">
                      Review
                    </button>
                  @endif
                @else
                  {{-- Coordinator: no approve/reject actions --}}
                  @if($payment->status === 'approved')
                    <span class="text-xs text-slate-400">Approved {{ $payment->approved_at?->format('d M') }}</span>
                  @elseif($payment->status === 'rejected')
                    <span class="text-xs text-rose-400" title="{{ $payment->rejection_reason }}">Rejected</span>
                  @endif
                @endif

                {{-- Approved/Rejected labels for non-pending rows (only shown when canApprove for pending above) --}}
                @if($canApprove)
                  @if(!$isMultiBatch || $payment->status !== 'pending')
                    @if($payment->status === 'approved')
                      <span class="text-xs text-slate-400">Approved {{ $payment->approved_at?->format('d M') }}</span>
                    @elseif($payment->status === 'rejected')
                      <span class="text-xs text-rose-400" title="{{ $payment->rejection_reason }}">Rejected</span>
                    @endif
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

{{-- Batch Reject Modal --}}
<div id="batch-reject-modal"
  class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm hidden items-center justify-center p-4">
  <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-2xl w-full max-w-md border border-slate-200 dark:border-slate-800 overflow-hidden">
    <div class="px-6 py-5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
      <h3 class="text-lg font-bold text-slate-900 dark:text-white flex items-center gap-2">
        <span class="material-icons text-rose-500">block</span>
        Reject Batch
      </h3>
      <button onclick="closeBatchRejectModal()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
        <span class="material-icons">close</span>
      </button>
    </div>
    <form id="batch-reject-form" method="POST" action="">
      @csrf
      <div class="p-6 space-y-4">
        <p id="batch-reject-info" class="text-sm text-slate-600 dark:text-slate-400"></p>
        <div>
          <label class="text-xs font-bold text-slate-500 uppercase block mb-1.5">Rejection Reason <span class="text-red-500">*</span></label>
          <textarea name="rejection_reason" rows="3" placeholder="Please explain why payment is rejected (min. 10 characters)..."
            class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none dark:text-white resize-none"></textarea>
          <p id="batch-reject-error" class="hidden text-xs text-red-500 mt-1 flex items-center gap-1">
            <span class="material-icons text-xs">error_outline</span> Please provide at least 10 characters.
          </p>
        </div>
      </div>
      <div class="px-6 py-4 bg-slate-50 dark:bg-slate-800/50 border-t border-slate-100 dark:border-slate-800 flex justify-end gap-3">
        <button type="button" onclick="closeBatchRejectModal()"
          class="px-5 py-2.5 rounded-lg text-sm font-semibold text-slate-600 hover:bg-slate-200 dark:text-slate-300 dark:hover:bg-slate-700 transition-colors">
          Cancel
        </button>
        <button type="submit"
          class="px-5 py-2.5 rounded-lg text-sm font-semibold bg-rose-500 hover:bg-rose-600 text-white shadow-lg shadow-rose-500/20 transition-all">
          Reject All
        </button>
      </div>
    </form>
  </div>
</div>

<script>
  function openBatchRejectModal(batchId, count) {
    document.getElementById('batch-reject-form').action = `/payments/batch/${batchId}/reject`;
    document.getElementById('batch-reject-info').textContent =
      `This will reject all ${count} pending payment(s) in this batch with the same reason.`;
    const el = document.getElementById('batch-reject-modal');
    el.classList.remove('hidden'); el.classList.add('flex');
    document.body.classList.add('overflow-hidden');
  }
  function closeBatchRejectModal() {
    const el = document.getElementById('batch-reject-modal');
    el.classList.add('hidden'); el.classList.remove('flex');
    document.body.classList.remove('overflow-hidden');
  }
  document.getElementById('batch-reject-form')?.addEventListener('submit', function (e) {
    const reason = this.querySelector('textarea[name="rejection_reason"]').value.trim();
    const errEl = document.getElementById('batch-reject-error');
    if (reason.length < 10) {
      errEl.classList.remove('hidden'); errEl.classList.add('flex');
      e.preventDefault();
    } else {
      errEl.classList.add('hidden'); errEl.classList.remove('flex');
    }
  });
  document.getElementById('batch-reject-modal')?.addEventListener('click', function (e) {
    if (e.target === this) closeBatchRejectModal();
  });
</script>

