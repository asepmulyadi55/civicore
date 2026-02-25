{{-- ============================================================
components/modals/record-payment.blade.php
Includes: Create Payment, Edit Payment, Proof Lightbox,
Review Modal, and all associated JavaScript.
============================================================ --}}
@props([
  'currency'              => \App\Models\Setting::get('currency_symbol', 'Rp'),
  'paidMonthsByResident'  => [],
])

{{-- ════════════════════════════════════════════════════════════════ --}}
{{-- CREATE PAYMENT MODAL --}}
{{-- ════════════════════════════════════════════════════════════════ --}}
<div id="create-modal-overlay"
  class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm hidden items-center justify-center p-4"
  onclick="if(event.target===this) closeCreateModal()">

  <div
    class="bg-white dark:bg-slate-900 w-full max-w-3xl rounded-2xl shadow-2xl flex flex-col max-h-[92vh] overflow-hidden">

    {{-- Header --}}
    <div class="px-8 py-6 border-b border-slate-100 dark:border-slate-800 flex justify-between items-start shrink-0">
      <div>
        <h2 class="text-2xl font-extrabold text-slate-900 dark:text-slate-100">Record Payment</h2>
        <div id="cm-header-info" class="flex items-center gap-2 mt-1 text-sm hidden">
          <span id="cm-resident-name" class="text-primary font-bold"></span>
          <span class="text-slate-400">•</span>
          <span id="cm-unit-label" class="text-slate-600 dark:text-slate-400"></span>
          <span class="text-slate-400">•</span>
          <span id="cm-rate-badge"
            class="bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 px-2 py-0.5 rounded text-xs font-semibold"></span>
        </div>
        <p id="cm-select-hint" class="text-sm text-slate-400 mt-1">Select a resident to continue</p>
      </div>
      <button onclick="closeCreateModal()"
        class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors p-1">
        <span class="material-icons">close</span>
      </button>
    </div>

    {{-- Scrollable body --}}
    <div class="flex-1 overflow-y-auto px-8 py-6 space-y-8">

      {{-- Step 1: Resident + Year --}}
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
        <div class="flex flex-col gap-2">
          <label class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">Resident</label>
          <div class="relative">
            <span class="material-icons absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-lg">person</span>
            <select id="cm-resident-select"
              class="w-full appearance-none pl-10 pr-9 py-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none dark:text-white"
              onchange="onResidentChange(this)">
              <option value="">— Select Resident —</option>
              @foreach(\App\Models\Resident::with('block')->where('is_active', true)->orderBy('fullname')->get() as $r)
                <option value="{{ $r->id }}" data-name="{{ $r->fullname }}" data-unit="Unit {{ $r->unit_number }}"
                  data-block="{{ $r->block?->name ?? '' }}">
                  {{ $r->fullname }} — {{ $r->block?->name }} Unit {{ $r->unit_number }}
                </option>
              @endforeach
            </select>
            <span
              class="material-icons absolute right-2.5 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400 text-[18px]">expand_more</span>
          </div>
        </div>
        <div class="flex flex-col gap-2">
          <label class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">Year</label>
          <div class="relative">
            <span
              class="material-icons absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-lg">calendar_today</span>
            <select id="cm-year-select"
              class="w-full appearance-none pl-10 pr-9 py-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none dark:text-white"
              onchange="onYearChange(this)">
              @for($y = now()->year; $y >= 2023; $y--)
                <option value="{{ $y }}" {{ $y === now()->year ? 'selected' : '' }}>{{ $y }}</option>
              @endfor
            </select>
            <span
              class="material-icons absolute right-2.5 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400 text-[18px]">expand_more</span>
          </div>
        </div>
      </div>

      {{-- Step 2: Month Grid --}}
      <div id="cm-months-section" class="opacity-40 pointer-events-none transition-opacity duration-300">
        <h3 class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-4">
          Select Months (<span id="cm-year-label">{{ now()->year }}</span>)
        </h3>
        <div id="cm-month-grid" class="grid grid-cols-3 sm:grid-cols-4 gap-3">
          {{-- Rendered by JS after resident is chosen --}}
        </div>
      </div>

      {{-- Step 3: Payment Details --}}
      <div id="cm-details-section" class="opacity-40 pointer-events-none transition-opacity duration-300">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

          {{-- Payment Method --}}
          <div class="flex flex-col gap-2">
            <label class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">Payment
              Method</label>
            <div class="relative">
              <span
                class="material-icons absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-lg">account_balance</span>
              <select id="cm-method"
                class="w-full appearance-none pl-10 pr-9 py-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none dark:text-white">
                <option value="">— None —</option>
                @foreach(\App\Models\PaymentMethod::active()->orderBy('label')->get() as $pm)
                  <option value="{{ $pm->id }}">{{ $pm->label }}</option>
                @endforeach
              </select>
              <span
                class="material-icons absolute right-2.5 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400 text-[18px]">expand_more</span>
            </div>
          </div>

          {{-- Proof Upload --}}
          <div class="flex flex-col gap-2">
            <label class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">Proof of
              Payment
              <span class="font-normal text-slate-400 normal-case">(optional)</span></label>
            <label id="cm-proof-label"
              class="border-2 border-dashed border-slate-200 dark:border-slate-700 rounded-xl p-4 flex flex-col items-center justify-center text-center cursor-pointer hover:bg-primary/5 hover:border-primary/50 transition-colors">
              <span class="material-icons text-primary mb-1">cloud_upload</span>
              <span id="cm-proof-name" class="text-xs font-medium text-slate-600 dark:text-slate-400">Click to upload
                receipt</span>
              <span class="text-[10px] text-slate-400 uppercase mt-1">PDF, JPG, PNG (Max 5MB)</span>
              <input id="cm-proof-input" type="file" accept="image/*,.pdf" class="sr-only"
                onchange="document.getElementById('cm-proof-name').textContent = this.files[0]?.name ?? 'Click to upload receipt'" />
            </label>
          </div>

          {{-- Status --}}
          <div class="flex flex-col gap-2">
            <label class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">Status</label>
            <div class="relative">
              <span
                class="material-icons absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-lg">verified</span>
              <select id="cm-status"
                class="w-full appearance-none pl-10 pr-9 py-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none dark:text-white">
                <option value="unpaid">Unpaid</option>
                <option value="pending">Pending (awaiting review)</option>
                <option value="approved">Approved</option>
              </select>
              <span
                class="material-icons absolute right-2.5 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400 text-[18px]">expand_more</span>
            </div>
          </div>

          {{-- Notes --}}
          <div class="flex flex-col gap-2">
            <label class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">Notes
              <span class="font-normal text-slate-400 normal-case">(optional)</span></label>
            <textarea id="cm-notes" rows="3" placeholder="Any additional notes..."
              class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none dark:text-white resize-none"></textarea>
          </div>
        </div>

        {{-- Amount per month --}}
        <div class="mt-5 flex flex-col gap-2">
          <label class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">Amount per Month
            ({{ $currency }})</label>
          <div class="relative">
            <span class="material-icons absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-lg">payments</span>
            <input id="cm-amount" type="number" min="0" step="1000" placeholder="e.g. 150000"
              class="w-full pl-10 pr-4 py-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none dark:text-white"
              oninput="updateSummary()" />
          </div>
        </div>
      </div>

      {{-- Summary Panel --}}
      <div id="cm-summary" class="hidden p-6 bg-primary/10 dark:bg-primary/5 rounded-2xl border border-primary/20">
        <div class="flex items-center justify-between">
          <div class="flex flex-col">
            <span class="text-xs font-bold text-primary uppercase tracking-widest">Calculated Total</span>
            <div class="flex items-baseline gap-2 mt-1">
              <span id="cm-total-amount"
                class="text-3xl font-extrabold text-slate-900 dark:text-slate-100">{{ $currency }} 0</span>
            </div>
          </div>
          <div class="text-right">
            <span id="cm-months-count" class="text-sm font-bold text-slate-700 dark:text-slate-300">0 Months
              Selected</span>
            <p id="cm-months-list" class="text-[11px] text-slate-500 mt-0.5"></p>
          </div>
        </div>
      </div>

    </div>

    {{-- Footer --}}
    <div class="px-8 py-5 border-t border-slate-100 dark:border-slate-800 flex items-center justify-end gap-3 shrink-0">
      <button onclick="closeCreateModal()"
        class="px-6 py-2.5 rounded-xl font-bold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
        Cancel
      </button>
      <button onclick="submitCreateModal()"
        class="bg-primary hover:bg-primary/90 text-white px-8 py-2.5 rounded-xl font-bold shadow-lg shadow-primary/20 flex items-center gap-2 transition-all active:scale-95">
        <span class="material-icons text-lg">verified</span>
        Confirm Payment
      </button>
    </div>

    {{-- Hidden form (submitted by JS) --}}
    <form id="cm-form" method="POST" action="{{ route('payments.store') }}" enctype="multipart/form-data"
      class="hidden">
      @csrf
    </form>
  </div>
