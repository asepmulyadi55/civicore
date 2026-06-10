{{-- Property Listings Filters --}}
<form method="GET" action="{{ route('property.index') }}"
  class="bg-white dark:bg-slate-900 p-4 rounded-xl border border-slate-200 dark:border-slate-800 flex flex-col sm:flex-row sm:flex-wrap sm:items-center gap-3">
  
  <div class="relative w-full sm:flex-grow sm:max-w-sm">
    <span class="material-icons absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-[18px]">search</span>
    <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('app.btn_search') }}..."
      class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all">
  </div>
  
  <div class="relative w-full sm:w-auto">
    <select name="type"
      class="w-full sm:w-auto appearance-none pl-3.5 pr-10 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all cursor-pointer">
      <option value="">{{ __('app.property_filter_all_types') }}</option>
      <option value="sell" {{ request('type') === 'sell' ? 'selected' : '' }}>{{ __('app.property_type_sell') }}</option>
      <option value="rent" {{ request('type') === 'rent' ? 'selected' : '' }}>{{ __('app.property_type_rent') }}</option>
    </select>
    <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 material-icons text-slate-400 text-[18px]">expand_more</span>
  </div>
  
  <div class="relative w-full sm:w-auto">
    <select name="status"
      class="w-full sm:w-auto appearance-none pl-3.5 pr-10 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all cursor-pointer">
      <option value="">{{ __('app.property_filter_all_status') }}</option>
      <option value="available" {{ request('status') === 'available' ? 'selected' : '' }}>{{ __('app.property_status_available') }}</option>
      <option value="sold"      {{ request('status') === 'sold'      ? 'selected' : '' }}>{{ __('app.property_status_sold') }}</option>
      <option value="rented"   {{ request('status') === 'rented'   ? 'selected' : '' }}>{{ __('app.property_status_rented') }}</option>
    </select>
    <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 material-icons text-slate-400 text-[18px]">expand_more</span>
  </div>
  
  <button type="submit"
    class="flex justify-center items-center gap-2 px-4 py-2.5 bg-primary hover:bg-primary/90 text-white rounded-xl text-sm font-semibold transition-all shadow-sm shadow-primary/20 w-full sm:w-auto">
    <span class="material-icons text-sm">search</span>
    {{ __('app.btn_filter') }}
  </button>
  
  @if(request()->hasAny(['search', 'type', 'status']))
    <a href="{{ route('property.index') }}"
      class="flex justify-center items-center gap-1 px-4 py-2.5 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400 text-sm font-semibold rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors w-full sm:w-auto">
      <span class="material-icons text-sm">close</span>
      {{ __('app.clear_filters') }}
    </a>
  @endif
</form>
