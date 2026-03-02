{{-- residents/_table.blade.php --}}

{{-- Flash messages --}}
@if (session('success'))
  <div
    class="mb-4 flex items-center gap-3 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 text-emerald-700 dark:text-emerald-400 px-4 py-3 rounded-lg text-sm font-medium">
    <span class="material-icons text-base">check_circle</span>
    {{ session('success') }}
  </div>
@endif



<div
  class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 overflow-hidden shadow-sm">
  <div class="overflow-x-auto">
    <table class="w-full text-left">
      <thead class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-800">
        <tr>
          <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Resident Name</th>
          <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Block / Unit</th>
          <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Phone Number</th>
          <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider text-right">Monthly Fee</th>
          <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Fee Since</th>
          <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Status</th>
          <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider text-center">Actions</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
        @forelse ($residents as $resident)
          @php
            $fee = $resident->feeHistories->first();
            $feeAmount = $fee?->amount ?? 0;
            $feeSince = $fee?->effective_from?->format('M Y') ?? '—';
            $initials = collect(explode(' ', $resident->fullname))->map(fn($w) => strtoupper($w[0]))->take(2)->implode('');
            $blockLabel = $resident->block?->name . ' · ' . $resident->unit_number;
            $isBlockA = $resident->block?->name === 'Block A';
          @endphp
          <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition-colors group"
            id="resident-row-{{ $resident->id }}">

            {{-- Name + Initials Avatar --}}
            <td class="px-6 py-4">
              <div class="flex items-center gap-3">
                <div
                  class="w-9 h-9 rounded-full bg-primary/10 text-primary flex items-center justify-center text-xs font-bold flex-shrink-0">
                  {{ $initials }}
                </div>
                <div>
                  <div class="text-sm font-semibold text-slate-900 dark:text-white">{{ $resident->fullname }}</div>
                  <div class="text-xs text-slate-400">Member since {{ $resident->created_at->format('Y') }}</div>
                </div>
              </div>
            </td>

            {{-- Block / Unit --}}
            <td class="px-6 py-4">
              <span
                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                              {{ $isBlockA ? 'bg-primary/10 text-primary' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300' }}">
                {{ $blockLabel }}
              </span>
            </td>

            {{-- Phone --}}
            <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-400">
              {{ $resident->phone ?? '—' }}
            </td>

            {{-- Monthly Fee --}}
            <td class="px-6 py-4 text-sm font-bold text-slate-900 dark:text-white text-right">
              {{ $currency }} {{ number_format($feeAmount, 0, ',', '.') }}
            </td>

            {{-- Fee Since --}}
            <td class="px-6 py-4 text-sm text-slate-500 dark:text-slate-400">{{ $feeSince }}</td>

            {{-- Status badge --}}
            <td class="px-6 py-4">
              @if ($resident->is_active)
                <span
                  class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400">
                  <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Active
                </span>
              @else
                <span
                  class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400">
                  <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span> Inactive
                </span>
              @endif
            </td>

            {{-- Actions --}}
            <td class="px-6 py-4">
              <div class="flex items-center justify-center gap-1">

                {{-- Edit --}}
                <button
                  onclick="openEditDrawer({{ $resident->id }}, {{ json_encode(['fullname' => $resident->fullname, 'phone' => $resident->phone, 'email' => $resident->email, 'block_id' => $resident->block_id, 'unit_number' => $resident->unit_number, 'is_active' => $resident->is_active]) }})"
                  class="p-1.5 text-slate-400 hover:text-primary hover:bg-primary/5 rounded-lg transition-colors"
                  title="Edit resident">
                  <span class="material-icons text-lg">edit</span>
                </button>

                {{-- Deactivate / Reactivate --}}
                @if($resident->is_active)
                  <button
                    onclick="openResidentConfirmModal('deactivate', {{ $resident->id }}, '{{ addslashes($resident->fullname) }}')"
                    class="p-1.5 text-slate-400 hover:text-amber-500 hover:bg-amber-50 dark:hover:bg-amber-900/20 rounded-lg transition-colors"
                    title="Deactivate resident">
                    <span class="material-icons text-lg">person_off</span>
                  </button>
                @else
                  <button disabled class="p-1.5 text-slate-200 dark:text-slate-700 rounded-lg cursor-not-allowed"
                    title="Already inactive">
                    <span class="material-icons text-lg">person_off</span>
                  </button>
                @endif

                {{-- Delete permanently --}}
                <button
                  onclick="openResidentConfirmModal('delete', {{ $resident->id }}, '{{ addslashes($resident->fullname) }}')"
                  class="p-1.5 text-slate-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors"
                  title="Delete permanently">
                  <span class="material-icons text-lg">delete_forever</span>
                </button>

              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="7" class="px-6 py-16 text-center">
              <div class="flex flex-col items-center gap-3 text-slate-400">
                <span class="material-icons text-5xl">people_outline</span>
                <p class="text-sm font-medium">No residents found.</p>
                @if(request()->hasAny(['search', 'block_id', 'status']))
                  <a href="{{ route('residents.index') }}" class="text-primary text-sm hover:underline">Clear filters</a>
                @endif
              </div>
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
  {{-- end overflow-x-auto --}}

  {{-- Pagination --}}
  @if($residents->hasPages())
    <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-800 flex items-center justify-between">
      <p class="text-sm text-slate-500">Showing {{ $residents->firstItem() }}–{{ $residents->lastItem() }} of
        {{ $residents->total() }} residents
      </p>
      <div class="flex items-center gap-1">
        @if ($residents->onFirstPage())
          <button
            class="p-2 rounded-lg border border-slate-200 dark:border-slate-700 text-slate-300 dark:text-slate-600 cursor-not-allowed"
            disabled>
            <span class="material-icons text-sm">chevron_left</span>
          </button>
        @else
          <a href="{{ $residents->previousPageUrl() }}"
            class="p-2 rounded-lg border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
            <span class="material-icons text-sm">chevron_left</span>
          </a>
        @endif

        @if ($residents->hasMorePages())
          <a href="{{ $residents->nextPageUrl() }}"
            class="p-2 rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
            <span class="material-icons text-sm">chevron_right</span>
          </a>
        @else
          <button class="p-2 rounded-lg border border-slate-200 dark:border-slate-700 text-slate-300 cursor-not-allowed"
            disabled>
            <span class="material-icons text-sm">chevron_right</span>
          </button>
        @endif
      </div>
    </div>
  @endif
</div>