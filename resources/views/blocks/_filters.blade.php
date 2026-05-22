{{-- blocks/_filters.blade.php --}}
<form method="GET" action="{{ route('blocks.index') }}"
  class="bg-white dark:bg-dark-card p-4 rounded-xl border border-slate-200 dark:border-white/5 flex flex-col sm:flex-row sm:flex-wrap sm:items-center gap-3">

  {{-- Search --}}
  <div class="relative w-full sm:flex-grow sm:max-w-sm">
    <span class="material-icons absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">search</span>
    <input type="text" name="search" value="{{ request('search') }}"
      placeholder="Search blocks by name or description…"
      class="w-full pl-9 pr-4 py-2 bg-slate-50 dark:bg-white/5 border border-slate-200 dark:border-white/5 rounded-lg text-sm
             focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all
             dark:text-slate-100 dark:placeholder-slate-500" />
  </div>

  {{-- Submit --}}
  <button type="submit"
    class="flex justify-center items-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-primary hover:bg-primary/90 rounded-lg shadow-sm transition-all w-full sm:w-auto">
    <span class="material-icons text-sm">search</span>
    Search
  </button>

  @if(request()->has('search') && request('search') !== '')
    <a href="{{ route('blocks.index') }}"
      class="flex justify-center items-center gap-1 px-3 py-2 text-sm font-medium text-slate-500 hover:text-primary transition-colors w-full sm:w-auto">
      <span class="material-icons text-sm">close</span>
      Clear
    </a>
  @endif

  {{-- Result count hint --}}
  @if(request('search'))
    <span class="ml-auto text-xs text-slate-400">
      {{ $blocks->count() }} block{{ $blocks->count() === 1 ? '' : 's' }} found
    </span>
  @endif

</form>
