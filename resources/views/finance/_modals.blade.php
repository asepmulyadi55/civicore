{{-- finance/_modals.blade.php — Add/Edit Transaction, Delete, Generate Report, Opening Balance, Revise --}}

{{-- ══════════════════════════════════════════════════════════════════
     1. Add / Edit Transaction Modal
═══════════════════════════════════════════════════════════════════════ --}}
<div id="fin-tx-modal"
  class="fixed inset-0 z-50 hidden bg-black/50 backdrop-blur-sm flex items-center justify-center p-4"
  role="dialog" aria-modal="true" aria-labelledby="fin-tx-modal-title">
  <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl w-full max-w-lg max-h-[90vh] flex flex-col"
       onclick="event.stopPropagation()">

    {{-- Header --}}
    <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200 dark:border-slate-700 flex-shrink-0">
      <h2 id="fin-tx-modal-title" class="text-base font-semibold text-slate-800 dark:text-slate-100">
        {{ __('app.fin_add_transaction') }}
      </h2>
      <button type="button" onclick="closeTxModal()"
        class="p-1.5 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
        <span class="material-icons text-[20px]">close</span>
      </button>
    </div>

    {{-- Form --}}
    <form id="fin-tx-form" method="POST" class="flex-1 overflow-y-auto px-6 py-5 space-y-4">
      @csrf
      <input type="hidden" id="fin-tx-method" name="_method" value="POST">

      {{-- Type --}}
      <div>
        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
          {{ __('app.fin_type') }} <span class="text-rose-500">*</span>
        </label>
        <div class="flex gap-3">
          @foreach(['income' => ['icon' => 'arrow_downward', 'color' => 'emerald', 'label' => __('app.fin_type_income')],
                    'expense' => ['icon' => 'arrow_upward', 'color' => 'rose', 'label' => __('app.fin_type_expense')]] as $val => $opt)
            <label class="flex-1 cursor-pointer">
              <input type="radio" name="type" value="{{ $val }}" class="sr-only peer" {{ $val === 'income' ? 'checked' : '' }}>
              <div class="flex items-center gap-2 p-3 rounded-xl border-2 border-slate-200 dark:border-slate-600
                   peer-checked:border-{{ $opt['color'] }}-500 peer-checked:bg-{{ $opt['color'] }}-50 dark:peer-checked:bg-{{ $opt['color'] }}-900/20 transition-colors">
                <span class="material-icons text-[18px] text-{{ $opt['color'] }}-500">{{ $opt['icon'] }}</span>
                <span class="text-sm font-medium text-slate-700 dark:text-slate-300">{{ $opt['label'] }}</span>
              </div>
            </label>
          @endforeach
        </div>
      </div>

      {{-- Description --}}
      <div>
        <label for="fin-tx-description" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
          {{ __('app.fin_description') }} <span class="text-rose-500">*</span>
        </label>
        <input type="text" id="fin-tx-description" name="description"
          placeholder="{{ __('app.fin_description_ph') }}" maxlength="255"
          class="w-full text-sm rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-700 dark:text-slate-300 px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-primary/30 placeholder-slate-400">
      </div>

      {{-- Amount + Date (side by side) --}}
      <div class="grid grid-cols-2 gap-4">
        <div>
          <label for="fin-tx-amount" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
            {{ __('app.fin_amount') }} <span class="text-rose-500">*</span>
          </label>
          <input type="number" id="fin-tx-amount" name="amount" min="0.01" step="0.01"
            class="w-full text-sm rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-700 dark:text-slate-300 px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-primary/30">
        </div>
        <div>
          <label for="fin-tx-date" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
            {{ __('app.fin_transaction_date') }} <span class="text-rose-500">*</span>
          </label>
          <input type="date" id="fin-tx-date" name="transaction_date"
            class="w-full text-sm rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-700 dark:text-slate-300 px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-primary/30">
        </div>
      </div>

      {{-- Category --}}
      <div>
        <label for="fin-tx-category" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
          {{ __('app.fin_category') }}
        </label>
        <input type="text" id="fin-tx-category" name="category" maxlength="100"
          placeholder="{{ __('app.fin_category_ph') }}"
          list="fin-modal-category-datalist"
          class="w-full text-sm rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-700 dark:text-slate-300 px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-primary/30 placeholder-slate-400">
        <datalist id="fin-modal-category-datalist">
          @foreach($categories as $cat)
            <option value="{{ $cat }}">
          @endforeach
        </datalist>
      </div>

      {{-- Notes --}}
      <div>
        <label for="fin-tx-notes" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
          {{ __('app.fin_notes') }}
        </label>
        <textarea id="fin-tx-notes" name="notes" rows="2" maxlength="1000"
          placeholder="{{ __('app.fin_notes_ph') }}"
          class="w-full text-sm rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-700 dark:text-slate-300 px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-primary/30 placeholder-slate-400 resize-none"></textarea>
      </div>
    </form>

    {{-- Footer --}}
    <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-slate-200 dark:border-slate-700 flex-shrink-0">
      <button type="button" onclick="closeTxModal()"
        class="px-4 py-2 text-sm font-medium text-slate-600 dark:text-slate-400 bg-slate-100 dark:bg-slate-700 rounded-lg hover:opacity-80 transition-opacity">
        {{ __('app.btn_cancel') }}
      </button>
      <button type="button" onclick="submitTxForm()"
        class="px-5 py-2 text-sm font-medium bg-primary text-white rounded-lg hover:opacity-90 transition-opacity shadow-sm">
        {{ __('app.btn_save') }}
      </button>
    </div>
  </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════
     2. Delete Transaction Modal
