{{-- Page Header --}}
<header
  class="h-16 bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between px-6 lg:px-8">
  <div class="flex items-center gap-4">
    <button class="lg:hidden p-2 rounded-lg border border-slate-200 dark:border-slate-800" onclick="toggleSidebar()">
      <span class="material-icons text-slate-500">menu</span>
    </button>
    <h1 class="text-xl font-bold text-slate-900 dark:text-white">User Access &amp; Roles</h1>
    <span
      class="px-2 py-1 text-xs font-semibold bg-primary/10 text-primary rounded-lg uppercase hidden sm:inline">CiviCore
      Admin</span>
  </div>
  <div class="flex items-center gap-4">
    {{-- Auto-Approval toggle --}}
    <div
      class="hidden sm:flex items-center gap-2 px-3 py-1.5 bg-background-light dark:bg-slate-800 rounded-full border border-slate-200 dark:border-slate-700">
      <span class="text-xs font-medium text-slate-500">Auto-Approval</span>
      <button
        class="relative inline-flex h-5 w-10 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 focus:outline-none bg-slate-300 dark:bg-slate-600"
        id="auto-approval-toggle" onclick="toggleAutoApproval(this)">
        <span
          class="translate-x-0 pointer-events-none inline-block h-4 w-4 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"></span>
      </button>
    </div>
    {{-- Opens Create User modal --}}
    <button onclick="openModal('modal-create')"
      class="flex items-center gap-2 bg-primary hover:bg-primary/90 text-white px-4 py-2 rounded-lg font-semibold transition-all shadow-sm shadow-primary/20 text-sm">
      <span class="material-icons text-sm">person_add</span>
      <span class="hidden sm:inline">Create User</span>
    </button>
    <button
      class="p-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg hover:border-primary/50 transition-all"
      onclick="toggleDark()" title="Toggle dark mode">
      <span class="material-icons text-slate-500 text-[20px]">dark_mode</span>
    </button>
  </div>
</header>