</div>

{{-- ════════════════════════════════════════════════════════════════ --}}
{{-- EDIT PAYMENT MODAL --}}
{{-- ════════════════════════════════════════════════════════════════ --}}
<div id="edit-modal-overlay"
  class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm hidden items-center justify-center p-4"
  onclick="if(event.target===this) closeEditModal()">

  <div
    class="bg-white dark:bg-slate-900 w-full max-w-2xl rounded-2xl shadow-2xl flex flex-col max-h-[92vh] overflow-hidden">

    {{-- Header --}}
    <div class="px-8 py-6 border-b border-slate-100 dark:border-slate-800 flex justify-between items-start shrink-0">
      <div>
        <h2 class="text-2xl font-extrabold text-slate-900 dark:text-slate-100">Edit Payment</h2>
        <div class="flex items-center gap-2 mt-1 text-sm">
          <span id="em-resident-name" class="text-primary font-bold"></span>
          <span class="text-slate-400">•</span>
          <span id="em-unit-label" class="text-slate-600 dark:text-slate-400"></span>
        </div>
      </div>
      <button onclick="closeEditModal()"
        class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors p-1">
        <span class="material-icons">close</span>
      </button>
    </div>

    {{-- Body --}}
    <div class="flex-1 overflow-y-auto px-8 py-6">
      <form id="em-form" method="POST" action="" enctype="multipart/form-data" class="space-y-5" novalidate>
        @csrf
        @method('PATCH')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
          {{-- Payment Month --}}
          <div class="flex flex-col gap-2">
            <label class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">Payment
              Month</label>
            <div class="relative">
              <span
                class="material-icons absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-lg">calendar_month</span>
              <input type="month" id="em-month" name="payment_month" required
                class="w-full pl-10 pr-4 py-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none dark:text-white" />
            </div>
          </div>

          {{-- Amount --}}
          <div class="flex flex-col gap-2">
            <label class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">Amount
              ({{ $currency }})</label>
            <div class="relative">
              <span
                class="material-icons absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-lg">payments</span>
              <input type="number" id="em-amount" name="amount" min="0" step="1000" required
                class="w-full pl-10 pr-4 py-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none dark:text-white" />
            </div>
          </div>

          {{-- Payment Method --}}
          <div class="flex flex-col gap-2">
            <label class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">Payment
              Method</label>
            <div class="relative">
              <span
                class="material-icons absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-lg">account_balance</span>
              <select id="em-method" name="payment_method_id"
                class="w-full appearance-none pl-10 pr-9 py-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none dark:text-white">
                <option value="">— None —</option>
                @foreach(\App\Models\PaymentMethod::active()->orderBy('label')->get() as $pm)
                  <option value="{{ $pm->id }}">{{ $pm->label }}</option>
                @endforeach
              </select>
              <span
                class="material-icons absolute right-2.5 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400 text-[18px]">expand_more</span>
            </div>
          </div>

          {{-- Status --}}
          <div class="flex flex-col gap-2">
            <label class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">Status</label>
            <div class="relative">
              <span
                class="material-icons absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-lg">verified</span>
              <select id="em-status" name="status"
                class="w-full appearance-none pl-10 pr-9 py-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none dark:text-white"
                onchange="toggleEditRejection(this.value)">
                <option value="unpaid">Unpaid</option>
                <option value="pending">Pending (awaiting review)</option>
                <option value="approved">Approved</option>
                <option value="rejected">Rejected</option>
              </select>
              <span
                class="material-icons absolute right-2.5 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400 text-[18px]">expand_more</span>
            </div>
          </div>
        </div>

        {{-- Rejection reason --}}
        <div id="em-rejection-wrap" class="flex flex-col gap-2 hidden">
          <label class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">Rejection
            Reason</label>
          <textarea id="em-rejection" name="rejection_reason" rows="3"
            class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none dark:text-white resize-none"></textarea>
        </div>

        {{-- Current Proof --}}
        <div id="em-proof-wrap" class="hidden">
          <label
            class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider block mb-2">Current
            Proof</label>
          <a id="em-proof-link" href="" target="_blank"
            class="inline-flex items-center gap-2 text-sm text-primary hover:underline">
            <span class="material-icons text-base">receipt_long</span>
            View current proof
          </a>
        </div>

        {{-- Replace Proof --}}
        <div class="flex flex-col gap-2">
          <label class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">Replace Proof
            <span class="font-normal text-slate-400 normal-case">(optional)</span></label>
          <label
            class="border-2 border-dashed border-slate-200 dark:border-slate-700 rounded-xl p-4 flex items-center gap-3 cursor-pointer hover:bg-primary/5 hover:border-primary/50 transition-colors">
            <span class="material-icons text-primary">cloud_upload</span>
            <span id="em-proof-name" class="text-sm text-slate-500">Click to upload new file</span>
            <input type="file" name="proof" accept="image/*,.pdf" class="sr-only"
              onchange="document.getElementById('em-proof-name').textContent = this.files[0]?.name ?? 'Click to upload new file'" />
          </label>
        </div>

        {{-- Notes --}}
        <div class="flex flex-col gap-2">
          <label class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">Notes
            <span class="font-normal text-slate-400 normal-case">(optional)</span></label>
          <textarea id="em-notes" name="notes" rows="3"
            class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none dark:text-white resize-none"></textarea>
        </div>

        {{-- Footer --}}
        <div class="flex gap-3 pt-2">
          <button type="button" onclick="closeEditModal()"
            class="flex-1 py-3 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-bold text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
            Cancel
          </button>
          <button type="submit"
            class="flex-1 py-3 bg-primary text-white rounded-xl text-sm font-bold hover:bg-primary/90 shadow-lg shadow-primary/20 transition-all flex items-center justify-center gap-2 active:scale-95">
            <span class="material-icons text-sm">save</span>
            Save Changes
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

