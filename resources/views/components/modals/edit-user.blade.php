@php
  $roles = \App\Models\Role::orderBy('name')->get();
  $blocks = \App\Models\Block::active()->orderBy('name')->get();
@endphp
{{-- ============================================================
Modal: Edit User — same design as Create User
Trigger: openEditModal(id, name, username, email, roleId, blockId, unitNumber)
============================================================ --}}
<div id="modal-edit"
  class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden"
  onclick="closeModalOnBackdrop(event, 'modal-edit')">
  <div
    class="bg-white dark:bg-slate-900 w-full max-w-xl rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-800 overflow-hidden">

    {{-- Header --}}
    <div class="px-8 py-6 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
      <div>
        <h2 class="text-xl font-bold text-slate-900 dark:text-white">{{ __('app.edit_user_title') }}</h2>
        <p id="edit-subtitle" class="text-sm text-slate-500 mt-0.5">{{ __('app.edit_user_desc') }}</p>
      </div>
      <button class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors"
        onclick="closeModal('modal-edit')">
        <span class="material-icons">close</span>
      </button>
    </div>

    {{-- Form --}}
    <form id="form-edit-user" method="POST" action="" class="p-8 pt-6 space-y-5 max-h-[80vh] overflow-y-auto"
      novalidate>
      @csrf
      @method('PATCH')
      <input type="hidden" name="_form_type" value="edit" />
      <input type="hidden" id="edit-user-id-field" name="_user_id" value="" />

      {{-- Name --}}
      <div class="space-y-1.5">
        <label class="text-xs font-bold text-slate-500 uppercase">{{ __('app.full_name') }} <span
            class="text-red-500">*</span></label>
        <input id="edit-name" name="name" type="text"
          class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none dark:text-white @error('name') border-red-500 @enderror"
          placeholder="{{ __('app.eg_john_smith') }}" oninput="clearUErr('js-eu-name')" />
        @error('name')
          <p class="text-xs text-red-500 flex items-center gap-1 mt-1">
            <span class="material-icons text-xs">error_outline</span> {{ $message }}
          </p>
        @enderror
        <p id="js-eu-name" class="hidden text-xs text-red-500 items-center gap-1 mt-1">
          <span class="material-icons text-xs">error_outline</span> {{ __('app.err_fullname') }}
        </p>
      </div>

      {{-- Username --}}
      <div class="space-y-1.5">
        <label class="text-xs font-bold text-slate-500 uppercase">{{ __('app.username') }} <span
            class="text-red-500">*</span></label>
        <input id="edit-username" name="username" type="text"
          class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none dark:text-white @error('username') border-red-500 @enderror"
          placeholder="{{ __('app.eg_jsmith') }}" oninput="clearUErr('js-eu-username')" />
        @error('username')
          <p class="text-xs text-red-500 flex items-center gap-1 mt-1">
            <span class="material-icons text-xs">error_outline</span> {{ $message }}
          </p>
        @enderror
        <p id="js-eu-username" class="hidden text-xs text-red-500 items-center gap-1 mt-1">
          <span class="material-icons text-xs">error_outline</span> {{ __('app.err_username') }}
        </p>
      </div>

      {{-- Email --}}
      <div class="space-y-1.5">
        <label class="text-xs font-bold text-slate-500 uppercase">{{ __('app.email_address') }} <span
            class="text-red-500">*</span></label>
        <input id="edit-email" name="email" type="email"
          class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none dark:text-white @error('email') border-red-500 @enderror"
          placeholder="{{ __('app.eg_email_john') }}" oninput="clearUErr('js-eu-email')" />
        @error('email')
          <p class="text-xs text-red-500 flex items-center gap-1 mt-1">
            <span class="material-icons text-xs">error_outline</span> {{ $message }}
          </p>
        @enderror
        <p id="js-eu-email" class="hidden text-xs text-red-500 items-center gap-1 mt-1">
          <span class="material-icons text-xs">error_outline</span> {{ __('app.err_email') }}
        </p>
      </div>

      {{-- New Password (optional) --}}
      <div class="space-y-1.5">
        <label class="text-xs font-bold text-slate-500 uppercase flex justify-between items-center">
          {{ __('app.new_password') }}
          <span
            class="text-[10px] text-slate-400 lowercase font-normal italic">{{ __('app.leave_blank_keep_current') }}</span>
        </label>
        <div class="relative">
          <input id="edit-password" name="password" type="password" autocomplete="new-password"
            class="w-full px-4 py-2.5 pr-10 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none dark:text-white @error('password') border-red-500 @enderror"
            placeholder="{{ __('app.min_8_chars') }}" oninput="checkPwStrength(this.value, 'eu')" />
          <button type="button" onclick="togglePw('edit-password','edit-pw-icon')"
            class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition-colors">
            <span id="edit-pw-icon" class="material-icons text-lg">visibility_off</span>
          </button>
        </div>
        <div id="pw-req-eu" class="hidden mt-2 space-y-1">
          <p id="eu-req-length" class="flex items-center gap-1.5 text-xs text-slate-400"><span class="material-icons text-sm">radio_button_unchecked</span> At least 8 characters</p>
          <p id="eu-req-upper"  class="flex items-center gap-1.5 text-xs text-slate-400"><span class="material-icons text-sm">radio_button_unchecked</span> One uppercase letter</p>
          <p id="eu-req-lower"  class="flex items-center gap-1.5 text-xs text-slate-400"><span class="material-icons text-sm">radio_button_unchecked</span> One lowercase letter</p>
          <p id="eu-req-number" class="flex items-center gap-1.5 text-xs text-slate-400"><span class="material-icons text-sm">radio_button_unchecked</span> One number</p>
          <p id="eu-req-symbol" class="flex items-center gap-1.5 text-xs text-slate-400"><span class="material-icons text-sm">radio_button_unchecked</span> One special character</p>
        </div>
        @error('password')
          <p class="text-xs text-red-500 flex items-center gap-1 mt-1">
            <span class="material-icons text-xs">error_outline</span> {{ $message }}
          </p>
        @enderror
      </div>

      {{-- Role Grid --}}
      <div class="space-y-2">
        <label class="text-xs font-bold text-slate-500 uppercase">{{ __('app.system_role') }}</label>

        <div class="grid grid-cols-2 gap-2">
          {{-- No Role card --}}
          <label class="cursor-pointer group col-span-2 sm:col-span-1">
            <input class="peer sr-only edit-role-radio" name="role_id" type="radio" value="" />
            <div class="relative p-3 rounded-xl border-2 border-slate-200 dark:border-slate-700
              hover:border-primary/50 peer-checked:border-primary peer-checked:bg-primary/5
              transition-all h-full flex items-center gap-3">
              <div class="absolute top-2 right-2 opacity-0 peer-checked:opacity-100 text-primary transition-opacity">
                <span class="material-icons text-sm">check_circle</span>
              </div>
              <div class="w-9 h-9 rounded-lg bg-slate-100 dark:bg-slate-700 text-slate-400
                flex items-center justify-center flex-shrink-0">
                <span class="material-icons text-lg">block</span>
              </div>
              <div>
                <div class="font-bold text-slate-900 dark:text-white text-xs">{{ __('app.no_role') }}</div>
                <div class="text-[10px] text-slate-500 leading-snug">{{ __('app.no_system_access') }}</div>
              </div>
            </div>
          </label>
          @foreach ($roles as $role)
            <label class="cursor-pointer group">
              <input class="peer sr-only edit-role-radio" name="role_id" type="radio" value="{{ $role->id }}" />
              <div class="relative p-3 rounded-xl border-2 border-slate-200 dark:border-slate-700
                                      hover:border-primary/50 peer-checked:border-primary peer-checked:bg-primary/5
                                      transition-all h-full flex items-center gap-3">
                <div class="absolute top-2 right-2 opacity-0 peer-checked:opacity-100 text-primary transition-opacity">
                  <span class="material-icons text-sm">check_circle</span>
                </div>
                <div
                  class="w-9 h-9 rounded-lg {{ $role->bg_class }} {{ $role->text_class }}
                                        flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-transform">
                  <span class="material-icons text-lg">{{ $role->icon }}</span>
                </div>
                <div>
                  <div class="font-bold text-slate-900 dark:text-white text-xs">{{ $role->label }}</div>
                </div>
              </div>
            </label>
          @endforeach
        </div>
      </div>

      {{-- Household Assignment ──────────────────────────────── --}}
      <div class="rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden">
        <div class="px-4 py-3 bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-700">
          <p class="text-xs font-bold text-slate-500 uppercase tracking-wide">{{ __('app.household_assignment') }}</p>
          <p class="text-[11px] text-slate-400 mt-0.5">{{ __('app.household_assignment_autofill_desc') }}</p>
        </div>
        <div class="p-4 space-y-4">

          <div id="edit-resident-badge-found"
            class="hidden items-center gap-2 text-xs text-emerald-700 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-900/20 px-3 py-2 rounded-lg border border-emerald-200 dark:border-emerald-800">
            <span class="material-icons text-sm">check_circle</span>
            {{ __('app.resident_record_found_locked') }}
          </div>
          <div id="edit-resident-badge-notfound"
            class="hidden items-center gap-2 text-xs text-amber-700 dark:text-amber-400 bg-amber-50 dark:bg-amber-900/20 px-3 py-2 rounded-lg border border-amber-200 dark:border-amber-800">
            <span class="material-icons text-sm">info</span>
            {{ __('app.no_resident_record_short') }}
          </div>
          <div id="edit-resident-badge-loading"
            class="hidden items-center gap-2 text-xs text-slate-500 px-3 py-2 rounded-lg">
            <svg class="animate-spin h-3.5 w-3.5 text-slate-400" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
            </svg>
            {{ __('app.looking_up_resident') }}
          </div>

          {{-- Block Select --}}
          <div class="space-y-1.5">
            <label class="text-xs font-bold text-slate-500 uppercase">{{ __('app.table_block') }}</label>
            <div class="relative">
              <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <span class="material-icons text-slate-400 text-sm">location_city</span>
              </span>
              <select id="edit-block-id" name="block_id"
                class="w-full pl-10 pr-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm text-slate-900 dark:text-white focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none">
                <option value="">{{ __('app.no_block_assigned') }}</option>
                @foreach ($blocks as $block)
                  <option value="{{ $block->id }}">{{ $block->name }}</option>
                @endforeach
              </select>
            </div>
          </div>

          {{-- Unit Number --}}
          <div class="space-y-1.5">
            <label class="text-xs font-bold text-slate-500 uppercase">{{ __('app.unit_no') }}</label>
            <div class="relative">
              <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <span class="material-icons text-slate-400 text-sm">home</span>
              </span>
              <select id="edit-unit-number" name="unit_number"
                class="w-full pl-10 pr-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm text-slate-900 dark:text-white focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none">
                <option value="">— {{ __('app.select_block_first') }} —</option>
              </select>
            </div>
          </div>

        </div>
      </div>

      {{-- Actions --}}
      <div class="flex items-center gap-4 pt-2">
        <button type="button" onclick="closeModal('modal-edit')"
          class="flex-1 px-6 py-3 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-bold text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
          {{ __('app.btn_cancel') }}
        </button>
        <button type="submit"
          class="flex-1 px-6 py-3 bg-primary text-white rounded-xl text-sm font-bold hover:bg-primary/90 shadow-lg shadow-primary/20 transition-all flex items-center justify-center gap-2">
          <span class="material-icons text-sm">save</span>
          {{ __('app.btn_save_changes') }}
        </button>
      </div>

    </form>
  </div>
