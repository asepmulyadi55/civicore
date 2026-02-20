{{-- ============================================================
Modal: Record / Verify Payment
Trigger via: openPaymentModal(initials, name, unit, rate)
============================================================ --}}
<div id="modal-payment"
  class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden"
  onclick="closeModalOnBackdrop(event, 'modal-payment')">
  <div
    class="bg-white dark:bg-slate-900 w-full max-w-3xl rounded-xl shadow-2xl flex flex-col max-h-[90vh] overflow-hidden border border-slate-200 dark:border-slate-800">

    {{-- Header --}}
    <div class="px-8 py-6 border-b border-slate-100 dark:border-slate-800 flex justify-between items-start">
      <div>
        <h2 class="text-2xl font-extrabold text-slate-900 dark:text-slate-100">Record Payment</h2>
        <div class="flex items-center gap-2 mt-1">
          <span id="pm-name" class="text-primary font-bold"></span>
          <span class="text-slate-400">•</span>
          <span id="pm-unit" class="text-slate-600 dark:text-slate-400 text-sm"></span>
          <span class="text-slate-400">•</span>
          <span id="pm-rate"
            class="bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 px-2 py-0.5 rounded text-xs font-semibold"></span>
        </div>
      </div>
      <button class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors mt-1"
        onclick="closeModal('modal-payment')">
        <span class="material-icons">close</span>
      </button>
    </div>

    {{-- Scrollable body --}}
    <div class="flex-1 overflow-y-auto px-8 py-6 space-y-8">

      {{-- Month Selection Grid --}}
      <div>
        <h3 class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-4">
          Select Months ({{ date('Y') }})
        </h3>
        <div class="grid grid-cols-3 sm:grid-cols-4 gap-3" id="month-grid">
          {{-- Rendered by JS --}}
        </div>
      </div>

      {{-- Payment Details --}}
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="flex flex-col gap-2">
          <label class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">Payment
            Method</label>
          <select id="pm-method"
            class="w-full h-12 rounded-lg border-slate-200 dark:border-slate-700 dark:bg-slate-800 focus:ring-primary focus:border-primary text-slate-700 dark:text-slate-200 text-sm">
            <option value="">Select Method</option>
            <option value="bank">Bank Transfer</option>
            <option value="cash">Cash</option>
            <option value="mobile">Mobile Pay</option>
            <option value="check">Check</option>
          </select>
        </div>
        <div class="flex flex-col gap-2">
          <label class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">Proof of
            Payment</label>
          <div
            class="border-2 border-dashed border-slate-200 dark:border-slate-700 rounded-lg p-4 flex flex-col items-center justify-center text-center cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
            <span class="material-icons text-primary mb-1">cloud_upload</span>
            <span class="text-xs font-medium text-slate-600 dark:text-slate-400">Click to upload receipt</span>
            <span class="text-[10px] text-slate-400 uppercase mt-1">PDF, JPG, PNG (Max 5MB)</span>
          </div>
        </div>
      </div>

      {{-- Summary Panel --}}
      <div class="p-6 bg-primary/10 dark:bg-primary/5 rounded-xl border border-primary/20">
        <div class="flex items-center justify-between">
          <div class="flex flex-col">
            <span class="text-xs font-bold text-primary uppercase tracking-widest">Calculated Total</span>
            <div class="flex items-baseline gap-2">
              <span id="pm-total" class="text-3xl font-extrabold text-slate-900 dark:text-slate-100">$0.00</span>
              <span class="text-slate-500 font-medium">USD</span>
            </div>
          </div>
          <div class="text-right">
            <span id="pm-count" class="text-sm font-bold text-slate-700 dark:text-slate-300">0 Months Selected</span>
            <p id="pm-months-label" class="text-[11px] text-slate-500 mt-0.5"></p>
          </div>
        </div>
      </div>

    </div>

    {{-- Footer --}}
    <div
      class="px-8 py-5 border-t border-slate-100 dark:border-slate-800 flex items-center justify-end gap-3 bg-slate-50/50 dark:bg-slate-800/20">
      <button type="button" onclick="closeModal('modal-payment')"
        class="px-6 py-2.5 rounded-lg font-bold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors text-sm">
        Cancel
      </button>
      <button type="button"
        class="px-5 py-2.5 rounded-lg font-bold border-2 border-rose-500 text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-500/10 transition-colors text-sm flex items-center gap-2">
        <span class="material-icons text-sm">cancel</span>
        Reject
      </button>
      <button type="button"
        class="bg-primary hover:bg-primary/90 text-white px-6 py-2.5 rounded-lg font-bold shadow-lg shadow-primary/20 flex items-center gap-2 transition-all active:scale-95 text-sm">
        <span class="material-icons text-sm">verified</span>
        Confirm Payment
      </button>
    </div>

  </div>
