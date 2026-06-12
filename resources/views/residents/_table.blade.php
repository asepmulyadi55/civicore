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
          <x-ui.sort-th column="fullname" :label="__('app.table_household')" />
          <x-ui.sort-th column="house_status" :label="__('app.table_house_status')" class="hidden sm:table-cell" />
          <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider text-right">
            {{ __('app.table_monthly_fee') }}
          </th>
          <x-ui.sort-th column="is_active" :label="__('app.table_status')" />
          <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider text-center">
            {{ __('app.table_actions') }}
          </th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
        @forelse ($residents as $resident)
          @php
            $fee        = $resident->feeHistories->first();
            $feeAmount  = $fee?->amount ?? 0;
            $headMember = $resident->familyMembers->first(); // only head is eager-loaded in index
            $displayName = $headMember?->fullname ?? $resident->fullname;
            $initials   = collect(explode(' ', $displayName))->map(fn($w) => strtoupper($w[0] ?? ''))->take(2)->implode('');
            $blockLabel = $resident->block?->name . ' · ' . $resident->unit_number;
            $isBlockA   = $resident->block?->name === 'Block A';
            $houseStatusMap = ['owner_occupied' => ['label' => __('app.house_owner_occupied'), 'class' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400'], 'vacant' => ['label' => __('app.house_vacant'), 'class' => 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400'], 'rented' => ['label' => __('app.house_rented'), 'class' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400'], 'public_facility' => ['label' => __('app.house_status_public_facility'), 'class' => 'bg-teal-100 text-teal-700 dark:bg-teal-900/30 dark:text-teal-400'], 'developer' => ['label' => __('app.house_status_developer'), 'class' => 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400']];
            $houseStatus = $houseStatusMap[$resident->house_status ?? 'owner_occupied'] ?? $houseStatusMap['owner_occupied'];
          @endphp
          <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition-colors group"
            id="resident-row-{{ $resident->id }}">

            {{-- Household: head name + block/unit + member count --}}
            <td class="px-6 py-4">
              <div class="flex items-center gap-3">
                @if($resident->photoUrl())
                  <img src="{{ $resident->photoUrl() }}" alt="{{ $displayName }}"
                    class="w-9 h-9 rounded-full object-cover border-2 border-slate-200 dark:border-slate-700 flex-shrink-0">
                @else
                  <div class="w-9 h-9 rounded-full bg-primary/10 text-primary flex items-center justify-center text-xs font-bold flex-shrink-0">
                    {{ $initials }}
                  </div>
                @endif
                <div>
                  <div class="flex items-center gap-2">
                    <span class="text-sm font-semibold text-slate-900 dark:text-white">{{ $displayName }}</span>
                    @if($headMember)
                      <span class="hidden sm:inline text-[10px] font-bold text-primary bg-primary/10 px-1.5 py-0.5 rounded-full uppercase">Head</span>
                    @endif
                  </div>
                  <div class="flex items-center gap-2 mt-0.5">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium
                      {{ $isBlockA ? 'bg-primary/10 text-primary' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300' }}">
                      {{ $blockLabel }}
                    </span>
                    @if(($resident->family_members_count ?? 0) > 0)
                      <span class="text-[10px] text-slate-400">{{ $resident->family_members_count }} member{{ $resident->family_members_count > 1 ? 's' : '' }}</span>
                    @endif
                  </div>
                </div>
              </div>
            </td>

            {{-- House Status --}}
            <td class="px-6 py-4 hidden sm:table-cell">
              <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold {{ $houseStatus['class'] }}">
                {{ $houseStatus['label'] }}
              </span>
            </td>

            {{-- Monthly Fee --}}
            <td class="px-6 py-4 text-sm font-bold text-slate-900 dark:text-white text-right">
              {{ $currency }} {{ number_format($feeAmount, 0, ',', '.') }}
            </td>

            {{-- Status badge --}}
            <td class="px-6 py-4">
              @if ($resident->is_active)
                <span
                  class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400">
                  <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> {{ __('app.status_active') }}
                </span>
              @else
                <span
                  class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400">
                  <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span> {{ __('app.status_inactive') }}
                </span>
              @endif
            </td>

            {{-- Actions --}}
            <td class="px-6 py-4">
              <div class="flex items-center justify-center gap-1">

                {{-- Edit — navigate to full edit page --}}
                @if(auth()->user()->can('residents.edit'))
                <a href="{{ route('residents.edit', $resident) }}"
                  class="p-1.5 text-slate-400 hover:text-primary hover:bg-primary/5 rounded-lg transition-colors"
                  title="{{ __('app.title_edit_resident') }}">
                  <span class="material-icons text-lg">edit</span>
                </a>
                @endif

                {{-- Deactivate / Reactivate --}}
                @if(auth()->user()->can('residents.edit'))
                @if($resident->is_active)
                  <button
                    onclick="openResidentConfirmModal('deactivate', '{{ $resident->id }}', '{{ addslashes($resident->fullname) }}')"
                    class="p-1.5 text-slate-400 hover:text-amber-500 hover:bg-amber-50 dark:hover:bg-amber-900/20 rounded-lg transition-colors"
                    title="{{ __('app.title_deactivate_resident') }}">
                    <span class="material-icons text-lg">person_off</span>
                  </button>
                @else
                  <button disabled class="p-1.5 text-slate-200 dark:text-slate-700 rounded-lg cursor-not-allowed"
                    title="{{ __('app.title_already_inactive') }}">
                    <span class="material-icons text-lg">person_off</span>
                  </button>
                @endif
                @endif

                {{-- Delete permanently --}}
                @if(auth()->user()->can('residents.delete'))
                <button
                  onclick="openResidentConfirmModal('delete', '{{ $resident->id }}', '{{ addslashes($resident->fullname) }}')"
                  class="p-1.5 text-slate-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors"
                  title="{{ __('app.title_delete_permanently') }}">
                  <span class="material-icons text-lg">delete_forever</span>
                </button>
                @endif

              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="5" class="px-6 py-16 text-center">
              <div class="flex flex-col items-center gap-3 text-slate-400">
                <span class="material-icons text-5xl">people_outline</span>
                <p class="text-sm font-medium">{{ __('app.no_residents_found') }}</p>
                @if(request()->hasAny(['search', 'block_id', 'status']))
                  <a href="{{ route('residents.index') }}"
                    class="text-primary text-sm hover:underline">{{ __('app.clear_filters') }}</a>
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
    <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-800 flex flex-col sm:flex-row items-center sm:justify-between gap-3">
      <p class="text-sm text-slate-500 text-center sm:text-left">{{ __('app.showing') }} {{ $residents->firstItem() }}–{{ $residents->lastItem() }}
        {{ __('app.of') }}
        {{ $residents->total() }} {{ __('app.residents_lowercase') }}
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

        @php
          $lastPage    = $residents->lastPage();
          $currentPage = $residents->currentPage();
          $start       = max(1, $currentPage - 2);
          $end         = min($lastPage, $currentPage + 2);
        @endphp

        @if ($start > 1)
          <a href="{{ $residents->url(1) }}" class="px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 text-sm text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">1</a>
          @if ($start > 2)
            <span class="px-1 text-slate-400 text-sm">&hellip;</span>
          @endif
        @endif

        @for ($p = $start; $p <= $end; $p++)
          @if ($p === $currentPage)
            <span class="px-3 py-1.5 rounded-lg bg-primary text-white text-sm font-semibold">{{ $p }}</span>
          @else
            <a href="{{ $residents->url($p) }}" class="px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 text-sm text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">{{ $p }}</a>
          @endif
        @endfor

        @if ($end < $lastPage)
          @if ($end < $lastPage - 1)
            <span class="px-1 text-slate-400 text-sm">&hellip;</span>
          @endif
          <a href="{{ $residents->url($lastPage) }}" class="px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 text-sm text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">{{ $lastPage }}</a>
        @endif

        @if ($residents->hasMorePages())
          <a href="{{ $residents->nextPageUrl() }}"
            class="p-2 rounded-lg border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
            <span class="material-icons text-sm">chevron_right</span>
          </a>
        @else
          <button
            class="p-2 rounded-lg border border-slate-200 dark:border-slate-700 text-slate-300 dark:text-slate-600 cursor-not-allowed"
            disabled>
            <span class="material-icons text-sm">chevron_right</span>
          </button>
        @endif
      </div>
    </div>
  @endif
</div>