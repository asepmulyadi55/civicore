{{-- Reports Filters --}}
<form method="GET" action="{{ route('reports.index') }}"
  class="bg-white dark:bg-slate-900 p-4 rounded-xl border border-slate-200 dark:border-slate-800 mb-6 shadow-sm">
  <div class="grid grid-cols-1 md:grid-cols-4 lg:grid-cols-5 gap-4">

    {{-- Year --}}
    <div class="space-y-1.5">
      <label class="text-xs font-bold text-slate-400 uppercase tracking-tight">{{ __('app.filter_year') }}</label>
      <div class="relative">
        <select name="year" onchange="this.form.submit()"
          class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary appearance-none px-4 py-2.5 pr-9 text-slate-700 dark:text-slate-200 outline-none">
          @foreach($years as $y)
            <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>{{ $y }}</option>
          @endforeach
        </select>
        <span
          class="material-icons absolute right-2.5 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400 text-[18px]">expand_more</span>
      </div>
    </div>

    {{-- Block (admin & treasurer only) --}}
    @unless($isCoordinator)
      <div class="space-y-1.5">
        <label class="text-xs font-bold text-slate-400 uppercase tracking-tight">{{ __('app.filter_block') }}</label>
        <div class="relative">
          <select name="block_id" onchange="this.form.submit()"
            class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary appearance-none px-4 py-2.5 pr-9 text-slate-700 dark:text-slate-200 outline-none">
            <option value="">{{ __('app.all_blocks') }}</option>
            @foreach($blocks as $block)
              <option value="{{ $block->id }}" {{ $blockId == $block->id ? 'selected' : '' }}>{{ $block->name }}</option>
            @endforeach
          </select>
          <span
            class="material-icons absolute right-2.5 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400 text-[18px]">expand_more</span>
        </div>
      </div>
    @endunless

    {{-- Search Resident --}}
    <div class="md:col-span-2 space-y-1.5">
      <label class="text-xs font-bold text-slate-400 uppercase tracking-tight">{{ __('app.search_resident') }}</label>
      <div class="relative">
        <span class="material-icons absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-lg">search</span>
        <input name="search" value="{{ $search }}"
          class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary pl-10 pr-4 py-2.5 text-slate-700 dark:text-slate-200 outline-none"
          placeholder="{{ __('app.search_name_unit') }}" />
      </div>
    </div>

    {{-- Apply / Clear --}}
    <div class="flex items-end gap-2">
      <button type="submit"
        class="flex-1 bg-primary/10 text-primary hover:bg-primary/20 py-2.5 rounded-lg text-sm font-bold transition-colors">
        {{ __('app.btn_apply') }}
      </button>
      @if($search || (!$isCoordinator && $blockId))
        <a href="{{ route('reports.index', ['year' => $year]) }}"
          class="py-2.5 px-3 border border-slate-200 dark:border-slate-700 rounded-lg text-sm text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
          <span class="material-icons text-base leading-none">close</span>
        </a>
      @endif
    </div>

  </div>
</form>