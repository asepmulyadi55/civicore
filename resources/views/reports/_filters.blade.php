{{-- Report Filters --}}
<section
  class="bg-white dark:bg-slate-900 p-4 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm no-print">
  <div class="grid grid-cols-1 md:grid-cols-4 lg:grid-cols-5 gap-4">
    <div class="space-y-1.5">
      <label class="text-xs font-bold text-slate-400 uppercase tracking-tight ml-1">Select Year</label>
      <div class="relative">
        <select
          class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-lg text-sm focus:ring-2 focus:ring-primary appearance-none px-4 py-2.5 text-slate-700 dark:text-slate-200">
          <option>2024</option>
          <option>2023</option>
          <option>2022</option>
        </select>
        <span
          class="material-icons absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400 text-[18px]">expand_more</span>
      </div>
    </div>
    <div class="space-y-1.5">
      <label class="text-xs font-bold text-slate-400 uppercase tracking-tight ml-1">Select Block</label>
      <div class="relative">
        <select
          class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-lg text-sm focus:ring-2 focus:ring-primary appearance-none px-4 py-2.5 text-slate-700 dark:text-slate-200">
          <option>Block A (Terrace)</option>
          <option>Block B (Apartments)</option>
          <option>Block C (Villas)</option>
          <option>Block D (Commercial)</option>
        </select>
        <span
          class="material-icons absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400 text-[18px]">expand_more</span>
      </div>
    </div>
    <div class="md:col-span-2 lg:col-span-2 space-y-1.5">
      <label class="text-xs font-bold text-slate-400 uppercase tracking-tight ml-1">Search Resident</label>
      <div class="relative">
        <span class="material-icons absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-lg">search</span>
        <input
          class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-lg text-sm focus:ring-2 focus:ring-primary pl-10 py-2.5 text-slate-700 dark:text-slate-200"
          placeholder="Search by name or unit number..." type="text" />
      </div>
    </div>
    <div class="flex items-end">
      <button
        class="w-full bg-primary/10 dark:bg-primary/20 text-primary hover:bg-primary/20 py-2.5 rounded-lg text-sm font-bold transition-colors">
        Search
      </button>
    </div>
  </div>
</section>