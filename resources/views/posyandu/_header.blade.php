{{-- posyandu/_header.blade.php --}}
<header class="shrink-0 bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800">

  {{-- Top bar --}}
  <div class="h-16 flex items-center justify-between px-6 lg:px-8">
    <div class="flex items-center gap-4">
      <button class="lg:hidden p-2 rounded-lg border border-slate-200 dark:border-slate-800" onclick="toggleSidebar()">
        <span class="material-icons text-slate-500">menu</span>
      </button>
      <span class="p-2 bg-teal-100 dark:bg-teal-900/30 rounded-lg">
        <span class="material-icons text-teal-600 text-[20px]">health_and_safety</span>
      </span>
      <div>
        <h1 class="text-xl font-bold text-slate-900 dark:text-white leading-tight">{{ __('app.posyandu_title') }}</h1>
        <p class="text-xs text-slate-400 leading-none">{{ __('app.posyandu_subtitle') }}</p>
      </div>
    </div>
    <button class="p-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg hover:border-primary/50 transition-all"
      onclick="toggleDark()" title="Toggle dark mode">
      <span class="material-icons text-slate-500 text-[20px]">dark_mode</span>
    </button>
  </div>

  {{-- Category pills --}}
  <div class="px-6 lg:px-8 pb-3 flex flex-wrap gap-2">
    @php
      use App\Http\Controllers\PosyanduController;
      $categories = PosyanduController::translatedCategories();
      $totalAll   = array_sum($categoryCounts);
      $colorMap   = [
        'pink'    => ['pill' => 'bg-pink-100 dark:bg-pink-900/30 text-pink-700 dark:text-pink-300',     'icon' => 'text-pink-500'],
        'purple'  => ['pill' => 'bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300','icon' => 'text-purple-500'],
        'blue'    => ['pill' => 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300',     'icon' => 'text-blue-500'],
        'indigo'  => ['pill' => 'bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300','icon' => 'text-indigo-500'],
        'emerald' => ['pill' => 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300','icon' => 'text-emerald-500'],
        'amber'   => ['pill' => 'bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300', 'icon' => 'text-amber-500'],
        'slate'   => ['pill' => 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400',    'icon' => 'text-slate-400'],
      ];
    @endphp

    {{-- All --}}
    <a href="{{ route('posyandu.index', request()->except('category', 'page')) }}"
       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold transition-all {{ !$categoryFilter ? 'bg-primary text-white shadow-sm shadow-primary/30' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700' }}">
      <span class="material-icons text-[14px]">people</span>
      {{ __('app.posyandu_all') }}&nbsp;<span class="opacity-80">{{ $totalAll }}</span>
    </a>

    @foreach($categories as $key => $cat)
      @php $colors = $colorMap[$cat['color']]; @endphp
      <a href="{{ route('posyandu.index', array_merge(request()->except('category', 'page'), ['category' => $key])) }}"
         class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold transition-all {{ $categoryFilter === $key ? 'ring-2 ring-offset-1 ring-current ' . $colors['pill'] : $colors['pill'] . ' opacity-80 hover:opacity-100' }}"
         title="{{ $cat['desc'] }}">
        <span class="material-icons text-[14px] {{ $colors['icon'] }}">{{ $cat['icon'] }}</span>
        {{ $cat['label'] }}&nbsp;<span class="opacity-70">{{ $categoryCounts[$key] ?? 0 }}</span>
      </a>
    @endforeach
  </div>

</header>