<x-layouts.app :title="__('app.media_manager')"
  class="font-display bg-background-light dark:bg-background-dark text-slate-800 dark:text-slate-200 antialiased min-h-screen">

  <x-nav.sidebar active="media" />

  <main class="lg:ml-64 flex flex-col h-screen overflow-hidden">

    {{-- Header --}}
    <header
      class="h-16 bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between px-6 lg:px-8 shrink-0">
      <div class="flex items-center gap-4">
        <button class="lg:hidden p-2 rounded-lg border border-slate-200 dark:border-slate-800"
          onclick="toggleSidebar()">
          <span class="material-icons text-slate-500">menu</span>
        </button>
        <h1 class="text-xl font-bold text-slate-900 dark:text-white">{{ __('app.media_manager') }}</h1>
        <span
          class="hidden sm:inline px-2.5 py-1 text-xs font-semibold bg-primary/10 text-primary rounded-lg uppercase">
          {{ $files->total() }} {{ __('app.media_files') }}
        </span>
      </div>
      <button
        class="p-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg hover:border-primary/50 transition-all"
        onclick="toggleDark()" title="{{ __('app.toggle_dark_mode') }}">
        <span class="material-icons text-slate-500 text-[20px]">dark_mode</span>
      </button>
    </header>

    <div class="flex-1 overflow-y-auto p-6 lg:p-8 space-y-6">

      {{-- Flash Messages --}}
      @foreach(['success', 'error'] as $type)
        @if(session($type))
          <div
            class="p-4 {{ $type === 'success' ? 'bg-emerald-50 dark:bg-emerald-900/20 border-emerald-200 text-emerald-700 dark:text-emerald-400' : 'bg-rose-50 dark:bg-rose-900/20 border-rose-200 text-rose-700 dark:text-rose-400' }} border rounded-xl flex items-center gap-3">
            <span class="material-icons text-sm">{{ $type === 'success' ? 'check_circle' : 'error' }}</span>
            <p class="text-sm">{{ session($type) }}</p>
          </div>
        @endif
      @endforeach

      {{-- Search & Filters --}}
      <form method="GET" action="{{ route('media.index') }}"
        class="flex flex-col sm:flex-row gap-3">
        <div class="relative flex-1">
          <span class="material-icons absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-[20px]">search</span>
          <input type="text" name="search" value="{{ request('search') }}"
            placeholder="{{ __('app.search_by_filename') }}"
            class="w-full pl-10 pr-4 py-2.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all" />
        </div>
        <select name="type"
          class="px-4 py-2.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
          <option value="">{{ __('app.all_types') }}</option>
          <option value="image" {{ request('type') === 'image' ? 'selected' : '' }}>{{ __('app.type_image') }}</option>
          <option value="document" {{ request('type') === 'document' ? 'selected' : '' }}>{{ __('app.type_document') }}</option>
        </select>
        <button type="submit"
          class="px-5 py-2.5 bg-primary hover:bg-primary/90 text-white text-sm font-bold rounded-xl transition-all shadow-sm shadow-primary/20">
          {{ __('app.btn_search') }}
        </button>
        @if(request('search') || request('type'))
          <a href="{{ route('media.index') }}"
            class="px-5 py-2.5 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400 text-sm font-bold rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 transition-all">
            {{ __('app.btn_clear') }}
          </a>
        @endif
      </form>

      {{-- Bulk Actions Bar (delete permission required) --}}
      @if(auth()->user()->can('media.delete'))
      <div id="bulk-bar"
        class="hidden items-center justify-between bg-primary/5 border border-primary/20 rounded-xl px-5 py-3">
        <span class="text-sm font-semibold text-primary">
          <span id="selected-count">0</span> file(s) selected
        </span>
        <div class="flex items-center gap-3">
          <button onclick="selectAll()"
            class="text-sm font-bold text-slate-600 dark:text-slate-400 hover:text-primary transition-colors">
            {{ __('app.select_all') }}
          </button>
          <span class="text-slate-300">|</span>
          <button onclick="deselectAll()"
            class="text-sm font-bold text-slate-600 dark:text-slate-400 hover:text-primary transition-colors">
            {{ __('app.deselect_all') }}
          </button>
          <span class="text-slate-300">|</span>
          <button onclick="submitBulkDelete()"
            class="flex items-center gap-1.5 px-3 py-1.5 bg-rose-500 hover:bg-rose-600 text-white text-sm font-bold rounded-lg transition-all">
            <span class="material-icons text-sm">delete_sweep</span>
            {{ __('app.btn_delete_selected') }}
          </button>
        </div>
      </div>
      @endif

      {{-- Select All Toggle --}}
      @if($files->count() > 0 && auth()->user()->can('media.delete'))
        <div class="flex items-center gap-3">
          <label class="flex items-center gap-2 cursor-pointer select-none text-sm text-slate-500 hover:text-slate-700 dark:hover:text-slate-300 transition-colors">
            <input type="checkbox" id="toggle-all" class="w-4 h-4 rounded accent-primary" onchange="toggleAll(this)">
            {{ __('app.select_all') }}
          </label>
        </div>
      @endif

      {{-- File Grid --}}
      @forelse($files as $file)
        @if($loop->first)
          <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4" id="file-grid">
        @endif

        <div class="group relative bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 overflow-hidden shadow-sm hover:shadow-md hover:border-primary/30 transition-all"
          data-id="{{ $file->id }}">

          {{-- Checkbox overlay (only shown when user has delete permission) --}}
          @if(auth()->user()->can('media.delete'))
          <div class="absolute top-2 left-2 z-10">
            <input type="checkbox" class="file-checkbox w-5 h-5 rounded accent-primary opacity-0 group-hover:opacity-100 transition-opacity cursor-pointer"
              data-id="{{ $file->id }}" onchange="updateBulkBar()">
          </div>
          @endif

          {{-- Thumbnail --}}
          <div class="aspect-square bg-slate-100 dark:bg-slate-800 flex items-center justify-center overflow-hidden relative">
            @if($file->disk === 'local')
              <span class="absolute top-1.5 right-1.5 z-10 inline-flex items-center gap-0.5 px-1.5 py-0.5 bg-slate-800/70 text-white text-[10px] font-bold rounded-md backdrop-blur-sm">
                <span class="material-icons text-[11px]">lock</span> Private
              </span>
            @endif
            @if($file->is_image)
              <img src="{{ $file->url }}" alt="{{ $file->original_name }}"
                class="w-full h-full object-cover transition-transform duration-200 group-hover:scale-105"
                loading="lazy"
                onerror="this.parentElement.innerHTML='<span class=\'material-icons text-4xl text-slate-300\'>broken_image</span>'">
            @else
              <span class="material-icons text-4xl text-slate-400">insert_drive_file</span>
            @endif
          </div>

          {{-- Info --}}
          <div class="p-2.5">
            <p class="text-xs font-semibold text-slate-700 dark:text-slate-300 truncate" title="{{ $file->original_name }}">
              {{ $file->original_name }}</p>
            <p class="text-[11px] text-slate-400 mt-0.5">{{ $file->human_size }}</p>
            <p class="text-[10px] text-slate-300 dark:text-slate-600 mt-0.5">{{ $file->created_at->format('d M Y') }}</p>
          </div>

          {{-- Actions overlay --}}
          <div class="absolute inset-0 bg-black/0 group-hover:bg-black/40 transition-all flex items-center justify-center gap-2 opacity-0 group-hover:opacity-100">
            @if($file->is_image)
              <a href="{{ $file->url }}" target="_blank"
                class="p-2 bg-white rounded-full shadow-md hover:bg-primary hover:text-white transition-all"
                title="View">
                <span class="material-icons text-lg">open_in_new</span>
              </a>
            @endif
            @if(auth()->user()->can('media.delete'))
              <button onclick="confirmDelete('{{ $file->id }}', '{{ addslashes($file->original_name) }}')"
                class="p-2 bg-white rounded-full shadow-md hover:bg-rose-500 hover:text-white transition-all"
                title="{{ __('app.btn_delete') }}">
                <span class="material-icons text-lg">delete_outline</span>
              </button>
            @endif
          </div>
        </div>

        @if($loop->last)
          </div>
        @endif
      @empty
        <div class="flex flex-col items-center justify-center py-24 text-center">
          <span class="material-icons text-5xl text-slate-300 dark:text-slate-700 mb-4">perm_media</span>
          <p class="text-slate-500 font-semibold">{{ __('app.no_media_found') }}</p>
          @if(request('search') || request('type'))
            <a href="{{ route('media.index') }}" class="mt-2 text-sm text-primary hover:underline">{{ __('app.clear_filters') }}</a>
          @endif
        </div>
      @endforelse

      {{-- Pagination --}}
      @if($files->hasPages())
        <div class="flex items-center justify-between pt-2">
          <p class="text-sm text-slate-500">
            {{ __('app.showing') }} {{ $files->firstItem() }}–{{ $files->lastItem() }}
            {{ __('app.of') }} {{ $files->total() }} {{ __('app.media_files') }}
          </p>
          <div class="flex items-center gap-1">
            @if($files->onFirstPage())
              <span class="px-3 py-1.5 text-sm text-slate-300 dark:text-slate-600 border border-slate-200 dark:border-slate-700 rounded-lg cursor-not-allowed">
                <span class="material-icons text-sm align-middle">chevron_left</span>
              </span>
            @else
              <a href="{{ $files->previousPageUrl() }}"
                class="px-3 py-1.5 text-sm text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-700 rounded-lg hover:border-primary/50 hover:text-primary transition-all">
                <span class="material-icons text-sm align-middle">chevron_left</span>
              </a>
            @endif

            @foreach($files->getUrlRange(max(1, $files->currentPage() - 2), min($files->lastPage(), $files->currentPage() + 2)) as $page => $url)
              @if($page === $files->currentPage())
                <span class="px-3 py-1.5 text-sm font-bold text-white bg-primary border border-primary rounded-lg">{{ $page }}</span>
              @else
                <a href="{{ $url }}"
                  class="px-3 py-1.5 text-sm text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-700 rounded-lg hover:border-primary/50 hover:text-primary transition-all">{{ $page }}</a>
              @endif
            @endforeach

            @if($files->hasMorePages())
              <a href="{{ $files->nextPageUrl() }}"
                class="px-3 py-1.5 text-sm text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-700 rounded-lg hover:border-primary/50 hover:text-primary transition-all">
                <span class="material-icons text-sm align-middle">chevron_right</span>
              </a>
            @else
              <span class="px-3 py-1.5 text-sm text-slate-300 dark:text-slate-600 border border-slate-200 dark:border-slate-700 rounded-lg cursor-not-allowed">
                <span class="material-icons text-sm align-middle">chevron_right</span>
              </span>
            @endif
          </div>
        </div>
      @endif

    </div>
  </main>

  {{-- Delete Single File Modal --}}
  <div id="delete-file-overlay"
    class="hidden fixed inset-0 z-50 bg-black/50 backdrop-blur-sm items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-2xl w-full max-w-sm">
      <div class="p-6 flex flex-col items-center gap-4 text-center">
        <div class="w-14 h-14 rounded-full bg-rose-100 dark:bg-rose-900/30 flex items-center justify-center">
          <span class="material-icons text-rose-500 text-2xl">delete_forever</span>
        </div>
        <div>
          <h3 class="text-lg font-bold">{{ __('app.confirm_delete_file') }}</h3>
          <p class="text-sm text-slate-500 mt-1"><strong id="delete-file-name" class="text-slate-700 dark:text-slate-300"></strong></p>
        </div>
        <div class="flex gap-3 w-full">
          <button onclick="closeDeleteModal()"
            class="flex-1 py-3 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-bold text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
            {{ __('app.btn_cancel') }}
          </button>
          <form id="delete-file-form" method="POST" action="" class="flex-1">
            @csrf @method('DELETE')
            <button type="submit"
              class="w-full py-3 bg-rose-500 text-white rounded-xl text-sm font-bold hover:bg-rose-600 transition-all active:scale-95">
              {{ __('app.btn_delete') }}
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>

  {{-- Bulk Delete Confirmation Modal --}}
  <div id="bulk-delete-overlay"
    class="hidden fixed inset-0 z-50 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-2xl w-full max-w-sm">
      <div class="p-6 flex flex-col items-center gap-4 text-center">
        <div class="w-14 h-14 rounded-full bg-rose-100 dark:bg-rose-900/30 flex items-center justify-center">
          <span class="material-icons text-rose-500 text-2xl">delete_sweep</span>
        </div>
        <div>
          <h3 class="text-lg font-bold text-slate-900 dark:text-white">{{ __('app.btn_delete_selected') }}</h3>
          <p id="bulk-delete-body" class="text-sm text-slate-500 dark:text-slate-400 mt-1"></p>
        </div>
        <div class="flex gap-3 w-full">
          <button onclick="closeBulkDeleteModal()"
            class="flex-1 py-3 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-bold
              text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
            {{ __('app.btn_cancel') }}
          </button>
          <button onclick="confirmBulkDelete()"
            class="flex-1 py-3 bg-rose-500 hover:bg-rose-600 text-white rounded-xl text-sm font-bold
              transition-all active:scale-95">
            {{ __('app.btn_delete') }}
          </button>
        </div>
      </div>
    </div>
  </div>

  {{-- Bulk Delete Form (hidden) --}}
  <form id="bulk-delete-form" method="POST" action="{{ route('media.bulk-destroy') }}" class="hidden">
    @csrf @method('DELETE')
    <div id="bulk-ids"></div>
  </form>

  <script>
    const mediaBaseUrl = "{{ url('/media') }}";

    function showOverlay(id) {
      const el = document.getElementById(id);
      el.classList.remove('hidden');
      el.classList.add('flex');
      document.body.style.overflow = 'hidden';
    }
    function hideOverlay(id) {
      const el = document.getElementById(id);
      el.classList.add('hidden');
      el.classList.remove('flex');
      document.body.style.overflow = '';
    }

    // ── Single delete ─────────────────────────────────────────────
    function confirmDelete(id, name) {
      document.getElementById('delete-file-name').textContent = name;
      document.getElementById('delete-file-form').action = `${mediaBaseUrl}/${id}`;
      showOverlay('delete-file-overlay');
    }
    function closeDeleteModal() { hideOverlay('delete-file-overlay'); }

    // ── Bulk select ───────────────────────────────────────────────
    function updateBulkBar() {
      const checked = document.querySelectorAll('.file-checkbox:checked');
      const bar = document.getElementById('bulk-bar');
      document.getElementById('selected-count').textContent = checked.length;
      if (checked.length > 0) {
        bar.classList.remove('hidden');
        bar.classList.add('flex');
      } else {
        bar.classList.add('hidden');
        bar.classList.remove('flex');
      }

      // Also update their visual checkboxes opacity
      checked.forEach(cb => cb.style.opacity = '1');
    }

    function toggleAll(master) {
      document.querySelectorAll('.file-checkbox').forEach(cb => {
        cb.checked = master.checked;
        cb.style.opacity = master.checked ? '1' : '';
      });
      updateBulkBar();
    }

    function selectAll() {
      document.getElementById('toggle-all').checked = true;
      document.querySelectorAll('.file-checkbox').forEach(cb => {
        cb.checked = true;
        cb.style.opacity = '1';
      });
      updateBulkBar();
    }

    function deselectAll() {
      document.getElementById('toggle-all').checked = false;
      document.querySelectorAll('.file-checkbox').forEach(cb => {
        cb.checked = false;
        cb.style.opacity = '';
      });
      updateBulkBar();
    }

    function submitBulkDelete() {
      const checked = document.querySelectorAll('.file-checkbox:checked');
      if (checked.length === 0) return;
      document.getElementById('bulk-delete-body').textContent =
        'You are about to permanently delete ' + checked.length + ' file' + (checked.length > 1 ? 's' : '') + '. This cannot be undone.';
      showOverlay('bulk-delete-overlay');
    }

    function closeBulkDeleteModal() { hideOverlay('bulk-delete-overlay'); }

    function confirmBulkDelete() {
      const checked = document.querySelectorAll('.file-checkbox:checked');
      const form = document.getElementById('bulk-delete-form');
      const container = document.getElementById('bulk-ids');
      container.innerHTML = '';
      checked.forEach(cb => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'ids[]';
        input.value = cb.dataset.id;
        container.appendChild(input);
      });
      form.submit();
    }

    // Show checkboxes when any item is hovered (persist when any checked)
    document.querySelectorAll('.file-checkbox').forEach(cb => {
      cb.addEventListener('change', updateBulkBar);
    });

    // Backdrop click to close overlays
    document.addEventListener('DOMContentLoaded', function () {
      ['delete-file-overlay', 'bulk-delete-overlay'].forEach(function (id) {
        document.getElementById(id).addEventListener('click', function (e) {
          if (e.target === this) hideOverlay(id);
        });
      });
    });
  </script>

</x-layouts.app>