</div>

<script>
  // ── Month names and state ──────────────────────────────────────
  const MONTHS = ['January', 'February', 'March', 'April', 'May', 'June',
    'July', 'August', 'September', 'October', 'November', 'December'];

  let pmRateValue = 0;
  let pmPaidMonths = [];   // indices of already-paid months (0-based)
  let pmSelected = new Set();

  function openPaymentModal(initials, name, unit, rate, paidMonths = []) {
    pmRateValue = parseFloat(rate) || 0;
    pmPaidMonths = paidMonths;
    pmSelected = new Set();

    document.getElementById('pm-name').textContent = name;
    document.getElementById('pm-unit').textContent = unit;
    document.getElementById('pm-rate').textContent = `$${pmRateValue.toFixed(2)} / month`;

    renderMonthGrid();
    updateSummary();
    openModal('modal-payment');
  }

  function renderMonthGrid() {
    const grid = document.getElementById('month-grid');
    grid.innerHTML = '';

    MONTHS.forEach((month, i) => {
      const isPaid = pmPaidMonths.includes(i);

      if (isPaid) {
        grid.insertAdjacentHTML('beforeend', `
          <div class="opacity-60 pointer-events-none bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg p-3 flex flex-col items-center justify-center gap-1">
            <span class="text-xs font-bold text-slate-400 uppercase">${month}</span>
            <div class="text-emerald-600 flex items-center gap-1">
              <span class="material-icons text-sm">check_circle</span>
              <span class="text-[10px] font-extrabold uppercase">Paid</span>
            </div>
          </div>`);
      } else {
        const id = `pm-month-${i}`;
        grid.insertAdjacentHTML('beforeend', `
          <label class="cursor-pointer group" onclick="toggleMonth(${i})">
            <div id="${id}"
              class="border-2 border-slate-200 dark:border-slate-700 rounded-lg p-3 flex flex-col items-center justify-center gap-1 transition-all h-full hover:border-primary/50">
              <span class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase">${month}</span>
              <span id="${id}-label" class="text-[10px] font-bold text-slate-400 uppercase">Unpaid</span>
            </div>
          </label>`);
      }
    });
  }

  function toggleMonth(index) {
    if (pmSelected.has(index)) {
      pmSelected.delete(index);
    } else {
      pmSelected.add(index);
    }
    const card = document.getElementById(`pm-month-${index}`);
    const label = document.getElementById(`pm-month-${index}-label`);
    const sel = pmSelected.has(index);
    card.classList.toggle('border-primary', sel);
    card.classList.toggle('bg-primary/5', sel);
    card.classList.toggle('border-slate-200', !sel);
    card.classList.toggle('dark:border-slate-700', !sel);
    label.textContent = sel ? 'Selected' : 'Unpaid';
    label.className = `text-[10px] font-bold uppercase ${sel ? 'text-primary' : 'text-slate-400'}`;
    updateSummary();
  }

  function updateSummary() {
    const count = pmSelected.size;
    const total = count * pmRateValue;
    document.getElementById('pm-total').textContent = `$${total.toFixed(2)}`;
    document.getElementById('pm-count').textContent = `${count} Month${count !== 1 ? 's' : ''} Selected`;
    const names = [...pmSelected].sort().map(i => MONTHS[i]);
    document.getElementById('pm-months-label').textContent =
      names.length ? `${names.join(', ')} @ $${pmRateValue.toFixed(2)} ea.` : '';
  }
</script>