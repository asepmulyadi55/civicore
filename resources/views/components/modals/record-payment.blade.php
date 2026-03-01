{{-- ============================================================
components/modals/record-payment.blade.php
Includes: Create Payment, Edit Payment, Proof Lightbox,
Review Modal, and all associated JavaScript.
============================================================ --}}
@props([
  'currency'                => \App\Models\Setting::get('currency_symbol', 'Rp'),
  'paidMonthsByResident'    => [],
  'pendingMonthsByResident' => [],
  'residentFees'            => [],
  'residents'               => collect(),
  'canApprove'              => false,
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
          <label class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">Resident <span class="text-red-500">*</span></label>
          <div class="relative">
            <span class="material-icons absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-lg">person</span>
            <select id="cm-resident-select"
              class="w-full appearance-none pl-10 pr-9 py-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none dark:text-white"
              onchange="onResidentChange(this)">
              <option value="">— Select Resident —</option>
              @foreach($residents as $r)
                <option value="{{ $r->id }}" data-name="{{ $r->fullname }}" data-unit="Unit {{ $r->unit_number }}"
                  data-block="{{ $r->block?->name ?? '' }}"
                  data-fee="{{ $r->currentFee()?->amount ?? 0 }}">
                  {{ $r->fullname }} — {{ $r->block?->name }} Unit {{ $r->unit_number }}
                </option>
              @endforeach
            </select>
            <span
              class="material-icons absolute right-2.5 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400 text-[18px]">expand_more</span>
          </div>
          <p id="cm-error-resident" class="hidden text-xs text-red-500 items-center gap-1">
            <span class="material-icons text-xs">error_outline</span> Please select a resident.
          </p>
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
        <h3 class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">
          Select Months (<span id="cm-year-label">{{ now()->year }}</span>)
          <span class="font-normal text-slate-400 lowercase normal-case">— select at least one</span>
        </h3>
        <p id="cm-error-months" class="hidden text-xs text-red-500 items-center gap-1 mb-3">
          <span class="material-icons text-xs">error_outline</span> Please select at least one month.
        </p>
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
            ({{ $currency }}) <span class="text-red-500">*</span></label>
          <div class="relative">
            <span class="material-icons absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-lg">payments</span>
            <input id="cm-amount" type="number" min="0" step="1000" placeholder="Select a resident first"
              readonly
              class="w-full pl-10 pr-4 py-3 bg-slate-100 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 rounded-xl text-sm outline-none dark:text-white cursor-not-allowed"
              oninput="updateSummary()" />
          </div>
          <p class="text-[11px] text-slate-400">Auto-filled from resident's current monthly fee.</p>
          <p id="cm-error-amount" class="hidden text-xs text-red-500 flex items-center gap-1">
            <span class="material-icons text-xs">error_outline</span> Please enter a valid amount per month.
          </p>
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
      <form id="em-form" method="POST" action="" enctype="multipart/form-data" class="space-y-5" novalidate onsubmit="return submitEditModal(event)">
        @csrf
        @method('PATCH')

        {{-- Year Selector --}}
        <div class="flex flex-col gap-2">
          <label class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">Year</label>
          <div class="relative">
            <span class="material-icons absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-lg">calendar_today</span>
            <select id="em-year-select"
              class="w-full appearance-none pl-10 pr-9 py-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none dark:text-white"
              onchange="onEmYearChange(this)">
              @for($y = now()->year; $y >= 2023; $y--)
                <option value="{{ $y }}" {{ $y === now()->year ? 'selected' : '' }}>{{ $y }}</option>
              @endfor
            </select>
            <span class="material-icons absolute right-2.5 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400 text-[18px]">expand_more</span>
          </div>
        </div>

        {{-- Month Grid --}}
        <div class="col-span-full">
          <h3 class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">
            Select Month(s) (<span id="em-year-label">{{ now()->year }}</span>)
            <span class="font-normal text-slate-400 lowercase normal-case">— select at least one</span>
          </h3>
          <p id="em-error-months" class="hidden text-xs text-red-500 items-center gap-1 mb-3">
            <span class="material-icons text-xs">error_outline</span> Please select at least one month.
          </p>
          <div id="em-month-grid" class="grid grid-cols-3 sm:grid-cols-4 gap-3">
            {{-- Rendered by JS when modal opens --}}
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

          {{-- Amount per month --}}
          <div class="flex flex-col gap-2">
            <label class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">Amount per Month
              ({{ $currency }})</label>
            <div class="relative">
              <span
                class="material-icons absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-lg">payments</span>
              <input type="number" id="em-amount" name="amount" min="0" step="1000" required
                readonly
                oninput="updateEmSummary()"
                class="w-full pl-10 pr-4 py-3 bg-slate-100 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 rounded-xl text-sm outline-none dark:text-white cursor-not-allowed" />
            </div>
            <p class="text-[11px] text-slate-400">Auto-filled from resident's current monthly fee.</p>
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
              @unless($canApprove)
              {{-- Read-only badge: shown by JS when status is approved/rejected for coordinator --}}
              <div id="em-status-readonly"
                class="hidden pl-10 pr-4 py-3 bg-slate-100 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 rounded-xl text-sm italic">
                <span id="em-status-readonly-label" class="text-slate-500 dark:text-slate-400"></span>
              </div>
              @endunless
              <select id="em-status" name="status"
                class="w-full appearance-none pl-10 pr-9 py-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none dark:text-white"
                onchange="toggleEditRejection(this.value)">
                <option value="unpaid">Unpaid</option>
                <option value="pending">Pending (awaiting review)</option>
                @if($canApprove)
                  <option value="approved">Approved</option>
                  <option value="rejected">Rejected</option>
                @endif
              </select>
              <span id="em-status-chevron"
                class="material-icons absolute right-2.5 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400 text-[18px]">expand_more</span>
            </div>
          </div>
        </div>

        {{-- Rejection reason (read-only for coordinators) --}}
        <div id="em-rejection-wrap" class="flex flex-col gap-2 hidden">
          <label class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">Rejection
            Reason</label>
          <textarea id="em-rejection" name="rejection_reason" rows="3"
            class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none dark:text-white resize-none"
            {{ $canApprove ? '' : 'disabled placeholder="Rejection reason from Treasurer"' }}></textarea>
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

        {{-- Summary Panel --}}
        <div id="em-summary" class="hidden p-5 bg-primary/10 dark:bg-primary/5 rounded-2xl border border-primary/20">
          <div class="flex items-center justify-between">
            <div class="flex flex-col">
              <span class="text-xs font-bold text-primary uppercase tracking-widest">Calculated Total</span>
              <span id="em-total-amount" class="text-2xl font-extrabold text-slate-900 dark:text-slate-100 mt-1">{{ $currency }} 0</span>
            </div>
            <div class="text-right">
              <span id="em-months-count" class="text-sm font-bold text-slate-700 dark:text-slate-300">0 Months Selected</span>
              <p id="em-months-list" class="text-[11px] text-slate-500 mt-0.5"></p>
            </div>
          </div>
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
      {{-- Coordinator notes (read-only) --}}
      <div id="modal-notes-wrap" class="hidden">
        <label class="block text-sm font-semibold mb-2 text-slate-700 dark:text-slate-300">
          Notes from Coordinator
        </label>
        <div id="modal-notes"
          class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-600 dark:text-slate-300 min-h-[60px] whitespace-pre-wrap"></div>
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


{{-- ── Payment Delete Confirmation Modal ────────────────────────────── --}}
<div id="payment-delete-modal"
  class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4"
  onclick="if(event.target===this) closePaymentDeleteModal()">
  <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-2xl w-full max-w-sm overflow-hidden
    transform transition-all duration-200 scale-95 opacity-0" id="pdm-card">
    <div class="flex flex-col items-center pt-8 pb-5 px-6 text-center">
      <div class="w-16 h-16 rounded-full bg-rose-100 dark:bg-rose-900/30 flex items-center justify-center mb-4">
        <span class="material-icons text-3xl text-rose-600">delete_outline</span>
      </div>
      <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-2">Delete Payment?</h3>
      <p id="pdm-body" class="text-sm text-slate-500 dark:text-slate-400 leading-relaxed"></p>
    </div>
    <div class="flex gap-3 px-6 pb-6">
      <button onclick="closePaymentDeleteModal()"
        class="flex-1 px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 text-sm font-semibold
          text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition-all">
        Cancel
      </button>
      <form id="pdm-form" method="POST" action="" class="flex-1">
        @csrf @method('DELETE')
        <button type="submit"
          class="w-full px-4 py-2.5 rounded-xl text-sm font-bold text-white bg-rose-600 hover:bg-rose-700 transition-all">
          Yes, Delete
        </button>
      </form>
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
  const paidMonthsMap    = @json($paidMonthsByResident ?? []);
  const pendingMonthsMap = @json($pendingMonthsByResident ?? []);
  const residentFeesMap  = @json($residentFees ?? []);
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
    // Clear any inline errors
    ['cm-error-resident', 'cm-error-months', 'cm-error-amount'].forEach(id => clearCmError(id));
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
    clearCmError('cm-error-resident');
    document.getElementById('cm-resident-name').textContent = opt.dataset.name;
    document.getElementById('cm-unit-label').textContent = opt.dataset.unit;
    // Auto-fill amount from resident's fee; allow manual entry if fee is 0
    const fee = parseFloat(opt.dataset.fee) || 0;
    const amtInput = document.getElementById('cm-amount');
    amtInput.value = fee || '';
    amtInput.readOnly = fee > 0;  // allow typing only if no fee is set
    amtInput.classList.toggle('cursor-not-allowed', fee > 0);
    amtInput.classList.toggle('bg-slate-100', fee > 0);
    amtInput.classList.toggle('dark:bg-slate-800/60', fee > 0);
    amtInput.classList.toggle('bg-slate-50', !fee);
    amtInput.classList.toggle('focus:border-primary', !fee);
    document.getElementById('cm-rate-badge').textContent = fee ? `${currency} ${fee.toLocaleString()} / month` : '— / month';
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
    const grid    = document.getElementById('cm-month-grid');
    const paid    = (paidMonthsMap[currentResident.id] || []);
    const pending = (pendingMonthsMap[currentResident.id] || []);
    grid.innerHTML = '';
    for (let m = 1; m <= 12; m++) {
      const padM  = String(m).padStart(2, '0');
      const key   = `${selectedYear}-${padM}`;
      const label = MONTH_NAMES[m - 1].toUpperCase();
      if (paid.includes(key)) {
        grid.insertAdjacentHTML('beforeend', `
          <div class="month-card-paid border border-slate-200 dark:border-slate-700 rounded-xl p-3 flex flex-col items-center justify-center gap-1">
            <span class="text-xs font-bold text-slate-400">${label}</span>
            <div class="text-emerald-600 flex items-center gap-1">
              <span class="material-icons text-sm">check_circle</span>
              <span class="text-[10px] font-extrabold tracking-tighter uppercase">Paid</span>
            </div>
          </div>`);
      } else if (pending.includes(key)) {
        grid.insertAdjacentHTML('beforeend', `
          <div class="month-card-paid border border-amber-200 dark:border-amber-800/40 rounded-xl p-3 flex flex-col items-center justify-center gap-1">
            <span class="text-xs font-bold text-slate-400">${label}</span>
            <div class="text-amber-500 flex items-center gap-1">
              <span class="material-icons text-sm">hourglass_top</span>
              <span class="text-[10px] font-extrabold tracking-tighter uppercase">Pending</span>
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
      showMonthError(false);
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

  function showCmError(id, show) {
    document.getElementById(id).classList.toggle('hidden', !show);
    if (show) document.getElementById(id).classList.add('flex');
  }
  function clearCmError(id) {
    const el = document.getElementById(id);
    if (el) { el.classList.add('hidden'); el.classList.remove('flex'); }
  }

  // Inline error for month selection — shown inside the month grid section header
  function showMonthError(show) {
    let el = document.getElementById('cm-error-months');
    if (!el) return;
    el.classList.toggle('hidden', !show);
    if (show) el.classList.add('flex');
  }

  function submitCreateModal() {
    let valid = true;
    if (!currentResident) { showCmError('cm-error-resident', true); valid = false; } else { clearCmError('cm-error-resident'); }
    if (selectedMonths.size === 0) { showMonthError(true); valid = false; } else { showMonthError(false); }
    const amt = parseFloat(document.getElementById('cm-amount').value);
    if (!amt || amt <= 0) { showCmError('cm-error-amount', true); valid = false; } else { clearCmError('cm-error-amount'); }
    if (!valid) return;
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
  const canApprove  = {{ $canApprove ? 'true' : 'false' }};
  let emSelectedMonths = new Set();
  let emYear = {{ now()->year }};
  let emResidentId = null;

  function renderEmGrid() {
    const grid    = document.getElementById('em-month-grid');
    const paid    = (paidMonthsMap[emResidentId] || []);
    const pending = (pendingMonthsMap[emResidentId] || []);
    grid.innerHTML = '';
    for (let m = 1; m <= 12; m++) {
      const padM = String(m).padStart(2, '0');
      const key  = `${emYear}-${padM}`;
      const label = MONTH_NAMES[m - 1].toUpperCase();
      // Exclude currently-edited months so they remain selectable
      const isEditedMonth = emSelectedMonths.has(key);
      if (!isEditedMonth && paid.includes(key)) {
        grid.insertAdjacentHTML('beforeend', `
          <div class="month-card-paid border border-slate-200 dark:border-slate-700 rounded-xl p-3 flex flex-col items-center justify-center gap-1">
            <span class="text-xs font-bold text-slate-400">${label}</span>
            <div class="text-emerald-600 flex items-center gap-1">
              <span class="material-icons text-sm">check_circle</span>
              <span class="text-[10px] font-extrabold tracking-tighter uppercase">Paid</span>
            </div>
          </div>`);
      } else if (!isEditedMonth && pending.includes(key)) {
        grid.insertAdjacentHTML('beforeend', `
          <div class="month-card-paid border border-amber-200 dark:border-amber-800/40 rounded-xl p-3 flex flex-col items-center justify-center gap-1">
            <span class="text-xs font-bold text-slate-400">${label}</span>
            <div class="text-amber-500 flex items-center gap-1">
              <span class="material-icons text-sm">hourglass_top</span>
              <span class="text-[10px] font-extrabold tracking-tighter uppercase">Pending</span>
            </div>
          </div>`);
      } else {
        const isSel = emSelectedMonths.has(key);
        grid.insertAdjacentHTML('beforeend', `
          <label class="cursor-pointer group">
            <input type="checkbox" data-key="${key}" class="hidden peer em-month-cb" ${isSel ? 'checked' : ''} onchange="onEmMonthToggle(this)" />
            <div class="border-2 ${isSel ? 'border-primary bg-primary/5' : 'border-slate-200 dark:border-slate-700'} rounded-xl p-3 flex flex-col items-center justify-center gap-1 transition-all h-full peer-checked:border-primary peer-checked:bg-primary/5 hover:border-primary/50">
              <span class="text-xs font-bold text-slate-700 dark:text-slate-300">${label}</span>
              <span class="text-[10px] font-bold ${isSel ? 'text-primary' : 'text-slate-400'} uppercase">${isSel ? 'Selected' : 'Unpaid'}</span>
            </div>
          </label>`);
      }
    }
    updateEmSummary();
  }

  function onEmYearChange(sel) {
    emYear = parseInt(sel.value);
    document.getElementById('em-year-label').textContent = emYear;
    const oldKeys = [...emSelectedMonths];
    const paid    = (paidMonthsMap[emResidentId] || []);
    const pending = (pendingMonthsMap[emResidentId] || []);
    emSelectedMonths.clear();
    oldKeys.forEach(k => {
      const newKey = `${emYear}-${k.split('-')[1]}`;
      if (!paid.includes(newKey) && !pending.includes(newKey)) emSelectedMonths.add(newKey);
    });
    renderEmGrid();
  }

  function onEmMonthToggle(cb) {
    const key   = cb.dataset.key;
    const inner = cb.closest('label').querySelector('div');
    const badge = inner.querySelectorAll('span')[1];
    if (cb.checked) {
      emSelectedMonths.add(key);
      document.getElementById('em-error-months')?.classList.add('hidden');
      inner.classList.add('border-primary', 'bg-primary/5');
      inner.classList.remove('border-slate-200', 'dark:border-slate-700');
      badge.textContent = 'Selected'; badge.classList.add('text-primary'); badge.classList.remove('text-slate-400');
    } else {
      emSelectedMonths.delete(key);
      inner.classList.remove('border-primary', 'bg-primary/5');
      inner.classList.add('border-slate-200');
      badge.textContent = 'Unpaid'; badge.classList.remove('text-primary'); badge.classList.add('text-slate-400');
    }
    updateEmSummary();
  }

  function updateEmSummary() {
    const amt   = parseFloat(document.getElementById('em-amount').value) || 0;
    const count = emSelectedMonths.size;
    const total = amt * count;
    const summaryEl = document.getElementById('em-summary');
    if (count === 0 || amt === 0) { summaryEl.classList.add('hidden'); return; }
    summaryEl.classList.remove('hidden');
    document.getElementById('em-total-amount').textContent = `${currency} ${total.toLocaleString()}`;
    document.getElementById('em-months-count').textContent = `${count} Month${count > 1 ? 's' : ''} Selected`;
    const names = [...emSelectedMonths].sort().map(k => MONTH_NAMES[parseInt(k.split('-')[1]) - 1]);
    document.getElementById('em-months-list').textContent = `${names.join(', ')} @ ${currency} ${amt.toLocaleString()} ea.`;
  }

  function submitEditModal(e) {
    e.preventDefault();
    if (emSelectedMonths.size === 0) {
      const errEl = document.getElementById('em-error-months');
      if (errEl) { errEl.classList.remove('hidden'); errEl.classList.add('flex'); }
      return false;
    }
    const form = document.getElementById('em-form');
    form.querySelectorAll('input[name="months[]"]').forEach(el => el.remove());
    emSelectedMonths.forEach(k => {
      const inp = document.createElement('input');
      inp.type = 'hidden'; inp.name = 'months[]'; inp.value = k;
      form.appendChild(inp);
    });
    const fileInput = form.querySelector('input[type="file"][name="proof"]');
    if (fileInput && fileInput.files.length) {
      const fd = new FormData(form);
      fetch(form.action, { method: 'POST', body: fd })
        .then(r => { if (r.redirected) window.location.href = r.url; else window.location.reload(); });
      return false;
    }
    form.submit();
    return false;
  }

  function openEditModal(id, residentId, name, unit, monthsCsv, amount, methodId, status, rejection, notes, proofUrl) {
    document.getElementById('em-resident-name').textContent = name;
    document.getElementById('em-unit-label').textContent = 'Unit ' + unit;

    // Parse comma-sep months string, e.g. "2026-01,2026-02"
    const monthsList = monthsCsv.split(',').map(m => m.trim()).filter(Boolean);
    emResidentId = residentId;
    emSelectedMonths.clear();
    // Use the first month's year as the initial year
    emYear = parseInt(monthsList[0].split('-')[0]);
    monthsList.forEach(m => emSelectedMonths.add(m));
    document.getElementById('em-year-select').value = emYear;
    document.getElementById('em-year-label').textContent = emYear;
    renderEmGrid();

    // Auto-fill from resident's current fee (fallback to stored amount if no fee)
    const fee = residentFeesMap[residentId] || amount;
    document.getElementById('em-amount').value = fee;
    updateEmSummary();
    document.getElementById('em-notes').value = notes;
    document.getElementById('em-rejection').value = rejection;
    document.getElementById('em-method').value = methodId ?? '';
    document.getElementById('em-proof-name').textContent = 'Click to upload new file';

    const isLocked = !canApprove && status === 'approved';
    const readonlyEl = document.getElementById('em-status-readonly');
    const selectEl   = document.getElementById('em-status');
    const chevronEl  = document.getElementById('em-status-chevron');
    if (readonlyEl) {
      const labels = { approved: 'Approved — cannot be changed' };
      readonlyEl.classList.toggle('hidden', !isLocked);
      document.getElementById('em-status-readonly-label').textContent = labels[status] ?? status;
      selectEl.classList.toggle('hidden', isLocked);
      if (chevronEl) chevronEl.classList.toggle('hidden', isLocked);
    }
    if (!isLocked) {
      selectEl.value = (!canApprove && status === 'rejected') ? 'pending' : status;
    }

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
  function openReviewModal(id, name, unit, amount, month, notes, batchId = null) {
    document.getElementById('modal-resident').textContent = name + ' (' + unit + ')';
    document.getElementById('modal-amount').textContent = amount;
    document.getElementById('modal-month').textContent = month;
    document.getElementById('modal-rejection-reason').value = '';
    document.getElementById('modal-error').classList.add('hidden');

    const approveForm = document.getElementById('modal-approve-form');
    const rejectForm  = document.getElementById('modal-reject-form');

    // Find or create the _method hidden inputs
    let approveMeth = approveForm.querySelector('input[name="_method"]');
    let rejectMeth  = rejectForm.querySelector('input[name="_method"]');

    if (batchId) {
      // Batch: POST to batch routes (no _method override needed)
      approveForm.action = `/payments/batch/${batchId}/approve`;
      rejectForm.action  = `/payments/batch/${batchId}/reject`;
      if (approveMeth) approveMeth.remove();
      if (rejectMeth)  rejectMeth.remove();
    } else {
      // Single: PATCH to individual routes
      approveForm.action = `/payments/${id}/approve`;
      rejectForm.action  = `/payments/${id}/reject`;
      if (!approveMeth) {
        approveForm.insertAdjacentHTML('afterbegin', '<input type="hidden" name="_method" value="PATCH">');
      } else { approveMeth.value = 'PATCH'; }
      if (!rejectMeth) {
        rejectForm.insertAdjacentHTML('afterbegin', '<input type="hidden" name="_method" value="PATCH">');
      } else { rejectMeth.value = 'PATCH'; }
    }

    // Show coordinator notes if any
    const notesWrap = document.getElementById('modal-notes-wrap');
    const notesEl   = document.getElementById('modal-notes');
    if (notes && notes.trim()) {
      notesEl.textContent = notes;
      notesWrap.classList.remove('hidden');
    } else {
      notesWrap.classList.add('hidden');
    }
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

  // ── Payment Delete Modal ────────────────────────────────────────────
  function openPaymentDeleteModal(id, name) {
    document.getElementById('pdm-body').innerHTML =
      `Payment record for <strong class="text-slate-800 dark:text-slate-200">${name}</strong> will be permanently removed. This <em>cannot</em> be undone.`;
    document.getElementById('pdm-form').action = `/payments/${id}`;
    const modal = document.getElementById('payment-delete-modal');
    const card  = document.getElementById('pdm-card');
    modal.classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
    requestAnimationFrame(() => {
      card.classList.remove('scale-95', 'opacity-0');
      card.classList.add('scale-100', 'opacity-100');
    });
  }
  function closePaymentDeleteModal() {
    const modal = document.getElementById('payment-delete-modal');
    const card  = document.getElementById('pdm-card');
    card.classList.remove('scale-100', 'opacity-100');
    card.classList.add('scale-95', 'opacity-0');
    setTimeout(() => {
      modal.classList.add('hidden');
      document.body.classList.remove('overflow-hidden');
    }, 150);
  }
  document.addEventListener('keydown', e => {
    if (e.key === 'Escape') closePaymentDeleteModal();
  });
</script>