═══════════════════════════════════════════════════════════════════════ --}}
<div id="fin-delete-modal"
  class="fixed inset-0 z-50 hidden bg-black/50 backdrop-blur-sm flex items-center justify-center p-4"
  role="dialog" aria-modal="true">
  <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl w-full max-w-sm p-6"
       onclick="event.stopPropagation()">
    <div class="flex items-start gap-4 mb-5">
      <div class="flex-shrink-0 w-11 h-11 rounded-full bg-rose-100 dark:bg-rose-900/30 flex items-center justify-center">
        <span class="material-icons text-rose-500 text-[22px]">delete_outline</span>
      </div>
      <div>
        <h3 class="text-base font-semibold text-slate-800 dark:text-slate-100">{{ __('app.fin_delete_transaction') }}</h3>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1" id="fin-delete-desc">{{ __('app.fin_delete_transaction_body') }}</p>
      </div>
    </div>
    <form id="fin-delete-form" method="POST">
      @csrf @method('DELETE')
      <div class="flex gap-3 justify-end">
        <button type="button" onclick="closeDeleteModal()"
          class="px-4 py-2 text-sm font-medium text-slate-600 dark:text-slate-400 bg-slate-100 dark:bg-slate-700 rounded-lg hover:opacity-80">
          {{ __('app.btn_cancel') }}
        </button>
        <button type="submit"
          class="px-4 py-2 text-sm font-medium bg-rose-600 text-white rounded-lg hover:opacity-90 transition-opacity">
          {{ __('app.btn_yes_delete') }}
        </button>
      </div>
    </form>
  </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════
     3. Generate Report Modal
═══════════════════════════════════════════════════════════════════════ --}}
<div id="fin-gen-modal"
  class="fixed inset-0 z-50 hidden bg-black/50 backdrop-blur-sm flex items-center justify-center p-4"
  role="dialog" aria-modal="true">
  <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl w-full max-w-sm p-6"
       onclick="event.stopPropagation()">
    <div class="flex items-center justify-between mb-5">
      <h3 class="text-base font-semibold text-slate-800 dark:text-slate-100">{{ __('app.fin_generate_report') }}</h3>
      <button type="button" onclick="closeGenerateReportModal()"
        class="p-1.5 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700">
        <span class="material-icons text-[20px]">close</span>
      </button>
    </div>
    <form method="POST" action="{{ route('finance.reports.generate') }}" class="space-y-4">
      @csrf
      <div>
        <label for="fin-gen-month" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
          {{ __('app.fin_report_month') }} <span class="text-rose-500">*</span>
        </label>
        <select id="fin-gen-month" name="month" required
          class="w-full text-sm rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-700 dark:text-slate-300 px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-primary/30">
          <option value="">{{ __('app.fin_select_month') }}</option>
          @foreach(range(1,12) as $m)
            <option value="{{ $m }}" {{ $m == $currentMonth ? 'selected' : '' }}>
              {{ \Carbon\Carbon::create(null, $m, 1)->format('F') }}
            </option>
          @endforeach
        </select>
      </div>
      <div>
        <label for="fin-gen-year" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
          {{ __('app.fin_report_year') }} <span class="text-rose-500">*</span>
        </label>
        <select id="fin-gen-year" name="year" required
          class="w-full text-sm rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-700 dark:text-slate-300 px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-primary/30">
          @foreach(range(now()->year, 2020) as $y)
            <option value="{{ $y }}" {{ $y == $currentYear ? 'selected' : '' }}>{{ $y }}</option>
          @endforeach
        </select>
      </div>
      <div class="flex gap-3 justify-end pt-1">
        <button type="button" onclick="closeGenerateReportModal()"
          class="px-4 py-2 text-sm font-medium text-slate-600 dark:text-slate-400 bg-slate-100 dark:bg-slate-700 rounded-lg hover:opacity-80">
          {{ __('app.btn_cancel') }}
        </button>
        <button type="submit"
          class="px-5 py-2 text-sm font-medium bg-primary text-white rounded-lg hover:opacity-90 transition-opacity shadow-sm">
          {{ __('app.fin_generate_report') }}
        </button>
      </div>
    </form>
  </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════
     4. Opening Balance Modal
