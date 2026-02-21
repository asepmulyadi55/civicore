{{-- Payment Management Page (Admin) --}}
<x-layouts.app title="Payments"
  class="font-display bg-background-light dark:bg-background-dark text-slate-800 dark:text-slate-200 antialiased min-h-screen">

  <x-nav.sidebar active="payments" />

  <main class="lg:ml-64 flex flex-col h-screen overflow-hidden">

    {{-- Top bar --}}
    <header class="h-16 border-b border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 flex items-center justify-between px-8 shrink-0">
      <div>
        <h1 class="text-xl font-bold">Payment Management</h1>
        <nav class="flex text-xs text-slate-500 gap-2">
          <span>Admin</span><span>/</span>
          <span class="text-primary font-medium">Payments</span>
        </nav>
      </div>
    </header>

    <div class="flex-1 overflow-y-auto p-8 space-y-6">

      {{-- Flash messages --}}
      @if(session('success'))
        <div class="p-4 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 rounded-xl flex items-center gap-3">
          <span class="material-icons text-emerald-500">check_circle</span>
          <p class="text-sm text-emerald-700 dark:text-emerald-400">{{ session('success') }}</p>
        </div>
      @endif
      @if(session('error'))
        <div class="p-4 bg-rose-50 dark:bg-rose-900/20 border border-rose-200 rounded-xl flex items-center gap-3">
          <span class="material-icons text-rose-500">error</span>
          <p class="text-sm text-rose-700 dark:text-rose-400">{{ session('error') }}</p>
        </div>
      @endif

      {{-- Summary Stats --}}
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white dark:bg-slate-900 p-6 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm">
          <div class="flex items-center justify-between">
            <span class="text-slate-500 font-medium">Total Pending</span>
            <div class="p-2 bg-amber-100 text-amber-600 rounded-lg">
              <span class="material-icons">pending_actions</span>
            </div>
          </div>
          <div class="mt-4">
            <h3 class="text-2xl font-bold">{{ $currency }} {{ number_format($pendingTotal) }}</h3>
            <p class="text-xs text-slate-400 mt-1">{{ $pendingCount }} payments awaiting review</p>
          </div>
        </div>
        <div class="bg-white dark:bg-slate-900 p-6 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm">
          <div class="flex items-center justify-between">
            <span class="text-slate-500 font-medium">Collected (This Month)</span>
            <div class="p-2 bg-emerald-100 text-emerald-600 rounded-lg">
              <span class="material-icons">payments</span>
            </div>
          </div>
          <div class="mt-4">
            <h3 class="text-2xl font-bold">{{ $currency }} {{ number_format($collectedMonth) }}</h3>
            <p class="text-xs text-emerald-500 mt-1">{{ now()->format('F Y') }}</p>
          </div>
        </div>
        <div class="bg-white dark:bg-slate-900 p-6 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm">
          <div class="flex items-center justify-between">
            <span class="text-slate-500 font-medium">Unpaid This Month</span>
            <div class="p-2 bg-rose-100 text-rose-600 rounded-lg">
              <span class="material-icons">warning</span>
            </div>
          </div>
          <div class="mt-4">
            <h3 class="text-2xl font-bold">{{ $unpaidCount }}</h3>
            <p class="text-xs text-slate-400 mt-1">Residents without approved payment</p>
          </div>
        </div>
      </div>

      {{-- Filters --}}
      <form method="GET" action="{{ route('payments.index') }}"
        class="bg-white dark:bg-slate-900 p-4 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm flex flex-col md:flex-row gap-4 items-center">
        <div class="relative flex-1 w-full">
          <span class="material-icons absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">search</span>
          <input name="search" value="{{ request('search') }}"
            class="w-full pl-10 pr-4 py-2 bg-slate-50 dark:bg-slate-800 border-none rounded-lg focus:ring-2 focus:ring-primary/50 text-sm"
            placeholder="Search resident name or unit..." />
        </div>
        <div class="flex gap-3 w-full md:w-auto flex-wrap">
          <select name="block_id" onchange="this.form.submit()"
            class="bg-slate-50 dark:bg-slate-800 border-none rounded-lg text-sm py-2 px-4 focus:ring-2 focus:ring-primary/50">
            <option value="">All Blocks</option>
            @foreach($blocks as $block)
              <option value="{{ $block->id }}" {{ request('block_id') == $block->id ? 'selected' : '' }}>{{ $block->name }}</option>
            @endforeach
          </select>
          <select name="status" onchange="this.form.submit()"
            class="bg-slate-50 dark:bg-slate-800 border-none rounded-lg text-sm py-2 px-4 focus:ring-2 focus:ring-primary/50">
            <option value="">All Status</option>
            <option value="pending"  {{ request('status') === 'pending'  ? 'selected' : '' }}>Pending</option>
            <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
            <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
          </select>
          <input type="month" name="month" value="{{ request('month') }}" onchange="this.form.submit()"
            class="bg-slate-50 dark:bg-slate-800 border-none rounded-lg text-sm py-2 px-4 focus:ring-2 focus:ring-primary/50" />
          @if(request()->hasAny(['search','block_id','status','month']))
            <a href="{{ route('payments.index') }}"
              class="flex items-center gap-1 text-sm text-slate-500 hover:text-slate-700 px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-lg transition-colors">
              <span class="material-icons text-base">close</span> Clear
            </a>
          @endif
        </div>
        <button type="submit" class="hidden">Search</button>
      </form>

      {{-- Payments Table --}}
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
                    {{ \Carbon\Carbon::parse($payment->payment_month)->format('F Y') }}
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
                    @endswitch
                  </td>
                  <td class="px-6 py-4 text-right">
                    @if($payment->status === 'pending')
                      <button onclick="openReviewModal({{ $payment->id }}, '{{ addslashes($payment->resident->fullname) }}', '{{ $payment->resident->unit_number }}', '{{ $currency }} {{ number_format($payment->amount) }}', '{{ \Carbon\Carbon::parse($payment->payment_month)->format('F Y') }}')"
                        class="text-primary hover:text-primary/80 font-bold text-xs uppercase tracking-widest px-3 py-1 border border-primary/20 rounded-lg hover:bg-primary/5 transition-all">
                        Review
                      </button>
                    @elseif($payment->status === 'approved')
                      <span class="text-xs text-slate-400">
                        Approved {{ $payment->approved_at?->format('d M') }}
                      </span>
                    @else
                      <span class="text-xs text-slate-400 truncate max-w-32 block text-right" title="{{ $payment->rejection_reason }}">
                        {{ \Illuminate\Support\Str::limit($payment->rejection_reason, 30) }}
                      </span>
                    @endif
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
    </div>
  </main>

  {{-- ── Review Modal ───────────────────────────────────────────────── --}}
  <div id="review-modal-overlay"
    class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm p-4 hidden flex items-center justify-center">
    <div class="bg-white dark:bg-slate-900 w-full max-w-lg rounded-2xl shadow-2xl overflow-hidden">
      <div class="p-8 flex flex-col gap-6">
        {{-- Header --}}
        <div class="flex items-center justify-between">
          <div>
            <h2 class="text-xl font-bold">Review Payment</h2>
            <p class="text-sm text-slate-500">Verify details before approving</p>
          </div>
          <button onclick="closeReviewModal()" class="text-slate-400 hover:text-slate-600 p-1">
            <span class="material-icons">close</span>
          </button>
        </div>

        {{-- Payment details --}}
        <div class="space-y-4">
          <div class="flex justify-between items-center pb-3 border-b border-slate-100 dark:border-slate-800">
            <span class="text-slate-500 text-sm">Resident</span>
            <span id="modal-resident" class="font-semibold text-sm"></span>
          </div>
          <div class="flex justify-between items-center pb-3 border-b border-slate-100 dark:border-slate-800">
            <span class="text-slate-500 text-sm">Month</span>
            <span id="modal-month" class="font-semibold text-sm"></span>
          </div>
          <div class="flex justify-between items-center pb-3 border-b border-slate-100 dark:border-slate-800">
            <span class="text-slate-500 text-sm">Amount</span>
            <span id="modal-amount" class="font-bold text-sm text-primary"></span>
          </div>
        </div>

        {{-- Rejection reason --}}
        <div>
          <label class="block text-sm font-semibold mb-2">Rejection Reason <span class="text-slate-400 font-normal">(only if rejecting)</span></label>
          <textarea id="modal-rejection-reason"
            class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-primary/50 focus:border-primary min-h-[100px] resize-none"
            placeholder="Example: Image is blurry, amount doesn't match..."></textarea>
          <p id="modal-error" class="text-rose-500 text-xs mt-1 hidden"></p>
        </div>

        {{-- Actions --}}
        <div class="grid grid-cols-2 gap-4">
          <form id="modal-reject-form" method="POST" action="">
            @csrf
            @method('PATCH')
            <input type="hidden" name="rejection_reason" id="modal-rejection-input" />
            <button type="submit"
              onclick="return submitReject()"
              class="w-full py-3 rounded-xl border-2 border-slate-200 dark:border-slate-700 font-bold text-sm hover:bg-rose-50 hover:border-rose-200 hover:text-rose-600 dark:hover:bg-rose-950/20 transition-all uppercase tracking-wide">
              Reject
            </button>
          </form>
          <form id="modal-approve-form" method="POST" action="">
            @csrf
            @method('PATCH')
            <button type="submit"
              class="w-full py-3 rounded-xl bg-primary text-white font-bold text-sm hover:bg-primary/90 shadow-lg shadow-primary/30 transition-all uppercase tracking-wide">
              Approve
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>

  <script>
    function openReviewModal(id, name, unit, amount, month) {
      document.getElementById('modal-resident').textContent = name + ' (' + unit + ')';
      document.getElementById('modal-amount').textContent = amount;
      document.getElementById('modal-month').textContent = month;
      document.getElementById('modal-rejection-reason').value = '';
      document.getElementById('modal-error').classList.add('hidden');
      document.getElementById('modal-approve-form').action = '/payments/' + id + '/approve';
      document.getElementById('modal-reject-form').action  = '/payments/' + id + '/reject';
      document.getElementById('review-modal-overlay').classList.remove('hidden');
      document.getElementById('review-modal-overlay').classList.add('flex');
    }

    function closeReviewModal() {
      document.getElementById('review-modal-overlay').classList.add('hidden');
      document.getElementById('review-modal-overlay').classList.remove('flex');
    }

    function submitReject() {
      const reason = document.getElementById('modal-rejection-reason').value.trim();
      if (reason.length < 10) {
        document.getElementById('modal-error').textContent = 'Please provide at least 10 characters for the rejection reason.';
        document.getElementById('modal-error').classList.remove('hidden');
        document.getElementById('modal-rejection-input').value = '';
        return false;
      }
      document.getElementById('modal-rejection-input').value = reason;
      document.getElementById('modal-error').classList.add('hidden');
      return true;
    }

    // Close on overlay click
    document.getElementById('review-modal-overlay').addEventListener('click', function(e) {
      if (e.target === this) closeReviewModal();
    });
  </script>

</x-layouts.app>