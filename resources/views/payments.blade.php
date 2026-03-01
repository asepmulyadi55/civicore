{{-- payments.blade.php — Orchestrator --}}
<x-layouts.app title="Payments"
  class="font-display bg-background-light dark:bg-background-dark text-slate-800 dark:text-slate-200 antialiased min-h-screen">

  <x-nav.sidebar active="payments" />

  {{-- Payment modals — pass controller vars as explicit props --}}
  <x-modals.record-payment :currency="$currency" :paidMonthsByResident="$paidMonthsByResident" :residents="$residents"
    :canApprove="$canApprove" />

  <main class="lg:ml-64 flex flex-col h-screen overflow-hidden">

    @include('payments._header')

    <div class="flex-1 overflow-y-auto p-8 space-y-6">

      {{-- Flash messages --}}
      @if(session('success'))
        <div
          class="p-4 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 rounded-xl flex items-center gap-3">
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

      @include('payments._stats')
      @include('payments._filters')
      @include('payments._table')

    </div>
  </main>

</x-layouts.app>