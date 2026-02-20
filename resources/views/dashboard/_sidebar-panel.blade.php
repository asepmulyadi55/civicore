{{-- Quick Actions + Community Status + Admin Memo --}}
<div
  class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm p-6 space-y-6">
  <h2 class="text-lg font-bold text-slate-900 dark:text-white">Quick Actions</h2>
  <div class="grid grid-cols-2 gap-4">
    <button
      class="flex flex-col items-center justify-center p-4 bg-primary text-white rounded-xl hover:bg-blue-600 transition-colors space-y-2 text-center group">
      <span class="material-icons text-2xl group-hover:scale-110 transition-transform">person_add</span>
      <span class="text-xs font-bold">Register Resident</span>
    </button>
    <button
      class="flex flex-col items-center justify-center p-4 bg-slate-100 dark:bg-slate-800 text-slate-900 dark:text-white rounded-xl hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors space-y-2 text-center group">
      <span class="material-icons text-2xl text-primary group-hover:scale-110 transition-transform">receipt_long</span>
      <span class="text-xs font-bold">New Payment</span>
    </button>
    <button
      class="flex flex-col items-center justify-center p-4 bg-slate-100 dark:bg-slate-800 text-slate-900 dark:text-white rounded-xl hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors space-y-2 text-center group">
      <span class="material-icons text-2xl text-primary group-hover:scale-110 transition-transform">campaign</span>
      <span class="text-xs font-bold">Broadcast</span>
    </button>
    <button
      class="flex flex-col items-center justify-center p-4 bg-slate-100 dark:bg-slate-800 text-slate-900 dark:text-white rounded-xl hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors space-y-2 text-center group">
      <span
        class="material-icons text-2xl text-primary group-hover:scale-110 transition-transform">picture_as_pdf</span>
      <span class="text-xs font-bold">Generate Report</span>
    </button>
  </div>

  <hr class="border-slate-100 dark:border-slate-800" />

  <div class="space-y-4">
    <h3 class="text-sm font-bold text-slate-500 uppercase tracking-wider">Community Status</h3>
    <div class="space-y-3">
      <div class="flex items-center justify-between">
        <span class="text-sm font-medium">Block A (Full)</span>
        <div class="w-32 h-2 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
          <div class="bg-primary h-full w-full"></div>
        </div>
      </div>
      <div class="flex items-center justify-between">
        <span class="text-sm font-medium">Block B</span>
        <div class="w-32 h-2 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
          <div class="bg-primary h-full w-3/4"></div>
        </div>
      </div>
      <div class="flex items-center justify-between">
        <span class="text-sm font-medium">Block C</span>
        <div class="w-32 h-2 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
          <div class="bg-primary h-full w-[45%]"></div>
        </div>
      </div>
    </div>
  </div>

  <div class="p-4 bg-amber-50 dark:bg-amber-900/10 border border-amber-100 dark:border-amber-900/30 rounded-lg">
    <div class="flex items-center space-x-2 text-amber-700 dark:text-amber-400 mb-2">
      <span class="material-icons text-sm">sticky_note_2</span>
      <span class="text-xs font-bold uppercase tracking-wider">Admin Memo</span>
    </div>
    <p class="text-xs text-amber-800 dark:text-amber-300 leading-relaxed">
      Upcoming maintenance for the Block A elevator scheduled for Oct 28. Notify all residents by tomorrow noon.
    </p>
  </div>
</div>