{{-- payments/_filters.blade.php --}}
<form method="GET" action="{{ route('payments.index') }}"
  class="bg-white dark:bg-slate-900 p-4 rounded-xl border border-slate-200 dark:border-slate-800 flex flex-col sm:flex-row sm:flex-wrap sm:items-end gap-3">

  {{-- Search --}}
  <div class="relative w-full sm:flex-grow sm:max-w-sm">
    <span class="material-icons absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-lg">search</span>
    <input name="search" value="{{ request('search') }}"
      class="w-full pl-10 pr-4 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 focus:border-primary focus:ring-2 focus:ring-primary/20 rounded-lg text-sm outline-none transition-all dark:text-slate-100"
      placeholder="{{ __('app.search_resident_unit') }}" type="text" />
  </div>

  {{-- Block filter (admin & treasurer only) --}}
  @unless(auth()->user()->isBlockCoordinator())
  <div class="relative w-full sm:w-auto">
    <select name="block_id"
      class="appearance-none w-full sm:w-auto bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 focus:border-primary focus:ring-2 focus:ring-primary/20 rounded-lg text-sm py-2 pl-4 pr-9 outline-none transition-all text-slate-600 dark:text-slate-300">
      <option value="">{{ __('app.all_blocks') }}</option>
      @foreach($blocks as $block)
        <option value="{{ $block->id }}" {{ request('block_id') == $block->id ? 'selected' : '' }}>
          {{ $block->name }}
        </option>
      @endforeach
    </select>
    <span class="material-icons absolute right-2.5 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400 text-[18px]">expand_more</span>
  </div>
  @endunless

  {{-- Status filter --}}
  <div class="relative w-full sm:w-auto">
    <select name="status"
      class="appearance-none w-full sm:w-auto bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 focus:border-primary focus:ring-2 focus:ring-primary/20 rounded-lg text-sm py-2 pl-4 pr-9 outline-none transition-all text-slate-600 dark:text-slate-300">
      <option value="">{{ __('app.all_status') }}</option>
      <option value="pending"  {{ request('status') === 'pending'  ? 'selected' : '' }}>{{ __('app.status_pending') }}</option>
      <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>{{ __('app.status_approved') }}</option>
      <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>{{ __('app.status_rejected') }}</option>
    </select>
    <span class="material-icons absolute right-2.5 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400 text-[18px]">expand_more</span>
  </div>

  {{-- Payment period month filter --}}
  <div class="relative w-full sm:w-auto">
    <select name="month"
      class="appearance-none w-full sm:w-auto bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 focus:border-primary focus:ring-2 focus:ring-primary/20 rounded-lg text-sm py-2 pl-4 pr-9 outline-none transition-all text-slate-600 dark:text-slate-300">
      <option value="">{{ __('app.all_months') }}</option>
      @foreach(range(1, 12) as $m)
        <option value="{{ now()->year }}-{{ str_pad($m, 2, '0', STR_PAD_LEFT) }}"
          {{ request('month') === now()->year.'-'.str_pad($m, 2, '0', STR_PAD_LEFT) ? 'selected' : '' }}>
          {{ \Carbon\Carbon::create(null, $m)->format('F') }}
        </option>
      @endforeach
    </select>
    <span class="material-icons absolute right-2.5 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400 text-[18px]">expand_more</span>
  </div>

  {{-- Recorded date filter (month + year) — to cross-validate with finance reports --}}
  <div class="flex flex-col gap-1 w-full sm:w-auto">
    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider pl-1">{{ __('app.filter_recorded') }}</label>
    <div class="flex flex-col sm:flex-row gap-3">
      <div class="relative w-full sm:w-auto">
        <select name="recorded_month"
          class="appearance-none w-full sm:w-auto bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 focus:border-primary focus:ring-2 focus:ring-primary/20 rounded-lg text-sm py-2 pl-4 pr-9 outline-none transition-all text-slate-600 dark:text-slate-300">
          <option value="">{{ __('app.all_months') }}</option>
          @foreach(range(1, 12) as $m)
            <option value="{{ $m }}" {{ request('recorded_month') == $m ? 'selected' : '' }}>
              {{ \Carbon\Carbon::create(null, $m)->format('M') }}
            </option>
          @endforeach
        </select>
        <span class="material-icons absolute right-2.5 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400 text-[18px]">expand_more</span>
      </div>
      <div class="relative w-full sm:w-auto">
        <select name="recorded_year"
          class="appearance-none w-full sm:w-auto bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 focus:border-primary focus:ring-2 focus:ring-primary/20 rounded-lg text-sm py-2 pl-4 pr-9 outline-none transition-all text-slate-600 dark:text-slate-300">
          <option value="">{{ __('app.all_years') }}</option>
          @foreach(range(now()->year, 2024) as $y)
            <option value="{{ $y }}" {{ request('recorded_year') == $y ? 'selected' : '' }}>{{ $y }}</option>
          @endforeach
        </select>
        <span class="material-icons absolute right-2.5 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400 text-[18px]">expand_more</span>
      </div>
    </div>
  </div>

  {{-- Search button --}}
  <button type="submit"
    class="flex justify-center items-center gap-2 px-4 py-2 bg-primary hover:bg-primary/90 text-white rounded-lg text-sm font-semibold transition-all shadow-sm shadow-primary/20 w-full sm:w-auto">
    <span class="material-icons text-sm">search</span>
    {{ __('app.btn_search') }}
  </button>

  {{-- Clear --}}
  @if(request()->hasAny(['search', 'block_id', 'status', 'month', 'recorded_month', 'recorded_year']))
    <a href="{{ route('payments.index') }}"
      class="flex justify-center items-center gap-1 px-3 py-2 text-sm font-medium text-slate-500 hover:text-primary transition-colors w-full sm:w-auto">
      <span class="material-icons text-sm">close</span>
      {{ __('app.btn_clear') }}
    </a>
  @endif

</form>