═══════════════════════════════════════════════════════════════════════ --}}
<div id="fin-ob-modal"
  class="fixed inset-0 z-50 hidden bg-black/50 backdrop-blur-sm flex items-center justify-center p-4"
  role="dialog" aria-modal="true">
  <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl w-full max-w-sm p-6"
       onclick="event.stopPropagation()">
    <div class="flex items-center justify-between mb-5">
      <h3 class="text-base font-semibold text-slate-800 dark:text-slate-100">{{ __('app.fin_opening_balance') }}</h3>
      <button type="button" onclick="closeOpeningBalanceModal()"
        class="p-1.5 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700">
        <span class="material-icons text-[20px]">close</span>
      </button>
    </div>
    <p id="fin-ob-period" class="text-sm text-slate-500 dark:text-slate-400 mb-4"></p>
    <form id="fin-ob-form" method="POST" class="space-y-4">
      @csrf @method('PATCH')
      <div>
        <label for="fin-ob-value" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
          {{ __('app.fin_opening_balance') }} <span class="text-rose-500">*</span>
        </label>
        <input type="number" id="fin-ob-value" name="opening_balance" min="0" step="0.01" required
          class="w-full text-sm rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-700 dark:text-slate-300 px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-primary/30">
      </div>
      <div class="flex gap-3 justify-end pt-1">
        <button type="button" onclick="closeOpeningBalanceModal()"
          class="px-4 py-2 text-sm font-medium text-slate-600 dark:text-slate-400 bg-slate-100 dark:bg-slate-700 rounded-lg hover:opacity-80">
          {{ __('app.btn_cancel') }}
        </button>
        <button type="submit"
          class="px-5 py-2 text-sm font-medium bg-primary text-white rounded-lg hover:opacity-90 transition-opacity shadow-sm">
          {{ __('app.btn_save') }}
        </button>
      </div>
    </form>
  </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════
     5. Revise Report Modal
═══════════════════════════════════════════════════════════════════════ --}}
<div id="fin-revise-modal"
  class="fixed inset-0 z-50 hidden bg-black/50 backdrop-blur-sm flex items-center justify-center p-4"
  role="dialog" aria-modal="true">
  <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl w-full max-w-md p-6"
       onclick="event.stopPropagation()">
    <div class="flex items-center justify-between mb-4">
      <h3 class="text-base font-semibold text-slate-800 dark:text-slate-100">{{ __('app.fin_revise_report') }}</h3>
      <button type="button" onclick="closeReviseReportModal()"
        class="p-1.5 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700">
        <span class="material-icons text-[20px]">close</span>
      </button>
    </div>
    <p class="text-sm text-slate-500 dark:text-slate-400 mb-4">
      {{ __('app.fin_period') }}: <span id="fin-revise-period" class="font-medium text-slate-700 dark:text-slate-300"></span>
    </p>
    <form id="fin-revise-form" method="POST" class="space-y-4">
      @csrf @method('PATCH')
      <div>
        <label for="fin-revise-notes" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
          {{ __('app.fin_revision_notes') }}
        </label>
        <textarea id="fin-revise-notes" name="notes" rows="3" maxlength="1000"
          placeholder="{{ __('app.fin_revision_notes_ph') }}"
          class="w-full text-sm rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-700 dark:text-slate-300 px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-primary/30 placeholder-slate-400 resize-none"></textarea>
      </div>
      <div class="flex gap-3 justify-end pt-1">
        <button type="button" onclick="closeReviseReportModal()"
          class="px-4 py-2 text-sm font-medium text-slate-600 dark:text-slate-400 bg-slate-100 dark:bg-slate-700 rounded-lg hover:opacity-80">
          {{ __('app.btn_cancel') }}
        </button>
        <button type="submit"
          class="px-5 py-2 text-sm font-medium bg-amber-500 text-white rounded-lg hover:opacity-90 transition-opacity shadow-sm">
          {{ __('app.fin_revise_report') }}
        </button>
      </div>
    </form>
  </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════
     JavaScript
