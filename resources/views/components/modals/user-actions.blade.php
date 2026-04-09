{{-- ============================================================
components/modals/user-actions.blade.php
Confirmation modal for: Deactivate / Delete user.
Trigger via: openUserConfirmModal('deactivate'|'delete', userId, userName)
Approve now uses its own modal: openApproveModal(userId, userName, email)
Also contains shared JS helpers: toggleSidebar, openModal, closeModal,
closeModalOnBackdrop, togglePw, openEditModal, openApproveModal.
============================================================ --}}

{{-- ── Confirmation Modal (Deactivate / Delete) ───────────────────── --}}
<div id="user-confirm-modal"
  class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4"
  onclick="if(event.target===this) closeUserConfirmModal()">
  <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-2xl w-full max-w-sm overflow-hidden">
    <div class="flex flex-col items-center pt-8 pb-5 px-6 text-center">
      <div id="ucm-icon-wrap" class="w-16 h-16 rounded-full flex items-center justify-center mb-4">
        <span id="ucm-icon" class="material-icons text-3xl"></span>
      </div>
      <h3 id="ucm-title" class="text-xl font-bold text-slate-900 dark:text-white mb-2"></h3>
      <p id="ucm-body" class="text-sm text-slate-500 dark:text-slate-400 leading-relaxed"></p>
    </div>
    <div class="flex gap-3 px-6 pb-6">
      <button onclick="closeUserConfirmModal()"
        class="flex-1 px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 text-sm font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition-all">
        {{ __('app.btn_cancel') }}
      </button>
      <form id="ucm-form-deactivate" method="POST" action="" class="flex-1 hidden">
        @csrf @method('PATCH')
        <button type="submit"
          class="w-full px-4 py-2.5 rounded-xl text-sm font-bold text-white bg-amber-500 hover:bg-amber-600 transition-all">
          {{ __('app.btn_yes_deactivate') }}
        </button>
      </form>
      <form id="ucm-form-reactivate" method="POST" action="" class="flex-1 hidden">
        @csrf @method('PATCH')
        <button type="submit"
          class="w-full px-4 py-2.5 rounded-xl text-sm font-bold text-white bg-emerald-600 hover:bg-emerald-700 transition-all">
          {{ __('app.btn_reactivate') }}
        </button>
      </form>
      <form id="ucm-form-delete" method="POST" action="" class="flex-1 hidden">
        @csrf @method('DELETE')
        <button type="submit"
          class="w-full px-4 py-2.5 rounded-xl text-sm font-bold text-white bg-red-600 hover:bg-red-700 transition-all">
          {{ __('app.btn_yes_delete') }}
        </button>
      </form>
    </div>
  </div>
</div>

