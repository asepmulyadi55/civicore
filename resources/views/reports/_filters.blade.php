{{-- Reports Filters --}}
<form method="GET" action="{{ route('reports.index') }}"
  class="bg-white dark:bg-slate-900 p-4 rounded-xl border border-slate-200 dark:border-slate-800 mb-6 flex flex-col sm:flex-row sm:flex-wrap sm:items-center gap-3">

  {{-- Year --}}
  <div class="relative w-full sm:w-auto">
    <select name="year"
      class="appearance-none w-full sm:w-auto bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 focus:border-primary focus:ring-2 focus:ring-primary/20 rounded-lg text-sm py-2 pl-4 pr-9 outline-none transition-all text-slate-600 dark:text-slate-300">
      <option value="">{{ __('app.filter_year') }}</option>
      @foreach($years as $y)
        <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>{{ $y }}</option>
      @endforeach
    </select>
    <span class="material-icons absolute right-2.5 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400 text-[18px]">expand_more</span>
  </div>

  {{-- Block (admin & treasurer only) --}}
  @unless($isCoordinator)
    <div class="relative w-full sm:w-auto">
      <select name="block_id"
        class="appearance-none w-full sm:w-auto bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 focus:border-primary focus:ring-2 focus:ring-primary/20 rounded-lg text-sm py-2 pl-4 pr-9 outline-none transition-all text-slate-600 dark:text-slate-300">
        <option value="">{{ __('app.all_blocks') }}</option>
        @foreach($blocks as $block)
          <option value="{{ $block->id }}" {{ $blockId == $block->id ? 'selected' : '' }}>{{ $block->name }}</option>
        @endforeach
      </select>
      <span class="material-icons absolute right-2.5 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400 text-[18px]">expand_more</span>
    </div>
  @endunless

  {{-- Search Resident --}}
  <div class="relative w-full sm:flex-grow sm:max-w-sm">
    <span class="material-icons absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">search</span>
    <input name="search" value="{{ $search }}"
      class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary pl-9 pr-4 py-2 text-slate-700 dark:text-slate-200 outline-none transition-all"
      placeholder="{{ __('app.search_name_unit') }}" />
  </div>

  {{-- Apply / Clear --}}
  <button type="submit"
    class="flex justify-center items-center gap-2 px-4 py-2 bg-primary hover:bg-primary/90 text-white rounded-lg text-sm font-semibold transition-all shadow-sm shadow-primary/20 w-full sm:w-auto">
    <span class="material-icons text-sm">search</span>
    {{ __('app.btn_apply') }}
  </button>
  
  @if($search || (!$isCoordinator && $blockId))
    <a href="{{ route('reports.index', ['year' => $year]) }}"
      class="flex justify-center items-center gap-1 px-3 py-2 text-sm font-medium text-slate-500 hover:text-primary transition-colors w-full sm:w-auto">
      <span class="material-icons text-sm">close</span>
      {{ __('app.clear_filters') }}
    </a>
  @endif

</form>