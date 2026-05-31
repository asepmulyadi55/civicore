{{-- posyandu/_filters.blade.php --}}
@php use App\Http\Controllers\PosyanduController; @endphp

<div class="space-y-3">

  {{-- Stat cards: Total / Male / Female --}}
  <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">

    {{-- Total --}}
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl px-4 py-3 flex items-center gap-3">
      <span class="p-1.5 bg-teal-100 dark:bg-teal-900/30 rounded-lg">
        <span class="material-icons text-teal-600 text-[18px]">groups</span>
      </span>
      <div>
        <p class="text-xs text-slate-400 leading-none mb-0.5">{{ __('app.posyandu_stat_total') }}</p>
        <p class="text-xl font-extrabold text-slate-900 dark:text-white leading-none">{{ $genderStats['total'] }}</p>
      </div>
    </div>

    {{-- Male --}}
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl px-4 py-3 flex items-center gap-3">
      <span class="p-1.5 bg-blue-100 dark:bg-blue-900/30 rounded-lg">
        <span class="material-icons text-blue-600 text-[18px]">male</span>
      </span>
      <div>
        <p class="text-xs text-slate-400 leading-none mb-0.5">{{ __('app.posyandu_stat_male') }}</p>
        <p class="text-xl font-extrabold text-blue-600 dark:text-blue-400 leading-none">{{ $genderStats['male'] }}</p>
        <p class="text-[10px] text-slate-400 leading-none mt-0.5">
          {{ $genderStats['total'] > 0 ? round($genderStats['male'] / $genderStats['total'] * 100) : 0 }}%
        </p>
      </div>
    </div>

    {{-- Female --}}
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl px-4 py-3 flex items-center gap-3">
      <span class="p-1.5 bg-pink-100 dark:bg-pink-900/30 rounded-lg">
        <span class="material-icons text-pink-600 text-[18px]">female</span>
      </span>
      <div>
        <p class="text-xs text-slate-400 leading-none mb-0.5">{{ __('app.posyandu_stat_female') }}</p>
        <p class="text-xl font-extrabold text-pink-600 dark:text-pink-400 leading-none">{{ $genderStats['female'] }}</p>
        <p class="text-[10px] text-slate-400 leading-none mt-0.5">
          {{ $genderStats['total'] > 0 ? round($genderStats['female'] / $genderStats['total'] * 100) : 0 }}%
        </p>
      </div>
    </div>

  </div>

  {{-- Filter bar --}}
  <form method="GET" action="{{ route('posyandu.index') }}"
    class="bg-white dark:bg-slate-900 p-4 rounded-xl border border-slate-200 dark:border-slate-800 flex flex-col sm:flex-row sm:flex-wrap sm:items-center gap-3">

    {{-- Preserve active category pill across filter form submissions --}}
    @if($categoryFilter)
      <input type="hidden" name="category" value="{{ $categoryFilter }}">
    @endif

    {{-- Search --}}
    <div class="relative w-full sm:flex-grow sm:max-w-sm">
      <span class="material-icons absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">search</span>
      <input type="text" name="search" value="{{ request('search') }}"
        placeholder="{{ __('app.posyandu_search_ph') }}"
        class="w-full pl-9 pr-4 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700
               rounded-lg text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all
               dark:text-slate-100 dark:placeholder-slate-500" />
    </div>

    {{-- Block filter --}}
    <div class="relative w-full sm:w-auto">
      <select name="block_id"
        class="appearance-none w-full sm:w-auto pl-4 pr-9 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200
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
    <div class="relative w-full sm:w-auto">
      <select name="category"
        class="appearance-none w-full sm:w-auto pl-4 pr-9 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200
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

    {{-- Gender filter --}}
    <div class="relative w-full sm:w-auto">
      <select name="gender"
        class="appearance-none w-full sm:w-auto pl-4 pr-9 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200
               dark:border-slate-700 rounded-lg text-sm text-slate-600 dark:text-slate-300
               focus:ring-2 focus:ring-primary/20 focus:border-primary">
        <option value="">{{ __('app.posyandu_all_genders') }}</option>
        <option value="male"   {{ request('gender') === 'male'   ? 'selected' : '' }}>
          {{ __('app.posyandu_gender_male') }}
        </option>
        <option value="female" {{ request('gender') === 'female' ? 'selected' : '' }}>
          {{ __('app.posyandu_gender_female') }}
        </option>
      </select>
      <span class="material-icons absolute right-2.5 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400 text-[18px]">expand_more</span>
    </div>

    {{-- Search button --}}
    <button type="submit"
      class="flex justify-center items-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-primary hover:bg-primary/90 rounded-lg shadow-sm shadow-primary/20 transition-all w-full sm:w-auto">
      <span class="material-icons text-sm">search</span>
      {{ __('app.btn_search') }}
    </button>

    @if(request()->hasAny(['search', 'block_id', 'category', 'gender']))
      <a href="{{ route('posyandu.index') }}"
        class="flex justify-center items-center gap-1 px-3 py-2 text-sm font-medium text-slate-500 hover:text-primary transition-colors w-full sm:w-auto">
        <span class="material-icons text-sm">close</span>
        {{ __('app.clear_filters') }}
      </a>
    @endif

  </form>

</div>