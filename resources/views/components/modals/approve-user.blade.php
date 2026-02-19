{{-- ============================================================
Modal: Approve User
Trigger: openApproveModal(initials, name, email)
============================================================ --}}
<div id="modal-approve"
  class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden"
  onclick="closeModalOnBackdrop(event, 'modal-approve')">
  <div
    class="bg-white dark:bg-slate-900 rounded-2xl shadow-2xl w-full max-w-2xl border border-slate-200 dark:border-slate-800 overflow-hidden">

    {{-- Header --}}
    <div
      class="px-6 py-5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between bg-slate-50/50 dark:bg-slate-800/30">
      <h3 class="text-lg font-bold text-slate-900 dark:text-white flex items-center gap-2">
        <span class="material-icons text-primary">verified_user</span>
        Approve User Account
      </h3>
      <button class="text-slate-400 hover:text-slate-500 dark:hover:text-slate-300 transition-colors"
        onclick="closeModal('modal-approve')">
        <span class="material-icons">close</span>
      </button>
    </div>

    {{-- Body --}}
    <div class="p-6 space-y-8 max-h-[80vh] overflow-y-auto">

      {{-- Pending user info card (JS-populated) --}}
      <div
        class="flex items-center gap-4 bg-blue-50/50 dark:bg-blue-900/10 p-4 rounded-xl border border-blue-100 dark:border-blue-900/20">
        <div id="approve-avatar"
          class="w-14 h-14 rounded-full bg-slate-200 dark:bg-slate-700 flex items-center justify-center text-slate-500 dark:text-slate-300 font-bold text-xl ring-4 ring-white dark:ring-slate-900 shadow-sm flex-shrink-0">
        </div>
        <div>
          <h4 id="approve-name" class="text-lg font-bold text-slate-900 dark:text-white"></h4>
          <p id="approve-email" class="text-sm text-slate-500 font-medium"></p>
          <div class="flex items-center gap-2 mt-1">
            <span
              class="text-xs px-2 py-0.5 bg-slate-200 dark:bg-slate-700 rounded text-slate-600 dark:text-slate-300">New
              Registration</span>
            <span class="text-xs text-slate-400">• Requested: 2 hours ago</span>
          </div>
        </div>
      </div>

      {{-- Role selector --}}
      <div>
        <h4
          class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider mb-4 flex items-center gap-2">
          <span class="material-icons text-base text-slate-400">admin_panel_settings</span>
          Select System Role
        </h4>
        <x-ui.role-selector name="approve_role" selected="coordinator" />
      </div>

      {{-- Block assignment --}}
      <div>
        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Block Assignment</label>
        <div class="relative">
          <span class="material-icons absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">domain</span>
          <select
            class="w-full pl-10 pr-4 py-2.5 bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded-lg focus:ring-primary focus:border-primary text-sm shadow-sm">
            <option>Select Block Phase...</option>
            <option>Phase 1 - North Wing</option>
            <option selected>Phase 2 - South Gardens</option>
            <option>Phase 3 - East Towers</option>
          </select>
        </div>
        <p class="text-xs text-slate-500 mt-1.5 ml-1">Assigns the user to manage a specific residential zone.</p>
      </div>

    </div>

    {{-- Footer --}}
    <div
      class="px-6 py-4 bg-slate-50 dark:bg-slate-800/50 border-t border-slate-100 dark:border-slate-800 flex justify-end gap-3">
      <button type="button" onclick="closeModal('modal-approve')"
        class="px-5 py-2.5 rounded-lg text-sm font-semibold text-slate-600 hover:bg-slate-200 dark:text-slate-300 dark:hover:bg-slate-700 transition-colors">
        Cancel
      </button>
      <button type="button"
        class="px-5 py-2.5 rounded-lg text-sm font-semibold bg-primary hover:bg-primary/90 text-white shadow-lg shadow-primary/30 transition-all flex items-center gap-2">
        <span class="material-icons text-sm">check</span>
        Approve &amp; Activate
      </button>
    </div>

  </div>
</div>