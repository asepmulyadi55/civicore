@props(['active' => ''])

@php
  $navItems = [
    ['key' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'dashboard', 'route' => 'dashboard', 'permission' => 'dashboard.view'],
    ['key' => 'payments', 'label' => 'Payments', 'icon' => 'payments', 'route' => 'payments.index', 'permission' => 'payments.view'],
    ['key' => 'residents', 'label' => 'Residents', 'icon' => 'people', 'route' => 'residents.index', 'permission' => 'residents.view'],
    ['key' => 'blocks', 'label' => 'Blocks', 'icon' => 'domain', 'route' => 'blocks.index', 'permission' => 'blocks.view'],
    ['key' => 'users', 'label' => 'User Management', 'icon' => 'manage_accounts', 'route' => 'users.index', 'permission' => 'users.view'],
    ['key' => 'roles', 'label' => 'Roles & Permissions', 'icon' => 'admin_panel_settings', 'route' => 'roles.index', 'permission' => 'roles.view'],
    ['key' => 'reports', 'label' => 'Reports', 'icon' => 'bar_chart', 'route' => 'reports.index', 'permission' => 'reports.view'],
    ['key' => 'events', 'label' => 'Events', 'icon' => 'event', 'route' => 'events.index', 'permission' => null],
    ['key' => 'settings', 'label' => 'Settings', 'icon' => 'settings', 'route' => 'settings.index', 'permission' => null],
  ];

  $user = Auth::user();
  $navItems = array_filter($navItems, function ($item) use ($user) {
    if ($item['permission'] === null)
      return true;
    return $user->can($item['permission']);
  });
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

  {{-- Nav links --}}
  <nav class="flex-1 px-4 space-y-1 mt-4">
    @foreach ($navItems as $item)
      @php
        $isActive = $active === $item['key'];
        $href = $item['route'] === '#' ? '#' : route($item['route']);
      @endphp
      <a href="{{ $href }}" class="flex items-center space-x-3 px-3 py-2.5 rounded-lg font-{{ $isActive ? 'semibold' : 'medium' }} transition-all group
                                  {{ $isActive
      ? 'bg-primary/10 text-primary'
      : 'text-slate-500 hover:text-primary hover:bg-primary/5' }}">
        <span class="material-icons text-[20px]">{{ $item['icon'] }}</span>
        <span>{{ __('app.nav_' . $item['key']) }}</span>
      </a>
    @endforeach
  </nav>
  <x-nav.user-footer />

</aside>

<script>
  function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('-translate-x-full');
    document.getElementById('sidebar-overlay').classList.toggle('hidden');
  }
</script>