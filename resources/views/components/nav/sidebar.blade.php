@props(['active' => ''])

@php
  use Illuminate\Support\Str;
  $user = Auth::user();

  // -- Nav type: flat (overview-only roles) vs grouped (staff roles) ----------
  if ($user->can('overview.view') && !$user->can('dashboard.view')) {
    // Simple flat nav for residents, posyandu, and any overview-only role
    $flatItems = [];
    if ($user->can('overview.view')) {
      $flatItems[] = ['key' => 'overview', 'label_raw' => __('app.nav_overview'), 'icon' => 'dashboard', 'route' => 'overview', 'permission' => 'overview.view'];
    }
    if ($user->resolveHouseholder()) {
      $flatItems[] = ['key' => 'household', 'label_raw' => __('app.nav_household'), 'icon' => 'home', 'route' => 'household.show', 'permission' => null];
    }
    if ($user->can('posyandu.view')) {
      $flatItems[] = ['key' => 'posyandu', 'label_raw' => 'Posyandu', 'icon' => 'health_and_safety', 'route' => 'posyandu.index', 'permission' => 'posyandu.view'];
    }
    if ($user->can('finance.view')) {
      $flatItems[] = ['key' => 'finance', 'label_raw' => __('app.nav_finance'), 'icon' => 'account_balance', 'route' => 'finance.index', 'permission' => 'finance.view'];
    }
    if ($user->can('payments.view')) {
      $flatItems[] = ['key' => 'payments', 'label_raw' => __('app.nav_payments'), 'icon' => 'payments', 'route' => 'payments.index', 'permission' => 'payments.view'];
    }
    if ($user->can('reports.view')) {
      $flatItems[] = ['key' => 'reports', 'label_raw' => __('app.nav_reports'), 'icon' => 'bar_chart', 'route' => 'reports.index', 'permission' => 'reports.view'];
    }
    $flatItems[] = ['key' => 'organization', 'label_raw' => __('app.nav_organization'), 'icon' => 'account_tree', 'route' => 'organization.index', 'permission' => null];
    $flatItems[] = ['key' => 'settings', 'label_raw' => __('app.nav_settings'), 'icon' => 'settings', 'route' => 'settings.index', 'permission' => null];
    $navGroups = [['label' => null, 'group_icon' => null, 'items' => $flatItems]];
  } else {
    $allGroups = [
      [
        'label' => null,
        'group_icon' => null,
        'items' => [
          ['key' => 'dashboard', 'label' => __('app.nav_dashboard'), 'icon' => 'dashboard', 'route' => 'dashboard', 'permission' => 'dashboard.view'],
        ],
      ],
      [
        'label' => __('app.nav_group_community'),
        'group_icon' => 'groups',
        'items' => [
          ['key' => 'householders', 'label' => __('app.nav_residents'), 'icon' => 'people', 'route' => 'householders.index', 'permission' => 'householders.view'],
          ['key' => 'blocks', 'label' => __('app.nav_blocks'), 'icon' => 'domain', 'route' => 'blocks.index', 'permission' => 'blocks.view'],
          ['key' => 'posyandu', 'label' => 'Posyandu', 'icon' => 'health_and_safety', 'route' => 'posyandu.index', 'permission' => 'posyandu.view'],
          ['key' => 'organization', 'label' => __('app.nav_organization'), 'icon' => 'account_tree', 'route' => 'organization.index', 'permission' => null],
        ],
      ],
      [
        'label' => __('app.nav_group_finance'),
        'group_icon' => 'attach_money',
        'items' => [
          ['key' => 'finance', 'label' => __('app.nav_finance'), 'icon' => 'account_balance', 'route' => 'finance.index', 'permission' => 'finance.view'],
          ['key' => 'payments', 'label' => __('app.nav_payments'), 'icon' => 'payments', 'route' => 'payments.index', 'permission' => 'payments.view'],
          ['key' => 'reports', 'label' => __('app.nav_reports'), 'icon' => 'bar_chart', 'route' => 'reports.index', 'permission' => 'reports.view'],
        ],
      ],
      [
        'label' => __('app.nav_group_administration'),
        'group_icon' => 'admin_panel_settings',
        'items' => [
          ['key' => 'users', 'label' => __('app.nav_users'), 'icon' => 'manage_accounts', 'route' => 'users.index', 'permission' => 'users.view'],
          ['key' => 'roles', 'label' => __('app.nav_roles'), 'icon' => 'admin_panel_settings', 'route' => 'roles.index', 'permission' => 'roles.view'],
          ['key' => 'homepage', 'label' => __('app.nav_homepage'), 'icon' => 'public', 'route' => 'homepage.index', 'permission' => 'homepage.view'],
          ['key' => 'media', 'label' => __('app.nav_media'), 'icon' => 'perm_media', 'route' => 'media.index', 'permission' => 'media.view'],
        ],
      ],
      [
        'label' => null,
        'group_icon' => null,
        'items' => [
          ['key' => 'settings', 'label' => __('app.nav_settings'), 'icon' => 'settings', 'route' => 'settings.index', 'permission' => null],
        ],
      ],
    ];

    // Add Household link for non-resident roles that are also linked to a resident record
    if ($user->resolveHouseholder()) {
      array_splice($allGroups, 1, 0, [
        [
          'label' => null,
          'group_icon' => null,
          'items' => [
            ['key' => 'household', 'label' => __('app.nav_my_household'), 'icon' => 'home_work', 'route' => 'household.show', 'permission' => null],
          ],
        ]
      ]);
    }

    // Filter out items the user cannot access, then filter out empty groups
    $navGroups = array_values(array_filter(
      array_map(function ($group) use ($user) {
        $group['items'] = array_values(array_filter($group['items'], function ($item) use ($user) {
          return $item['permission'] === null || $user->can($item['permission']);
        }));
        return $group;
      }, $allGroups),
      fn($g) => count($g['items']) > 0
    ));
  }
