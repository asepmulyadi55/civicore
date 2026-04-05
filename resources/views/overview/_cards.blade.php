{{-- Summary cards: identity, active fee, total paid this year --}}
<div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-6">

  {{-- Identity Card --}}
  <div
    class="col-span-1 md:col-span-2 bg-white dark:bg-slate-900 p-6 rounded-xl border border-slate-200 dark:border-slate-800 flex items-center justify-between shadow-sm">
    <div class="flex items-center gap-4">
      <div class="w-16 h-16 bg-primary/10 text-primary rounded-xl flex items-center justify-center">
        <span class="material-icons text-3xl">person</span>
      </div>
      <div>
        <h2 class="text-lg font-bold">{{ $resident->fullname }}</h2>
        <p class="text-slate-500 text-sm">Unit: <span class="font-mono font-bold">{{ $resident->block?->name }} -
            {{ $resident->unit_number }}</span></p>
        <div class="mt-1 flex items-center gap-2">
          @if($resident->is_active)
            <span
              class="px-2 py-0.5 bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400 text-[10px] font-bold uppercase rounded">Active</span>
          @else
            <span
              class="px-2 py-0.5 bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-400 text-[10px] font-bold uppercase rounded">Inactive</span>
          @endif
          <span class="text-xs text-slate-400">• Resident since {{ $resident->created_at->format('M Y') }}</span>
        </div>
      </div>
    </div>
  </div>

  {{-- Active Fee Card --}}
  <div class="bg-primary text-white p-6 rounded-xl shadow-lg flex flex-col justify-between">
    <span class="text-sm font-medium opacity-80 uppercase tracking-wider">Active Monthly Fee</span>
    <div>
      <span class="text-3xl font-extrabold">{{ $currency }} {{ number_format($currentFee) }}</span>
      <p class="text-xs opacity-75 mt-1">
        Due on the
        {{ $dueDayLabel }}{{ (int) $dueDayLabel === 1 ? 'st' : ((int) $dueDayLabel === 2 ? 'nd' : ((int) $dueDayLabel === 3 ? 'rd' : 'th')) }}
        of every month
      </p>
    </div>
  </div>

  {{-- Total Paid This Year --}}
  <div
    class="bg-white dark:bg-slate-900 p-6 rounded-xl border border-slate-200 dark:border-slate-800 flex flex-col justify-between shadow-sm">
    <span class="text-sm font-medium text-slate-500 uppercase tracking-wider">Total Paid {{ $currentYear }}</span>
    <div>
      <span class="text-2xl font-bold">{{ $currency }} {{ number_format($totalPaidYear) }}</span>
      <div class="flex items-center gap-1 text-emerald-500 text-xs font-bold mt-1">
        <span class="material-icons text-xs">check_circle</span>
        <span>{{ $paidMonthsYear }} of 12 Months</span>
      </div>
    </div>
  </div>

</div>

{{-- Admin Memo / Community Notice --}}
@php $adminMemo = \App\Models\Setting::get('admin_memo', ''); @endphp
@if($adminMemo)
  <div class="p-4 bg-amber-50 dark:bg-amber-900/10 border border-amber-100 dark:border-amber-900/30 rounded-xl">
    <div class="flex items-center space-x-2 text-amber-700 dark:text-amber-400 mb-2">
      <span class="material-icons text-sm">sticky_note_2</span>
      <span class="text-xs font-bold uppercase tracking-wider">Notice</span>
    </div>
    <p class="text-sm text-amber-800 dark:text-amber-300 leading-relaxed">{{ $adminMemo }}</p>
  </div>
@endif