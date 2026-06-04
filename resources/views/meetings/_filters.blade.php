{{-- meetings/_filters.blade.php --}}
<form method="GET" action="{{ route('meetings.index') }}"
  class="bg-white dark:bg-slate-900 p-4 rounded-xl border border-slate-200 dark:border-slate-800 flex flex-col sm:flex-row sm:flex-wrap sm:items-center gap-3">

  {{-- Topic search --}}
  <div class="relative w-full sm:flex-grow sm:max-w-sm">
    <span class="material-icons absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">search</span>
    <input type="text" name="q" value="{{ request('q') }}"
      placeholder="{{ __('app.meeting_search_ph') }}"
      class="w-full pl-9 pr-4 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all dark:text-slate-100 dark:placeholder-slate-500 outline-none" />
  </div>

  {{-- Month filter --}}
  <div class="relative w-full sm:w-auto">
    <select name="month"
      class="appearance-none w-full sm:w-auto pl-4 pr-9 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm text-slate-600 dark:text-slate-300 focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none">
      <option value="">{{ __('app.meeting_all_months') }}</option>
      @foreach(range(1, 12) as $m)
        <option value="{{ $m }}" {{ request('month') == $m ? 'selected' : '' }}>
          {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
        </option>
      @endforeach
    </select>
    <span class="material-icons absolute right-2.5 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400 text-[18px]">expand_more</span>
  </div>

  {{-- Year filter --}}
  @if($availableYears->isNotEmpty())
    <div class="relative w-full sm:w-auto">
      <select name="year"
        class="appearance-none w-full sm:w-auto pl-4 pr-9 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm text-slate-600 dark:text-slate-300 focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none">
        <option value="">{{ __('app.meeting_all_years') }}</option>
        @foreach($availableYears as $yr)
          <option value="{{ $yr }}" {{ request('year') == $yr ? 'selected' : '' }}>{{ $yr }}</option>
        @endforeach
      </select>
      <span class="material-icons absolute right-2.5 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400 text-[18px]">expand_more</span>
    </div>
  @endif

  {{-- Search button --}}
  <button type="submit"
    class="flex justify-center items-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-primary hover:bg-primary/90 rounded-lg shadow-sm shadow-primary/20 transition-all w-full sm:w-auto">
    <span class="material-icons text-sm">search</span>
    {{ __('app.btn_search') }}
  </button>

  {{-- Clear filters --}}
  @if(request()->hasAny(['q', 'month', 'year']))
    <a href="{{ route('meetings.index') }}"
      class="flex justify-center items-center gap-1 px-3 py-2 text-sm font-medium text-slate-500 hover:text-primary transition-colors w-full sm:w-auto">
      <span class="material-icons text-sm">close</span>
      {{ __('app.clear_filters') }}
    </a>
  @endif

</form>
