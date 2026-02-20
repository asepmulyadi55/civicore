{{-- Payment Stats (uses x-ui.stat-card) --}}
<section class="grid grid-cols-1 md:grid-cols-3 gap-6">

  <x-ui.stat-card icon="pending_actions" icon-bg="bg-amber-100 dark:bg-amber-500/10" icon-text="text-amber-600"
    label="Total Pending Approval" value="$12,450.00" />

  <x-ui.stat-card icon="account_balance_wallet" icon-bg="bg-emerald-100 dark:bg-emerald-500/10"
    icon-text="text-emerald-600" label="Collected (This Month)" value="$84,200.50" badge="8.2%" badge-style="emerald" />

  <x-ui.stat-card icon="error_outline" icon-bg="bg-rose-100 dark:bg-rose-500/10" icon-text="text-rose-600"
    label="Total Unpaid Dues" value="$4,120.00" badge="Critical" badge-style="rose" />

</section>