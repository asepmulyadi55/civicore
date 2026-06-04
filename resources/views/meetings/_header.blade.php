{{-- meetings/_header.blade.php --}}
<header class="h-16 bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between px-6 lg:px-8">

  <div class="flex items-center gap-4">
    <button class="lg:hidden p-2 rounded-lg border border-slate-200 dark:border-slate-800" onclick="toggleSidebar()">
      <span class="material-icons text-slate-500">menu</span>
    </button>
    <h1 class="text-xl font-bold text-slate-900 dark:text-white">{{ __('app.meeting_title') }}</h1>
    <span class="hidden sm:inline px-2 py-1 text-xs font-semibold bg-primary/10 text-primary rounded-lg">
      {{ $meetings->total() }} {{ __('app.meeting_count_label') }}
    </span>
  </div>

  <div class="flex items-center gap-3">
    {{-- Add Meeting --}}
    @if(auth()->user()->can('meetings.create'))
      <button type="button" onclick="openModal('modal-add-meeting')"
        class="flex items-center gap-2 bg-primary hover:bg-primary/90 text-white px-4 py-2 rounded-lg font-semibold transition-all shadow-sm shadow-primary/20 text-sm">
        <span class="material-icons text-sm">add</span>
        <span class="hidden sm:inline">{{ __('app.meeting_add') }}</span>
      </button>
    @endif

    {{-- Dark mode toggle --}}
    <button
      class="p-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg hover:border-primary/50 transition-all"
      onclick="toggleDark()" title="{{ __('app.toggle_dark_mode') }}">
      <span class="material-icons text-slate-500 text-[20px]">dark_mode</span>
    </button>
  </div>

</header>
