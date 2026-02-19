{{-- ============================================================
Modal: Edit User
Trigger: openEditModal(initials, first, last, username, email, role)
JS populates the fields; the role radio is pre-selected via JS.
============================================================ --}}
<div id="modal-edit"
  class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden"
  onclick="closeModalOnBackdrop(event, 'modal-edit')">
  <div
    class="bg-white dark:bg-slate-900 w-full max-w-xl rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-800 overflow-hidden">

    {{-- Header --}}
    <div class="px-8 py-6 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
      <div class="flex items-center gap-3">
        <div id="edit-avatar"
          class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center text-primary font-bold text-sm flex-shrink-0">
        </div>
        <div>
          <h2 class="text-xl font-bold text-slate-900 dark:text-white">Edit User</h2>
          <p id="edit-subtitle" class="text-sm text-slate-500"></p>
        </div>
      </div>
      <button class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors"
        onclick="closeModal('modal-edit')">
        <span class="material-icons">close</span>
      </button>
    </div>

    {{-- Form --}}
    <form class="p-8 space-y-6 max-h-[80vh] overflow-y-auto">

      <div class="grid grid-cols-2 gap-4">
        <div class="space-y-1.5">
          <label class="text-xs font-bold text-slate-500 uppercase">First Name</label>
          <input id="edit-firstname"
            class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border-transparent focus:border-primary focus:ring-0 rounded-lg text-sm"
            placeholder="e.g. John" type="text" />
        </div>
        <div class="space-y-1.5">
          <label class="text-xs font-bold text-slate-500 uppercase">Last Name</label>
          <input id="edit-lastname"
            class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border-transparent focus:border-primary focus:ring-0 rounded-lg text-sm"
            placeholder="e.g. Smith" type="text" />
        </div>
      </div>

      <div class="space-y-1.5">
        <label class="text-xs font-bold text-slate-500 uppercase">Username</label>
        <input id="edit-username"
          class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border-transparent focus:border-primary focus:ring-0 rounded-lg text-sm"
          placeholder="e.g. jsmith" type="text" />
      </div>

      <div class="space-y-1.5">
        <label class="text-xs font-bold text-slate-500 uppercase">Email Address</label>
        <input id="edit-email"
          class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border-transparent focus:border-primary focus:ring-0 rounded-lg text-sm"
          placeholder="john.smith@example.com" type="email" />
      </div>

      <div class="space-y-1.5">
        <label class="text-xs font-bold text-slate-500 uppercase flex justify-between">
          New Password
          <span class="text-[10px] text-slate-400 lowercase font-normal italic">Leave blank to keep current</span>
        </label>
        <input
          class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border-transparent focus:border-primary focus:ring-0 rounded-lg text-sm"
          placeholder="Min. 8 characters" type="password" />
      </div>

      {{-- Role selector — JS will set the correct radio via openEditModal() --}}
      <div class="space-y-3">
        <label class="text-xs font-bold text-slate-500 uppercase block">System Role</label>
        <x-ui.role-selector name="edit_role" selected="" />
      </div>

      {{-- Block assignment --}}
      <div class="space-y-1.5 pt-2 border-t border-slate-100 dark:border-slate-800">
        <label class="text-xs font-bold text-slate-500 uppercase flex justify-between">
          Block Assignment
          <span class="text-[10px] text-primary lowercase font-normal italic">*Required for coordinators</span>
        </label>
        <div class="relative">
          <span class="material-icons absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-lg">domain</span>
          <select id="edit-block"
            class="w-full pl-10 pr-4 py-2.5 bg-slate-50 dark:bg-slate-800 border-transparent focus:border-primary focus:ring-0 rounded-lg text-sm">
            <option value="">Select a block or phase...</option>
            <option>Block A - Pinecrest</option>
            <option>Block B - Oakridge</option>
            <option>Block C - Maple View</option>
            <option>Block D - Riverfront</option>
            <option>Phase 2 - Common Grounds</option>
          </select>
        </div>
      </div>

      {{-- Actions --}}
      <div class="flex items-center gap-4 pt-2">
        <button type="button" onclick="closeModal('modal-edit')"
          class="flex-1 px-6 py-3 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-bold text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
          Cancel
        </button>
        <button type="submit"
          class="flex-1 px-6 py-3 bg-primary text-white rounded-xl text-sm font-bold hover:bg-primary/90 shadow-lg shadow-primary/20 transition-all flex items-center justify-center gap-2">
          <span class="material-icons text-sm">save</span>
          Save Changes
        </button>
      </div>

    </form>
  </div>
</div>