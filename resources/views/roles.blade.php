<x-layouts.app title="Roles & Permissions"
  class="font-display bg-background-light dark:bg-background-dark text-slate-800 dark:text-slate-200 antialiased min-h-screen">

  <x-nav.sidebar active="roles" />

  <main class="lg:ml-64 flex flex-col h-screen overflow-hidden">
    {{-- Header --}}
    <header
      class="h-16 bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between px-6 lg:px-8 shrink-0">
      <div class="flex items-center gap-4">
        <button class="lg:hidden p-2 rounded-lg border border-slate-200 dark:border-slate-800"
          onclick="toggleSidebar()">
          <span class="material-icons text-slate-500">menu</span>
        </button>
        <h1 class="text-xl font-bold text-slate-900 dark:text-white">Roles & Permissions</h1>
        <span
          class="hidden sm:inline px-2 py-1 text-xs font-semibold bg-primary/10 text-primary rounded-lg uppercase">Manage</span>
      </div>
      <div class="flex items-center gap-3">
        @if(auth()->user()->isAdmin())
          <button onclick="openAddRoleModal()"
            class="flex items-center gap-2 bg-primary hover:bg-primary/90 text-white px-4 py-2 rounded-lg font-semibold transition-all shadow-sm shadow-primary/20 text-sm">
            <span class="material-icons text-sm">add</span>
            <span class="hidden sm:inline">Add Role</span>
          </button>
        @endif
        <button
          class="p-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg hover:border-primary/50 transition-all"
          onclick="toggleDark()" title="Toggle dark mode">
          <span class="material-icons text-slate-500 text-[20px]">dark_mode</span>
        </button>
      </div>
    </header>

    <div class="flex-1 overflow-y-auto p-8 space-y-6">
      {{-- Flash messages --}}
      @foreach(['success', 'error'] as $type)
        @if(session($type))
          <div
            class="p-4 {{ $type === 'success' ? 'bg-emerald-50 dark:bg-emerald-900/20 border-emerald-200 text-emerald-700 dark:text-emerald-400' : 'bg-rose-50 dark:bg-rose-900/20 border-rose-200 text-rose-700 dark:text-rose-400' }} border rounded-xl flex items-center gap-3">
            <span class="material-icons text-sm">{{ $type === 'success' ? 'check_circle' : 'error' }}</span>
            <p class="text-sm">{{ session($type) }}</p>
          </div>
        @endif
      @endforeach

      {{-- Roles Table --}}
      <div
        class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full text-left border-collapse">
            <thead>
              <tr class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-800">
                <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Role</th>
                <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Description</th>
                <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Users</th>
                <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Permissions</th>
                @if(auth()->user()->isAdmin())
                  <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider text-right">Actions</th>
                @endif
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
              @forelse($roles as $role)
                <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors">
                  <td class="px-6 py-4">
                    <div class="flex items-center gap-3">
                      <div
                        class="w-9 h-9 rounded-xl {{ $role->bg_class ?? 'bg-slate-100 dark:bg-slate-800' }} flex items-center justify-center">
                        <span
                          class="material-icons text-lg {{ $role->text_class ?? 'text-slate-500' }}">{{ $role->icon ?? 'person' }}</span>
                      </div>
                      <div>
                        <p class="font-semibold text-sm">{{ $role->label }}</p>
                        <p class="text-xs text-slate-400 font-mono">{{ $role->name }}</p>
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4 text-sm text-slate-500 max-w-xs">{{ $role->description ?? '—' }}</td>
                  <td class="px-6 py-4">
                    <span
                      class="px-2.5 py-1 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 rounded-lg text-xs font-bold">{{ $role->users_count ?? 0 }}</span>
                  </td>
                  <td class="px-6 py-4">
                    @if($role->name === 'admin')
                      <span
                        class="inline-flex items-center gap-1 px-2.5 py-1 bg-primary/10 text-primary rounded-full text-xs font-bold">
                        <span class="material-icons text-xs">verified</span> Full Access
                      </span>
                    @else
                      @php
                        $count = collect($role->permissions ?? [])->filter()->count();
                        $total = collect(\App\Models\Role::$availablePermissions)->flatten()->count();
                      @endphp
                      <span class="text-sm text-slate-600 dark:text-slate-400">{{ $count }} / {{ $total }}
                        permissions</span>
                    @endif
                  </td>
                  @if(auth()->user()->isAdmin())
                    <td class="px-6 py-4">
                      <div class="flex items-center justify-end gap-1">
                        @if($role->name !== 'admin')
                          {{-- Edit Permissions --}}
                          <button
                            onclick="openPermissionsModal({{ $role->id }}, '{{ addslashes($role->label) }}', {{ json_encode($role->permissions ?? new stdClass()) }})"
                            class="p-1.5 text-slate-400 hover:text-primary hover:bg-primary/5 rounded-lg transition-colors"
                            title="Edit Permissions">
                            <span class="material-icons text-lg">tune</span>
                          </button>
                          {{-- Edit Role --}}
                          <button
                            onclick="openEditRoleModal({{ $role->id }}, '{{ addslashes($role->label) }}', '{{ addslashes($role->description ?? '') }}')"
                            class="p-1.5 text-slate-400 hover:text-primary hover:bg-primary/5 rounded-lg transition-colors"
                            title="Edit Role">
                            <span class="material-icons text-lg">edit</span>
                          </button>
                          {{-- Delete --}}
                          <button onclick="openDeleteRoleModal({{ $role->id }}, '{{ addslashes($role->label) }}')"
                            class="p-1.5 text-slate-400 hover:text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-900/20 rounded-lg transition-colors"
                            title="Delete Role">
                            <span class="material-icons text-lg">delete_outline</span>
                          </button>
                        @endif
                      </div>
                    </td>
                  @endif
                </tr>
              @empty
                <tr>
                  <td colspan="5" class="px-6 py-16 text-center text-slate-400">No roles found.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </main>

  {{-- ── Add Role Modal ─────────────────────────────────────── --}}
  <div id="add-role-overlay"
    class="hidden fixed inset-0 z-50 bg-black/50 backdrop-blur-sm items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-2xl w-full max-w-md">
      <div class="p-6 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
        <h3 class="text-lg font-bold">Add New Role</h3>
        <button onclick="closeAddRoleModal()" class="text-slate-400 hover:text-slate-600"><span
            class="material-icons">close</span></button>
      </div>
      <form method="POST" action="{{ route('roles.store') }}" class="p-6 space-y-4" novalidate id="add-role-form">
        @csrf
        <div class="flex flex-col gap-1.5">
          <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">Role Name (slug) <span
              class="text-red-500">*</span></label>
          <input type="text" id="ar-name" name="name" placeholder="e.g. finance_manager" pattern="[a-zA-Z0-9_\-]+"
            class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm outline-none focus:border-primary focus:ring-2 focus:ring-primary/20 dark:text-white"
            oninput="hideFmErr('ar-err-name')" />
          <p class="text-[11px] text-slate-400">Only letters, numbers, underscores, hyphens. Used internally.</p>
          <p id="ar-err-name" class="hidden text-xs text-red-500 flex items-center gap-1"><span
              class="material-icons text-xs">error_outline</span> Role name is required.</p>
        </div>
        <div class="flex flex-col gap-1.5">
          <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">Display Label <span
              class="text-red-500">*</span></label>
          <input type="text" id="ar-label" name="label" placeholder="e.g. Finance Manager"
            class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm outline-none focus:border-primary focus:ring-2 focus:ring-primary/20 dark:text-white"
            oninput="hideFmErr('ar-err-label')" />
          <p id="ar-err-label" class="hidden text-xs text-red-500 flex items-center gap-1"><span
              class="material-icons text-xs">error_outline</span> Display label is required.</p>
        </div>
        <div class="flex flex-col gap-1.5">
          <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">Description</label>
          <input type="text" name="description" placeholder="Brief description of this role"
            class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm outline-none focus:border-primary focus:ring-2 focus:ring-primary/20 dark:text-white" />
        </div>
        <div class="flex gap-3 pt-2">
          <button type="button" onclick="closeAddRoleModal()"
            class="flex-1 py-3 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-bold text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">Cancel</button>
          <button type="button" onclick="submitAddRole()"
            class="flex-1 py-3 bg-primary text-white rounded-xl text-sm font-bold hover:bg-primary/90 shadow-lg shadow-primary/20 transition-all active:scale-95">Create
            Role</button>
        </div>
      </form>
    </div>
  </div>

  {{-- ── Edit Role Modal ─────────────────────────────────────── --}}
  <div id="edit-role-overlay"
    class="hidden fixed inset-0 z-50 bg-black/50 backdrop-blur-sm items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-2xl w-full max-w-md">
      <div class="p-6 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
        <h3 class="text-lg font-bold">Edit Role</h3>
        <button onclick="closeEditRoleModal()" class="text-slate-400 hover:text-slate-600"><span
            class="material-icons">close</span></button>
      </div>
      <form id="edit-role-form" method="POST" action="" class="p-6 space-y-4" novalidate>
        @csrf @method('PATCH')
        <div class="flex flex-col gap-1.5">
          <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">Display Label <span
              class="text-red-500">*</span></label>
          <input type="text" id="er-label" name="label"
            class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm outline-none focus:border-primary focus:ring-2 focus:ring-primary/20 dark:text-white"
            oninput="hideFmErr('er-err-label')" />
          <p id="er-err-label" class="hidden text-xs text-red-500 flex items-center gap-1"><span
              class="material-icons text-xs">error_outline</span> Display label is required.</p>
        </div>
        <div class="flex flex-col gap-1.5">
          <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">Description</label>
          <input type="text" id="er-description" name="description"
            class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm outline-none focus:border-primary focus:ring-2 focus:ring-primary/20 dark:text-white" />
        </div>
        <div class="flex gap-3 pt-2">
          <button type="button" onclick="closeEditRoleModal()"
            class="flex-1 py-3 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-bold text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">Cancel</button>
          <button type="button" onclick="submitEditRole()"
            class="flex-1 py-3 bg-primary text-white rounded-xl text-sm font-bold hover:bg-primary/90 shadow-lg shadow-primary/20 transition-all active:scale-95">Save
            Changes</button>
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
          <h3 class="text-lg font-bold">Delete Role?</h3>
          <p class="text-sm text-slate-500 mt-1">Are you sure you want to delete <strong id="delete-role-name"
              class="text-slate-700 dark:text-slate-300"></strong>? This cannot be undone.</p>
        </div>
        <div class="flex gap-3 w-full">
          <button onclick="closeDeleteRoleModal()"
            class="flex-1 py-3 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-bold text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">Cancel</button>
          <form id="delete-role-form" method="POST" action="" class="flex-1">
            @csrf @method('DELETE')
            <button type="submit"
              class="w-full py-3 bg-rose-500 text-white rounded-xl text-sm font-bold hover:bg-rose-600 transition-all active:scale-95">Delete</button>
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
          <h3 class="text-lg font-bold">Permissions</h3>
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
              'dashboard' => 'Dashboard',
              'residents' => 'Residents',
              'blocks' => 'Blocks',
              'payments' => 'Payments',
              'reports' => 'Reports',
              'users' => 'User Management',
              'roles' => 'Roles & Permissions',
            ];
          @endphp

          {{-- Header row --}}
          <div class="grid grid-cols-6 gap-2 mb-3">
            <div class="text-xs font-bold text-slate-500 uppercase tracking-wider">Module</div>
            @foreach($allActions as $action)
              <div class="text-xs font-bold text-slate-500 uppercase tracking-wider text-center">{{ ucfirst($action) }}
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
              class="text-xs font-bold text-primary hover:underline">Select All</button>
            <span class="text-slate-300">·</span>
            <button type="button" onclick="selectAllPerms(false)"
              class="text-xs font-bold text-slate-400 hover:underline">Deselect All</button>
          </div>

          <div class="flex gap-3 mt-6">
            <button type="button" onclick="closePermissionsModal()"
              class="flex-1 py-3 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-bold text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">Cancel</button>
            <button type="submit"
              class="flex-1 py-3 bg-primary text-white rounded-xl text-sm font-bold hover:bg-primary/90 shadow-lg shadow-primary/20 transition-all active:scale-95">
              <span class="material-icons text-sm align-middle mr-1">save</span> Save Permissions
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  {{-- Pass Blade-generated base URL to JS to avoid hardcoded paths --}}
  <script>
    const rolesBaseUrl = "{{ url('/roles') }}";

    // ── Add Role Modal ───────────────────────
    function openAddRoleModal() {
      // Reset form & errors
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

    // ── Edit Role Modal ──────────────────────
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

    // ── Delete Confirmation Modal ────────────
    function openDeleteRoleModal(id, label) {
      document.getElementById('delete-role-name').textContent = label;
      document.getElementById('delete-role-form').action = `${rolesBaseUrl}/${id}`;
      showOverlay('delete-role-overlay');
    }
    function closeDeleteRoleModal() { hideOverlay('delete-role-overlay'); }

    // ── Permissions Modal ────────────────────
    function openPermissionsModal(id, label, permissions) {
      document.getElementById('perms-role-label').textContent = label;
      document.getElementById('perms-form').action = `${rolesBaseUrl}/${id}/permissions`;

      // Restore checkbox states from saved permissions
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

    // ── Shared helpers ───────────────────────
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
    function showFmErr(id) {
      document.getElementById(id)?.classList.remove('hidden');
    }
    function hideFmErr(id) {
      document.getElementById(id)?.classList.add('hidden');
    }

    // Sync checkmark icon when checkbox toggled
    document.addEventListener('change', e => {
      if (!e.target.classList.contains('perm-cb')) return;
      const mark = e.target.nextElementSibling?.querySelector('.checkmark');
      if (mark) mark.classList.toggle('hidden', !e.target.checked);
    });
  </script>

</x-layouts.app>