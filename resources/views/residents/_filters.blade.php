{{-- residents/_filters.blade.php --}}
<form method="GET" action="{{ route('residents.index') }}"
  class="bg-white dark:bg-slate-900 p-4 rounded-xl border border-slate-200 dark:border-slate-800 mb-6 flex flex-wrap items-center gap-3">

  {{-- Search --}}
  <div class="relative flex-grow max-w-sm">
    <span class="material-icons absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">search</span>
    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name, unit, or phone..."
      class="w-full pl-9 pr-4 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all dark:text-slate-100 dark:placeholder-slate-500" />
  </div>

  {{-- Block filter --}}
  <div class="relative">
    <select name="block_id"
      class="appearance-none pl-4 pr-9 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm text-slate-600 dark:text-slate-300 focus:ring-2 focus:ring-primary/20 focus:border-primary">
      <option value="">All Blocks</option>
      @foreach ($blocks as $block)
        <option value="{{ $block->id }}" {{ request('block_id') == $block->id ? 'selected' : '' }}>
          {{ $block->name }}
        </option>
      @endforeach
    </select>
    <span
      class="material-icons absolute right-2.5 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400 text-[18px]">expand_more</span>
  </div>

  {{-- Status filter --}}
  <div class="relative">
    <select name="status"
      class="appearance-none pl-4 pr-9 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm text-slate-600 dark:text-slate-300 focus:ring-2 focus:ring-primary/20 focus:border-primary">
      <option value="">All Status</option>
      <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
      <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
    </select>
    <span
      class="material-icons absolute right-2.5 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400 text-[18px]">expand_more</span>
  </div>

  {{-- Search button --}}
  <button type="submit"
    class="flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-primary hover:bg-primary/90 rounded-lg shadow-sm shadow-primary/20 transition-all">
    <span class="material-icons text-sm">search</span>
    Search
  </button>

  @if(request()->hasAny(['search', 'block_id', 'status']))
    <a href="{{ route('residents.index') }}"
      class="flex items-center gap-1 px-3 py-2 text-sm font-medium text-slate-500 hover:text-primary transition-colors">
      <span class="material-icons text-sm">close</span>
      Clear
    </a>
  @endif
</form>