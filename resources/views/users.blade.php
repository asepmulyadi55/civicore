<x-layouts.app title="User Management"
  class="font-display bg-background-light dark:bg-background-dark text-slate-800 dark:text-slate-200 antialiased min-h-screen">

  {{-- Sidebar --}}
  <x-nav.sidebar active="users" />

  {{-- ── Modals ──────────────────────────────────────────────── --}}
  <x-modals.create-user />
  <x-modals.edit-user />

  {{-- Confirmation Modal (Approve / Deactivate / Delete) --}}
  <div id="user-confirm-modal"
    class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4"
    onclick="if(event.target===this) closeUserConfirmModal()">
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-2xl w-full max-w-sm overflow-hidden">
      <div id="ucm-icon-area" class="flex flex-col items-center pt-8 pb-5 px-6 text-center">
        <div id="ucm-icon-wrap" class="w-16 h-16 rounded-full flex items-center justify-center mb-4">
          <span id="ucm-icon" class="material-icons text-3xl"></span>
        </div>
        <h3 id="ucm-title" class="text-xl font-bold text-slate-900 dark:text-white mb-2"></h3>
        <p id="ucm-body" class="text-sm text-slate-500 dark:text-slate-400 leading-relaxed"></p>
      </div>
      <div class="flex gap-3 px-6 pb-6">
        <button onclick="closeUserConfirmModal()"
          class="flex-1 px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 text-sm font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition-all">
          Cancel
        </button>
        <form id="ucm-form-approve" method="POST" action="" class="flex-1">
          @csrf @method('PATCH')
          <button type="submit"
            class="w-full px-4 py-2.5 rounded-xl text-sm font-bold text-white bg-emerald-600 hover:bg-emerald-700 transition-all">Yes,
            Approve</button>
        </form>
        <form id="ucm-form-deactivate" method="POST" action="" class="flex-1">
          @csrf @method('PATCH')
          <button type="submit"
            class="w-full px-4 py-2.5 rounded-xl text-sm font-bold text-white bg-amber-500 hover:bg-amber-600 transition-all">Yes,
            Deactivate</button>
        </form>
        <form id="ucm-form-delete" method="POST" action="" class="flex-1">
          @csrf @method('DELETE')
          <button type="submit"
            class="w-full px-4 py-2.5 rounded-xl text-sm font-bold text-white bg-red-600 hover:bg-red-700 transition-all">Yes,
            Delete</button>
        </form>
      </div>
    </div>
  </div>

  {{-- ── Main Content ────────────────────────────────────────── --}}
  <main class="lg:ml-64 flex flex-col min-h-screen">

    @include('users._header')

    <div class="flex-1 p-6 lg:p-8 space-y-6">

      {{-- Flash messages --}}
      @if (session('success'))
        <div
          class="p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-900/30 rounded-xl flex items-center space-x-3">
          <span class="material-icons text-green-500">check_circle</span>
          <p class="text-sm text-green-700 dark:text-green-400">{{ session('success') }}</p>
        </div>
      @endif
      @if (session('error'))
        <div
          class="p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-900/30 rounded-xl flex items-center space-x-3">
          <span class="material-icons text-red-500">error</span>
          <p class="text-sm text-red-700 dark:text-red-400">{{ session('error') }}</p>
        </div>
      @endif

      @include('users._stats')
      @include('users._filters')
      @include('users._table')

    </div>
  </main>

  <script>
    function toggleSidebar() {
      document.getElementById('sidebar').classList.toggle('-translate-x-full');
      document.getElementById('sidebar-overlay').classList.toggle('hidden');
    }
    function openModal(id) {
      document.getElementById(id).classList.remove('hidden');
      document.body.classList.add('overflow-hidden');
    }
    function closeModal(id) {
      document.getElementById(id).classList.add('hidden');
      document.body.classList.remove('overflow-hidden');
    }
    function closeModalOnBackdrop(event, id) {
      if (event.target === document.getElementById(id)) closeModal(id);
    }

    // Password visibility toggle (shared by Create/Edit modals)
    function togglePw(inputId, iconId) {
      const input = document.getElementById(inputId);
      const icon = document.getElementById(iconId);
      const show = input.type === 'password';
      input.type = show ? 'text' : 'password';
      icon.textContent = show ? 'visibility' : 'visibility_off';
    }

    // ── Edit Modal ──────────────────────────────────────────────────
    // Opens the x-modals.edit-user component (id="modal-edit")
    function openEditModal(id, name, username, email, roleId, blockId) {
      document.getElementById('edit-name').value = name;
      document.getElementById('edit-username').value = username;
      document.getElementById('edit-email').value = email;
      document.getElementById('edit-block').value = blockId ?? '';
      document.getElementById('edit-password').value = '';

      // Pre-select the correct role card radio
      document.querySelectorAll('.edit-role-radio').forEach(radio => {
        radio.checked = (radio.value !== '' && parseInt(radio.value) === roleId)
          || (radio.value === '' && !roleId);
      });

      document.getElementById('form-edit-user').action = `/users/${id}`;
      openModal('modal-edit');
    }

    // ── Confirmation Modal (Approve / Deactivate / Delete) ──────────
    const UCM_CONFIGS = {
      approve: {
        iconWrap: 'bg-emerald-100 dark:bg-emerald-900/30',
        icon: 'verified', iconColor: 'text-emerald-600 dark:text-emerald-400',
        title: 'Approve User?',
        body: (name) => `<strong class="text-slate-800 dark:text-slate-200">${name}</strong> will be activated and can log in.`,
        form: 'ucm-form-approve', route: (id) => `/users/${id}/approve`,
      },
      deactivate: {
        iconWrap: 'bg-amber-100 dark:bg-amber-900/30',
        icon: 'person_off', iconColor: 'text-amber-600 dark:text-amber-400',
        title: 'Deactivate User?',
        body: (name) => `<strong class="text-slate-800 dark:text-slate-200">${name}</strong> will be deactivated and can no longer log in.`,
        form: 'ucm-form-deactivate', route: (id) => `/users/${id}/deactivate`,
      },
      delete: {
        iconWrap: 'bg-red-100 dark:bg-red-900/30',
        icon: 'delete_forever', iconColor: 'text-red-600 dark:text-red-400',
        title: 'Delete User?',
        body: (name) => `<strong class="text-slate-800 dark:text-slate-200">${name}</strong> will be permanently deleted. This cannot be undone.`,
        form: 'ucm-form-delete', route: (id) => `/users/${id}`,
      },
    };

    function openUserConfirmModal(action, userId, userName) {
      const cfg = UCM_CONFIGS[action];
      document.getElementById('ucm-icon-wrap').className =
        `w-16 h-16 rounded-full flex items-center justify-center mb-4 ${cfg.iconWrap}`;
      const iconEl = document.getElementById('ucm-icon');
      iconEl.textContent = cfg.icon;
      iconEl.className = `material-icons text-3xl ${cfg.iconColor}`;
      document.getElementById('ucm-title').textContent = cfg.title;
      document.getElementById('ucm-body').innerHTML = cfg.body(userName);
      ['approve', 'deactivate', 'delete'].forEach(a =>
        document.getElementById(`ucm-form-${a}`).classList.toggle('hidden', a !== action)
      );
      document.getElementById(`ucm-form-${action}`).action = cfg.route(userId);
      document.getElementById('user-confirm-modal').classList.remove('hidden');
      document.body.classList.add('overflow-hidden');
    }

    function closeUserConfirmModal() {
      document.getElementById('user-confirm-modal').classList.add('hidden');
      document.body.classList.remove('overflow-hidden');
    }

    document.addEventListener('keydown', e => {
      if (e.key === 'Escape') {
        closeModal('modal-create');
        closeModal('modal-edit');
        closeUserConfirmModal();
      }
    });
  </script>

</x-layouts.app>