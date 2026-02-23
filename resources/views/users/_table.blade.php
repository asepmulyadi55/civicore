{{-- Users Table with confirmation modals and Edit button --}}

{{-- ── Flash Messages ─────────────────────────────────────── --}}
@if(session('success'))
  <div
    class="mb-4 flex items-center gap-3 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 text-emerald-700 dark:text-emerald-400 px-4 py-3 rounded-lg text-sm font-medium">
    <span class="material-icons text-base">check_circle</span>
    {{ session('success') }}
  </div>
@endif

@if(session('error'))
  <div
    class="mb-4 flex items-center gap-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-400 px-4 py-3 rounded-lg text-sm font-medium">
    <span class="material-icons text-base">error_outline</span>
    {{ session('error') }}
  </div>
@endif

@if($errors->any())
  <div
    class="mb-4 flex items-start gap-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-400 px-4 py-3 rounded-lg text-sm">
    <span class="material-icons text-base mt-0.5">error</span>
    <ul class="space-y-0.5">
      @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
      @endforeach
    </ul>
  </div>
@endif

{{-- ── Table ────────────────────────────────────────────────── --}}
<div
  class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
  <div class="overflow-x-auto">
    <table class="w-full text-left border-collapse">
      <thead>
        <tr class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-800">
          <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">User</th>
          <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Email</th>
          <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Role</th>
          <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Status</th>
          <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider text-right">Actions</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-200 dark:divide-slate-800">

        @forelse($users as $user)
          @php
            $initials = collect(explode(' ', $user->name))->map(fn($w) => strtoupper($w[0]))->take(2)->implode('');
            $avatarColors = ['bg-primary/10 text-primary', 'bg-blue-100 text-blue-600', 'bg-emerald-100 text-emerald-600', 'bg-amber-100 text-amber-600', 'bg-indigo-100 text-indigo-600'];
            $color = $avatarColors[$user->id % count($avatarColors)];
            $isPending = !$user->is_active;
            $isSelf = $user->id === auth()->id();
            $roleBadge = match ($user->role?->name) {
              'admin' => 'bg-purple-100 text-purple-700',
              'treasurer' => 'bg-amber-100 text-amber-700',
              'block_coordinator' => 'bg-indigo-100 text-indigo-700',
              'resident' => 'bg-sky-100 text-sky-700',
              default => 'bg-slate-100 text-slate-600',
            };
          @endphp
          <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/20 transition-colors">

            {{-- User Details --}}
            <td class="px-6 py-4">
              <div class="flex items-center gap-3">
                <div
                  class="w-10 h-10 rounded-full {{ $color }} flex items-center justify-center font-bold text-sm flex-shrink-0">
                  {{ $initials }}
                </div>
                <div>
                  <div class="font-bold text-slate-900 dark:text-white">{{ $user->name }}</div>
                  <div class="text-xs text-slate-400">@{{ $user->username }}</div>
                </div>
              </div>
            </td>

            {{-- Email --}}
            <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-400">{{ $user->email }}</td>

            {{-- Role --}}
            <td class="px-6 py-4">
              @if($user->role)
                <span class="px-2 py-1 text-[10px] font-bold {{ $roleBadge }} rounded-lg uppercase">
                  {{ str_replace('_', ' ', $user->role->name) }}
                </span>
              @else
                <span class="text-xs text-slate-400 italic">No role</span>
              @endif
            </td>

            {{-- Status --}}
            <td class="px-6 py-4">
              @if($user->is_active)
                <span
                  class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400">
                  <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>Active
                </span>
              @else
                <span
                  class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400">
                  <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>Pending
                </span>
              @endif
            </td>

            {{-- Actions --}}
            <td class="px-6 py-4 text-right">
              <div class="flex justify-end gap-1.5 items-center">

                {{-- Edit (always shown) --}}
                <button onclick="openUserEditModal(
                      {{ $user->id }},
                      {{ json_encode($user->name) }},
                      {{ json_encode($user->username) }},
                      {{ json_encode($user->email) }},
                      {{ $user->role_id ?? 'null' }}
                    )" class="p-1.5 text-slate-400 hover:text-primary hover:bg-primary/10 rounded-lg transition-colors"
                  title="Edit user">
                  <span class="material-icons text-lg">edit</span>
                </button>

                @if($isPending)
                  {{-- Approve button → triggers modal --}}
                  <button onclick="openUserConfirmModal('approve', {{ $user->id }}, {{ json_encode($user->name) }})"
                    class="bg-primary text-white text-[10px] px-3 py-1.5 rounded font-bold uppercase tracking-wider hover:bg-primary/90 transition-colors flex items-center gap-1">
                    <span class="material-icons text-xs">verified</span>
                    Approve
                  </button>
                @else
                  @if(!$isSelf)
                    {{-- Deactivate button → triggers modal --}}
                    <button onclick="openUserConfirmModal('deactivate', {{ $user->id }}, {{ json_encode($user->name) }})"
                      class="p-1.5 text-slate-400 hover:text-amber-500 hover:bg-amber-50 dark:hover:bg-amber-900/20 rounded-lg transition-colors"
                      title="Deactivate">
                      <span class="material-icons text-lg">person_off</span>
                    </button>
                  @endif
                @endif

                {{-- Delete button → triggers modal --}}
                @if(!$isSelf)
                  <button onclick="openUserConfirmModal('delete', {{ $user->id }}, {{ json_encode($user->name) }})"
                    class="p-1.5 text-slate-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors"
                    title="Delete">
                    <span class="material-icons text-lg">delete_outline</span>
                  </button>
                @endif

              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="5" class="px-6 py-16 text-center">
              <div class="flex flex-col items-center gap-3 text-slate-400">
                <span class="material-icons text-5xl">manage_accounts</span>
                <p class="text-sm font-medium">No users found.</p>
                @if(request()->hasAny(['search', 'role_id', 'status']))
                  <a href="{{ route('users.index') }}" class="text-primary text-sm hover:underline">Clear filters</a>
                @endif
              </div>
            </td>
          </tr>
        @endforelse

      </tbody>
    </table>
  </div>

  {{-- Pagination --}}
  <div class="p-4 border-t border-slate-200 dark:border-slate-800 flex items-center justify-between flex-wrap gap-3">
    <span class="text-sm text-slate-500">
      Showing {{ $users->firstItem() ?? 0 }}–{{ $users->lastItem() ?? 0 }} of {{ $users->total() }} users
    </span>
    {{ $users->links() }}
  </div>
