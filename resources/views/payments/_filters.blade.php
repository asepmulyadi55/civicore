{{-- payments/_filters.blade.php --}}
<form method="GET" action="{{ route('payments.index') }}">
  <div class="bg-white dark:bg-slate-900 p-4 rounded-xl border border-slate-200 dark:border-slate-800 flex flex-wrap gap-3 items-center">

    {{-- Search --}}
    <div class="flex-1 min-w-[220px] relative">
      <span class="material-icons absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-lg">search</span>
      <input name="search" value="{{ request('search') }}"
        class="w-full pl-10 pr-4 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 focus:border-primary focus:ring-2 focus:ring-primary/20 rounded-lg text-sm outline-none transition-all dark:text-slate-100"
        placeholder="{{ __('app.search_resident_unit') }}" type="text" />
    </div>

    <div class="flex flex-wrap gap-2 items-center">

      {{-- Block filter (admin & treasurer only) --}}
      @unless(auth()->user()->isBlockCoordinator())
      <div class="relative">
        <select name="block_id"
          class="appearance-none bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 focus:border-primary focus:ring-2 focus:ring-primary/20 rounded-lg text-sm py-2 pl-4 pr-9 outline-none transition-all text-slate-600 dark:text-slate-300">
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
      <div class="relative">
        <select name="status"
          class="appearance-none bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 focus:border-primary focus:ring-2 focus:ring-primary/20 rounded-lg text-sm py-2 pl-4 pr-9 outline-none transition-all text-slate-600 dark:text-slate-300">
          <option value="">{{ __('app.all_status') }}</option>
          <option value="pending"  {{ request('status') === 'pending'  ? 'selected' : '' }}>{{ __('app.status_pending') }}</option>
          <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>{{ __('app.status_approved') }}</option>
          <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>{{ __('app.status_rejected') }}</option>
        </select>
        <span class="material-icons absolute right-2.5 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400 text-[18px]">expand_more</span>
      </div>

      {{-- Month filter --}}
      <div class="relative">
        <select name="month"
          class="appearance-none bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 focus:border-primary focus:ring-2 focus:ring-primary/20 rounded-lg text-sm py-2 pl-4 pr-9 outline-none transition-all text-slate-600 dark:text-slate-300">
          <option value="">{{ __('app.all_months') }}</option>
          @foreach(range(1, 12) as $m)
            @php $mStr = now()->setMonth($m)->format('Y-m'); @endphp
            <option value="{{ now()->year }}-{{ str_pad($m, 2, '0', STR_PAD_LEFT) }}"
              {{ request('month') === now()->year.'-'.str_pad($m, 2, '0', STR_PAD_LEFT) ? 'selected' : '' }}>
              {{ \Carbon\Carbon::create(null, $m)->format('F') }}
            </option>
          @endforeach
        </select>
        <span class="material-icons absolute right-2.5 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400 text-[18px]">expand_more</span>
      </div>

      {{-- Search button --}}
      <button type="submit"
        class="flex items-center gap-2 px-4 py-2 bg-primary hover:bg-primary/90 text-white rounded-lg text-sm font-semibold transition-all shadow-sm shadow-primary/20">
        <span class="material-icons text-sm">search</span>
        {{ __('app.btn_search') }}
      </button>

      {{-- Clear --}}
      @if(request()->hasAny(['search', 'block_id', 'status', 'month']))
        <a href="{{ route('payments.index') }}"
          class="flex items-center gap-1 px-3 py-2 text-sm font-medium text-slate-500 hover:text-primary transition-colors">
          <span class="material-icons text-sm">close</span>
          {{ __('app.btn_clear') }}
        </a>
      @endif

    </div>
  </div>
</form>