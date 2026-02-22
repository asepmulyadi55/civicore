{{-- Search & Filter Bar (#10: removed "More Filters", added Search button; #11: now wired to UserController filter)
--}}
<form method="GET" action="{{ route('users.index') }}">
  <div
    class="bg-white dark:bg-slate-900 p-4 rounded-xl border border-slate-200 dark:border-slate-800 flex flex-wrap gap-4 items-center">
    {{-- Search --}}
    <div class="flex-1 min-w-[240px] relative">
      <span class="material-icons absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-lg">search</span>
      <input name="search" value="{{ request('search') }}"
        class="w-full pl-10 pr-4 py-2 bg-background-light dark:bg-slate-800 border border-slate-200 dark:border-slate-700 focus:border-primary focus:ring-2 focus:ring-primary/20 rounded-lg text-sm outline-none transition-all"
        placeholder="Search by name, email or username..." type="text" />
    </div>
    <div class="flex gap-2 flex-wrap items-center">
      {{-- Role filter --}}
      <div class="relative">
        <select name="role_id"
          class="appearance-none bg-background-light dark:bg-slate-800 border border-slate-200 dark:border-slate-700 focus:border-primary focus:ring-2 focus:ring-primary/20 rounded-lg text-sm py-2 pl-4 pr-9 outline-none transition-all">
          <option value="">All Roles</option>
          @foreach($roles as $role)
            <option value="{{ $role->id }}" {{ request('role_id') == $role->id ? 'selected' : '' }}>
              {{ ucfirst(str_replace('_', ' ', $role->name)) }}
            </option>
          @endforeach
        </select>
        <span
          class="material-icons absolute right-2.5 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400 text-[18px]">expand_more</span>
      </div>
      {{-- Status filter --}}
      <div class="relative">
        <select name="status"
          class="appearance-none bg-background-light dark:bg-slate-800 border border-slate-200 dark:border-slate-700 focus:border-primary focus:ring-2 focus:ring-primary/20 rounded-lg text-sm py-2 pl-4 pr-9 outline-none transition-all">
          <option value="">All Status</option>
          <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
          <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
          <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending Approval</option>
        </select>
        <span
          class="material-icons absolute right-2.5 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400 text-[18px]">expand_more</span>
      </div>
      {{-- Search button --}}
      <button type="submit"
        class="flex items-center gap-2 px-4 py-2 bg-primary hover:bg-primary/90 text-white rounded-lg text-sm font-semibold transition-all shadow-sm shadow-primary/20">
        <span class="material-icons text-sm">search</span>
        Search
      </button>
    </div>
  </div>
</form>