{{-- Unit Management Page for a Block --}}
<x-layouts.app :title="__('app.unit_management') . ' — ' . $block->name"
  class="font-display bg-background-light dark:bg-background-dark text-slate-800 dark:text-slate-200 antialiased min-h-screen">

  <x-nav.sidebar active="blocks" />

  <div class="lg:pl-64 min-h-screen bg-background-light dark:bg-background-dark flex flex-col">

    {{-- Header --}}
    <header
      class="h-16 bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between px-6 lg:px-8">
      <div class="flex items-center gap-3 min-w-0">
        <button class="lg:hidden p-2 rounded-lg border border-slate-200 dark:border-slate-800"
          onclick="toggleSidebar()">
          <span class="material-icons text-slate-500">menu</span>
        </button>
        {{-- Breadcrumb --}}
        <a href="{{ route('blocks.index') }}"
          class="hidden sm:flex items-center gap-1 text-sm text-slate-500 hover:text-primary transition-colors">
          <span class="material-icons text-base">apartment</span>
          <span>{{ __('app.block_management') }}</span>
        </a>
        <span class="hidden sm:inline text-slate-300 dark:text-slate-600">/</span>
        <h1 class="text-xl font-bold text-slate-900 dark:text-white truncate">{{ $block->name }}</h1>
        <span class="hidden sm:inline px-2.5 py-1 text-xs font-semibold bg-primary/10 text-primary rounded-lg whitespace-nowrap">
          {{ $totalCount }} {{ __('app.units_count') }}
        </span>
      </div>
      <div class="flex items-center gap-3 flex-shrink-0">
        @if(auth()->user()->can('blocks.edit'))
        <button onclick="openAddUnitModal()"
          class="flex items-center gap-2 bg-primary hover:bg-primary/90 text-white px-4 py-2 rounded-lg font-semibold transition-all shadow-sm shadow-primary/20 text-sm">
          <span class="material-icons text-sm">add</span>
          <span class="hidden sm:inline">{{ __('app.btn_add_unit') }}</span>
        </button>
        @endif
        <button
          class="p-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg hover:border-primary/50 transition-all"
          onclick="toggleDark()" title="Toggle dark mode">
          <span class="material-icons text-slate-500 text-[20px]">dark_mode</span>
        </button>
      </div>
    </header>

    {{-- Body --}}
    <main class="flex-1 p-6 lg:p-8 space-y-6">

      {{-- Flash: success --}}
      @if(session('success'))
        <div class="p-4 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 rounded-xl flex items-center gap-3">
          <span class="material-icons text-emerald-500">check_circle</span>
          <p class="text-sm text-emerald-700 dark:text-emerald-400">{{ session('success') }}</p>
        </div>
      @endif

      {{-- Flash: error (non-delete-unit) --}}
      @if(session('error'))
        <div class="p-4 bg-rose-50 dark:bg-rose-900/20 border border-rose-200 dark:border-rose-800 rounded-xl flex items-center gap-3">
          <span class="material-icons text-rose-500">error</span>
          <p class="text-sm text-rose-700 dark:text-rose-400">{{ session('error') }}</p>
        </div>
      @endif

      {{-- Stats Row --}}
      <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-4 text-center">
          <p class="text-3xl font-bold text-slate-900 dark:text-white">{{ $totalCount }}</p>
          <p class="text-xs text-slate-500 font-medium uppercase mt-1">{{ __('app.units_count') }}</p>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-xl border border-amber-200 dark:border-amber-800/40 p-4 text-center">
          <p class="text-3xl font-bold text-amber-500">{{ $ownerOccupiedCount }}</p>
          <p class="text-xs text-amber-500/80 font-medium uppercase mt-1">{{ __('app.house_status_owner_occupied') }}</p>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-xl border border-amber-200 dark:border-amber-800/40 p-4 text-center">
          <p class="text-3xl font-bold text-amber-500">{{ $rentedCount }}</p>
          <p class="text-xs text-amber-500/80 font-medium uppercase mt-1">{{ __('app.house_status_rented') }}</p>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-4 text-center">
          <p class="text-3xl font-bold text-slate-900 dark:text-white">{{ $vacantCount }}</p>
          <p class="text-xs text-slate-500 font-medium uppercase mt-1">{{ __('app.house_status_vacant') }}</p>
        </div>
      </div>

      {{-- Units Grid --}}
      @if($units->isEmpty())
        <div class="text-center py-24 bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800">
          <span class="material-icons text-5xl text-slate-300 dark:text-slate-600 block mb-4">home_work</span>
          <h2 class="text-lg font-bold text-slate-700 dark:text-slate-300">{{ __('app.no_units_yet') }}</h2>
          <p class="text-slate-500 mt-2 text-sm">{{ __('app.add_first_unit') }}</p>
          @if(auth()->user()->can('blocks.edit'))
          <button onclick="openAddUnitModal()"
            class="mt-6 inline-flex items-center gap-2 bg-primary hover:bg-primary/90 text-white px-5 py-2.5 rounded-xl font-semibold transition-all text-sm">
            <span class="material-icons text-sm">add</span>{{ __('app.btn_add_unit') }}
          </button>
          @endif
        </div>
      @else
        <form id="bulk-delete-units-form" action="{{ route('blocks.units.bulk-destroy', $block) }}" method="POST">
          @csrf
          @method('DELETE')

          {{-- Bulk Action Bar --}}
          <div id="bulk-action-bar-units" class="hidden mb-4 p-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl flex items-center justify-between shadow-sm transition-all">
            <div class="flex items-center gap-3">
              <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" id="select-all-units" class="w-4 h-4 rounded border-slate-300 text-primary focus:ring-primary/30 bg-slate-50 dark:bg-slate-800" onchange="toggleAllUnits(this)">
                <span class="text-sm font-semibold text-slate-700 dark:text-slate-300">{{ __('app.select_all') ?? 'Select All' }}</span>
              </label>
              <span class="text-sm text-slate-500 border-l border-slate-200 dark:border-slate-700 pl-3">
                <span id="selected-count-units">0</span> selected
              </span>
            </div>
            <button type="button" onclick="confirmBulkDelete(event, 'bulk-delete-units-form', 'Are you sure you want to delete the selected units?')" class="flex items-center gap-1.5 px-3 py-1.5 bg-rose-500 hover:bg-rose-600 text-white text-xs font-bold rounded-lg transition-colors">
              <span class="material-icons text-sm">delete</span> Delete Selected
            </button>
          </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
          @foreach($units as $unit)
            @php
              // $hasResident → resident is linked (used for: resident name display + delete guard)
              // $isNotVacant → house_status is not vacant (used for: icon + border color)
              $hasResident = $unit->householder !== null;
              $isNotVacant = $unit->house_status !== 'vacant';
              $houseStatusColors = [
                'owner_occupied' => 'bg-primary/10 text-primary',
                'rented'         => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
                'vacant'         => 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400',
                'public_facility'=> 'bg-teal-100 text-teal-700 dark:bg-teal-900/30 dark:text-teal-400',
                'developer'      => 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400',
              ];
              $statusColor = $houseStatusColors[$unit->house_status] ?? 'bg-slate-100 text-slate-500';
              $statusLabel = __('app.house_status_' . $unit->house_status);
            @endphp
              <div
              class="bg-white dark:bg-slate-900 rounded-xl border {{ $isNotVacant ? 'border-primary/30' : 'border-slate-200 dark:border-slate-800' }} shadow-sm p-4 flex flex-col gap-3 hover:shadow-md transition-all relative">

              {{-- Checkbox for bulk delete --}}
              @if(auth()->user()->can('blocks.edit'))
              <div class="absolute top-3 right-3 z-10">
                <input type="checkbox" name="ids[]" value="{{ $unit->id }}" class="unit-checkbox w-4 h-4 rounded border-slate-300 text-primary focus:ring-primary/30 bg-slate-50 dark:bg-slate-800 cursor-pointer" onchange="updateBulkActionBarUnits()">
              </div>
              @endif

              {{-- Unit number + status badge --}}
              <div class="flex items-start justify-between gap-2 mr-6">
                <div class="flex items-center gap-2 min-w-0">
                  <div class="w-9 h-9 rounded-lg {{ $isNotVacant ? 'bg-primary/10 text-primary' : 'bg-slate-100 text-slate-400 dark:bg-slate-800' }} flex items-center justify-center flex-shrink-0">
                    <span class="material-icons text-base">{{ $isNotVacant ? 'person' : 'home' }}</span>
                  </div>
                  <div class="min-w-0">
                    <p class="font-bold text-slate-900 dark:text-white text-sm">{{ $unit->unit_number }}</p>
                    <span class="inline-block text-[10px] font-semibold px-1.5 py-0.5 rounded {{ $statusColor }} mt-0.5">
                      {{ $statusLabel }}
                    </span>
                  </div>
                </div>
                @if($unit->is_active)
                  <span class="flex-shrink-0 text-[9px] font-bold uppercase bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 px-1.5 py-0.5 rounded">
                    {{ __('app.status_active') }}
                  </span>
                @else
                  <span class="flex-shrink-0 text-[9px] font-bold uppercase bg-slate-200 dark:bg-slate-700 text-slate-500 px-1.5 py-0.5 rounded">
                    {{ __('app.status_inactive') }}
                  </span>
                @endif
              </div>

              {{-- Resident info --}}
              <div class="flex-1">
                @if($hasResident)
                  <div class="flex items-center gap-2">
                    <span class="material-icons text-sm text-slate-400">person_outline</span>
                    <a href="{{ route('householders.index', ['search' => $unit->householder->fullname]) }}"
                      class="text-xs text-slate-700 dark:text-slate-300 font-medium hover:text-primary transition-colors truncate">
                      {{ $unit->householder->fullname }}
                    </a>
                  </div>
                @else
                  <p class="text-xs text-slate-400 italic">{{ __('app.unit_no_resident') }}</p>
                @endif
                @if($unit->notes)
                  <p class="text-[11px] text-slate-400 mt-1 line-clamp-2">{{ $unit->notes }}</p>
                @endif
              </div>

              {{-- Actions --}}
              @if(auth()->user()->can('blocks.edit'))
              <div class="flex gap-2 pt-2 border-t border-slate-100 dark:border-slate-800">
                <button
                  onclick="openEditUnitModal('{{ $unit->id }}','{{ addslashes($unit->unit_number) }}','{{ $unit->house_status }}',{{ $unit->is_active ? 'true' : 'false' }},'{{ addslashes($unit->notes ?? '') }}')"
                  class="flex-1 flex items-center justify-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-slate-600 dark:text-slate-300 bg-slate-50 dark:bg-slate-800 hover:bg-primary/10 hover:text-primary rounded-lg transition-colors border border-slate-200 dark:border-slate-700">
                  <span class="material-icons text-sm">edit</span>{{ __('app.btn_edit') }}
                </button>
                <button
                  onclick="openDeleteUnitModal('{{ $unit->id }}','{{ addslashes($unit->unit_number) }}',{{ $hasResident ? 'true' : 'false' }})"
                  class="flex items-center justify-center p-1.5 text-slate-400 hover:text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-950/20 rounded-lg transition-colors border border-slate-200 dark:border-slate-700">
                  <span class="material-icons text-sm">delete_outline</span>
                </button>
              </div>
              @endif
            </div>
          @endforeach
        </div>
        </form>

        <script>
          function toggleAllUnits(source) {
            const checkboxes = document.querySelectorAll('.unit-checkbox');
            checkboxes.forEach(cb => { cb.checked = source.checked; });
            updateBulkActionBarUnits();
          }

          function updateBulkActionBarUnits() {
            const checkboxes = document.querySelectorAll('.unit-checkbox');
            const checkedCount = Array.from(checkboxes).filter(cb => cb.checked).length;
            const actionBar = document.getElementById('bulk-action-bar-units');
            const selectAll = document.getElementById('select-all-units');
            const countLabel = document.getElementById('selected-count-units');

            if (checkedCount > 0) {
              actionBar.classList.remove('hidden');
              actionBar.classList.add('flex');
            } else {
              actionBar.classList.add('hidden');
              actionBar.classList.remove('flex');
            }

            if (countLabel) countLabel.textContent = checkedCount;
            if (selectAll) selectAll.checked = (checkedCount === checkboxes.length && checkboxes.length > 0);
          }
        </script>
      @endif
    </main>
  </div>

  {{-- ===== Add Unit Modal ===== --}}
  <div id="modal-add-unit"
    class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-2xl w-full max-w-md overflow-hidden">
      <div class="flex items-center justify-between px-6 pt-6 pb-4 border-b border-slate-100 dark:border-slate-800">
        <div>
          <h3 class="text-lg font-bold text-slate-900 dark:text-white">{{ __('app.add_unit_title') }}</h3>
          <p class="text-xs text-slate-500 mt-0.5">{{ $block->name }}</p>
        </div>
        <button onclick="closeAddUnitModal()" class="p-2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 rounded-lg">
          <span class="material-icons">close</span>
        </button>
      </div>
      <form method="POST" action="{{ route('blocks.units.store', $block) }}" class="p-6 space-y-4" novalidate>
        @csrf
        <input type="hidden" name="_edit_unit" value="">
        {{-- Unit Number --}}
        <div class="flex flex-col gap-2">
          <label class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">
            {{ __('app.unit_number_label') }} <span class="text-red-500">*</span>
          </label>
          <input id="add-unit-number" type="text" name="unit_number"
            placeholder="{{ __('app.unit_number_placeholder') }}"
            value="{{ old('unit_number') }}"
            class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-primary/40 focus:border-primary outline-none transition-all @error('unit_number') border-red-500 @enderror"
            oninput="clearUErr('js-um-unit_number')" maxlength="30">
          @error('unit_number')
            <p class="text-xs text-red-500 flex items-center gap-1">
              <span class="material-icons text-xs">error_outline</span> {{ $message }}
            </p>
          @enderror
          <p id="js-um-unit_number" class="hidden text-xs text-red-500 items-center gap-1">
            <span class="material-icons text-xs">error_outline</span> {{ __('app.err_unit') }}
          </p>
        </div>
        {{-- House Status --}}
        <div>
          <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">
            {{ __('app.house_status_label') }}
          </label>
          <select name="house_status"
            class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-primary/40 focus:border-primary outline-none transition-all">
            <option value="owner_occupied" {{ old('house_status','owner_occupied')==='owner_occupied'?'selected':'' }}>
              {{ __('app.house_status_owner_occupied') }}
            </option>
            <option value="rented" {{ old('house_status')==='rented'?'selected':'' }}>
              {{ __('app.house_status_rented') }}
            </option>
            <option value="vacant" {{ old('house_status')==='vacant'?'selected':'' }}>
              {{ __('app.house_status_vacant') }}
            </option>
            <option value="public_facility" {{ old('house_status')==='public_facility'?'selected':'' }}>
              {{ __('app.house_status_public_facility') }}
            </option>
            <option value="developer" {{ old('house_status')==='developer'?'selected':'' }}>
              {{ __('app.house_status_developer') }}
            </option>
          </select>
        </div>
        {{-- Notes --}}
        <div>
          <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">
            {{ __('app.unit_notes_label') }}
            <span class="text-slate-400 font-normal">{{ __('app.description_optional') }}</span>
          </label>
          <textarea name="notes" rows="2"
            placeholder="{{ __('app.unit_notes_placeholder') }}"
            class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-primary/40 focus:border-primary outline-none transition-all resize-none">{{ old('notes') }}</textarea>
        </div>
        {{-- Active --}}
        <label class="flex items-center gap-3 cursor-pointer">
          <input type="hidden" name="is_active" value="0">
          <input type="checkbox" name="is_active" value="1" {{ old('is_active','1')==='1'?'checked':'' }}
            class="w-4 h-4 rounded text-primary focus:ring-primary">
          <div>
            <p class="text-sm font-semibold text-slate-700 dark:text-slate-300">{{ __('app.unit_active') }}</p>
            <p class="text-xs text-slate-400">{{ __('app.unit_inactive_hint') }}</p>
          </div>
        </label>
        <div class="flex gap-3 pt-2">
          <button type="button" onclick="closeAddUnitModal()"
            class="flex-1 px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 text-sm font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition-all">
            {{ __('app.btn_cancel') }}
          </button>
          <button type="submit"
            class="flex-1 px-4 py-2.5 rounded-xl bg-primary hover:bg-primary/90 text-sm font-bold text-white transition-all">
            {{ __('app.btn_save_block') }}
          </button>
        </div>
      </form>
    </div>
  </div>

  {{-- ===== Edit Unit Modal ===== --}}
  <div id="modal-edit-unit"
    class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-2xl w-full max-w-md overflow-hidden">
      <div class="flex items-center justify-between px-6 pt-6 pb-4 border-b border-slate-100 dark:border-slate-800">
        <h3 class="text-lg font-bold text-slate-900 dark:text-white">{{ __('app.edit_unit_title') }}</h3>
        <button onclick="closeEditUnitModal()" class="p-2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 rounded-lg">
          <span class="material-icons">close</span>
        </button>
      </div>
      <form id="edit-unit-form" method="POST" action="" class="p-6 space-y-4" novalidate>
        @csrf @method('PUT')
        <input type="hidden" name="_edit_unit" value="1">
        <div class="flex flex-col gap-2">
          <label class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">
            {{ __('app.unit_number_label') }} <span class="text-red-500">*</span>
          </label>
          <input id="edit-unit-number" type="text" name="unit_number"
            class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-primary/40 focus:border-primary outline-none transition-all"
            oninput="clearUErr('js-uem-unit_number')" maxlength="30">
          <p id="js-uem-unit_number" class="hidden text-xs text-red-500 items-center gap-1">
            <span class="material-icons text-xs">error_outline</span> {{ __('app.err_unit') }}
          </p>
        </div>
        <div>
          <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">
            {{ __('app.house_status_label') }}
          </label>
          <select id="edit-unit-status" name="house_status"
            class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-primary/40 focus:border-primary outline-none transition-all">
            <option value="owner_occupied">{{ __('app.house_status_owner_occupied') }}</option>
            <option value="rented">{{ __('app.house_status_rented') }}</option>
            <option value="vacant">{{ __('app.house_status_vacant') }}</option>
            <option value="public_facility">{{ __('app.house_status_public_facility') }}</option>
            <option value="developer">{{ __('app.house_status_developer') }}</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">
            {{ __('app.unit_notes_label') }}
            <span class="text-slate-400 font-normal">{{ __('app.description_optional') }}</span>
          </label>
          <textarea id="edit-unit-notes" name="notes" rows="2"
            placeholder="{{ __('app.unit_notes_placeholder') }}"
            class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-primary/40 focus:border-primary outline-none transition-all resize-none"></textarea>
        </div>
        <label class="flex items-center gap-3 cursor-pointer">
          <input type="hidden" name="is_active" value="0">
          <input id="edit-unit-active" type="checkbox" name="is_active" value="1"
            class="w-4 h-4 rounded text-primary focus:ring-primary">
          <div>
            <p class="text-sm font-semibold text-slate-700 dark:text-slate-300">{{ __('app.unit_active') }}</p>
            <p class="text-xs text-slate-400">{{ __('app.unit_inactive_hint') }}</p>
          </div>
        </label>
        <div class="flex gap-3 pt-2">
          <button type="button" onclick="closeEditUnitModal()"
            class="flex-1 px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 text-sm font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition-all">
            {{ __('app.btn_cancel') }}
          </button>
          <button type="submit"
            class="flex-1 px-4 py-2.5 rounded-xl bg-primary hover:bg-primary/90 text-sm font-bold text-white transition-all">
            {{ __('app.btn_save_block') }}
          </button>
        </div>
      </form>
    </div>
  </div>

  {{-- ===== Delete Unit Modal ===== --}}
  <div id="modal-delete-unit"
    class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-2xl w-full max-w-sm overflow-hidden">
      <div id="delete-unit-blocked" class="hidden p-6 flex flex-col items-center text-center">
        <div class="w-14 h-14 rounded-full bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center mb-4">
          <span class="material-icons text-amber-600 dark:text-amber-400 text-2xl">person_off</span>
        </div>
        <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-1">{{ __('app.delete_unit_title') }}</h3>
        <p class="text-sm text-slate-500 mb-1">{{ __('app.unit_delete_has_resident') }}</p>
        <p class="text-sm text-slate-500">{{ __('app.unit_delete_move_first') }}</p>
        <button onclick="closeDeleteUnitModal()"
          class="mt-5 px-6 py-2.5 rounded-xl bg-primary hover:bg-primary/90 text-sm font-bold text-white transition-all">
          {{ __('app.btn_close') }}
        </button>
      </div>
      <div id="delete-unit-confirm" class="hidden">
        <div class="p-6 flex flex-col items-center text-center">
          <div class="w-14 h-14 rounded-full bg-rose-100 dark:bg-rose-900/30 flex items-center justify-center mb-4">
            <span class="material-icons text-rose-600 dark:text-rose-400 text-2xl">delete_forever</span>
          </div>
          <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-1">{{ __('app.delete_unit_title') }}</h3>
          <p class="text-sm text-slate-500 dark:text-slate-400" id="delete-unit-confirm-text"></p>
        </div>
        <div class="flex gap-3 px-6 pb-6">
          <button type="button" onclick="closeDeleteUnitModal()"
            class="flex-1 px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 text-sm font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition-all">
            {{ __('app.btn_cancel') }}
          </button>
          <form id="delete-unit-form" method="POST" action="" class="flex-1">
            @csrf @method('DELETE')
            <button type="submit"
              class="w-full px-4 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-sm font-bold text-white transition-all">
              {{ __('app.btn_yes_delete') }}
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>

  {{-- Session-triggered delete-blocked modal (from redirect) --}}
  @if(session('error_delete_unit'))
    @php
      $blockedUnit = \App\Models\Unit::with('householder')->find(session('error_delete_unit'));
    @endphp
    @if($blockedUnit)
    <script>
      document.addEventListener('DOMContentLoaded', function () {
        const modal = document.getElementById('modal-delete-unit');
        document.getElementById('delete-unit-blocked').classList.remove('hidden');
        document.getElementById('delete-unit-confirm').classList.add('hidden');
        modal.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
      });
    </script>
    @endif
  @endif

  <script>
    const unitBaseUrl = "{{ url('/blocks/' . $block->id . '/units') }}";
    const deleteConfirmTpl = @json(__('app.unit_delete_confirm'));

    // ---- Add ----
    function openAddUnitModal() {
      document.getElementById('modal-add-unit').classList.remove('hidden');
      document.body.classList.add('overflow-hidden');
    }
    function closeAddUnitModal() {
      document.getElementById('modal-add-unit').classList.add('hidden');
      document.body.classList.remove('overflow-hidden');
    }

    // ---- Edit ----
    function openEditUnitModal(id, unitNumber, houseStatus, isActive, notes) {
      const form = document.getElementById('edit-unit-form');
      form.action = unitBaseUrl + '/' + id;
      document.getElementById('edit-unit-number').value = unitNumber;
      document.getElementById('edit-unit-status').value = houseStatus;
      document.getElementById('edit-unit-notes').value = notes;
      document.getElementById('edit-unit-active').checked = isActive;
      document.getElementById('modal-edit-unit').classList.remove('hidden');
      document.body.classList.add('overflow-hidden');
    }
    function closeEditUnitModal() {
      document.getElementById('modal-edit-unit').classList.add('hidden');
      document.body.classList.remove('overflow-hidden');
    }

    // ---- Delete ----
    function openDeleteUnitModal(id, unitNumber, isOccupied) {
      const modal = document.getElementById('modal-delete-unit');
      const blocked = document.getElementById('delete-unit-blocked');
      const confirm = document.getElementById('delete-unit-confirm');
      if (isOccupied) {
        blocked.classList.remove('hidden');
        confirm.classList.add('hidden');
      } else {
        blocked.classList.add('hidden');
        confirm.classList.remove('hidden');
        document.getElementById('delete-unit-form').action = unitBaseUrl + '/' + id;
        document.getElementById('delete-unit-confirm-text').textContent =
          deleteConfirmTpl.replace(':unit', unitNumber);
      }
      modal.classList.remove('hidden');
      document.body.classList.add('overflow-hidden');
    }
    function closeDeleteUnitModal() {
      document.getElementById('modal-delete-unit').classList.add('hidden');
      document.body.classList.remove('overflow-hidden');
    }

    // Close on backdrop click / Escape
    ['modal-add-unit', 'modal-edit-unit', 'modal-delete-unit'].forEach(id => {
      const el = document.getElementById(id);
      el.addEventListener('click', e => { if (e.target === el) { el.classList.add('hidden'); document.body.classList.remove('overflow-hidden'); } });
    });
    document.addEventListener('keydown', e => {
      if (e.key === 'Escape') {
        closeAddUnitModal(); closeEditUnitModal(); closeDeleteUnitModal();
      }
    });

    function clearUErr(id) {
      const el = document.getElementById(id);
      if (el) { el.classList.add('hidden'); el.classList.remove('flex'); }
    }
    function showUErr(id) {
      const el = document.getElementById(id);
      if (el) { el.classList.remove('hidden'); el.classList.add('flex'); }
    }

    document.getElementById('modal-add-unit')?.querySelector('form')?.addEventListener('submit', function (e) {
      const num = document.getElementById('add-unit-number');
      if (!num || num.value.trim() === '') { showUErr('js-um-unit_number'); e.preventDefault(); }
      else clearUErr('js-um-unit_number');
    });
    document.getElementById('edit-unit-form')?.addEventListener('submit', function (e) {
      const num = document.getElementById('edit-unit-number');
      if (!num || num.value.trim() === '') { showUErr('js-uem-unit_number'); e.preventDefault(); }
      else clearUErr('js-uem-unit_number');
    });

    // Re-open add modal on validation error
    @if($errors->any() && !old('_edit_unit'))
    document.addEventListener('DOMContentLoaded', () => openAddUnitModal());
    @endif
  </script>

</x-layouts.app>

