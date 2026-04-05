@props(['active' => ''])

@php
  use Illuminate\Support\Str;
  $user = Auth::user();

  // ── Resident role: simple flat nav ─────────────────────────────────────────
  if ($user->isResident()) {
    $flatItems = [
      ['key' => 'overview', 'label_raw' => 'Overview', 'icon' => 'dashboard', 'route' => 'overview', 'permission' => null],
    ];
    if ($user->resident) {
      $flatItems[] = ['key' => 'household', 'label_raw' => 'Household', 'icon' => 'home', 'route' => 'household.show', 'permission' => null];
    }
    $flatItems[] = ['key' => 'settings', 'label_raw' => 'Settings', 'icon' => 'settings', 'route' => 'settings.index', 'permission' => null];
    $navGroups = [['label' => null, 'items' => $flatItems]];
  } else {
    // ── All other roles: grouped nav ─────────────────────────────────────────
    $allGroups = [
      [
        'label' => null,
        'items' => [
          ['key' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'dashboard', 'route' => 'dashboard', 'permission' => null],
        ],
      ],
      [
        'label' => 'Community',
        'items' => [
          ['key' => 'residents', 'label' => 'Residents', 'icon' => 'people', 'route' => 'residents.index', 'permission' => 'residents.view'],
          ['key' => 'blocks',    'label' => 'Blocks',    'icon' => 'domain',  'route' => 'blocks.index',   'permission' => 'blocks.view'],
        ],
      ],
      [
        'label' => 'Finance',
        'items' => [
          ['key' => 'payments', 'label' => 'Payments', 'icon' => 'payments',  'route' => 'payments.index', 'permission' => 'payments.view'],
          ['key' => 'reports',  'label' => 'Reports',  'icon' => 'bar_chart', 'route' => 'reports.index',  'permission' => 'reports.view'],
        ],
      ],
      [
        'label' => 'Administration',
        'items' => [
          ['key' => 'users',    'label' => 'User Management',      'icon' => 'manage_accounts',    'route' => 'users.index',    'permission' => 'users.view'],
          ['key' => 'roles',    'label' => 'Roles & Permissions',  'icon' => 'admin_panel_settings','route' => 'roles.index',    'permission' => 'roles.view'],
          ['key' => 'homepage', 'label' => 'Homepage',             'icon' => 'public',              'route' => 'homepage.index', 'permission' => 'homepage.view'],
          ['key' => 'media',    'label' => 'Media Manager',        'icon' => 'perm_media',          'route' => 'media.index',    'permission' => 'media.view'],
        ],
      ],
      [
        'label' => null,
        'items' => [
          ['key' => 'settings', 'label' => 'Settings', 'icon' => 'settings', 'route' => 'settings.index', 'permission' => null],
        ],
      ],
    ];

    // Add Household link for non-resident roles that are also linked to a resident record
    if ($user->resident) {
      array_splice($allGroups, 1, 0, [[
        'label' => null,
        'items' => [
          ['key' => 'household', 'label' => 'My Household', 'icon' => 'home_work', 'route' => 'household.show', 'permission' => null],
        ],
      ]]);
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
  class="fixed inset-y-0 left-0 w-64 bg-white dark:bg-slate-900 border-r border-slate-200 dark:border-slate-800 flex flex-col z-50 -translate-x-full lg:translate-x-0 transition-transform duration-300"
  id="sidebar">

  {{-- Brand --}}
  <div class="p-6 flex items-center space-x-3">
    <div class="bg-primary p-2 rounded-lg">
      <span class="material-icons text-white">apartment</span>
    </div>
    <span class="text-xl font-extrabold tracking-tight text-primary">CiviCore</span>
  </div>

  {{-- Nav --}}
  <nav class="flex-1 px-4 overflow-y-auto mt-2 pb-4 space-y-1">
    @foreach ($navGroups as $group)

      @if(!empty($group['label']))
        {{-- ── Accordion group ─────────────────────────────────────────────── --}}
        @php
          $groupActive = collect($group['items'])->contains('key', $active);
          $groupId = 'grp-' . Str::slug($group['label']);
          $groupIcon = match($group['label']) {
            'Community'      => 'groups',
            'Finance'        => 'attach_money',
            'Administration' => 'admin_panel_settings',
            default          => 'folder',
          };
        @endphp

        {{-- Trigger --}}
        <button type="button"
          onclick="toggleGroup('{{ $groupId }}')"
          class="w-full flex items-center space-x-3 px-3 py-2.5 rounded-lg font-medium transition-all
            {{ $groupActive ? 'text-primary' : 'text-slate-500 hover:text-primary hover:bg-primary/5' }}">
          <span class="material-icons text-[20px]">{{ $groupIcon }}</span>
          <span class="flex-1 text-left">{{ $group['label'] }}</span>
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
              class="flex items-center space-x-3 px-3 py-2 rounded-lg text-sm font-{{ $isActive ? 'semibold' : 'medium' }} transition-all
                {{ $isActive ? 'bg-primary/10 text-primary' : 'text-slate-500 hover:text-primary hover:bg-primary/5' }}">
              <span class="material-icons text-[18px]">{{ $item['icon'] }}</span>
              <span>{{ $label }}</span>
            </a>
          @endforeach
        </div>

      @else
        {{-- ── Flat items ───────────────────────────────────────────────────── --}}
        @foreach ($group['items'] as $item)
          @php
            $isActive = $active === $item['key'];
            $label = isset($item['label_raw']) ? $item['label_raw'] : $item['label'];
          @endphp
          <a href="{{ route($item['route']) }}"
            class="flex items-center space-x-3 px-3 py-2.5 rounded-lg font-{{ $isActive ? 'semibold' : 'medium' }} transition-all
              {{ $isActive ? 'bg-primary/10 text-primary' : 'text-slate-500 hover:text-primary hover:bg-primary/5' }}">
            <span class="material-icons text-[20px]">{{ $item['icon'] }}</span>
            <span>{{ $label }}</span>
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
    var panel   = document.getElementById(id);
    var chevron = document.getElementById(id + '-chevron');
    var hidden  = panel.classList.toggle('hidden');
    if (chevron) chevron.textContent = hidden ? 'expand_more' : 'expand_less';
  }
</script>
</script>