{{-- ── Proof Lightbox ─────────────────────────────────────────────── --}}
<div id="proof-modal"
  class="fixed inset-0 z-50 bg-slate-900/80 backdrop-blur-sm hidden flex items-center justify-center p-4"
  onclick="if(event.target===this) closeProofModal()">
  <div class="relative max-w-2xl w-full">
    <button onclick="closeProofModal()"
      class="absolute -top-10 right-0 text-white/70 hover:text-white flex items-center gap-1 text-sm">
      <span class="material-icons text-base">close</span> Close
    </button>
    <img id="proof-img" src="" alt="Payment Proof" class="w-full rounded-xl shadow-2xl object-contain max-h-[80vh]" />
    <a id="proof-link" href="" target="_blank"
      class="mt-3 flex items-center justify-center gap-2 text-white/70 hover:text-white text-sm transition-colors">
      <span class="material-icons text-sm">open_in_new</span> Open full image
    </a>
  </div>
</div>

{{-- ── Review Modal ───────────────────────────────────────────────── --}}
<div id="review-modal-overlay"
  class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm p-4 hidden flex items-center justify-center">
  <div class="bg-white dark:bg-slate-900 w-full max-w-lg rounded-2xl shadow-2xl overflow-hidden">
    <div class="p-8 flex flex-col gap-6">
      <div class="flex items-center justify-between">
        <div>
          <h2 class="text-xl font-bold">Review Payment</h2>
          <p class="text-sm text-slate-500">Verify details before approving</p>
        </div>
        <button onclick="closeReviewModal()" class="text-slate-400 hover:text-slate-600 p-1">
          <span class="material-icons">close</span>
        </button>
      </div>
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
      <div>
        <label class="block text-sm font-semibold mb-2">Rejection Reason
          <span class="text-slate-400 font-normal">(only if rejecting)</span></label>
        <textarea id="modal-rejection-reason"
          class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-primary/50 focus:border-primary min-h-[100px] resize-none"
          placeholder="Example: Image is blurry, amount doesn't match..."></textarea>
        <p id="modal-error" class="text-rose-500 text-xs mt-1 hidden"></p>
      </div>
      <div class="grid grid-cols-2 gap-4">
        <form id="modal-reject-form" method="POST" action="">
          @csrf @method('PATCH')
          <input type="hidden" name="rejection_reason" id="modal-rejection-input" />
          <button type="submit" onclick="return submitReject()"
            class="w-full py-3 rounded-xl border-2 border-slate-200 dark:border-slate-700 font-bold text-sm hover:bg-rose-50 hover:border-rose-200 hover:text-rose-600 dark:hover:bg-rose-950/20 transition-all uppercase tracking-wide">
            Reject
          </button>
        </form>
        <form id="modal-approve-form" method="POST" action="">
          @csrf @method('PATCH')
          <button type="submit"
            class="w-full py-3 rounded-xl bg-primary text-white font-bold text-sm hover:bg-primary/90 shadow-lg shadow-primary/30 transition-all uppercase tracking-wide">
            Approve
          </button>
        </form>
      </div>
    </div>
  </div>