</div>

{{-- ══════════════════════════════════════════════════════════════ --}}
{{-- Confirmation Modal (shared for Approve / Deactivate / Delete) --}}
{{-- ══════════════════════════════════════════════════════════════ --}}
<div id="user-confirm-modal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4"
  onclick="if(event.target===this) closeUserConfirmModal()">
  <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm"></div>
  <div class="relative bg-white dark:bg-slate-900 rounded-2xl shadow-2xl w-full max-w-sm overflow-hidden">

    {{-- Icon area --}}
    <div id="ucm-icon-area" class="flex flex-col items-center pt-8 pb-5 px-6 text-center">
      <div id="ucm-icon-wrap" class="w-16 h-16 rounded-full flex items-center justify-center mb-4">
        <span id="ucm-icon" class="material-icons text-3xl"></span>
      </div>
      <h3 id="ucm-title" class="text-xl font-bold text-slate-900 dark:text-white mb-2"></h3>
      <p id="ucm-body" class="text-sm text-slate-500 dark:text-slate-400"></p>
    </div>

    {{-- Footer buttons --}}
    <div class="flex gap-3 px-6 pb-6">
      <button onclick="closeUserConfirmModal()"
        class="flex-1 px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 text-sm font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition-all">
        Cancel
      </button>
      {{-- Hidden forms — submitted by JS --}}
      <form id="ucm-form-approve" method="POST" action="" class="flex-1">
        @csrf @method('PATCH')
        <button type="submit"
          class="w-full px-4 py-2.5 rounded-xl text-sm font-bold text-white bg-emerald-600 hover:bg-emerald-700 transition-all">
          Yes, Approve
        </button>
      </form>
      <form id="ucm-form-deactivate" method="POST" action="" class="flex-1">
        @csrf @method('PATCH')
        <button type="submit"
          class="w-full px-4 py-2.5 rounded-xl text-sm font-bold text-white bg-amber-500 hover:bg-amber-600 transition-all">
          Yes, Deactivate
        </button>
      </form>
      <form id="ucm-form-delete" method="POST" action="" class="flex-1">
        @csrf @method('DELETE')
        <button type="submit"
          class="w-full px-4 py-2.5 rounded-xl text-sm font-bold text-white bg-red-600 hover:bg-red-700 transition-all">
          Yes, Delete
        </button>
      </form>
    </div>
  </div>