═══════════════════════════════════════════════════════════════════════ --}}
<script>
// ── Transaction Modal ─────────────────────────────────────────────────────────
function openAddTransactionModal() {
  const form = document.getElementById('fin-tx-form');
  form.action = '{{ route('finance.transactions.store') }}';
  document.getElementById('fin-tx-method').value = 'POST';
  document.getElementById('fin-tx-modal-title').textContent = '{{ __('app.fin_add_transaction') }}';
  form.reset();
  // Default date = today
  document.getElementById('fin-tx-date').value = new Date().toISOString().split('T')[0];
  document.getElementById('fin-tx-modal').classList.remove('hidden');
  document.getElementById('fin-tx-modal').classList.add('flex');
}

function openEditTransactionModal(data) {
  const form = document.getElementById('fin-tx-form');
  const routeBase = '{{ url('/finance/transactions') }}';
  form.action = routeBase + '/' + data.id;
  document.getElementById('fin-tx-method').value = 'PUT';
  document.getElementById('fin-tx-modal-title').textContent = '{{ __('app.fin_edit_transaction') }}';

  // Fill fields
  const typeRadios = form.querySelectorAll('input[name="type"]');
  typeRadios.forEach(r => r.checked = (r.value === data.type));
  document.getElementById('fin-tx-description').value = data.description || '';
  document.getElementById('fin-tx-amount').value = data.amount || '';
  document.getElementById('fin-tx-date').value = data.transaction_date || '';
  document.getElementById('fin-tx-category').value = data.category || '';
  document.getElementById('fin-tx-notes').value = data.notes || '';

  document.getElementById('fin-tx-modal').classList.remove('hidden');
  document.getElementById('fin-tx-modal').classList.add('flex');
}

function closeTxModal() {
  document.getElementById('fin-tx-modal').classList.add('hidden');
  document.getElementById('fin-tx-modal').classList.remove('flex');
}

function submitTxForm() {
  document.getElementById('fin-tx-form').submit();
}

// ── Delete Modal ──────────────────────────────────────────────────────────────
function openDeleteTransactionModal(id, description) {
  const form = document.getElementById('fin-delete-form');
  form.action = '{{ url('/finance/transactions') }}/' + id;
  document.getElementById('fin-delete-desc').textContent =
    '{{ __('app.fin_delete_transaction_body') }} "' + description + '"';
  document.getElementById('fin-delete-modal').classList.remove('hidden');
  document.getElementById('fin-delete-modal').classList.add('flex');
}

function closeDeleteModal() {
  document.getElementById('fin-delete-modal').classList.add('hidden');
  document.getElementById('fin-delete-modal').classList.remove('flex');
}

// ── Generate Report Modal ─────────────────────────────────────────────────────
function openGenerateReportModal() {
  document.getElementById('fin-gen-modal').classList.remove('hidden');
  document.getElementById('fin-gen-modal').classList.add('flex');
}

function closeGenerateReportModal() {
  document.getElementById('fin-gen-modal').classList.add('hidden');
  document.getElementById('fin-gen-modal').classList.remove('flex');
}

// ── Opening Balance Modal ─────────────────────────────────────────────────────
function openOpeningBalanceModal(data) {
  const form = document.getElementById('fin-ob-form');
  form.action = '{{ url('/finance/reports') }}/' + data.id + '/opening-balance';
  document.getElementById('fin-ob-period').textContent = data.period;
  document.getElementById('fin-ob-value').value = data.opening_balance || 0;
  document.getElementById('fin-ob-modal').classList.remove('hidden');
  document.getElementById('fin-ob-modal').classList.add('flex');
}

function closeOpeningBalanceModal() {
  document.getElementById('fin-ob-modal').classList.add('hidden');
  document.getElementById('fin-ob-modal').classList.remove('flex');
}

// ── Revise Report Modal ───────────────────────────────────────────────────────
function openReviseReportModal(id, period) {
  const form = document.getElementById('fin-revise-form');
  form.action = '{{ url('/finance/reports') }}/' + id + '/revise';
  document.getElementById('fin-revise-period').textContent = period;
  document.getElementById('fin-revise-notes').value = '';
  document.getElementById('fin-revise-modal').classList.remove('hidden');
  document.getElementById('fin-revise-modal').classList.add('flex');
}

function closeReviseReportModal() {
  document.getElementById('fin-revise-modal').classList.add('hidden');
  document.getElementById('fin-revise-modal').classList.remove('flex');
}

// ── Close on backdrop click ───────────────────────────────────────────────────
['fin-tx-modal','fin-delete-modal','fin-gen-modal','fin-ob-modal','fin-revise-modal'].forEach(id => {
  const el = document.getElementById(id);
  if (el) el.addEventListener('click', function(e) {
    if (e.target === this) {
      this.classList.add('hidden');
      this.classList.remove('flex');
    }
  });
});
</script>