</div>

<style>
  .month-card-paid {
    opacity: 0.55;
    pointer-events: none;
    background-color: #f1f5f9;
  }

  .dark .month-card-paid {
    background-color: #1e293b;
  }
</style>

<script>
  const paidMonthsMap = @json($paidMonthsByResident ?? []);
  const currency = '{{ $currency }}';

  let selectedMonths = new Set();
  let selectedYear = {{ now()->year }};
  let currentResident = null;

  const MONTH_NAMES = ['January', 'February', 'March', 'April', 'May', 'June',
    'July', 'August', 'September', 'October', 'November', 'December'];

  // ── CREATE MODAL ───────────────────────────────────────────────────
  function openCreateModal() {
    resetCreateModal();
    const el = document.getElementById('create-modal-overlay');
    el.classList.remove('hidden'); el.classList.add('flex');
    document.body.classList.add('overflow-hidden');
  }
  function closeCreateModal() {
    const el = document.getElementById('create-modal-overlay');
    el.classList.add('hidden'); el.classList.remove('flex');
    document.body.classList.remove('overflow-hidden');
  }
  function resetCreateModal() {
    document.getElementById('cm-resident-select').value = '';
    document.getElementById('cm-year-select').value = '{{ now()->year }}';
    document.getElementById('cm-method').value = '';
    document.getElementById('cm-status').value = 'unpaid';
    document.getElementById('cm-notes').value = '';
    document.getElementById('cm-amount').value = '';
    document.getElementById('cm-proof-input').value = '';
    document.getElementById('cm-proof-name').textContent = 'Click to upload receipt';
    document.getElementById('cm-header-info').classList.add('hidden');
    document.getElementById('cm-select-hint').classList.remove('hidden');
    document.getElementById('cm-months-section').classList.add('opacity-40', 'pointer-events-none');
    document.getElementById('cm-details-section').classList.add('opacity-40', 'pointer-events-none');
    document.getElementById('cm-summary').classList.add('hidden');
    document.getElementById('cm-month-grid').innerHTML = '';
    document.getElementById('cm-year-label').textContent = selectedYear;
    selectedMonths.clear(); selectedYear = {{ now()->year }}; currentResident = null;
  }

  function onResidentChange(sel) {
    const opt = sel.options[sel.selectedIndex];
    if (!opt.value) {
      currentResident = null;
      document.getElementById('cm-header-info').classList.add('hidden');
      document.getElementById('cm-select-hint').classList.remove('hidden');
      document.getElementById('cm-months-section').classList.add('opacity-40', 'pointer-events-none');
      document.getElementById('cm-details-section').classList.add('opacity-40', 'pointer-events-none');
      document.getElementById('cm-summary').classList.add('hidden');
      document.getElementById('cm-month-grid').innerHTML = '';
      selectedMonths.clear(); return;
    }
    currentResident = { id: opt.value, name: opt.dataset.name, unit: opt.dataset.unit };
    document.getElementById('cm-resident-name').textContent = opt.dataset.name;
    document.getElementById('cm-unit-label').textContent = opt.dataset.unit;
    document.getElementById('cm-rate-badge').textContent = '— / month';
    document.getElementById('cm-header-info').classList.remove('hidden');
    document.getElementById('cm-select-hint').classList.add('hidden');
    document.getElementById('cm-months-section').classList.remove('opacity-40', 'pointer-events-none');
    document.getElementById('cm-details-section').classList.remove('opacity-40', 'pointer-events-none');
    selectedMonths.clear(); renderMonthGrid(); updateSummary();
  }

  function onYearChange(sel) {
    selectedYear = parseInt(sel.value);
    document.getElementById('cm-year-label').textContent = selectedYear;
    selectedMonths.clear();
    if (currentResident) renderMonthGrid();
    updateSummary();
  }

  function renderMonthGrid() {
    const grid = document.getElementById('cm-month-grid');
    const paid = (paidMonthsMap[currentResident.id] || []);
    grid.innerHTML = '';
    for (let m = 1; m <= 12; m++) {
      const padM = String(m).padStart(2, '0');
      const key = `${selectedYear}-${padM}`;
      const isPaid = paid.includes(key);
      const label = MONTH_NAMES[m - 1].toUpperCase();
      if (isPaid) {
        grid.insertAdjacentHTML('beforeend', `
          <div class="month-card-paid border border-slate-200 dark:border-slate-700 rounded-xl p-3 flex flex-col items-center justify-center gap-1">
            <span class="text-xs font-bold text-slate-400">${label}</span>
            <div class="text-emerald-600 flex items-center gap-1">
              <span class="material-icons text-sm">check_circle</span>
              <span class="text-[10px] font-extrabold tracking-tighter uppercase">Paid</span>
            </div>
          </div>`);
      } else {
        const isSel = selectedMonths.has(key);
        grid.insertAdjacentHTML('beforeend', `
          <label class="cursor-pointer group">
            <input type="checkbox" data-key="${key}" class="hidden peer month-cb" ${isSel ? 'checked' : ''} onchange="onMonthToggle(this)" />
            <div class="border-2 ${isSel ? 'border-primary bg-primary/5' : 'border-slate-200 dark:border-slate-700'} rounded-xl p-3 flex flex-col items-center justify-center gap-1 transition-all h-full peer-checked:border-primary peer-checked:bg-primary/5 hover:border-primary/50">
              <span class="text-xs font-bold text-slate-700 dark:text-slate-300">${label}</span>
              <span class="text-[10px] font-bold ${isSel ? 'text-primary' : 'text-slate-400'} uppercase">${isSel ? 'Selected' : 'Unpaid'}</span>
            </div>
          </label>`);
      }
    }
  }

  function onMonthToggle(cb) {
    const key = cb.dataset.key;
    const inner = cb.closest('label').querySelector('div');
    const badge = inner.querySelectorAll('span')[1];
    if (cb.checked) {
      selectedMonths.add(key);
      inner.classList.add('border-primary', 'bg-primary/5');
      inner.classList.remove('border-slate-200', 'dark:border-slate-700');
      badge.textContent = 'Selected'; badge.classList.add('text-primary'); badge.classList.remove('text-slate-400');
    } else {
      selectedMonths.delete(key);
      inner.classList.remove('border-primary', 'bg-primary/5');
      inner.classList.add('border-slate-200');
      badge.textContent = 'Unpaid'; badge.classList.remove('text-primary'); badge.classList.add('text-slate-400');
    }
    updateSummary();
  }

  function updateSummary() {
    const amt = parseFloat(document.getElementById('cm-amount').value) || 0;
    const count = selectedMonths.size;
    const total = amt * count;
    document.getElementById('cm-rate-badge').textContent = `${currency} ${amt.toLocaleString()} / month`;
    if (count === 0 || amt === 0) { document.getElementById('cm-summary').classList.add('hidden'); return; }
    document.getElementById('cm-summary').classList.remove('hidden');
    document.getElementById('cm-total-amount').textContent = `${currency} ${total.toLocaleString()}`;
    document.getElementById('cm-months-count').textContent = `${count} Month${count > 1 ? 's' : ''} Selected`;
    const names = [...selectedMonths].sort().map(k => MONTH_NAMES[parseInt(k.split('-')[1]) - 1]);
    document.getElementById('cm-months-list').textContent = `${names.join(', ')} @ ${currency} ${amt.toLocaleString()} ea.`;
  }

  function submitCreateModal() {
    if (!currentResident) { alert('Please select a resident.'); return; }
    if (selectedMonths.size === 0) { alert('Please select at least one month.'); return; }
    const amt = parseFloat(document.getElementById('cm-amount').value);
    if (!amt || amt <= 0) { alert('Please enter a valid amount per month.'); return; }
    const form = document.getElementById('cm-form');
    form.innerHTML = `<input type="hidden" name="_token" value="{{ csrf_token() }}">`;
    form.insertAdjacentHTML('beforeend', `<input type="hidden" name="resident_id" value="${currentResident.id}">`);
    form.insertAdjacentHTML('beforeend', `<input type="hidden" name="amount" value="${amt}">`);
    form.insertAdjacentHTML('beforeend', `<input type="hidden" name="payment_method_id" value="${document.getElementById('cm-method').value}">`);
    form.insertAdjacentHTML('beforeend', `<input type="hidden" name="status" value="${document.getElementById('cm-status').value}">`);
    form.insertAdjacentHTML('beforeend', `<input type="hidden" name="notes" value="${document.getElementById('cm-notes').value}">`);
    [...selectedMonths].sort().forEach(key => {
      form.insertAdjacentHTML('beforeend', `<input type="hidden" name="months[]" value="${key}">`);
    });
    const fileInput = document.getElementById('cm-proof-input');
    if (fileInput.files.length) {
      const fd = new FormData(form);
      fd.append('proof', fileInput.files[0]);
      fetch(form.action, { method: 'POST', body: fd })
        .then(r => { if (r.redirected) window.location.href = r.url; else window.location.reload(); });
      return;
    }
    form.submit();
  }

  // ── EDIT MODAL ─────────────────────────────────────────────────────
  function openEditModal(id, name, unit, month, amount, methodId, status, rejection, notes, proofUrl) {
    document.getElementById('em-resident-name').textContent = name;
    document.getElementById('em-unit-label').textContent = 'Unit ' + unit;
    document.getElementById('em-month').value = month;
    document.getElementById('em-amount').value = amount;
    document.getElementById('em-notes').value = notes;
    document.getElementById('em-status').value = status;
    document.getElementById('em-rejection').value = rejection;
    document.getElementById('em-method').value = methodId ?? '';
    document.getElementById('em-proof-name').textContent = 'Click to upload new file';
    toggleEditRejection(status);
    const proofWrap = document.getElementById('em-proof-wrap');
    if (proofUrl) { document.getElementById('em-proof-link').href = proofUrl; proofWrap.classList.remove('hidden'); }
    else { proofWrap.classList.add('hidden'); }
    document.getElementById('em-form').action = `/payments/${id}`;
    const el = document.getElementById('edit-modal-overlay');
    el.classList.remove('hidden'); el.classList.add('flex');
    document.body.classList.add('overflow-hidden');
  }
  function closeEditModal() {
    const el = document.getElementById('edit-modal-overlay');
    el.classList.add('hidden'); el.classList.remove('flex');
    document.body.classList.remove('overflow-hidden');
  }
  function toggleEditRejection(status) {
    document.getElementById('em-rejection-wrap').classList.toggle('hidden', status !== 'rejected');
  }

  // ── Proof lightbox ─────────────────────────────────────────────────
  function openProofModal(url) {
    document.getElementById('proof-img').src = url;
    document.getElementById('proof-link').href = url;
    const m = document.getElementById('proof-modal');
    m.classList.remove('hidden'); m.classList.add('flex');
  }
  function closeProofModal() {
    const m = document.getElementById('proof-modal');
    m.classList.add('hidden'); m.classList.remove('flex');
  }

  // ── Review modal ───────────────────────────────────────────────────
  function openReviewModal(id, name, unit, amount, month) {
    document.getElementById('modal-resident').textContent = name + ' (' + unit + ')';
    document.getElementById('modal-amount').textContent = amount;
    document.getElementById('modal-month').textContent = month;
    document.getElementById('modal-rejection-reason').value = '';
    document.getElementById('modal-error').classList.add('hidden');
    document.getElementById('modal-approve-form').action = '/payments/' + id + '/approve';
    document.getElementById('modal-reject-form').action = '/payments/' + id + '/reject';
    const el = document.getElementById('review-modal-overlay');
    el.classList.remove('hidden'); el.classList.add('flex');
    document.body.classList.add('overflow-hidden');
  }
  function closeReviewModal() {
    const el = document.getElementById('review-modal-overlay');
    el.classList.add('hidden'); el.classList.remove('flex');
    document.body.classList.remove('overflow-hidden');
  }
  function submitReject() {
    const reason = document.getElementById('modal-rejection-reason').value.trim();
    if (reason.length < 10) {
      document.getElementById('modal-error').textContent = 'Please provide at least 10 characters for the rejection reason.';
      document.getElementById('modal-error').classList.remove('hidden');
      return false;
    }
    document.getElementById('modal-rejection-input').value = reason;
    document.getElementById('modal-error').classList.add('hidden');
    return true;
  }

  document.getElementById('review-modal-overlay').addEventListener('click', function (e) {
    if (e.target === this) closeReviewModal();
  });
  document.addEventListener('keydown', e => {
    if (e.key === 'Escape') { closeCreateModal(); closeEditModal(); closeReviewModal(); closeProofModal(); }
  });
</script>