{{-- Users Table --}}
<div
  class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
  <div class="overflow-x-auto">
    <table class="w-full text-left border-collapse">
      <thead>
        <tr class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-800">
          <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">User Details</th>
          <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Email</th>
          <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Role</th>
          <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Status</th>
          <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider text-right">Actions</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-200 dark:divide-slate-800">

        {{-- Row: Active Admin --}}
        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/20 transition-colors">
          <td class="px-6 py-4">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center text-primary font-bold">
                JD</div>
              <div>
                <div class="font-bold text-slate-900 dark:text-white">Jane Doe</div>
                <div class="text-xs text-slate-400 uppercase">CiviCore Staff</div>
              </div>
            </div>
          </td>
          <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-400">jane.doe@example.com</td>
          <td class="px-6 py-4">
            <span
              class="px-2 py-1 text-[10px] font-bold bg-purple-100 text-purple-700 rounded-lg uppercase">Admin</span>
          </td>
          <td class="px-6 py-4">
            <span
              class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400">
              <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>Active
            </span>
          </td>
          <td class="px-6 py-4 text-right">
            <div class="flex justify-end gap-2">
              <button title="Edit" onclick="openEditModal('JD','Jane','Doe','jdoe','jane.doe@example.com','admin')"
                class="p-1.5 text-slate-400 hover:text-primary transition-colors">
                <span class="material-icons text-xl">edit</span>
              </button>
              <button title="Delete" class="p-1.5 text-slate-400 hover:text-red-500 transition-colors">
                <span class="material-icons text-xl">delete_outline</span>
              </button>
            </div>
          </td>
        </tr>

        {{-- Row: Active Treasurer --}}
        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/20 transition-colors">
          <td class="px-6 py-4">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-bold">
                RM</div>
              <div>
                <div class="font-bold text-slate-900 dark:text-white">Robert Miller</div>
                <div class="text-xs text-slate-400 uppercase">Finance Team</div>
              </div>
            </div>
          </td>
          <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-400">robert.m@civicore.com</td>
          <td class="px-6 py-4">
            <span
              class="px-2 py-1 text-[10px] font-bold bg-amber-100 text-amber-700 rounded-lg uppercase">Treasurer</span>
          </td>
          <td class="px-6 py-4">
            <span
              class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400">
              <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>Active
            </span>
          </td>
          <td class="px-6 py-4 text-right">
            <div class="flex justify-end gap-2">
              <button title="Edit"
                onclick="openEditModal('RM','Robert','Miller','rmiller','robert.m@civicore.com','treasurer')"
                class="p-1.5 text-slate-400 hover:text-primary transition-colors">
                <span class="material-icons text-xl">edit</span>
              </button>
              <button title="Delete" class="p-1.5 text-slate-400 hover:text-red-500 transition-colors">
                <span class="material-icons text-xl">delete_outline</span>
              </button>
            </div>
          </td>
        </tr>

        {{-- Row: Inactive Coordinator --}}
        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/20 transition-colors">
          <td class="px-6 py-4">
            <div class="flex items-center gap-3">
              <div
                class="w-10 h-10 rounded-full bg-orange-100 flex items-center justify-center text-orange-600 font-bold">
                SW</div>
              <div>
                <div class="font-bold text-slate-900 dark:text-white">Sarah Wilson</div>
                <div class="text-xs text-slate-400 uppercase">Block Phase 2</div>
              </div>
            </div>
          </td>
          <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-400">sarah.w@residence.net</td>
          <td class="px-6 py-4">
            <span
              class="px-2 py-1 text-[10px] font-bold bg-indigo-100 text-indigo-700 rounded-lg uppercase">Coordinator</span>
          </td>
          <td class="px-6 py-4">
            <span
              class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-800 dark:bg-slate-700 dark:text-slate-300">
              <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>Inactive
            </span>
          </td>
          <td class="px-6 py-4 text-right">
            <div class="flex justify-end gap-2">
              <button title="Edit"
                onclick="openEditModal('SW','Sarah','Wilson','swilson','sarah.w@residence.net','coordinator')"
                class="p-1.5 text-slate-400 hover:text-primary transition-colors">
                <span class="material-icons text-xl">edit</span>
              </button>
              <button title="Delete" class="p-1.5 text-slate-400 hover:text-red-500 transition-colors">
                <span class="material-icons text-xl">delete_outline</span>
              </button>
            </div>
          </td>
        </tr>

        {{-- Row: Pending Coordinator --}}
        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/20 transition-colors">
          <td class="px-6 py-4">
            <div class="flex items-center gap-3">
              <div
                class="w-10 h-10 rounded-full bg-slate-200 flex items-center justify-center text-slate-500 font-bold">MK
              </div>
              <div>
                <div class="font-bold text-slate-900 dark:text-white">Mark Kendrick</div>
                <div class="text-xs text-slate-400 uppercase">New Application</div>
              </div>
            </div>
          </td>
          <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-400">mark.k@outlook.com</td>
          <td class="px-6 py-4">
            <span
              class="px-2 py-1 text-[10px] font-bold bg-indigo-100 text-indigo-700 rounded-lg uppercase">Coordinator</span>
          </td>
          <td class="px-6 py-4">
            <span
              class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400">
              <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>Pending
            </span>
          </td>
          <td class="px-6 py-4 text-right">
            <div class="flex justify-end gap-2">
              <button onclick="openApproveModal('MK','Mark Kendrick','mark.k@outlook.com')"
                class="bg-primary text-white text-[10px] px-3 py-1.5 rounded font-bold uppercase tracking-wider hover:bg-primary/90 transition-colors">
                Approve
              </button>
              <button title="Delete" class="p-1.5 text-slate-400 hover:text-red-500 transition-colors">
                <span class="material-icons text-xl">delete_outline</span>
              </button>
            </div>
          </td>
        </tr>

      </tbody>
    </table>
  </div>

  {{-- Pagination --}}
  <div class="p-4 border-t border-slate-200 dark:border-slate-800 flex items-center justify-between">
    <span class="text-sm text-slate-500">Showing 4 of 1,248 CiviCore users</span>
    <div class="flex gap-2">
      <button
        class="p-2 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800 disabled:opacity-50"
        disabled>
        <span class="material-icons">chevron_left</span>
      </button>
      <button
        class="p-2 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800">
        <span class="material-icons">chevron_right</span>
      </button>
    </div>
  </div>
</div>