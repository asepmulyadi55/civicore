{{-- Blocks Management Page --}}
<x-layouts.app :title="__('app.nav_blocks')"
  class="font-display bg-background-light dark:bg-background-dark text-slate-800 dark:text-slate-200 antialiased min-h-screen">

  <x-nav.sidebar active="blocks" />

  {{-- Block form modals --}}
  <x-modals.block-form />

  <div class="lg:pl-64 min-h-screen bg-background-light dark:bg-background-dark flex flex-col">

    {{-- Header --}}
    <header
      class="h-16 bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between px-6 lg:px-8">
      <div class="flex items-center gap-4">
        <button class="lg:hidden p-2 rounded-lg border border-slate-200 dark:border-slate-800"
          onclick="toggleSidebar()">
          <span class="material-icons text-slate-500">menu</span>
        </button>
        <h1 class="text-xl font-bold text-slate-900 dark:text-white">{{ __('app.block_management') }}</h1>
        <span {{ __('app.blocks_count') }}</span>
      </div>
      <div class="flex items-center gap-3">
        <button onclick="openAddBlockModal()"
          class="flex items-center gap-2 bg-primary hover:bg-primary/90 text-white px-4 py-2 rounded-lg font-semibold transition-all shadow-sm shadow-primary/20 text-sm">
          <span class="material-icons text-sm">add</span>
          <span class="hidden sm:inline">{{ __('app.btn_add_block') }}</span>
        </button>
        <button
          class="p-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg hover:border-primary/50 transition-all"
          onclick="toggleDark()" title="Toggle dark mode">
          <span class="material-icons text-slate-500 text-[20px]">dark_mode</span>
        </button>
      </div>
    </header>

    {{-- Body --}}
    <main class="flex-1 p-6 lg:p-8 space-y-6">

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
              <div class="grid grid-cols-2 gap-3">
                <div class="bg-slate-50 dark:bg-slate-800 rounded-lg p-3 text-center">
                  <p class="text-xl font-bold text-slate-900 dark:text-white">{{ $block->active_residents_count }}</p>
                  <p class="text-[10px] text-slate-500 font-medium uppercase mt-0.5">{{ __('app.status_active') }}</p>
                </div>
                <div class="bg-slate-50 dark:bg-slate-800 rounded-lg p-3 text-center">
                  <p class="text-xl font-bold text-slate-900 dark:text-white">{{ $block->residents_count }}</p>
                  <p class="text-[10px] text-slate-500 font-medium uppercase mt-0.5">{{ __('app.total_label') }}</p>
                </div>
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
                  <button
                    onclick="openEditBlockDrawer({{ $block->id }}, '{{ addslashes($block->name) }}', '{{ addslashes($block->description ?? '') }}', {{ $block->is_active ? 'true' : 'false' }})"
                    class="p-2 text-slate-400 hover:text-primary hover:bg-primary/10 rounded-lg transition-colors"
                    title="{{ __('app.title_edit_block') }}">
                    <span class="material-icons text-sm">edit</span>
                  </button>
                  <button type="button" onclick="openDeleteBlockModal({{ $block->id }}, '{{ addslashes($block->name) }}')"
                    class="p-2 text-slate-400 hover:text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-950/20 rounded-lg transition-colors"
                    title="{{ __('app.title_delete_block') }}">
                    <span class="material-icons text-sm">delete_outline</span>
                  </button>
                </div>
              </div>
            </div>
          @endforeach
        </div>
      @endif
    </main>
  </div>

  {{-- Delete Confirmation Modal --}}
  <div id="modal-delete-block"
    class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-2xl w-full max-w-sm overflow-hidden">
      <div class="p-6 flex flex-col items-center text-center">
        <div class="w-14 h-14 rounded-full bg-rose-100 dark:bg-rose-900/30 flex items-center justify-center mb-4">
          <span class="material-icons text-rose-600 dark:text-rose-400 text-2xl">delete_forever</span>
        </div>
        <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-1">{{ __('app.delete_block_title') }}</h3>
        <p class="text-sm text-slate-500 dark:text-slate-400">
          <span class="font-semibold text-slate-700 dark:text-slate-200" id="delete-block-name"></span>
          {{ __('app.delete_block_body') }}
        </p>
      </div>
      <div class="flex gap-3 px-6 pb-6">
        <button type="button" onclick="closeDeleteBlockModal()"
          class="flex-1 px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 text-sm font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition-all">
          {{ __('app.btn_cancel') }}
        </button>
        <form id="delete-block-form" method="POST" action="" class="flex-1">
          @csrf @method('DELETE')
          <button type="submit"
            class="w-full px-4 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-sm font-bold text-white transition-all">
            {{ __('app.btn_yes_delete') }}
          </button>
        </form>
      </div>
    </div>
  </div>

  <script>
    function openDeleteBlockModal(id, name) {
      document.getElementById('delete-block-name').textContent = name;
      document.getElementById('delete-block-form').action = '/blocks/' + id;
      document.getElementById('modal-delete-block').classList.remove('hidden');
      document.body.classList.add('overflow-hidden');
    }
    function closeDeleteBlockModal() {
      document.getElementById('modal-delete-block').classList.add('hidden');
      document.body.classList.remove('overflow-hidden');
    }
    document.getElementById('modal-delete-block').addEventListener('click', function (e) {
      if (e.target === this) closeDeleteBlockModal();
    });
    document.addEventListener('keydown', e => {
      if (e.key === 'Escape') closeDeleteBlockModal();
    });
  </script>

</x-layouts.app>