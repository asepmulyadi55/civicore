{{-- ── Add Role Modal ─────────────────────────────────────── --}}
<div id="add-role-overlay"
  class="hidden fixed inset-0 z-50 bg-black/50 backdrop-blur-sm items-center justify-center p-4">
  <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-2xl w-full max-w-md">
    <div class="p-6 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
      <h3 class="text-lg font-bold">{{ __('app.add_new_role') }}</h3>
      <button onclick="closeAddRoleModal()" class="text-slate-400 hover:text-slate-600"><span
          class="material-icons">close</span></button>
    </div>
    <form method="POST" action="{{ route('roles.store') }}" class="p-6 space-y-4" novalidate id="add-role-form">
      @csrf
      <div class="flex flex-col gap-1.5">
        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">{{ __('app.role_name_slug') }} <span
            class="text-red-500">*</span></label>
        <input type="text" id="ar-name" name="name" placeholder="{{ __('app.eg_finance_manager') }}"
          pattern="[a-zA-Z0-9_\-]+"
          class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm outline-none focus:border-primary focus:ring-2 focus:ring-primary/20 dark:text-white"
          oninput="hideFmErr('ar-err-name')" />
        <p class="text-[11px] text-slate-400">{{ __('app.role_slug_hint') }}</p>
        <p id="ar-err-name" class="hidden text-xs text-red-500 flex items-center gap-1"><span
            class="material-icons text-xs">error_outline</span> {{ __('app.err_role_name') }}</p>
      </div>
      <div class="flex flex-col gap-1.5">
        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">{{ __('app.display_label') }} <span
            class="text-red-500">*</span></label>
        <input type="text" id="ar-label" name="label" placeholder="{{ __('app.eg_finance_manager_label') }}"
          class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm outline-none focus:border-primary focus:ring-2 focus:ring-primary/20 dark:text-white"
          oninput="hideFmErr('ar-err-label')" />
        <p id="ar-err-label" class="hidden text-xs text-red-500 flex items-center gap-1"><span
            class="material-icons text-xs">error_outline</span> {{ __('app.err_role_label') }}</p>
      </div>
      <div class="flex flex-col gap-1.5">
        <label
          class="text-xs font-bold text-slate-500 uppercase tracking-wider">{{ __('app.form_description') }}</label>
        <input type="text" name="description" placeholder="{{ __('app.role_desc_placeholder') }}"
          class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm outline-none focus:border-primary focus:ring-2 focus:ring-primary/20 dark:text-white" />
      </div>
      <div class="flex gap-3 pt-2">
        <button type="button" onclick="closeAddRoleModal()"
          class="flex-1 py-3 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-bold text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">{{ __('app.btn_cancel') }}</button>
        <button type="button" onclick="submitAddRole()"
          class="flex-1 py-3 bg-primary text-white rounded-xl text-sm font-bold hover:bg-primary/90 shadow-lg shadow-primary/20 transition-all active:scale-95">{{ __('app.btn_create_role') }}</button>
      </div>
    </form>
  </div>
</div>

{{-- ── Edit Role Modal ─────────────────────────────────────── --}}
<div id="edit-role-overlay"
  class="hidden fixed inset-0 z-50 bg-black/50 backdrop-blur-sm items-center justify-center p-4">
  <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-2xl w-full max-w-md">
    <div class="p-6 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
      <h3 class="text-lg font-bold">{{ __('app.edit_role_title') }}</h3>
      <button onclick="closeEditRoleModal()" class="text-slate-400 hover:text-slate-600"><span
          class="material-icons">close</span></button>
    </div>
    <form id="edit-role-form" method="POST" action="" class="p-6 space-y-4" novalidate>
      @csrf @method('PATCH')
      <div class="flex flex-col gap-1.5">
        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">{{ __('app.display_label') }} <span
            class="text-red-500">*</span></label>
        <input type="text" id="er-label" name="label"
          class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm outline-none focus:border-primary focus:ring-2 focus:ring-primary/20 dark:text-white"
          oninput="hideFmErr('er-err-label')" />
        <p id="er-err-label" class="hidden text-xs text-red-500 flex items-center gap-1"><span
            class="material-icons text-xs">error_outline</span> {{ __('app.err_role_label') }}</p>
      </div>
      <div class="flex flex-col gap-1.5">
        <label
          class="text-xs font-bold text-slate-500 uppercase tracking-wider">{{ __('app.form_description') }}</label>
        <input type="text" id="er-description" name="description"
          class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm outline-none focus:border-primary focus:ring-2 focus:ring-primary/20 dark:text-white" />
      </div>
      <div class="flex gap-3 pt-2">
        <button type="button" onclick="closeEditRoleModal()"
          class="flex-1 py-3 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-bold text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">{{ __('app.btn_cancel') }}</button>
        <button type="button" onclick="submitEditRole()"
          class="flex-1 py-3 bg-primary text-white rounded-xl text-sm font-bold hover:bg-primary/90 shadow-lg shadow-primary/20 transition-all active:scale-95">{{ __('app.btn_save_changes') }}</button>
      </div>
    </form>
  </div>
