{{-- finance/_transactions.blade.php --}}
@php
  $fmt = fn($n) => number_format((float)$n, 0, ',', '.');
@endphp

{{-- Filters --}}
<form method="GET" action="{{ route('finance.index') }}"
  class="bg-white dark:bg-slate-900 p-4 rounded-xl border border-slate-200 dark:border-slate-800 mb-6 flex flex-col sm:flex-row sm:flex-wrap sm:items-center gap-3">
  <input type="hidden" name="tab" value="transactions">
  
  {{-- Type filter --}}
  <div class="relative w-full sm:w-auto">
    <select name="tx_type"
      class="appearance-none w-full sm:w-auto bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 focus:border-primary focus:ring-2 focus:ring-primary/20 rounded-lg text-sm py-2 pl-4 pr-9 outline-none transition-all text-slate-600 dark:text-slate-300">
      <option value="">{{ __('app.fin_all_types') }}</option>
      <option value="income"  {{ request('tx_type') === 'income'  ? 'selected' : '' }}>{{ __('app.fin_type_income') }}</option>
      <option value="expense" {{ request('tx_type') === 'expense' ? 'selected' : '' }}>{{ __('app.fin_type_expense') }}</option>
    </select>
    <span class="material-icons absolute right-2.5 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400 text-[18px]">expand_more</span>
  </div>

  {{-- Category filter --}}
  <div class="relative w-full sm:w-auto">
    <input type="text" name="tx_category" value="{{ request('tx_category') }}"
      class="w-full sm:w-auto bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary px-4 py-2 text-slate-700 dark:text-slate-300 outline-none transition-all"
      placeholder="{{ __('app.fin_category_ph') }}"
      list="fin-category-datalist">
    <datalist id="fin-category-datalist">
      @foreach($categories as $cat)
        <option value="{{ $cat }}">
      @endforeach
    </datalist>
  </div>

  {{-- Month filter --}}
  <div class="relative w-full sm:w-auto">
    <select name="tx_month"
      class="appearance-none w-full sm:w-auto bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 focus:border-primary focus:ring-2 focus:ring-primary/20 rounded-lg text-sm py-2 pl-4 pr-9 outline-none transition-all text-slate-600 dark:text-slate-300">
      <option value="">{{ __('app.fin_select_month') }}</option>
      @php $txMonthVal = request('tx_month') ? (int)request('tx_month') : ''; @endphp
      @foreach(range(1,12) as $m)
        <option value="{{ $m }}" {{ (string)$txMonthVal === (string)$m ? 'selected' : '' }}>
          {{ \Carbon\Carbon::create(null, $m)->format('F') }}
        </option>
      @endforeach
    </select>
    <span class="material-icons absolute right-2.5 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400 text-[18px]">expand_more</span>
  </div>

  {{-- Year filter --}}
  <div class="relative w-full sm:w-auto">
    <select name="tx_year"
      class="appearance-none w-full sm:w-auto bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 focus:border-primary focus:ring-2 focus:ring-primary/20 rounded-lg text-sm py-2 pl-4 pr-9 outline-none transition-all text-slate-600 dark:text-slate-300">
      <option value="">{{ __('app.fin_all_years') }}</option>
      @foreach(range(now()->year, 2020) as $y)
        <option value="{{ $y }}" {{ request('tx_year') == $y ? 'selected' : '' }}>{{ $y }}</option>
      @endforeach
    </select>
    <span class="material-icons absolute right-2.5 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400 text-[18px]">expand_more</span>
  </div>

  {{-- Apply / Clear --}}
  <button type="submit"
    class="flex justify-center items-center gap-2 px-4 py-2 bg-primary hover:bg-primary/90 text-white rounded-lg text-sm font-semibold transition-all shadow-sm shadow-primary/20 w-full sm:w-auto">
    <span class="material-icons text-sm">search</span>
    {{ __('app.btn_apply') }}
  </button>
  
  @if(request()->hasAny(['tx_type', 'tx_category', 'tx_month', 'tx_year']))
    <a href="{{ route('finance.index', ['tab' => 'transactions']) }}"
      class="flex justify-center items-center gap-1 px-3 py-2 text-sm font-medium text-slate-500 hover:text-primary transition-colors w-full sm:w-auto">
      <span class="material-icons text-sm">close</span>
      {{ __('app.clear_filters') }}
    </a>
  @endif