</div>

<script>
  // ── Edit-User form: client-side validation ────────────────────────
  document.getElementById('form-edit-user')?.addEventListener('submit', function (e) {
    let valid = true;
    const checks = [
      ['edit-name', 'js-eu-name', v => v.trim().length > 0],
      ['edit-username', 'js-eu-username', v => v.trim().length > 0],
      ['edit-email', 'js-eu-email', v => v.trim().length > 0 && v.includes('@')],
    ];
    checks.forEach(([fieldId, errId, fn]) => {
      const el = document.getElementById(fieldId);
      if (el && !fn(el.value)) {
        showUErr(errId);
        valid = false;
      } else if (el) clearUErr(errId);
    });
    if (!valid) e.preventDefault();
  });

  // ── Unit dropdown loader (shared by edit + approve modals) ──────────
  const USER_MODAL_UNITS_URL = '{{ url('/api/blocks') }}';

  function loadUnitsForUserModal(blockId, selectId, currentUnitNum) {
    const sel = document.getElementById(selectId);
    if (!sel) return Promise.resolve();
    sel.innerHTML = '<option value="">Loading…</option>';
    sel.disabled = true;
    if (!blockId) {
      sel.innerHTML = '<option value="">— {{ __('app.select_block_first') }} —</option>';
      sel.disabled = false;
      return Promise.resolve();
    }
    return fetch(`${USER_MODAL_UNITS_URL}/${blockId}/units`)
      .then(r => r.json())
      .then(units => {
        sel.innerHTML = '<option value="">— {{ __('app.select_unit') }} —</option>';
        units.forEach(u => {
          const opt = document.createElement('option');
          opt.value = u.unit_number;
          opt.textContent = u.unit_number;
          if (currentUnitNum && u.unit_number === currentUnitNum) opt.selected = true;
          sel.appendChild(opt);
        });
        sel.disabled = false;
      })
      .catch(() => {
        sel.innerHTML = '<option value="">— {{ __('app.failed_to_load') }} —</option>';
        sel.disabled = false;
      });
  }

  // ── Resident lookup helpers ───────────────────────────────────────
  const CHECK_EMAIL_URL = '{{ route('users.check-resident-email') }}';
  const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]')?.content
    || '{{ csrf_token() }}';

  function setEditResidentBadge(state) {
    // state: 'found' | 'notfound' | 'loading' | 'none'
    ['found', 'notfound', 'loading'].forEach(s => {
      document.getElementById(`edit-resident-badge-${s}`)?.classList.toggle('hidden', s !== state);
      document.getElementById(`edit-resident-badge-${s}`)?.classList.toggle('flex', s === state);
    });
  }

  function lockEditHousehold(blockId, unitNumber) {
    const blockSel = document.getElementById('edit-block-id');
    blockSel.value = blockId ?? '';
    blockSel.disabled = true;
    blockSel.classList.add('opacity-60', 'cursor-not-allowed');
    loadUnitsForUserModal(blockId, 'edit-unit-number', unitNumber ?? '').then(() => {
      const unitSel = document.getElementById('edit-unit-number');
      if (unitSel) {
        unitSel.disabled = true;
        unitSel.classList.add('opacity-60', 'cursor-not-allowed');
      }
    });
  }

  function unlockEditHousehold() {
    const blockSel = document.getElementById('edit-block-id');
    blockSel.disabled = false;
    blockSel.classList.remove('opacity-60', 'cursor-not-allowed');
    const unitSel = document.getElementById('edit-unit-number');
    if (unitSel) {
      unitSel.disabled = false;
      unitSel.classList.remove('opacity-60', 'cursor-not-allowed');
    }
    // Do NOT reload units here — the initial loadUnitsForUserModal call in
    // openEditModal already populated the dropdown with the correct selection.
    // Reloading here (with currentUnitNum=null) would clear the selection.
  }

  function lookupResidentForEdit(email) {
    if (!email || !email.includes('@')) {
      setEditResidentBadge('none');
      unlockEditHousehold();
      return;
    }
    setEditResidentBadge('loading');
    fetch(CHECK_EMAIL_URL, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN },
      body: JSON.stringify({ email }),
    })
      .then(r => r.json())
      .then(data => {
        if (data.found) {
          setEditResidentBadge('found');
          lockEditHousehold(data.block_id, data.unit_number);
        } else {
          setEditResidentBadge('notfound');
          unlockEditHousehold();
        }
      })
      .catch(() => {
        setEditResidentBadge('none');
        unlockEditHousehold();
      });
  }

  // Trigger on email change
  document.getElementById('edit-email')?.addEventListener('change', function () {
    lookupResidentForEdit(this.value.trim());
  });

  // Block change → reload unit dropdown
  document.getElementById('edit-block-id')?.addEventListener('change', function () {
    loadUnitsForUserModal(this.value, 'edit-unit-number', null);
  });
</script>