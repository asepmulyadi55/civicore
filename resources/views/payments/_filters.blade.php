{{-- Search & Filter Bar --}}
<div
  class="bg-white dark:bg-slate-900 p-4 rounded-xl border border-slate-200 dark:border-slate-800 flex flex-wrap gap-4 items-center">
  <div class="flex-1 min-w-[240px] relative">
    <span class="material-icons absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-lg">search</span>
    <input
      class="w-full pl-10 pr-4 py-2 bg-background-light dark:bg-slate-800 border-transparent focus:border-primary focus:ring-0 rounded-lg text-sm"
      placeholder="Search by resident, unit, or transaction ID..." type="text" />
  </div>
  <div class="flex flex-wrap gap-2">
    <select
      class="bg-background-light dark:bg-slate-800 border-transparent focus:border-primary focus:ring-0 rounded-lg text-sm py-2 px-4 pr-8">
      <option>All Blocks</option>
      <option>Block A - Pinecrest</option>
      <option>Block B - Oakridge</option>
      <option>Block C - Maple View</option>
    </select>
    <div class="flex items-center bg-background-light dark:bg-slate-800 border-transparent rounded-lg px-2">
      <span class="material-icons text-slate-400 text-sm mr-1 px-1">calendar_today</span>
      <input class="bg-transparent border-none focus:ring-0 text-sm py-2 w-28" placeholder="Start Date" type="text" />
      <span class="text-slate-400 px-1">to</span>
      <input class="bg-transparent border-none focus:ring-0 text-sm py-2 w-28" placeholder="End Date" type="text" />
    </div>
    <select
      class="bg-background-light dark:bg-slate-800 border-transparent focus:border-primary focus:ring-0 rounded-lg text-sm py-2 px-4 pr-8">
      <option>All Methods</option>
      <option>Bank Transfer</option>
      <option>Cash</option>
      <option>Check</option>
      <option>Online Portal</option>
    </select>
    <button
      class="flex items-center gap-2 px-4 py-2 border border-slate-200 dark:border-slate-700 rounded-lg text-sm font-medium text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
      <span class="material-icons text-sm">file_download</span>
      Export
    </button>
  </div>
</div>