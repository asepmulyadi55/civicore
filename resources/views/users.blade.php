<x-layouts.app title="User Management"
  class="font-display bg-background-light dark:bg-background-dark text-slate-800 dark:text-slate-200 antialiased min-h-screen">

  {{-- Sidebar --}}
  <x-nav.sidebar active="users" />

  {{-- ── Modals ──────────────────────────────────────────────── --}}
  <x-modals.create-user />
  <x-modals.edit-user />
  <x-modals.approve-user />

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
    // ── Sidebar mobile toggle ─────────────────────────────────
    function toggleSidebar() {
      document.getElementById('sidebar').classList.toggle('-translate-x-full');
      document.getElementById('sidebar-overlay').classList.toggle('hidden');
    }

    // ── Auto-Approval toggle ──────────────────────────────────
    function toggleAutoApproval(btn) {
      const thumb = btn.querySelector('span');
      const isOn = btn.classList.contains('bg-primary');
      btn.classList.toggle('bg-primary', !isOn);
      btn.classList.toggle('bg-slate-300', isOn);
      thumb.classList.toggle('translate-x-5', !isOn);
      thumb.classList.toggle('translate-x-0', isOn);
    }

    // ── Modal helpers ─────────────────────────────────────────
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

    document.addEventListener('keydown', e => {
      if (e.key === 'Escape')
        ['modal-create', 'modal-edit', 'modal-approve'].forEach(closeModal);
    });

    // ── Edit modal ────────────────────────────────────────────
    function openEditModal(initials, first, last, username, email, role) {
      document.getElementById('edit-avatar').textContent = initials;
      document.getElementById('edit-subtitle').textContent = email;
      document.getElementById('edit-firstname').value = first;
      document.getElementById('edit-lastname').value = last;
      document.getElementById('edit-username').value = username;
      document.getElementById('edit-email').value = email;

      // Uncheck all, then check the matching radio
      document.querySelectorAll('input[name="edit_role"]').forEach(r => r.checked = false);
      const radio = document.querySelector(`input[name="edit_role"][data-key="${role}"]`);
      if (radio) radio.checked = true;

      openModal('modal-edit');
    }

    // ── Approve modal ─────────────────────────────────────────
    function openApproveModal(initials, name, email) {
      document.getElementById('approve-avatar').textContent = initials;
      document.getElementById('approve-name').textContent = name;
      document.getElementById('approve-email').textContent = email;
      openModal('modal-approve');
    }
  </script>

</x-layouts.app>