<script>
  // ── Shared UI helpers ─────────────────────────────────────────────
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

  function togglePw(inputId, iconId) {
    const input = document.getElementById(inputId);
    const icon = document.getElementById(iconId);
    const show = input.type === 'password';
    input.type = show ? 'text' : 'password';
    icon.textContent = show ? 'visibility' : 'visibility_off';
  }

  // ── Edit modal helper ─────────────────────────────────────────────
  function openEditModal(id, name, username, email, roleId, blockId, unitNumber) {
    document.getElementById('edit-name').value = name;
    document.getElementById('edit-username').value = username;
    document.getElementById('edit-email').value = email;
    document.getElementById('edit-password').value = '';
    document.querySelectorAll('.edit-role-radio').forEach(radio => {
      radio.checked = (radio.value !== '' && radio.value === roleId) ||
        (radio.value === '' && !roleId);
    });
    document.getElementById('edit-user-id-field').value = id;
    document.getElementById('form-edit-user').action = `${usersBaseUrl}/${id}`;

    // Set block and load units for current user data, then lookup resident
    const blockSel = document.getElementById('edit-block-id');
    if (blockSel) blockSel.value = blockId ?? '';
    if (typeof loadUnitsForUserModal === 'function') {
      loadUnitsForUserModal(blockId ?? '', 'edit-unit-number', unitNumber ?? '');
    }

    // Reset badges
    ['found', 'notfound', 'loading'].forEach(s => {
      const el = document.getElementById(`edit-resident-badge-${s}`);
      if (el) { el.classList.add('hidden'); el.classList.remove('flex'); }
    });

    // Run resident lookup for the current email
    if (typeof lookupResidentForEdit === 'function') {
      lookupResidentForEdit(email);
    }

    openModal('modal-edit');
  }

  // ── Approve modal helper ──────────────────────────────────────────
  const usersBaseUrl    = "{{ url('/users') }}";
  const CHECK_URL_APPROVE = '{{ route('users.check-resident-email') }}';
  const CSRF_APPROVE = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';

  function openApproveModal(userId, userName, userEmail, currentBlockId, currentUnitNumber) {
    document.getElementById('approve-subtitle').textContent = userName + ' — ' + userEmail;
    document.getElementById('form-approve-user').action = `${usersBaseUrl}/${userId}/approve`;

    const blockSel = document.getElementById('approve-block-id');
    // Pre-fill with the values already saved on the user record
    if (blockSel) { blockSel.value = currentBlockId ?? ''; blockSel.disabled = false; }
    if (typeof loadUnitsForUserModal === 'function') {
      loadUnitsForUserModal(currentBlockId ?? '', 'approve-unit-number', currentUnitNumber ?? '');
    }

    // Reset badges
    ['found', 'notfound'].forEach(s => {
      const el = document.getElementById(`approve-resident-badge-${s}`);
      if (el) { el.classList.add('hidden'); el.classList.remove('flex'); }
    });

    openModal('modal-approve');

    // Run lookup if email provided
    if (userEmail) {
      fetch(CHECK_URL_APPROVE, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_APPROVE },
        body: JSON.stringify({ email: userEmail }),
      })
        .then(r => r.json())
        .then(data => {
          const badgeFound = document.getElementById('approve-resident-badge-found');
          const badgeNot = document.getElementById('approve-resident-badge-notfound');
          if (data.found) {
            badgeFound?.classList.remove('hidden');
            badgeFound?.classList.add('flex');
            badgeNot?.classList.add('hidden');
            if (blockSel) { blockSel.value = data.block_id ?? ''; blockSel.disabled = true; }
            if (typeof loadUnitsForUserModal === 'function') {
              loadUnitsForUserModal(data.block_id, 'approve-unit-number', data.unit_number ?? '').then(() => {
                const apprSel = document.getElementById('approve-unit-number');
                if (apprSel) { apprSel.disabled = true; apprSel.classList.add('opacity-60', 'cursor-not-allowed'); }
              });
            }
          } else {
            badgeNot?.classList.remove('hidden');
            badgeNot?.classList.add('flex');
            badgeFound?.classList.add('hidden');
          }
        })
        .catch(() => { });
    }
  }

  // ── Confirm modal (deactivate / delete) ───────────────────────────
  const UCM_CONFIGS = {
    deactivate: {
      iconWrap: 'bg-amber-100 dark:bg-amber-900/30',
      icon: 'person_off',
      iconColor: 'text-amber-600 dark:text-amber-400',
      title: '{{ __('app.deactivate_user_title') }}',
      body: name => `<strong class="text-slate-800 dark:text-slate-200">${name}</strong> {{ __('app.deactivate_user_body') }}`,
      form: 'ucm-form-deactivate',
      route: id => `${usersBaseUrl}/${id}/deactivate`,
    },
    reactivate: {
      iconWrap: 'bg-emerald-100 dark:bg-emerald-900/30',
      icon: 'person_add',
      iconColor: 'text-emerald-600 dark:text-emerald-400',
      title: '{{ __('app.reactivate_user_title') }}',
      body: name => `{{ __('app.reactivate_user_body_before') }} <strong class="text-slate-800 dark:text-slate-200">${name}</strong>{{ __('app.reactivate_user_body_after') }}`,
      form: 'ucm-form-reactivate',
      route: id => `${usersBaseUrl}/${id}/reactivate`,
    },
    delete: {
      iconWrap: 'bg-red-100 dark:bg-red-900/30',
      icon: 'delete_forever',
      iconColor: 'text-red-600 dark:text-red-400',
      title: '{{ __('app.delete_user_title') }}',
      body: name => `<strong class="text-slate-800 dark:text-slate-200">${name}</strong> {{ __('app.delete_user_body') }}`,
      form: 'ucm-form-delete',
      route: id => `${usersBaseUrl}/${id}`,
    },
  };

  function openUserConfirmModal(action, userId, userName) {
    const cfg = UCM_CONFIGS[action];
    if (!cfg) return;
    document.getElementById('ucm-icon-wrap').className =
      `w-16 h-16 rounded-full flex items-center justify-center mb-4 ${cfg.iconWrap}`;
    const iconEl = document.getElementById('ucm-icon');
    iconEl.textContent = cfg.icon;
    iconEl.className = `material-icons text-3xl ${cfg.iconColor}`;
    document.getElementById('ucm-title').textContent = cfg.title;
    document.getElementById('ucm-body').innerHTML = cfg.body(userName);
    ['deactivate', 'reactivate', 'delete'].forEach(a =>
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
      closeModal('modal-approve');
      closeUserConfirmModal();
    }
  });

  // Block change in approve modal → reload unit dropdown
  document.getElementById('approve-block-id')?.addEventListener('change', function () {
    if (typeof loadUnitsForUserModal === 'function') {
      loadUnitsForUserModal(this.value, 'approve-unit-number', null);
    }
  });
</script>