@endphp

{{-- Mobile overlay (triggered by each page's toggleSidebar()) --}}
<div class="fixed inset-0 bg-black/50 z-40 lg:hidden hidden" id="sidebar-overlay" onclick="toggleSidebar()"></div>

<aside
  class="fixed inset-y-0 left-0 w-64 bg-white dark:bg-dark-card border-r border-slate-200/60 dark:border-white/5 flex flex-col z-50 -translate-x-full lg:translate-x-0 transition-transform duration-300"
  id="sidebar">

  {{-- Brand --}}
  <div class="p-8 flex items-center space-x-3">
    <div class="w-8 h-8 flex items-center justify-center border border-primary/20 dark:border-secondary/20 rounded transition-colors duration-300">
      <span class="material-symbols-outlined text-primary dark:text-secondary text-xl">architecture</span>
    </div>
    <span class="text-xl font-bold tracking-tight text-primary dark:text-white font-headline transition-colors duration-300">Dwipapuri.</span>
  </div>

  {{-- Nav --}}
  <nav class="flex-1 px-4 overflow-y-auto mt-2 pb-4 space-y-1">
    @foreach ($navGroups as $group)

      @if(!empty($group['label']))
        @php
          $groupActive = collect($group['items'])->contains('key', $active);
          $groupId = 'grp-' . Str::slug($group['label']);
          $groupIcon = $group['group_icon'] ?? 'folder';
        @endphp

        {{-- Trigger --}}
        <button type="button" onclick="toggleGroup('{{ $groupId }}')"
          class="w-full flex items-center space-x-3 px-4 py-2.5 rounded-lg font-medium transition-all
          {{ $groupActive ? 'text-primary dark:text-secondary' : 'text-slate-500 dark:text-slate-400 hover:text-primary dark:hover:text-white hover:bg-slate-50 dark:hover:bg-white/5' }}">
          <span class="material-icons text-[20px]">{{ $groupIcon }}</span>
          <span class="flex-1 text-left text-sm">{{ $group['label'] }}</span>
          <span class="material-icons text-[18px] transition-transform duration-200 opacity-60" id="{{ $groupId }}-chevron">
            {{ $groupActive ? 'expand_less' : 'expand_more' }}
          </span>
        </button>

        {{-- Children --}}
        <div id="{{ $groupId }}" class="{{ $groupActive ? '' : 'hidden' }} pl-3 space-y-0.5 mt-0.5">
          @foreach ($group['items'] as $item)
            @php
              $isActive = $active === $item['key'];
              $label = isset($item['label_raw']) ? $item['label_raw'] : $item['label'];
            @endphp
            <a href="{{ route($item['route']) }}"
              class="flex items-center space-x-3 px-4 py-2 rounded-lg text-sm font-{{ $isActive ? 'semibold' : 'medium' }} transition-all
              {{ $isActive ? 'bg-primary/5 dark:bg-secondary/10 text-primary dark:text-secondary' : 'text-slate-500 dark:text-slate-400 hover:text-primary dark:hover:text-white hover:bg-slate-50 dark:hover:bg-white/5' }}">
              <span class="material-icons text-[18px]">{{ $item['icon'] }}</span>
              <span>{{ $label }}</span>
            </a>
          @endforeach
        </div>

      @else
        @foreach ($group['items'] as $item)
          @php
            $isActive = $active === $item['key'];
            $label = isset($item['label_raw']) ? $item['label_raw'] : $item['label'];
          @endphp
          <a href="{{ route($item['route']) }}"
            class="flex items-center space-x-3 px-4 py-2.5 rounded-lg font-{{ $isActive ? 'semibold' : 'medium' }} transition-all
            {{ $isActive ? 'bg-primary/5 dark:bg-secondary/10 text-primary dark:text-secondary' : 'text-slate-500 dark:text-slate-400 hover:text-primary dark:hover:text-white hover:bg-slate-50 dark:hover:bg-white/5' }}">
            <span class="material-icons text-[20px]">{{ $item['icon'] }}</span>
            <span class="text-sm">{{ $label }}</span>
          </a>
        @endforeach
      @endif

    @endforeach
  </nav>

  <x-nav.user-footer />
</aside>

<script>
  function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('-translate-x-full');
    document.getElementById('sidebar-overlay').classList.toggle('hidden');
  }

  function toggleGroup(id) {
    var panel = document.getElementById(id);
    var chevron = document.getElementById(id + '-chevron');
    var hidden = panel.classList.toggle('hidden');
    if (chevron) chevron.textContent = hidden ? 'expand_more' : 'expand_less';
  }
</script>
</script>