</form>

{{-- Table --}}
<div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
  @if($transactions->isEmpty())
    <div class="text-center py-16 text-slate-400 dark:text-slate-500">
      <span class="material-icons text-4xl block mb-3">receipt_long</span>
      <p class="text-sm">{{ __('app.fin_no_transactions') }}</p>
    </div>
  @else
    <div class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead class="bg-slate-50 dark:bg-slate-700/50">
          <tr class="text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">
            <th class="px-4 py-3">{{ __('app.fin_transaction_date') }}</th>
            <th class="px-4 py-3">{{ __('app.fin_description') }}</th>
            <th class="px-4 py-3">{{ __('app.fin_category') }}</th>
            <th class="px-4 py-3">{{ __('app.fin_type') }}</th>
            <th class="px-4 py-3 text-right">{{ __('app.fin_amount') }}</th>
            <th class="px-4 py-3 text-right">{{ __('app.table_actions') }}</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
          @foreach($transactions as $tx)
            @php
              $badge   = $tx->typeBadge();
              $locked  = false; // will be evaluated on server for display hint only
            @endphp
            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors group">
              <td class="px-4 py-3 text-slate-600 dark:text-slate-400 whitespace-nowrap">
                {{ $tx->transaction_date->format('d M Y') }}
              </td>
              <td class="px-4 py-3">
                <p class="font-medium text-slate-700 dark:text-slate-300">{{ $tx->description }}</p>
                @if($tx->notes)
                  <p class="text-xs text-slate-400 dark:text-slate-500 truncate max-w-xs">{{ $tx->notes }}</p>
                @endif
              </td>
              <td class="px-4 py-3 text-slate-500 dark:text-slate-400">
                {{ $tx->category ?: '—' }}
              </td>
              <td class="px-4 py-3">
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $badge['class'] }}">
                  {{ $badge['label'] }}
                </span>
              </td>
              <td class="px-4 py-3 text-right font-semibold whitespace-nowrap
                {{ $tx->type === 'income' ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                {{ $tx->type === 'income' ? '+' : '−' }}{{ $currency }} {{ $fmt($tx->amount) }}
              </td>
              <td class="px-4 py-3 text-right">
                <div class="flex items-center justify-end gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                  @if($canManage)
                    <button type="button"
                      onclick="openEditTransactionModal({{ json_encode([
                        'id'               => $tx->id,
                        'type'             => $tx->type,
                        'category'         => $tx->category,
                        'amount'           => $tx->amount,
                        'description'      => $tx->description,
                        'notes'            => $tx->notes,
                        'transaction_date' => $tx->transaction_date->format('Y-m-d'),
                      ]) }})"
                      class="p-1.5 rounded-lg text-slate-400 hover:text-sky-600 hover:bg-sky-50 dark:hover:bg-sky-900/20 transition-colors"
                      title="{{ __('app.fin_edit_transaction') }}">
                      <span class="material-icons text-[18px]">edit</span>
                    </button>
                  @endif
                  @if(auth()->user()->can('finance.delete'))
                    <button type="button"
                      onclick="openDeleteTransactionModal('{{ $tx->id }}', {{ json_encode($tx->description) }})"
                      class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-900/20 transition-colors"
                      title="{{ __('app.btn_yes_delete') }}">
                      <span class="material-icons text-[18px]">delete_outline</span>
                    </button>
                  @endif
                </div>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>

    {{-- Pagination --}}
    @if($transactions->hasPages())
      <div class="px-4 py-3 border-t border-slate-100 dark:border-slate-700">
        {{ $transactions->links() }}
      </div>
    @endif
  @endif
</div>
