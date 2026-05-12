{{-- Flash messages, search/filter form, bulk bar, select-all toggle --}}

{{-- Flash Messages --}}
@foreach(['success', 'error'] as $type)
  @if(session($type))
    <div
      class="p-4 {{ $type === 'success' ? 'bg-emerald-50 dark:bg-emerald-900/20 border-emerald-200 text-emerald-700 dark:text-emerald-400' : 'bg-rose-50 dark:bg-rose-900/20 border-rose-200 text-rose-700 dark:text-rose-400' }} border rounded-xl flex items-center gap-3">
      <span class="material-icons text-sm">{{ $type === 'success' ? 'check_circle' : 'error' }}</span>
      <p class="text-sm">{{ session($type) }}</p>
    </div>
  @endif
@endforeach

{{-- Search & Filters --}}
<form method="GET" action="{{ route('media.index') }}"
  class="flex flex-col sm:flex-row gap-3">
  {{-- Preserve active folder across filter submissions --}}
  @if($folder)
    <input type="hidden" name="folder" value="{{ $folder }}">
  @endif
  <div class="relative flex-1">
    <span class="material-icons absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-[20px]">search</span>
    <input type="text" name="search" value="{{ request('search') }}"
      placeholder="{{ __('app.search_by_filename') }}"
      class="w-full pl-10 pr-4 py-2.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all" />
  </div>
  <select name="type"
    class="px-4 py-2.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
    <option value="">{{ __('app.all_types') }}</option>
    <option value="image" {{ request('type') === 'image' ? 'selected' : '' }}>{{ __('app.type_image') }}</option>
    <option value="document" {{ request('type') === 'document' ? 'selected' : '' }}>{{ __('app.type_document') }}</option>
  </select>
  <button type="submit"
    class="px-5 py-2.5 bg-primary hover:bg-primary/90 text-white text-sm font-bold rounded-xl transition-all shadow-sm shadow-primary/20">
    {{ __('app.btn_search') }}
  </button>
  @if(request('search') || request('type'))
    <a href="{{ route('media.index', $folder ? ['folder' => $folder] : []) }}"
      class="px-5 py-2.5 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400 text-sm font-bold rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 transition-all">
      {{ __('app.btn_clear') }}
    </a>
  @endif
</form>

{{-- Bulk Actions Bar (delete permission required) --}}
@if(auth()->user()->can('media.delete'))
<div id="bulk-bar"
  class="hidden items-center justify-between bg-primary/5 border border-primary/20 rounded-xl px-5 py-3">
  <span class="text-sm font-semibold text-primary">
    <span id="selected-count">0</span> file(s) selected
  </span>
  <div class="flex items-center gap-3">
    <button onclick="selectAll()"
      class="text-sm font-bold text-slate-600 dark:text-slate-400 hover:text-primary transition-colors">
      {{ __('app.select_all') }}
    </button>
    <span class="text-slate-300">|</span>
    <button onclick="deselectAll()"
      class="text-sm font-bold text-slate-600 dark:text-slate-400 hover:text-primary transition-colors">
      {{ __('app.deselect_all') }}
    </button>
    <span class="text-slate-300">|</span>
    <button onclick="submitBulkDelete()"
      class="flex items-center gap-1.5 px-3 py-1.5 bg-rose-500 hover:bg-rose-600 text-white text-sm font-bold rounded-lg transition-all">
      <span class="material-icons text-sm">delete_sweep</span>
      {{ __('app.btn_delete_selected') }}
    </button>
  </div>
</div>
@endif

{{-- Select All Toggle --}}
@if($files->count() > 0 && auth()->user()->can('media.delete'))
  <div class="flex items-center gap-3">
    <label class="flex items-center gap-2 cursor-pointer select-none text-sm text-slate-500 hover:text-slate-700 dark:hover:text-slate-300 transition-colors">
      <input type="checkbox" id="toggle-all" class="w-4 h-4 rounded accent-primary" onchange="toggleAll(this)">
      {{ __('app.select_all') }}
    </label>
  </div>
@endif