</div>

{{-- ── Delete Confirmation Modal ────────────────────────────── --}}
<div id="delete-role-overlay"
  class="hidden fixed inset-0 z-50 bg-black/50 backdrop-blur-sm items-center justify-center p-4">
  <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-2xl w-full max-w-sm">
    <div class="p-6 flex flex-col items-center gap-4 text-center">
      <div class="w-14 h-14 rounded-full bg-rose-100 dark:bg-rose-900/30 flex items-center justify-center">
        <span class="material-icons text-rose-500 text-2xl">delete_forever</span>
      </div>
      <div>
        <h3 class="text-lg font-bold">{{ __('app.delete_role_title') }}</h3>
        <p class="text-sm text-slate-500 mt-1">{{ __('app.delete_role_confirm_1') }} <strong id="delete-role-name"
            class="text-slate-700 dark:text-slate-300"></strong>{{ __('app.delete_role_confirm_2') }}</p>
      </div>
      <div class="flex gap-3 w-full">
        <button onclick="closeDeleteRoleModal()"
          class="flex-1 py-3 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-bold text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">{{ __('app.btn_cancel') }}</button>
        <form id="delete-role-form" method="POST" action="" class="flex-1">
          @csrf @method('DELETE')
          <button type="submit"
            class="w-full py-3 bg-rose-500 text-white rounded-xl text-sm font-bold hover:bg-rose-600 transition-all active:scale-95">{{ __('app.btn_delete') }}</button>
        </form>
      </div>
    </div>
  </div>
</div>

