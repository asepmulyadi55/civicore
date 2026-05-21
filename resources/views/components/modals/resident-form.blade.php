{{-- ============================================================
  components/modals/resident-form.blade.php
  Add Resident Modal + Edit Resident Modal (with Monthly Fee)
  Trigger: openAddResidentModal() / openEditDrawer(id, data)
============================================================ --}}
@props([
  'blocks'   => [],
  'currency' => \App\Models\Setting::get('currency_symbol', 'Rp'),
])

{{-- ════════════════════════════════════════════════════════════════ --}}
{{-- ADD RESIDENT MODAL                                              --}}
{{-- ════════════════════════════════════════════════════════════════ --}}
<div id="add-resident-modal"
  class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm hidden items-center justify-center p-4"
  onclick="if(event.target===this) closeAddResidentModal()">

  <div class="bg-white dark:bg-slate-900 w-full max-w-xl rounded-2xl shadow-2xl flex flex-col max-h-[92vh] overflow-hidden">

    {{-- Header --}}
    <div class="px-8 py-6 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center shrink-0">
      {{-- Add New Resident (Header label update) --}}
      <div>
        <h2 class="text-2xl font-extrabold text-slate-900 dark:text-slate-100">{{ __('app.add_new_resident') }}</h2>
        <p class="text-sm text-slate-400 mt-0.5">{{ __('app.add_resident_desc') }}</p>
      </div>
      <button onclick="closeAddResidentModal()"
        class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors p-1">
        <span class="material-icons">close</span>
      </button>
    </div>

    {{-- Body --}}
    <div class="flex-1 overflow-y-auto px-8 py-6">
      <form id="form-add-resident" method="POST" action="{{ route('residents.store') }}" class="space-y-5" novalidate>
        @csrf
        <input type="hidden" name="_form" value="add" />

        {{-- Full Name --}}
        <div class="flex flex-col gap-2">
          <label class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">
            {{ __('app.full_name') }} <span class="text-red-500">*</span>
          </label>
          <div class="relative">
            <span class="material-icons absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-lg">person</span>
            <input id="add-fullname" type="text" name="fullname" value="{{ old('fullname') }}" placeholder="{{ __('app.eg_name') }}"
              class="w-full pl-10 pr-4 py-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none dark:text-white @error('fullname') border-red-500 @enderror"
              oninput="clearRmErr('js-rm-fullname')" />
          </div>
          @error('fullname') <p class="text-xs text-red-500">{{ $message }}</p> @enderror
          <p id="js-rm-fullname" class="hidden text-xs text-red-500 items-center gap-1">
            <span class="material-icons text-xs">error_outline</span> {{ __('app.err_fullname') }}
          </p>
        </div>

        {{-- Phone --}}
        <div class="flex flex-col gap-2">
          <label class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">{{ __('app.phone_number') }}</label>
          <div class="relative">
            <span class="material-icons absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-lg">phone</span>
            <input type="tel" name="phone" value="{{ old('phone') }}" placeholder="{{ __('app.eg_phone') }}"
              class="w-full pl-10 pr-4 py-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none dark:text-white @error('phone') border-red-500 @enderror" />
          </div>
          @error('phone') <p class="text-xs text-red-500">{{ $message }}</p> @enderror
        </div>

        {{-- Email --}}
        <div class="flex flex-col gap-2">
          <label class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">
            {{ __('app.email_address') }} <span class="font-normal text-slate-400 normal-case">{{ __('app.email_optional_hint') }}</span>
          </label>
          <div class="relative">
            <span class="material-icons absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-lg">email</span>
            <input type="email" name="email" value="{{ old('email') }}" placeholder="{{ __('app.eg_email') }}"
              class="w-full pl-10 pr-4 py-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none dark:text-white @error('email') border-red-500 @enderror" />
          </div>
          @error('email') <p class="text-xs text-red-500">{{ $message }}</p> @enderror
        </div>

        <div class="border-t border-slate-100 dark:border-slate-800"></div>

        {{-- Block + Unit --}}
        <div class="grid grid-cols-2 gap-4">
          <div class="flex flex-col gap-2">
            <label class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">
              {{ __('app.block') }} <span class="text-red-500">*</span>
            </label>
            <div class="relative">
              <span class="material-icons absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-lg">domain</span>
              <select id="add-block_id" name="block_id"
                class="w-full appearance-none pl-10 pr-9 py-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none dark:text-white @error('block_id') border-red-500 @enderror"
                onchange="clearRmErr('js-rm-block_id'); loadUnitsForAdd(this.value)">
                <option value="">{{ __('app.select_block') }}</option>
                @foreach($blocks as $block)
                  <option value="{{ $block->id }}" {{ old('block_id') == $block->id ? 'selected' : '' }}>{{ $block->name }}</option>
                @endforeach
              </select>
              <span class="material-icons absolute right-2.5 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400 text-[18px]">expand_more</span>
            </div>
            @error('block_id') <p class="text-xs text-red-500">{{ $message }}</p> @enderror
            <p id="js-rm-block_id" class="hidden text-xs text-red-500 items-center gap-1">
              <span class="material-icons text-xs">error_outline</span> {{ __('app.err_block') }}
            </p>
          </div>
          <div class="flex flex-col gap-2">
            <label class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">
              {{ __('app.unit_no') }} <span class="text-red-500">*</span>
            </label>
            <div class="relative">
              <span class="material-icons absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-lg">meeting_room</span>
              <select id="add-unit_id" name="unit_id" disabled
                class="w-full appearance-none pl-10 pr-9 py-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none dark:text-white @error('unit_id') border-red-500 @enderror"
                onchange="clearRmErr('js-rm-unit_id')">
                <option value="">{{ __('app.select_block') }}</option>
              </select>
              <span class="material-icons absolute right-2.5 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400 text-[18px]">expand_more</span>
            </div>
            @error('unit_id') <p class="text-xs text-red-500">{{ $message }}</p> @enderror
            <p id="js-rm-unit_id" class="hidden text-xs text-red-500 items-center gap-1">
              <span class="material-icons text-xs">error_outline</span> {{ __('app.err_unit') }}
            </p>
          </div>
        </div>

        <div class="border-t border-slate-100 dark:border-slate-800"></div>

        {{-- Family Card Number --}}
        <div class="flex flex-col gap-2">
          <label class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">
            {{ __('app.no_kk') }} <span class="font-normal normal-case text-slate-400">{{ __('app.optional') }}</span>
          </label>
          <input type="text" name="family_card_number" value="{{ old('family_card_number') }}"
            placeholder="{{ __('app.kk_placeholder') }}" maxlength="20"
            class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none dark:text-white">
        </div>

        <div class="border-t border-slate-100 dark:border-slate-800"></div>

        {{-- Monthly Fee + Start Month --}}
        <div class="grid grid-cols-2 gap-4">
          <div class="flex flex-col gap-2">
            <label class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">
              {{ __('app.monthly_fee') }} ({{ $currency }}) <span class="text-red-500">*</span>
            </label>
            <div class="relative">
              <span class="material-icons absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-lg">payments</span>
              <input id="add-monthly_fee" type="number" name="monthly_fee" value="{{ old('monthly_fee') }}" placeholder="500000" min="0" step="1000"
                class="w-full pl-10 pr-4 py-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none dark:text-white @error('monthly_fee') border-red-500 @enderror"
                oninput="clearRmErr('js-rm-monthly_fee')" />
            </div>
            @error('monthly_fee') <p class="text-xs text-red-500">{{ $message }}</p> @enderror
            <p id="js-rm-monthly_fee" class="hidden text-xs text-red-500 items-center gap-1">
              <span class="material-icons text-xs">error_outline</span> {{ __('app.err_monthly_fee') }}
            </p>
          </div>
          <div class="flex flex-col gap-2">
            <label class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">
              {{ __('app.fee_start_month') }} <span class="text-red-500">*</span>
            </label>
            <div class="relative">
              <span class="material-icons absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-lg">calendar_today</span>
              <input id="add-fee_start" type="month" name="fee_start" value="{{ old('fee_start', now()->format('Y-m')) }}"
                class="w-full pl-10 pr-4 py-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none dark:text-white dark:[color-scheme:dark] @error('fee_start') border-red-500 @enderror"
                oninput="clearRmErr('js-rm-fee_start')" />
            </div>
            @error('fee_start') <p class="text-xs text-red-500">{{ $message }}</p> @enderror
            <p id="js-rm-fee_start" class="hidden text-xs text-red-500 items-center gap-1">
              <span class="material-icons text-xs">error_outline</span> {{ __('app.err_fee_start') }}
            </p>
          </div>
        </div>

        {{-- Footer --}}
        <div class="flex gap-3 pt-2">
          <button type="button" onclick="closeAddResidentModal()"
            class="flex-1 py-3 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-bold text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
            {{ __('app.btn_cancel') }}
          </button>
          <button type="submit"
            class="flex-1 py-3 bg-primary text-white rounded-xl text-sm font-bold hover:bg-primary/90 shadow-lg shadow-primary/20 transition-all flex items-center justify-center gap-2 active:scale-95">
            <span class="material-icons text-sm">person_add</span>
            {{ __('app.btn_save_resident') }}
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

