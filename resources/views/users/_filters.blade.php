{{-- Search & Filter Bar --}}
<form method="GET" action="{{ route('users.index') }}"
  class="bg-white dark:bg-dark-card p-4 rounded-xl border border-slate-200 dark:border-white/5 flex flex-wrap gap-3 items-center">

  {{-- Search --}}
  <div class="flex-1 min-w-[240px] relative">
    <span class="material-icons absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-lg">search</span>
    <input name="search" value="{{ request('search') }}"
      class="w-full pl-10 pr-4 py-2 bg-slate-50 dark:bg-white/5 border border-slate-200 dark:border-white/5
             focus:border-primary focus:ring-2 focus:ring-primary/20 rounded-lg text-sm outline-none transition-all
             dark:text-slate-100 dark:placeholder-slate-500"
      placeholder="{{ __('app.search_users_placeholder') }}" type="text" />
  </div>

  <div class="flex gap-2 flex-wrap items-center">
    {{-- Role filter --}}
    <div class="relative">
      <select name="role_id"
        class="appearance-none bg-slate-50 dark:bg-white/5 border border-slate-200 dark:border-white/5
               focus:border-primary focus:ring-2 focus:ring-primary/20 rounded-lg text-sm py-2 pl-4 pr-9 outline-none
               transition-all text-slate-600 dark:text-slate-300">
        <option value="">{{ __('app.all_roles') }}</option>
        @foreach($roles as $role)
          <option value="{{ $role->id }}" {{ request('role_id') == $role->id ? 'selected' : '' }}>
            {{ ucfirst(str_replace('_', ' ', $role->name)) }}
          </option>
        @endforeach
      </select>
      <span class="material-icons absolute right-2.5 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400 text-[18px]">expand_more</span>
    </div>

    {{-- Status filter --}}
    <div class="relative">
      <select name="status"
        class="appearance-none bg-slate-50 dark:bg-white/5 border border-slate-200 dark:border-white/5
               focus:border-primary focus:ring-2 focus:ring-primary/20 rounded-lg text-sm py-2 pl-4 pr-9 outline-none
               transition-all text-slate-600 dark:text-slate-300">
        <option value="">{{ __('app.all_status') }}</option>
        <option value="active"   {{ request('status') === 'active'   ? 'selected' : '' }}>{{ __('app.status_active') }}</option>
        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>{{ __('app.status_inactive') }}</option>
        <option value="pending"  {{ request('status') === 'pending'  ? 'selected' : '' }}>{{ __('app.pending_approval') }}</option>
      </select>
      <span class="material-icons absolute right-2.5 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400 text-[18px]">expand_more</span>
    </div>

    {{-- Search button --}}
    <button type="submit"
      class="flex items-center gap-2 px-4 py-2 bg-primary hover:bg-primary/90 text-white rounded-lg text-sm font-semibold transition-all shadow-sm shadow-primary/20">
      <span class="material-icons text-sm">search</span>
      {{ __('app.btn_search') }}
    </button>

    {{-- Clear filters --}}
    @if(request()->hasAny(['search', 'role_id', 'status']))
      <a href="{{ route('users.index') }}"
        class="flex items-center gap-1 px-3 py-2 text-sm font-medium text-slate-500 hover:text-primary transition-colors">
        <span class="material-icons text-sm">close</span>
        {{ __('app.clear_filters') }}
      </a>
    @endif
  </div>
</form>