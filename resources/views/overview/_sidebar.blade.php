{{-- Resident-facing sidebar — matches admin sidebar style --}}
<div class="fixed inset-0 bg-black/50 z-40 lg:hidden hidden" id="sidebar-overlay" onclick="toggleSidebar()"></div>

<aside id="resident-sidebar"
  class="fixed inset-y-0 left-0 w-64 bg-white dark:bg-slate-900 border-r border-slate-200 dark:border-slate-800 flex flex-col z-50 -translate-x-full lg:translate-x-0 transition-transform duration-300">

  {{-- Brand --}}
  <div class="p-6 flex items-center space-x-3">
    <div class="bg-primary p-2 rounded-lg">
      <span class="material-icons text-white">apartment</span>
    </div>
    <span class="text-xl font-extrabold tracking-tight text-primary">CiviCore</span>
  </div>

  {{-- Nav --}}
  <nav class="flex-1 px-4 space-y-1 mt-4">
    @php $currentRoute = Route::currentRouteName(); @endphp
    @php
      $residentNav = [
        ['route' => 'overview', 'icon' => 'dashboard', 'label' => 'Overview'],
      ];
      if (auth()->user()->resident) {
        $residentNav[] = ['route' => 'household.show', 'icon' => 'home', 'label' => 'Household'];
      }
      $residentNav[] = ['route' => 'settings.index', 'icon' => 'settings', 'label' => 'Settings'];
    @endphp
    @foreach($residentNav as $item)
      @php $isActive = $currentRoute === $item['route']; @endphp
      <a href="{{ route($item['route']) }}"
        class="flex items-center space-x-3 px-3 py-2.5 rounded-lg font-{{ $isActive ? 'semibold' : 'medium' }} transition-all group
          {{ $isActive ? 'bg-primary/10 text-primary' : 'text-slate-500 hover:text-primary hover:bg-primary/5' }}">
        <span class="material-icons text-[20px]">{{ $item['icon'] }}</span>
        <span>{{ $item['label'] }}</span>
      </a>
    @endforeach
  </nav>

  <x-nav.user-footer />

</aside>

<script>
  function toggleSidebar() {
    document.getElementById('resident-sidebar').classList.toggle('-translate-x-full');
    document.getElementById('sidebar-overlay').classList.toggle('hidden');
  }
</script>