{{-- ════════════════════════════════════════════════════════════════ --}}
{{-- EDIT RESIDENT MODAL                                             --}}
{{-- ════════════════════════════════════════════════════════════════ --}}
<div id="edit-resident-modal"
  class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm hidden items-center justify-center p-4"
  onclick="if(event.target===this) closeEditResidentModal()">

  <div class="bg-white dark:bg-slate-900 w-full max-w-xl rounded-2xl shadow-2xl flex flex-col max-h-[92vh] overflow-hidden">

    {{-- Header --}}
    <div class="px-8 py-6 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center shrink-0">
      <div>
        <h2 class="text-2xl font-extrabold text-slate-900 dark:text-slate-100">{{ __('app.edit_resident') }}</h2>
        <div class="flex items-center gap-2 mt-0.5 text-sm">
          <span id="erm-unit-badge" class="px-2 py-0.5 bg-primary/10 text-primary rounded-lg text-xs font-bold"></span>
          <span id="erm-name-sub" class="text-slate-400 text-xs"></span>
        </div>
      </div>
      <button onclick="closeEditResidentModal()"
        class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors p-1">
        <span class="material-icons">close</span>
      </button>
    </div>

    {{-- Body --}}
    <div class="flex-1 overflow-y-auto px-8 py-6">
      <form id="form-edit-resident" method="POST" action="" class="space-y-5">
        @csrf
        @method('PUT')
        <input type="hidden" name="_form" value="edit" />

        {{-- Full Name --}}
        <div class="flex flex-col gap-2">
          <label class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">
            {{ __('app.full_name') }} <span class="text-red-500">*</span>
          </label>
          <div class="relative">
            <span class="material-icons absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-lg">person</span>
            <input type="text" id="edit-fullname" name="fullname"
              class="w-full pl-10 pr-4 py-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none dark:text-white" />
          </div>
        </div>

        {{-- Phone --}}
        <div class="flex flex-col gap-2">
          <label class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">{{ __('app.phone_number') }}</label>
          <div class="relative">
            <span class="material-icons absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-lg">phone</span>
            <input type="tel" id="edit-phone" name="phone"
              class="w-full pl-10 pr-4 py-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none dark:text-white" />
          </div>
        </div>

        {{-- Email --}}
        <div class="flex flex-col gap-2">
          <label class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">
            {{ __('app.email_address') }} <span class="font-normal text-slate-400 normal-case">(optional)</span>
          </label>
          <div class="relative">
            <span class="material-icons absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-lg">email</span>
            <input type="email" id="edit-email" name="email"
              class="w-full pl-10 pr-4 py-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none dark:text-white" />
          </div>
        </div>

        <div class="border-t border-slate-100 dark:border-slate-800"></div>

        {{-- Block + Unit --}}
        <div class="grid grid-cols-2 gap-4">
          <div class="flex flex-col gap-2">
            <label class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">
              {{ __('app.block') }} <span class="text-red-500">*</span>
            </label>
            <div class="relative">
              <span class="material-icons absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-lg">domain</span>
              <select id="edit-block_id" name="block_id"
                onchange="loadUnitsForEdit(this.value, null)"
                class="w-full appearance-none pl-10 pr-9 py-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none dark:text-white">
                @foreach($blocks as $block)
                  <option value="{{ $block->id }}">{{ $block->name }}</option>
                @endforeach
              </select>
              <span class="material-icons absolute right-2.5 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400 text-[18px]">expand_more</span>
            </div>
          </div>
          <div class="flex flex-col gap-2">
            <label class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">
              {{ __('app.unit_no') }} <span class="text-red-500">*</span>
            </label>
            <div class="relative">
              <span class="material-icons absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-lg">meeting_room</span>
              <select id="edit-unit_id" name="unit_id" disabled
                class="w-full appearance-none pl-10 pr-9 py-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none dark:text-white">
                <option value="">{{ __('app.units_loading') }}</option>
              </select>
              <span class="material-icons absolute right-2.5 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400 text-[18px]">expand_more</span>
            </div>
          </div>
        </div>

        <div class="border-t border-slate-100 dark:border-slate-800"></div>

        {{-- Monthly Fee update (optional — only creates new FeeHistory if filled) --}}
        <div class="rounded-xl bg-amber-50 dark:bg-amber-900/10 border border-amber-200 dark:border-amber-700/30 p-4 space-y-4">
          <div class="flex items-start gap-2">
            <span class="material-icons text-amber-500 text-lg mt-0.5">info</span>
            <div>
              <p class="text-sm font-semibold text-amber-700 dark:text-amber-400">{{ __('app.update_monthly_fee') }}</p>
              <p class="text-xs text-amber-600/80 dark:text-amber-500/80 mt-0.5">
                {{ __('app.update_fee_hint') }}
              </p>
            </div>
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div class="flex flex-col gap-2">
              <label class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">
                {{ __('app.new_monthly_fee') }} ({{ $currency }})
              </label>
              <div class="relative">
                <span class="material-icons absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-lg">payments</span>
                <input type="number" id="edit-monthly_fee" name="new_monthly_fee" min="0" step="1000"
                  placeholder="{{ __('app.leave_blank_keep_current') }}"
                  class="w-full pl-10 pr-4 py-3 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none dark:text-white" />
              </div>
            </div>
            <div class="flex flex-col gap-2">
              <label class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">
                {{ __('app.effective_from') }}
              </label>
              <div class="relative">
                <span class="material-icons absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-lg">calendar_today</span>
                <input type="month" id="edit-fee_start" name="new_fee_start" value="{{ now()->format('Y-m') }}"
                  class="w-full pl-10 pr-4 py-3 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none dark:text-white dark:[color-scheme:dark]" />
              </div>
            </div>
          </div>
        </div>

        {{-- Active status --}}
        <label class="flex items-center gap-3 cursor-pointer p-3 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
          <input type="checkbox" id="edit-is_active" name="is_active" value="1"
            class="w-4 h-4 text-primary rounded border-slate-300 focus:ring-primary/20" />
          <div>
            <span class="text-sm font-semibold text-slate-700 dark:text-slate-300">{{ __('app.active_resident') }}</span>
            <p class="text-xs text-slate-400">{{ __('app.uncheck_inactive_hint') }}</p>
          </div>
        </label>

        {{-- Footer --}}
        <div class="flex gap-3 pt-2">
          <button type="button" onclick="closeEditResidentModal()"
            class="flex-1 py-3 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-bold text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
            {{ __('app.btn_cancel') }}
          </button>
          <button type="submit"
            class="flex-1 py-3 bg-primary text-white rounded-xl text-sm font-bold hover:bg-primary/90 shadow-lg shadow-primary/20 transition-all flex items-center justify-center gap-2 active:scale-95">
            <span class="material-icons text-sm">save</span>
            {{ __('app.btn_save_changes') }}
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
  // ── Add Resident Modal ────────────────────────────────────────────
  function openAddResidentModal() {
    const el = document.getElementById('add-resident-modal');
    el.classList.remove('hidden'); el.classList.add('flex');
    document.body.classList.add('overflow-hidden');
  }
  function closeAddResidentModal() {
    const el = document.getElementById('add-resident-modal');
    el.classList.add('hidden'); el.classList.remove('flex');
    document.body.classList.remove('overflow-hidden');
  }

  // ── Edit Resident Modal ───────────────────────────────────────────
  const apiBlocksUrl = "{{ url('/api/blocks') }}";

  async function loadUnitsIntoSelect(blockId, selectId, currentUnitId) {
    const sel = document.getElementById(selectId);
    if (!sel) return;
    if (!blockId) {
      sel.innerHTML = '<option value="">{{ __('app.select_block') }}</option>';
      sel.disabled = true;
      return;
    }
    sel.innerHTML = '<option value="">{{ __('app.units_loading') }}</option>';
    sel.disabled = true;
    try {
      const qs = currentUnitId ? '?current_unit_id=' + currentUnitId : '';
      const res = await fetch(`${apiBlocksUrl}/${blockId}/units${qs}`);
      const units = await res.json();
      if (!units.length) {
        sel.innerHTML = '<option value="">{{ __('app.no_units_in_block') }}</option>';
        return;
      }
      sel.innerHTML = '<option value="">{{ __('app.select_unit') }}</option>';
      units.forEach(u => {
        const opt = document.createElement('option');
        opt.value = u.id;
        opt.textContent = u.unit_number + (u.is_occupied ? ' ({{ __('app.occupied_count') }})' : '');
        opt.disabled = u.is_occupied;
        if (u.id === currentUnitId) opt.selected = true;
        sel.appendChild(opt);
      });
      sel.disabled = false;
    } catch {
      sel.innerHTML = '<option value="">{{ __('app.select_unit') }}</option>';
      sel.disabled = false;
    }
  }

  function loadUnitsForAdd(blockId) {
    loadUnitsIntoSelect(blockId, 'add-unit_id', null);
  }

  async function loadUnitsForEdit(blockId, currentUnitId) {
    await loadUnitsIntoSelect(blockId, 'edit-unit_id', currentUnitId);
  }

  async function openEditDrawer(id, data) {
    document.getElementById('edit-fullname').value    = data.fullname;
    document.getElementById('edit-phone').value       = data.phone    || '';
    document.getElementById('edit-email').value       = data.email    || '';
    document.getElementById('edit-block_id').value    = data.block_id;
    document.getElementById('edit-is_active').checked = data.is_active;
    document.getElementById('edit-monthly_fee').value = '';
    document.getElementById('edit-fee_start').value   = '{{ now()->format("Y-m") }}';
    document.getElementById('erm-unit-badge').textContent = data.unit_number || '';
    document.getElementById('erm-name-sub').textContent   = data.fullname;
    document.getElementById('form-edit-resident').action  = `{{ url('/residents') }}/${id}`;
    const el = document.getElementById('edit-resident-modal');
    el.classList.remove('hidden'); el.classList.add('flex');
    document.body.classList.add('overflow-hidden');
    await loadUnitsForEdit(data.block_id, data.unit_id ?? null);
  }
  function closeEditResidentModal() {
    const el = document.getElementById('edit-resident-modal');
    el.classList.add('hidden'); el.classList.remove('flex');
    document.body.classList.remove('overflow-hidden');
  }

  // ── Backward-compat alias for header button ───────────────────────
  function openResidentDrawer() { openAddResidentModal(); }

  document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
      closeAddResidentModal();
      closeEditResidentModal();
    }
  });

  // Re-open add modal if validation fails (and reload units)
  @if($errors->any() && old('_form') === 'add')
    document.addEventListener('DOMContentLoaded', () => {
      openAddResidentModal();
      const preBlock = @json(old('block_id'));
      const preUnit  = @json(old('unit_id'));
      if (preBlock) loadUnitsIntoSelect(preBlock, 'add-unit_id', preUnit || null);
    });
  @endif

  // ── Resident modal: client-side validation ────────────────────────
  function clearRmErr(id) {
    const el = document.getElementById(id);
    if (el) { el.classList.add('hidden'); el.classList.remove('flex'); }
  }
  function showRmErr(id) {
    const el = document.getElementById(id);
    if (el) { el.classList.remove('hidden'); el.classList.add('flex'); }
  }

  document.getElementById('form-add-resident')?.addEventListener('submit', function (e) {
    let valid = true;
    const checks = [
      ['add-fullname',    'js-rm-fullname',    v => v.trim().length > 0],
      ['add-block_id',   'js-rm-block_id',    v => v !== ''],
      ['add-unit_id',    'js-rm-unit_id',     v => v !== ''],
      ['add-monthly_fee','js-rm-monthly_fee', v => v.trim().length > 0 && parseFloat(v) >= 0],
      ['add-fee_start',  'js-rm-fee_start',   v => v.trim().length > 0],
    ];
    checks.forEach(([fieldId, errId, fn]) => {
      const el = document.getElementById(fieldId);
      if (el && !fn(el.value)) { showRmErr(errId); valid = false; }
      else if (el) clearRmErr(errId);
    });
    if (!valid) e.preventDefault();
  });

  document.getElementById('form-edit-resident')?.addEventListener('submit', function (e) {
    let valid = true;
    const checks = [
      ['edit-fullname',    'js-erm-fullname',    v => v.trim().length > 0],
      ['edit-block_id', 'js-erm-block_id', v => v !== ''],
      ['edit-unit_id',  'js-erm-unit_id',  v => v !== ''],
    ];
    checks.forEach(([fieldId, errId, fn]) => {
      const el = document.getElementById(fieldId);
      if (el && !fn(el.value)) { showRmErr(errId); valid = false; }
      else if (el) clearRmErr(errId);
    });
    if (!valid) e.preventDefault();
  });
</script>
