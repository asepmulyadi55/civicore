{{-- Dashboard Stats Cards — real DB data --}}
<section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">

  <x-ui.stat-card icon="account_balance_wallet" icon-bg="bg-primary/10" icon-text="text-primary"
    :label="__('app.stat_this_month_collections')"
    value="{{ $currency }} {{ number_format($totalCollected, 0, ',', '.') }}" badge-style="emerald" />

  <x-ui.stat-card icon="pending_actions" icon-bg="bg-amber-100 dark:bg-amber-500/10" icon-text="text-amber-500"
    :label="__('app.stat_pending_approvals')" value="{{ $pendingCount }}" />

  <x-ui.stat-card icon="priority_high" icon-bg="bg-rose-100 dark:bg-rose-500/10" icon-text="text-rose-500"
    :label="__('app.stat_unpaid_residents')" value="{{ $unpaidCount }}" :badge="$unpaidCount > 0 ? __('app.stat_needs_attention') : __('app.stat_all_paid')"
    badge-style="{{ $unpaidCount > 0 ? 'rose' : 'emerald' }}" />

  <x-ui.stat-card icon="people_alt" icon-bg="bg-indigo-100 dark:bg-indigo-500/10" icon-text="text-indigo-500"
    :label="__('app.stat_active_residents')" value="{{ number_format($activeResidents) }}" />

</section>