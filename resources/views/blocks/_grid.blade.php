{{-- Flash --}}
@if(session('success'))
  <div class="p-4 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 rounded-xl flex items-center gap-3">
    <span class="material-icons text-emerald-500">check_circle</span>
    <p class="text-sm text-emerald-700 dark:text-emerald-400">{{ session('success') }}</p>
  </div>
@endif
@if(session('error'))
  <div class="p-4 bg-rose-50 dark:bg-rose-900/20 border border-rose-200 rounded-xl flex items-center gap-3">
    <span class="material-icons text-rose-500">error</span>
    <p class="text-sm text-rose-700 dark:text-rose-400">{{ session('error') }}</p>
  </div>
@endif

{{-- Blocks Grid --}}
@if($blocks->isEmpty())
  <div class="text-center py-24 bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800">
    <span class="material-icons text-5xl text-slate-300 dark:text-slate-600 block mb-4">apartment</span>
    <h2 class="text-lg font-bold text-slate-700 dark:text-slate-300">{{ __('app.no_blocks_yet') }}</h2>
    <p class="text-slate-500 mt-2 text-sm">{{ __('app.add_first_block') }}</p>
  </div>
@else
  <form id="bulk-delete-blocks-form" action="{{ route('blocks.bulk-destroy') }}" method="POST">
    @csrf
    @method('DELETE')

    {{-- Bulk Action Bar --}}
    <div id="bulk-action-bar" class="hidden mb-4 p-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl flex items-center justify-between shadow-sm transition-all">
      <div class="flex items-center gap-3">
        <label class="flex items-center gap-2 cursor-pointer">
          <input type="checkbox" id="select-all-blocks" class="w-4 h-4 rounded border-slate-300 text-primary focus:ring-primary/30 bg-slate-50 dark:bg-slate-800" onchange="toggleAllBlocks(this)">
          <span class="text-sm font-semibold text-slate-700 dark:text-slate-300">{{ __('app.select_all') ?? 'Select All' }}</span>
        </label>
        <span class="text-sm text-slate-500 border-l border-slate-200 dark:border-slate-700 pl-3">
          <span id="selected-count">0</span> selected
        </span>
      </div>
      <button type="button" onclick="confirmBulkDelete(event, 'bulk-delete-blocks-form', 'Are you sure you want to delete the selected blocks?')" class="flex items-center gap-1.5 px-3 py-1.5 bg-rose-500 hover:bg-rose-600 text-white text-xs font-bold rounded-lg transition-colors">
        <span class="material-icons text-sm">delete</span> Delete Selected
      </button>
    </div>

  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
    @foreach($blocks as $block)
      @php $coordIds = $block->coordinators->pluck('id')->toJson(); @endphp
      <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm p-6 flex flex-col gap-4 group hover:shadow-md hover:border-primary/30 transition-all relative">

        {{-- Checkbox for bulk delete --}}
        @if(auth()->user()->can('blocks.delete'))
        <div class="absolute top-4 right-4 z-10">
          <input type="checkbox" name="ids[]" value="{{ $block->id }}" class="block-checkbox w-4 h-4 rounded border-slate-300 text-primary focus:ring-primary/30 bg-slate-50 dark:bg-slate-800 cursor-pointer" onchange="updateBulkActionBar()">
        </div>
        @endif

        {{-- Icon + Name --}}
        <div class="flex items-start gap-4">
          <div class="w-12 h-12 bg-primary/10 text-primary rounded-xl flex items-center justify-center flex-shrink-0">
            <span class="material-icons text-2xl">apartment</span>
          </div>
          <div class="flex-1 min-w-0">
            <h3 class="font-bold text-slate-900 dark:text-white truncate">{{ $block->name }}</h3>
            @if($block->description)
              <p class="text-xs text-slate-500 mt-0.5 line-clamp-2">{{ $block->description }}</p>
            @endif
          </div>
        </div>

        {{-- Stats --}}
        <div class="grid grid-cols-3 gap-2">
          <div class="bg-slate-50 dark:bg-slate-800 rounded-lg p-2 text-center">
            <p class="text-xl font-bold text-slate-900 dark:text-white">{{ $block->units_count }}</p>
            <p class="text-[9px] text-slate-500 font-medium uppercase mt-0.5 leading-tight">{{ __('app.units_count') }}</p>
          </div>
          <div class="bg-emerald-50 dark:bg-emerald-900/10 rounded-lg p-2 text-center">
            <p class="text-xl font-bold text-emerald-500">{{ $block->owner_occupied_units_count }}</p>
            <p class="text-[9px] text-emerald-500/80 font-medium uppercase mt-0.5 leading-tight">{{ __('app.house_status_owner_occupied') }}</p>
          </div>
          <div class="bg-amber-50 dark:bg-amber-900/10 rounded-lg p-2 text-center">
            <p class="text-xl font-bold text-amber-500">{{ $block->rented_units_count }}</p>
            <p class="text-[9px] text-amber-500/80 font-medium uppercase mt-0.5 leading-tight">{{ __('app.house_status_rented') }}</p>
          </div>
          <div class="bg-slate-50 dark:bg-slate-800 rounded-lg p-2 text-center">
            <p class="text-xl font-bold text-slate-900 dark:text-white">{{ $block->vacant_units_count }}</p>
            <p class="text-[9px] text-slate-500 font-medium uppercase mt-0.5 leading-tight">{{ __('app.house_status_vacant') }}</p>
          </div>
          <div class="bg-teal-50 dark:bg-teal-900/10 rounded-lg p-2 text-center">
            <p class="text-xl font-bold text-teal-500">{{ $block->public_facility_units_count }}</p>
            <p class="text-[9px] text-teal-500/80 font-medium uppercase mt-0.5 leading-tight">{{ __('app.house_status_public_facility') }}</p>
          </div>
          <div class="bg-indigo-50 dark:bg-indigo-900/10 rounded-lg p-2 text-center">
            <p class="text-xl font-bold text-indigo-500">{{ $block->developer_units_count }}</p>
            <p class="text-[9px] text-indigo-500/80 font-medium uppercase mt-0.5 leading-tight">{{ __('app.house_status_developer') }}</p>
          </div>
        </div>

        {{-- Coordinators --}}
        <div class="py-2 border-t border-slate-100 dark:border-slate-800">
          <div class="flex items-center gap-1.5 flex-wrap">
            <span class="material-icons text-sm text-slate-400 flex-shrink-0">manage_accounts</span>
            @forelse($block->coordinators as $coord)
              @php $initials = strtoupper(substr($coord->name, 0, 2)); @endphp
              <div class="flex items-center gap-1 bg-primary/5 rounded-full px-2 py-0.5">
                <div class="w-4 h-4 rounded-full bg-primary/20 text-primary flex items-center justify-center text-[8px] font-bold flex-shrink-0">
                  {{ $initials }}
                </div>
                <span class="text-[10px] font-medium text-primary truncate max-w-[80px]">{{ $coord->name }}</span>
              </div>
            @empty
              <span class="text-xs text-slate-400 italic">{{ __('app.no_coordinator_assigned') }}</span>
            @endforelse
          </div>
        </div>

        {{-- Status + Actions --}}
        <div class="flex items-center justify-between pt-2 border-t border-slate-100 dark:border-slate-800">
          @if($block->is_active)
            <span class="px-2 py-0.5 bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400 text-[10px] font-bold uppercase rounded">{{ __('app.status_active') }}</span>
          @else
            <span class="px-2 py-0.5 bg-slate-200 text-slate-500 text-[10px] font-bold uppercase rounded">{{ __('app.status_inactive') }}</span>
          @endif

          <div class="flex gap-1">
            @if(auth()->user()->can('blocks.view'))
              <a href="{{ route('blocks.units.index', $block) }}"
                class="p-2 text-slate-400 hover:text-primary hover:bg-primary/10 rounded-lg transition-colors"
                title="{{ __('app.btn_manage_units') }}">
                <span class="material-icons text-sm">home_work</span>
              </a>
            @endif
            @if(auth()->user()->can('blocks.edit'))
              <button
                onclick="openEditBlockDrawer('{{ $block->id }}', '{{ addslashes($block->name) }}', '{{ addslashes($block->description ?? '') }}', {{ $block->is_active ? 'true' : 'false' }}, {{ $coordIds }})"
                class="p-2 text-slate-400 hover:text-primary hover:bg-primary/10 rounded-lg transition-colors"
                title="{{ __('app.title_edit_block') }}">
                <span class="material-icons text-sm">edit</span>
              </button>
            @endif
            @if(auth()->user()->can('blocks.delete'))
              <button type="button" onclick="openDeleteBlockModal('{{ $block->id }}', '{{ addslashes($block->name) }}')"
                class="p-2 text-slate-400 hover:text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-950/20 rounded-lg transition-colors"
                title="{{ __('app.title_delete_block') }}">
                <span class="material-icons text-sm">delete_outline</span>
              </button>
            @endif
          </div>
        </div>
      </div>
    @endforeach
  </div>
  </form>

  <script>
    function toggleAllBlocks(source) {
      const checkboxes = document.querySelectorAll('.block-checkbox');
      checkboxes.forEach(cb => { cb.checked = source.checked; });
      updateBulkActionBar();
    }

    function updateBulkActionBar() {
      const checkboxes = document.querySelectorAll('.block-checkbox');
      const checkedCount = Array.from(checkboxes).filter(cb => cb.checked).length;
      const actionBar = document.getElementById('bulk-action-bar');
      const selectAll = document.getElementById('select-all-blocks');
      const countLabel = document.getElementById('selected-count');

      if (checkedCount > 0) {
        actionBar.classList.remove('hidden');
        actionBar.classList.add('flex');
      } else {
        actionBar.classList.add('hidden');
        actionBar.classList.remove('flex');
      }

      if (countLabel) countLabel.textContent = checkedCount;
      if (selectAll) selectAll.checked = (checkedCount === checkboxes.length && checkboxes.length > 0);
    }
  </script>
@endif