{{-- ── Permissions Modal ────────────────────────────────────── --}}
<div id="perms-overlay"
  class="hidden fixed inset-0 z-50 bg-black/50 backdrop-blur-sm items-center justify-center p-4">
  <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] flex flex-col">
    <div class="p-6 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
      <div>
        <h3 class="text-lg font-bold">{{ __('app.permissions_title') }}</h3>
        <p id="perms-role-label" class="text-sm text-slate-500 mt-0.5"></p>
      </div>
      <button onclick="closePermissionsModal()" class="text-slate-400 hover:text-slate-600"><span
          class="material-icons">close</span></button>
    </div>
    <div class="flex-1 overflow-y-auto p-6">
      <form id="perms-form" method="POST" action="">
        @csrf @method('PATCH')

        @php
          $allPerms = \App\Models\Role::$availablePermissions;
          $allActions = ['view', 'create', 'edit', 'delete', 'approve'];
          $moduleLabels = [
            'overview'  => __('app.nav_overview'),
            'dashboard' => __('app.nav_dashboard'),
            'homepage'  => __('app.nav_homepage'),
            'householders' => __('app.nav_residents'),
            'household' => __('app.household_assignment') ?? 'Household',
            'blocks' => __('app.nav_blocks'),
            'payments' => __('app.nav_payments'),
            'reports' => __('app.nav_reports'),
            'posyandu' => 'Posyandu',
            'users' => __('app.nav_users'),
            'roles' => __('app.nav_roles'),
            'property' => __('app.nav_property'),
            'media' => __('app.nav_media'),
          ];
        @endphp

        <div class="grid grid-cols-6 gap-2 mb-3">
          <div class="text-xs font-bold text-slate-500 uppercase tracking-wider">{{ __('app.module_title') }}</div>
          @foreach($allActions as $action)
            <div class="text-xs font-bold text-slate-500 uppercase tracking-wider text-center">
              {{ __('app.action_' . $action) }}
            </div>
          @endforeach
        </div>

        <div class="space-y-1">
          @foreach($allPerms as $module => $actions)
            <div
              class="grid grid-cols-6 gap-2 items-center px-3 py-3 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800/40 transition-colors">
              <div class="text-sm font-semibold text-slate-700 dark:text-slate-300">
                {{ $moduleLabels[$module] ?? ucfirst($module) }}
              </div>
              @foreach($allActions as $action)
                <div class="flex justify-center">
                  @if(in_array($action, $actions))
                    @php
                      $key = "{$module}.{$action}";
                      $postKey = "{$module}_{$action}";
                    @endphp
                    <label class="cursor-pointer flex items-center justify-center">
                      <input type="checkbox" name="{{ $postKey }}" value="1" class="perm-cb sr-only peer"
                        data-key="{{ $key }}" />
                      <div
                        class="w-6 h-6 rounded-lg border-2 border-slate-300 dark:border-slate-600 peer-checked:bg-primary peer-checked:border-primary flex items-center justify-center transition-all hover:border-primary/50">
                        <span class="checkmark material-icons text-sm text-white hidden">check</span>
                      </div>
                    </label>
                  @else
                    <div
                      class="w-6 h-6 rounded-lg bg-slate-100 dark:bg-slate-800/60 border border-dashed border-slate-200 dark:border-slate-700 opacity-40"
                      title="N/A"></div>
                  @endif
                </div>
              @endforeach
            </div>
          @endforeach
        </div>

        <div class="mt-4 flex gap-2 items-center">
          <button type="button" onclick="selectAllPerms(true)"
            class="text-xs font-bold text-primary hover:underline">{{ __('app.select_all') }}</button>
          <span class="text-slate-300">·</span>
          <button type="button" onclick="selectAllPerms(false)"
            class="text-xs font-bold text-slate-400 hover:underline">{{ __('app.deselect_all') }}</button>
        </div>

        <div class="flex gap-3 mt-6">
          <button type="button" onclick="closePermissionsModal()"
            class="flex-1 py-3 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-bold text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">{{ __('app.btn_cancel') }}</button>
          <button type="submit"
            class="flex-1 py-3 bg-primary text-white rounded-xl text-sm font-bold hover:bg-primary/90 shadow-lg shadow-primary/20 transition-all active:scale-95">
            <span class="material-icons text-sm align-middle mr-1">save</span> {{ __('app.btn_save_permissions') }}
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
  const rolesBaseUrl = "{{ url('/roles') }}";

  function openAddRoleModal() {
    document.getElementById('add-role-form').reset();
    ['ar-err-name', 'ar-err-label'].forEach(id => hideFmErr(id));
    showOverlay('add-role-overlay');
  }
  function closeAddRoleModal() { hideOverlay('add-role-overlay'); }
  function submitAddRole() {
    let valid = true;
    const name = document.getElementById('ar-name').value.trim();
    const label = document.getElementById('ar-label').value.trim();
    if (!name) { showFmErr('ar-err-name'); valid = false; }
    if (!label) { showFmErr('ar-err-label'); valid = false; }
    if (valid) document.getElementById('add-role-form').submit();
  }

  function openEditRoleModal(id, label, description) {
    document.getElementById('er-label').value = label;
    document.getElementById('er-description').value = description;
    hideFmErr('er-err-label');
    document.getElementById('edit-role-form').action = `${rolesBaseUrl}/${id}`;
    showOverlay('edit-role-overlay');
  }
  function closeEditRoleModal() { hideOverlay('edit-role-overlay'); }
  function submitEditRole() {
    const label = document.getElementById('er-label').value.trim();
    if (!label) { showFmErr('er-err-label'); return; }
    document.getElementById('edit-role-form').submit();
  }

  function openDeleteRoleModal(id, label) {
    document.getElementById('delete-role-name').textContent = label;
    document.getElementById('delete-role-form').action = `${rolesBaseUrl}/${id}`;
    showOverlay('delete-role-overlay');
  }
  function closeDeleteRoleModal() { hideOverlay('delete-role-overlay'); }

  function openPermissionsModal(id, label, permissions) {
    document.getElementById('perms-role-label').textContent = label;
    document.getElementById('perms-form').action = `${rolesBaseUrl}/${id}/permissions`;
    document.querySelectorAll('.perm-cb').forEach(cb => {
      const checked = !!permissions[cb.dataset.key];
      cb.checked = checked;
      const mark = cb.nextElementSibling?.querySelector('.checkmark');
      if (mark) mark.classList.toggle('hidden', !checked);
    });
    showOverlay('perms-overlay');
  }
  function closePermissionsModal() { hideOverlay('perms-overlay'); }

  function selectAllPerms(state) {
    document.querySelectorAll('.perm-cb').forEach(cb => {
      cb.checked = state;
      const mark = cb.nextElementSibling?.querySelector('.checkmark');
      if (mark) mark.classList.toggle('hidden', !state);
    });
  }

  function showOverlay(id) {
    const el = document.getElementById(id);
    el.classList.remove('hidden'); el.classList.add('flex');
    document.body.classList.add('overflow-hidden');
  }
  function hideOverlay(id) {
    const el = document.getElementById(id);
    el.classList.add('hidden'); el.classList.remove('flex');
    document.body.classList.remove('overflow-hidden');
  }
  function showFmErr(id) { document.getElementById(id)?.classList.remove('hidden'); }
  function hideFmErr(id) { document.getElementById(id)?.classList.add('hidden'); }

  document.addEventListener('change', e => {
    if (!e.target.classList.contains('perm-cb')) return;
    const mark = e.target.nextElementSibling?.querySelector('.checkmark');
    if (mark) mark.classList.toggle('hidden', !e.target.checked);
  });
</script>