</div>

{{-- ══════════════════════════════════════════════════════════════ --}}
{{-- Edit User Modal --}}
{{-- ══════════════════════════════════════════════════════════════ --}}
<div id="user-edit-modal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4"
  onclick="if(event.target===this) closeUserEditModal()">
  <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm"></div>
  <div class="relative bg-white dark:bg-slate-900 rounded-2xl shadow-2xl w-full max-w-md overflow-hidden">

    {{-- Header --}}
    <div class="flex items-center justify-between px-6 pt-6 pb-4 border-b border-slate-100 dark:border-slate-800">
      <h3 class="text-xl font-bold text-slate-900 dark:text-white">Edit User</h3>
      <button onclick="closeUserEditModal()"
        class="p-2 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-full transition-colors">
        <span class="material-icons text-slate-400">close</span>
      </button>
    </div>

    {{-- Form --}}
    <form id="user-edit-form" method="POST" action="" class="p-6 space-y-4">
      @csrf @method('PATCH')

      {{-- Name --}}
      <div>
        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
          Full Name <span class="text-red-500">*</span>
        </label>
        <input id="ue-name" type="text" name="name" required
          class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition-all dark:text-white" />
      </div>

      {{-- Username --}}
      <div>
        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
          Username <span class="text-red-500">*</span>
        </label>
        <input id="ue-username" type="text" name="username" required
          class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition-all dark:text-white" />
      </div>

      {{-- Email --}}
      <div>
        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
          Email <span class="text-red-500">*</span>
        </label>
        <input id="ue-email" type="email" name="email" required
          class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition-all dark:text-white" />
      </div>

      {{-- Role --}}
      <div>
        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Role</label>
        <div class="relative">
          <select id="ue-role" name="role_id"
            class="w-full appearance-none px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition-all dark:text-white pr-9">
            <option value="">— No Role —</option>
            @foreach($roles as $role)
              <option value="{{ $role->id }}">{{ ucfirst(str_replace('_', ' ', $role->name)) }}</option>
            @endforeach
          </select>
          <span
            class="material-icons absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400 text-[18px]">expand_more</span>
        </div>
      </div>

      <div class="h-px bg-slate-100 dark:bg-slate-800"></div>

      {{-- New Password (optional) --}}
      <div>
        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
          New Password
          <span class="text-slate-400 font-normal text-xs ml-1">(leave blank to keep current)</span>
        </label>
        <div class="relative">
          <input id="ue-password" type="password" name="password" autocomplete="new-password" placeholder="••••••••"
            class="w-full px-4 py-2.5 pr-10 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition-all dark:text-white" />
          <button type="button" onclick="togglePasswordVisibility()"
            class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition-colors">
            <span id="ue-pw-icon" class="material-icons text-lg">visibility_off</span>
          </button>
        </div>
        <p class="mt-1 text-xs text-slate-400">Minimum 8 characters if changing.</p>
      </div>

      {{-- Footer --}}
      <div class="flex gap-3 pt-2">
        <button type="button" onclick="closeUserEditModal()"
          class="flex-1 px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 text-sm font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition-all">
          Cancel
        </button>
        <button type="submit"
          class="flex-1 px-4 py-2.5 rounded-xl bg-primary hover:bg-primary/90 text-sm font-bold text-white transition-all shadow-sm shadow-primary/20">
          Save Changes
        </button>
      </div>
    </form>
  </div>
