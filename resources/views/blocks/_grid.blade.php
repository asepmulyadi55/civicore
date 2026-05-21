{{-- Flash --}}
@if(session('success'))
  <div
    class="p-4 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 rounded-xl flex items-center gap-3">
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
  <div
    class="text-center py-24 bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800">
    <span class="material-icons text-5xl text-slate-300 dark:text-slate-600 block mb-4">apartment</span>
    <h2 class="text-lg font-bold text-slate-700 dark:text-slate-300">{{ __('app.no_blocks_yet') }}</h2>
    <p class="text-slate-500 mt-2 text-sm">{{ __('app.add_first_block') }}</p>
  </div>
@else
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
    @foreach($blocks as $block)
      <div
        class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm p-6 flex flex-col gap-4 group hover:shadow-md hover:border-primary/30 transition-all">
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
        <div class="grid grid-cols-4 gap-2">
          <div class="bg-slate-50 dark:bg-slate-800 rounded-lg p-2 text-center">
            <p class="text-xl font-bold text-slate-900 dark:text-white">{{ $block->units_count }}</p>
            <p class="text-[9px] text-slate-500 font-medium uppercase mt-0.5 leading-tight">{{ __('app.units_count') }}</p>
          </div>
          <div class="bg-primary/5 dark:bg-primary/10 rounded-lg p-2 text-center">
            <p class="text-xl font-bold text-primary">{{ $block->owner_occupied_units_count }}</p>
            <p class="text-[9px] text-primary/70 font-medium uppercase mt-0.5 leading-tight">{{ __('app.house_status_owner_occupied') }}</p>
          </div>
          <div class="bg-amber-50 dark:bg-amber-900/10 rounded-lg p-2 text-center">
            <p class="text-xl font-bold text-amber-500">{{ $block->rented_units_count }}</p>
            <p class="text-[9px] text-amber-500/80 font-medium uppercase mt-0.5 leading-tight">{{ __('app.house_status_rented') }}</p>
          </div>
          <div class="bg-slate-50 dark:bg-slate-800 rounded-lg p-2 text-center">
            <p class="text-xl font-bold text-slate-400">{{ $block->vacant_units_count }}</p>
            <p class="text-[9px] text-slate-500 font-medium uppercase mt-0.5 leading-tight">{{ __('app.house_status_vacant') }}</p>
          </div>
        </div>

        {{-- Coordinator --}}
        <div class="flex items-center gap-2 py-2 border-t border-slate-100 dark:border-slate-800">
          <span class="material-icons text-sm text-slate-400">manage_accounts</span>
          @forelse($block->coordinators as $coord)
            @php $initials = strtoupper(substr($coord->name, 0, 2)); @endphp
            <div
              class="w-6 h-6 rounded-full bg-primary/10 text-primary flex items-center justify-center text-[10px] font-bold flex-shrink-0">
              {{ $initials }}
            </div>
            <span class="text-xs font-medium text-slate-700 dark:text-slate-300 truncate">{{ $coord->name }}</span>
          @empty
            <span class="text-xs text-slate-400 italic">No coordinator assigned</span>
          @endforelse
        </div>

        {{-- Status + Actions --}}
        <div class="flex items-center justify-between pt-2 border-t border-slate-100 dark:border-slate-800">
          @if($block->is_active)
            <span
              class="px-2 py-0.5 bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400 text-[10px] font-bold uppercase rounded">{{ __('app.status_active') }}</span>
          @else
            <span
              class="px-2 py-0.5 bg-slate-200 text-slate-500 text-[10px] font-bold uppercase rounded">{{ __('app.status_inactive') }}</span>
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
              onclick="openEditBlockDrawer('{{ $block->id }}', '{{ addslashes($block->name) }}', '{{ addslashes($block->description ?? '') }}', {{ $block->is_active ? 'true' : 'false' }})"
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
@endif
