{{-- Payments Table --}}
<div
  class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
  <div class="overflow-x-auto">
    <table class="w-full text-left border-collapse">
      <thead>
        <tr class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-800">
          <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Resident</th>
          <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Amount</th>
          <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Date</th>
          <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Method</th>
          <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Status</th>
          <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider text-right">Actions</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-200 dark:divide-slate-800">

        {{-- Approved --}}
        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/20 transition-colors">
          <td class="px-6 py-4">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center text-primary font-bold">
                JD</div>
              <div>
                <div class="font-bold text-slate-900 dark:text-white">Jane Doe</div>
                <div class="text-xs text-slate-500">Block A - Unit 402</div>
              </div>
            </div>
          </td>
          <td class="px-6 py-4 text-sm font-bold text-slate-900 dark:text-white">$450.00</td>
          <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-400">Oct 12, 2023</td>
          <td class="px-6 py-4">
            <div class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-400">
              <span class="material-icons text-sm">account_balance</span> Bank Transfer
            </div>
          </td>
          <td class="px-6 py-4">
            <span
              class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400">
              <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>Approved
            </span>
          </td>
          <td class="px-6 py-4 text-right">
            <button title="View Receipt" class="p-1.5 text-slate-400 hover:text-primary transition-colors">
              <span class="material-icons text-xl">receipt_long</span>
            </button>
            <button title="Edit" class="p-1.5 text-slate-400 hover:text-primary transition-colors"
              onclick="openPaymentModal('JD','Jane Doe','Block A - Unit 402', 50, [0,1,2])">
              <span class="material-icons text-xl">edit</span>
            </button>
          </td>
        </tr>

        {{-- Pending → Verify opens modal --}}
        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/20 transition-colors">
          <td class="px-6 py-4">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-bold">
                RM</div>
              <div>
                <div class="font-bold text-slate-900 dark:text-white">Robert Miller</div>
                <div class="text-xs text-slate-500">Block B - Unit 105</div>
              </div>
            </div>
          </td>
          <td class="px-6 py-4 text-sm font-bold text-slate-900 dark:text-white">$1,200.00</td>
          <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-400">Oct 14, 2023</td>
          <td class="px-6 py-4">
            <div class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-400">
              <span class="material-icons text-sm">payments</span> Cash
            </div>
          </td>
          <td class="px-6 py-4">
            <span
              class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400">
              <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>Pending
            </span>
          </td>
          <td class="px-6 py-4 text-right">
            <div class="flex justify-end gap-2">
              <button onclick="openPaymentModal('RM','Robert Miller','Block B - Unit 105', 50, [0,1])"
                class="bg-primary text-white text-[10px] px-3 py-1.5 rounded font-bold uppercase tracking-wider hover:bg-primary/90 transition-colors flex items-center gap-1">
                <span class="material-icons text-xs">verified</span>
                Verify
              </button>
            </div>
          </td>
        </tr>

        {{-- Unpaid --}}
        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/20 transition-colors">
          <td class="px-6 py-4">
            <div class="flex items-center gap-3">
              <div
                class="w-10 h-10 rounded-full bg-orange-100 flex items-center justify-center text-orange-600 font-bold">
                SW</div>
              <div>
                <div class="font-bold text-slate-900 dark:text-white">Sarah Wilson</div>
                <div class="text-xs text-slate-500">Block C - Unit 002</div>
              </div>
            </div>
          </td>
          <td class="px-6 py-4 text-sm font-bold text-slate-900 dark:text-white">$620.00</td>
          <td class="px-6 py-4 text-sm text-slate-400">—</td>
          <td class="px-6 py-4 text-sm text-slate-400 italic">N/A</td>
          <td class="px-6 py-4">
            <span
              class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium bg-rose-100 text-rose-800 dark:bg-rose-900/30 dark:text-rose-400">
              <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>Unpaid
            </span>
          </td>
          <td class="px-6 py-4 text-right">
            <button title="Send Reminder" class="p-1.5 text-slate-400 hover:text-primary transition-colors">
              <span class="material-icons text-xl">notification_add</span>
            </button>
            <button title="Record Payment" class="p-1.5 text-slate-400 hover:text-primary transition-colors"
              onclick="openPaymentModal('SW','Sarah Wilson','Block C - Unit 002', 50, [])">
              <span class="material-icons text-xl">edit</span>
            </button>
          </td>
        </tr>

        {{-- Approved --}}
        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/20 transition-colors">
          <td class="px-6 py-4">
            <div class="flex items-center gap-3">
              <div
                class="w-10 h-10 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 font-bold">
                MK</div>
              <div>
                <div class="font-bold text-slate-900 dark:text-white">Mark Kendrick</div>
                <div class="text-xs text-slate-500">Block B - Unit 201</div>
              </div>
            </div>
          </td>
          <td class="px-6 py-4 text-sm font-bold text-slate-900 dark:text-white">$450.00</td>
          <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-400">Oct 10, 2023</td>
          <td class="px-6 py-4">
            <div class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-400">
              <span class="material-icons text-sm">credit_card</span> Online Portal
            </div>
          </td>
          <td class="px-6 py-4">
            <span
              class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400">
              <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>Approved
            </span>
          </td>
          <td class="px-6 py-4 text-right">
            <button title="View Receipt" class="p-1.5 text-slate-400 hover:text-primary transition-colors">
              <span class="material-icons text-xl">receipt_long</span>
            </button>
            <button title="Edit" class="p-1.5 text-slate-400 hover:text-primary transition-colors"
              onclick="openPaymentModal('MK','Mark Kendrick','Block B - Unit 201', 50, [0,1,2])">
              <span class="material-icons text-xl">edit</span>
            </button>
          </td>
        </tr>

      </tbody>
    </table>
  </div>

  {{-- Pagination --}}
  <div class="p-4 border-t border-slate-200 dark:border-slate-800 flex items-center justify-between">
    <span class="text-sm text-slate-500">Showing 4 of 248 transactions</span>
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