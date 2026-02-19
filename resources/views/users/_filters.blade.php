{{-- Search & Filter Bar --}}
<div
  class="bg-white dark:bg-slate-900 p-4 rounded-xl border border-slate-200 dark:border-slate-800 flex flex-wrap gap-4 items-center">
  <div class="flex-1 min-w-[240px] relative">
    <span class="material-icons absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-lg">search</span>
    <input
      class="w-full pl-10 pr-4 py-2 bg-background-light dark:bg-slate-800 border-transparent focus:border-primary focus:ring-0 rounded-lg text-sm"
      placeholder="Search by name, email or ID..." type="text" />
  </div>
  <div class="flex gap-2 flex-wrap">
    <select
      class="bg-background-light dark:bg-slate-800 border-transparent focus:border-primary focus:ring-0 rounded-lg text-sm py-2 px-4 pr-8">
      <option>All Roles</option>
      <option>Admin</option>
      <option>Treasurer</option>
      <option>Block Coordinator</option>
    </select>
    <select
      class="bg-background-light dark:bg-slate-800 border-transparent focus:border-primary focus:ring-0 rounded-lg text-sm py-2 px-4 pr-8">
      <option>All Status</option>
      <option>Active</option>
      <option>Inactive</option>
      <option>Pending Approval</option>
    </select>
    <button
      class="flex items-center gap-2 px-4 py-2 border border-slate-200 dark:border-slate-700 rounded-lg text-sm font-medium text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
      <span class="material-icons text-sm">filter_list</span>
      More Filters
    </button>
  </div>
</div>