</div>

{{-- ── Modal JavaScript ───────────────────────────────────────── --}}
<script>
  // ── Confirmation Modal ──────────────────────────────────────────
  const UCM_CONFIGS = {
    approve: {
      iconWrap: 'bg-emerald-100 dark:bg-emerald-900/30',
      icon: 'verified',
      iconColor: 'text-emerald-600 dark:text-emerald-400',
      title: 'Approve User?',
      body: (name) => `<strong class="text-slate-800 dark:text-slate-200">${name}</strong> will be activated and can log in to the system.`,
      form: 'ucm-form-approve',
      route: (id) => `/users/${id}/approve`,
    },
    deactivate: {
      iconWrap: 'bg-amber-100 dark:bg-amber-900/30',
      icon: 'person_off',
      iconColor: 'text-amber-600 dark:text-amber-400',
      title: 'Deactivate User?',
      body: (name) => `<strong class="text-slate-800 dark:text-slate-200">${name}</strong> will be deactivated and will no longer be able to log in.`,
      form: 'ucm-form-deactivate',
      route: (id) => `/users/${id}/deactivate`,
    },
    delete: {
      iconWrap: 'bg-red-100 dark:bg-red-900/30',
      icon: 'delete_forever',
      iconColor: 'text-red-600 dark:text-red-400',
      title: 'Delete User?',
      body: (name) => `<strong class="text-slate-800 dark:text-slate-200">${name}</strong> will be permanently deleted. This action cannot be undone.`,
      form: 'ucm-form-delete',
      route: (id) => `/users/${id}`,
    },
  };

  function openUserConfirmModal(action, userId, userName) {
    const cfg = UCM_CONFIGS[action];

    // Set icon area
    document.getElementById('ucm-icon-wrap').className = `w-16 h-16 rounded-full flex items-center justify-center mb-4 ${cfg.iconWrap}`;
    const iconEl = document.getElementById('ucm-icon');
    iconEl.textContent = cfg.icon;
    iconEl.className = `material-icons text-3xl ${cfg.iconColor}`;

    // Set text
    document.getElementById('ucm-title').textContent = cfg.title;
    document.getElementById('ucm-body').innerHTML = cfg.body(userName);

    // Show only the relevant form button, hide others
    ['approve', 'deactivate', 'delete'].forEach(a => {
      document.getElementById(`ucm-form-${a}`).classList.toggle('hidden', a !== action);
    });

    // Set form action
    document.getElementById(`ucm-form-${action}`).action = cfg.route(userId);

    // Show modal
    document.getElementById('user-confirm-modal').classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
  }

  function closeUserConfirmModal() {
    document.getElementById('user-confirm-modal').classList.add('hidden');
    document.body.classList.remove('overflow-hidden');
  }

  // ── Edit Modal ──────────────────────────────────────────────────
  function openUserEditModal(id, name, username, email, roleId) {
    document.getElementById('ue-name').value = name;
    document.getElementById('ue-username').value = username;
    document.getElementById('ue-email').value = email;
    document.getElementById('ue-role').value = roleId ?? '';
    document.getElementById('ue-password').value = '';
    document.getElementById('user-edit-form').action = `/users/${id}`;

    document.getElementById('user-edit-modal').classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
  }

  function closeUserEditModal() {
    document.getElementById('user-edit-modal').classList.add('hidden');
    document.body.classList.remove('overflow-hidden');
  }

  function togglePasswordVisibility() {
    const input = document.getElementById('ue-password');
    const icon = document.getElementById('ue-pw-icon');
    const show = input.type === 'password';
    input.type = show ? 'text' : 'password';
    icon.textContent = show ? 'visibility' : 'visibility_off';
  }

  // Close on Escape
  document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
      closeUserConfirmModal();
      closeUserEditModal();
    }
  });
</script>