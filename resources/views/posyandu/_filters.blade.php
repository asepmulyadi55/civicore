{{-- posyandu/_filters.blade.php --}}
@php use App\Http\Controllers\PosyanduController; @endphp

<form method="GET" action="{{ route('posyandu.index') }}"
  class="bg-white dark:bg-slate-900 p-4 rounded-xl border border-slate-200 dark:border-slate-800 flex flex-wrap items-center gap-3">

  {{-- Preserve active category pill across filter form submissions --}}
  @if($categoryFilter)
    <input type="hidden" name="category" value="{{ $categoryFilter }}">
  @endif

  {{-- Search --}}
  <div class="relative flex-grow max-w-sm">
    <span class="material-icons absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">search</span>
    <input type="text" name="search" value="{{ request('search') }}"
      placeholder="{{ __('app.posyandu_search_ph') }}"
      class="w-full pl-9 pr-4 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700
             rounded-lg text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all
             dark:text-slate-100 dark:placeholder-slate-500" />
  </div>

  {{-- Block filter --}}
  <div class="relative">
    <select name="block_id"
      class="appearance-none pl-4 pr-9 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200
             dark:border-slate-700 rounded-lg text-sm text-slate-600 dark:text-slate-300
             focus:ring-2 focus:ring-primary/20 focus:border-primary">
      <option value="">{{ __('app.posyandu_all_blocks') }}</option>
      @foreach ($blocks as $block)
        <option value="{{ $block->id }}" {{ request('block_id') == $block->id ? 'selected' : '' }}>
          {{ $block->name }}
        </option>
      @endforeach
    </select>
    <span class="material-icons absolute right-2.5 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400 text-[18px]">expand_more</span>
  </div>

  {{-- Category filter --}}
  <div class="relative">
    <select name="category"
      class="appearance-none pl-4 pr-9 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200
             dark:border-slate-700 rounded-lg text-sm text-slate-600 dark:text-slate-300
             focus:ring-2 focus:ring-primary/20 focus:border-primary">
      <option value="">{{ __('app.posyandu_all_cats') }}</option>
      @foreach(PosyanduController::translatedCategories() as $key => $cat)
        <option value="{{ $key }}" {{ request('category') === $key ? 'selected' : '' }}>
          {{ $cat['label'] }} ({{ $cat['desc'] }})
        </option>
      @endforeach
    </select>
    <span class="material-icons absolute right-2.5 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400 text-[18px]">expand_more</span>
  </div>

  {{-- Search button --}}
  <button type="submit"
    class="flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-primary hover:bg-primary/90 rounded-lg shadow-sm shadow-primary/20 transition-all">
    <span class="material-icons text-sm">search</span>
    {{ __('app.btn_search') }}
  </button>

  @if(request()->hasAny(['search', 'block_id', 'category']))
    <a href="{{ route('posyandu.index') }}"
      class="flex items-center gap-1 px-3 py-2 text-sm font-medium text-slate-500 hover:text-primary transition-colors">
      <span class="material-icons text-sm">close</span>
      {{ __('app.clear_filters') }}
    </a>
  @endif

</form>
