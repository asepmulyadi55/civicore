{{-- Recent Activity Table --}}
<div
  class="xl:col-span-2 bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden flex flex-col">
  <div class="p-6 border-b border-slate-200 dark:border-slate-800 flex justify-between items-center">
    <h2 class="text-lg font-bold text-slate-900 dark:text-white">Recent Activity</h2>
    <button class="text-sm font-semibold text-primary hover:underline">View All</button>
  </div>
  <div class="overflow-x-auto">
    <table class="w-full text-left">
      <thead>
        <tr class="bg-slate-50 dark:bg-slate-800/50 text-slate-500 text-xs uppercase tracking-wider font-bold">
          <th class="px-6 py-4">Resident</th>
          <th class="px-6 py-4">Activity Type</th>
          <th class="px-6 py-4">Unit/Block</th>
          <th class="px-6 py-4">Date</th>
          <th class="px-6 py-4 text-right">Status</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/20 transition-colors">
          <td class="px-6 py-4">
            <div class="flex items-center space-x-3">
              <div class="w-8 h-8 rounded-full bg-primary/10 flex items-center justify-center">
                <span class="material-icons text-primary text-sm">person</span>
              </div>
              <span class="text-sm font-semibold">Mark Spencer</span>
            </div>
          </td>
          <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-400">Monthly Fee Paid</td>
          <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-400">Block A - 402</td>
          <td class="px-6 py-4 text-sm text-slate-500">Oct 24, 2:45 PM</td>
          <td class="px-6 py-4 text-right">
            <span
              class="px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400">Success</span>
          </td>
        </tr>
        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/20 transition-colors">
          <td class="px-6 py-4">
            <div class="flex items-center space-x-3">
              <div class="w-8 h-8 rounded-full bg-primary/10 flex items-center justify-center">
                <span class="material-icons text-primary text-sm">person</span>
              </div>
              <span class="text-sm font-semibold">Sarah Jenkins</span>
            </div>
          </td>
          <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-400">Registration Request</td>
          <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-400">Block C - 105</td>
          <td class="px-6 py-4 text-sm text-slate-500">Oct 24, 11:20 AM</td>
          <td class="px-6 py-4 text-right">
            <span
              class="px-2.5 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400">Pending</span>
          </td>
        </tr>
        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/20 transition-colors">
          <td class="px-6 py-4">
            <div class="flex items-center space-x-3">
              <div class="w-8 h-8 rounded-full bg-primary/10 flex items-center justify-center">
                <span class="material-icons text-primary text-sm">person</span>
              </div>
              <span class="text-sm font-semibold">David Chen</span>
            </div>
          </td>
          <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-400">Amenity Booking</td>
          <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-400">Gym Facility</td>
          <td class="px-6 py-4 text-sm text-slate-500">Oct 23, 5:10 PM</td>
          <td class="px-6 py-4 text-right">
            <span
              class="px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400">Success</span>
          </td>
        </tr>
        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/20 transition-colors">
          <td class="px-6 py-4">
            <div class="flex items-center space-x-3">
              <div class="w-8 h-8 rounded-full bg-primary/10 flex items-center justify-center">
                <span class="material-icons text-primary text-sm">person</span>
              </div>
              <span class="text-sm font-semibold">Elena Rodriguez</span>
            </div>
          </td>
          <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-400">Late Payment Alert</td>
          <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-400">Block B - 201</td>
          <td class="px-6 py-4 text-sm text-slate-500">Oct 23, 9:00 AM</td>
          <td class="px-6 py-4 text-right">
            <span
              class="px-2.5 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-400">Overdue</span>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</div>