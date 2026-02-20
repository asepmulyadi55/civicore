@props(['active' => ''])

@php
  /**
   * Navigation items definition.
   * To add, rename, or reorder a link — edit ONLY this array.
   *
   * Keys:
   *   key   – matches the value passed to the `active` prop
   *   label – display name in the sidebar
   *   icon  – Material Icons name
   *   route – named Laravel route (use '#' for not-yet-implemented pages)
   */
  $navItems = [
    ['key' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'dashboard', 'route' => 'dashboard'],
    ['key' => 'payments', 'label' => 'Payments', 'icon' => 'payments', 'route' => 'payments.index'],
    ['key' => 'residents', 'label' => 'Residents', 'icon' => 'people', 'route' => '#'],
    ['key' => 'blocks', 'label' => 'Blocks', 'icon' => 'domain', 'route' => '#'],
    ['key' => 'users', 'label' => 'User Management', 'icon' => 'manage_accounts', 'route' => 'users.index'],
    ['key' => 'reports', 'label' => 'Reports', 'icon' => 'bar_chart', 'route' => 'reports.index'],
    ['key' => 'events', 'label' => 'Events', 'icon' => 'event', 'route' => '#'],
    ['key' => 'settings', 'label' => 'Settings', 'icon' => 'settings', 'route' => '#'],
  ];
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
        <span>{{ $item['label'] }}</span>
      </a>
    @endforeach
  </nav>

  {{-- Logged-in user + logout --}}
  <div class="p-4 border-t border-slate-200 dark:border-slate-800">
    <div class="flex items-center space-x-3 p-2 bg-slate-50 dark:bg-slate-800/50 rounded-xl">
      <div class="w-10 h-10 rounded-lg bg-primary/10 flex items-center justify-center flex-shrink-0">
        <span class="material-icons text-primary">person</span>
      </div>
      <div class="flex-1 overflow-hidden">
        <p class="text-sm font-bold truncate">{{ Auth::user()->name }}</p>
        <p class="text-xs text-slate-500 truncate">{{ Auth::user()->email }}</p>
      </div>
      <form action="{{ route('logout') }}" method="POST">
        @csrf
        <button type="submit" class="text-slate-400 hover:text-primary transition-colors" title="Logout">
          <span class="material-icons text-sm">logout</span>
        </button>
      </form>
    </div>
  </div>

</aside>