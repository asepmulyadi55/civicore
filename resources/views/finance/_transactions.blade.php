{{-- finance/_transactions.blade.php --}}
@php
  $fmt = fn($n) => number_format((float)$n, 0, ',', '.');
@endphp

{{-- Filters --}}
<form method="GET" action="{{ route('finance.index') }}" class="flex flex-wrap gap-3 items-end">
  <input type="hidden" name="tab" value="transactions">

  {{-- Type filter --}}
  <div class="flex flex-col gap-1">
    <label class="text-xs font-medium text-slate-500 dark:text-slate-400">{{ __('app.fin_filter_type') }}</label>
    <select name="tx_type"
      class="text-sm rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-700 dark:text-slate-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary/30">
      <option value="">{{ __('app.fin_all_types') }}</option>
      <option value="income"  {{ request('tx_type') === 'income'  ? 'selected' : '' }}>{{ __('app.fin_type_income') }}</option>
      <option value="expense" {{ request('tx_type') === 'expense' ? 'selected' : '' }}>{{ __('app.fin_type_expense') }}</option>
    </select>
  </div>

  {{-- Category filter --}}
  <div class="flex flex-col gap-1">
    <label class="text-xs font-medium text-slate-500 dark:text-slate-400">{{ __('app.fin_filter_category') }}</label>
    <input type="text" name="tx_category" value="{{ request('tx_category') }}"
      placeholder="{{ __('app.fin_category_ph') }}"
      class="text-sm rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-700 dark:text-slate-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary/30 w-44"
      list="fin-category-datalist">
    <datalist id="fin-category-datalist">
      @foreach($categories as $cat)
        <option value="{{ $cat }}">
      @endforeach
    </datalist>
  </div>

  {{-- Month filter --}}
  <div class="flex flex-col gap-1">
    <label class="text-xs font-medium text-slate-500 dark:text-slate-400">{{ __('app.fin_filter_month') }}</label>
    <input type="month" name="tx_month" value="{{ request('tx_month') }}"
      class="text-sm rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-700 dark:text-slate-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary/30">
  </div>

  <button type="submit"
    class="px-4 py-2 text-sm font-medium bg-primary text-white rounded-lg hover:opacity-90 transition-opacity">
    {{ __('app.btn_search') }}
  </button>

  @if(request()->hasAny(['tx_type', 'tx_category', 'tx_month']))
    <a href="{{ route('finance.index', ['tab' => 'transactions']) }}"
       class="px-4 py-2 text-sm font-medium text-slate-500 dark:text-slate-400 bg-slate-100 dark:bg-slate-700 rounded-lg hover:opacity-80 transition-opacity">
      {{ __('app.btn_clear') }}
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
