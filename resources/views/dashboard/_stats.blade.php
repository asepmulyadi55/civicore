{{-- Dashboard Stats Cards — real DB data --}}
<section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">

  <x-ui.stat-card icon="account_balance_wallet" icon-bg="bg-primary/10" icon-text="text-primary"
    label="This Month's Collections" value="{{ $currency }} {{ number_format($totalCollected, 0, ',', '.') }}"
    badge-style="emerald" />

  <x-ui.stat-card icon="pending_actions" icon-bg="bg-amber-100 dark:bg-amber-500/10" icon-text="text-amber-500"
    label="Pending Approvals" value="{{ $pendingCount }}" />

  <x-ui.stat-card icon="priority_high" icon-bg="bg-rose-100 dark:bg-rose-500/10" icon-text="text-rose-500"
    label="Unpaid Residents" value="{{ $unpaidCount }}" badge="{{ $unpaidCount > 0 ? 'Needs attention' : 'All paid' }}"
    badge-style="{{ $unpaidCount > 0 ? 'rose' : 'emerald' }}" />

  <x-ui.stat-card icon="people_alt" icon-bg="bg-indigo-100 dark:bg-indigo-500/10" icon-text="text-indigo-500"
    label="Active Residents" value="{{ number_format($activeResidents) }}" />

</section>