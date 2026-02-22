{{-- Blocks Management Page --}}
<x-layouts.app title="Blocks"
  class="font-display bg-background-light dark:bg-background-dark text-slate-800 dark:text-slate-200 antialiased min-h-screen">

  <x-nav.sidebar active="blocks" />

  <div class="lg:pl-64 min-h-screen bg-background-light dark:bg-background-dark flex flex-col">

    {{-- Header --}}
    <header
      class="h-16 bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between px-6 lg:px-8">
      <div class="flex items-center gap-4">
        <button class="lg:hidden p-2 rounded-lg border border-slate-200 dark:border-slate-800"
          onclick="toggleSidebar()">
          <span class="material-icons text-slate-500">menu</span>
        </button>
        <h1 class="text-xl font-bold text-slate-900 dark:text-white">Block Management</h1>
        <span
          class="hidden sm:inline px-2 py-1 text-xs font-semibold bg-primary/10 text-primary rounded-lg uppercase">{{ $blocks->count() }}
          Blocks</span>
      </div>
      <div class="flex items-center gap-3">
        <button onclick="openBlockDrawer()"
          class="flex items-center gap-2 bg-primary hover:bg-primary/90 text-white px-4 py-2 rounded-lg font-semibold transition-all shadow-sm shadow-primary/20 text-sm">
          <span class="material-icons text-sm">add</span>
          <span class="hidden sm:inline">Add Block</span>
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
          <h2 class="text-lg font-bold text-slate-700 dark:text-slate-300">No Blocks Yet</h2>
          <p class="text-slate-500 mt-2 text-sm">Add the first block to get started.</p>
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
                  <p class="text-[10px] text-slate-500 font-medium uppercase mt-0.5">Active</p>
                </div>
                <div class="bg-slate-50 dark:bg-slate-800 rounded-lg p-3 text-center">
                  <p class="text-xl font-bold text-slate-900 dark:text-white">{{ $block->residents_count }}</p>
                  <p class="text-[10px] text-slate-500 font-medium uppercase mt-0.5">Total</p>
                </div>
              </div>

              {{-- Status + Actions --}}
              <div class="flex items-center justify-between pt-2 border-t border-slate-100 dark:border-slate-800">
                @if($block->is_active)
                  <span
                    class="px-2 py-0.5 bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400 text-[10px] font-bold uppercase rounded">Active</span>
                @else
                  <span
                    class="px-2 py-0.5 bg-slate-200 text-slate-500 text-[10px] font-bold uppercase rounded">Inactive</span>
                @endif
                <div class="flex gap-1">
                  <button
                    onclick="openEditBlockDrawer({{ $block->id }}, '{{ addslashes($block->name) }}', '{{ addslashes($block->description ?? '') }}', {{ $block->is_active ? 'true' : 'false' }})"
                    class="p-2 text-slate-400 hover:text-primary hover:bg-primary/10 rounded-lg transition-colors"
                    title="Edit">
                    <span class="material-icons text-sm">edit</span>
                  </button>
                  <button type="button" onclick="openDeleteBlockModal({{ $block->id }}, '{{ addslashes($block->name) }}')"
                    class="p-2 text-slate-400 hover:text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-950/20 rounded-lg transition-colors"
                    title="Delete">
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

  {{-- ── Add/Edit Drawer ─────────────────────────────────────────────── --}}
  <div id="block-drawer-overlay" onclick="closeBlockDrawer()"
    class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-40 hidden"></div>

  <aside id="block-drawer"
    class="fixed right-0 top-0 h-full w-full max-w-md bg-white dark:bg-slate-900 shadow-2xl z-50 transform translate-x-full transition-transform duration-300 flex flex-col">
    <div class="p-6 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
      <h3 id="block-drawer-title" class="text-xl font-bold">Add New Block</h3>
      <button onclick="closeBlockDrawer()"
        class="p-2 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-full transition-colors">
        <span class="material-icons text-slate-400">close</span>
      </button>
    </div>

    <div class="flex-1 overflow-y-auto p-6">

      {{-- ADD FORM --}}
      <form id="form-add-block" method="POST" action="{{ route('blocks.store') }}" class="space-y-5" novalidate>
        @csrf

        <div>
          <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Block Name <span
              class="text-rose-500">*</span></label>
          <input type="text" name="name" value="{{ old('name') }}" required
            class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:ring-2 focus:ring-primary/50 focus:border-primary @error('name') border-red-500 dark:border-red-500 @enderror"
            placeholder="e.g. Block A, Tower B" />
          @error('name')
            <p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
          @enderror
        </div>
        <div>
          <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Description <span
              class="text-slate-400 font-normal">(optional)</span></label>
          <textarea name="description" rows="3"
            class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:ring-2 focus:ring-primary/50 focus:border-primary resize-none"
            placeholder="e.g. Terrace units on the east wing">{{ old('description') }}</textarea>
        </div>
        <button type="submit"
          class="w-full py-2.5 rounded-lg text-sm font-bold text-white bg-primary hover:bg-primary/90 transition-all shadow-lg shadow-primary/20">
          Save Block
        </button>
      </form>

      {{-- EDIT FORM --}}
      <form id="form-edit-block" method="POST" action="" class="space-y-5 hidden" novalidate>
        @csrf @method('PUT')
        <div>
          <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Block Name <span
              class="text-rose-500">*</span></label>
          <input id="edit-block-name" type="text" name="name" required
            class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:ring-2 focus:ring-primary/50 focus:border-primary @error('name') border-red-500 dark:border-red-500 @enderror" />
          @error('name')
            <p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
          @enderror
        </div>
        <div>
          <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Description</label>
          <textarea id="edit-block-description" name="description" rows="3"
            class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:ring-2 focus:ring-primary/50 focus:border-primary resize-none"></textarea>
        </div>
        <div class="flex items-center gap-3">
          <input id="edit-block-active" type="checkbox" name="is_active" value="1" class="w-4 h-4 text-primary">
          <label for="edit-block-active" class="text-sm font-medium text-slate-700 dark:text-slate-300">Active</label>
        </div>
        <button type="submit"
          class="w-full py-2.5 rounded-lg text-sm font-bold text-white bg-primary hover:bg-primary/90 transition-all shadow-lg shadow-primary/20">
          Update Block
        </button>
      </form>
    </div>
  </aside>

  <script>
    function openBlockDrawer() {
      document.getElementById('block-drawer-title').textContent = 'Add New Block';
      document.getElementById('form-add-block').classList.remove('hidden');
      document.getElementById('form-edit-block').classList.add('hidden');
      document.getElementById('block-drawer').classList.remove('translate-x-full');
      document.getElementById('block-drawer-overlay').classList.remove('hidden');
    }

    function openEditBlockDrawer(id, name, description, isActive) {
      document.getElementById('block-drawer-title').textContent = 'Edit Block';
      document.getElementById('form-add-block').classList.add('hidden');
      document.getElementById('form-edit-block').classList.remove('hidden');
      document.getElementById('form-edit-block').action = '/blocks/' + id;
      document.getElementById('edit-block-name').value = name;
      document.getElementById('edit-block-description').value = description;
      document.getElementById('edit-block-active').checked = isActive;
      document.getElementById('block-drawer').classList.remove('translate-x-full');
      document.getElementById('block-drawer-overlay').classList.remove('hidden');
    }

    function closeBlockDrawer() {
      document.getElementById('block-drawer').classList.add('translate-x-full');
      document.getElementById('block-drawer-overlay').classList.add('hidden');
    }

    // Auto-open drawer on validation error (add form)
    @if($errors->any() && !old('_edit'))
      window.addEventListener('DOMContentLoaded', () => openBlockDrawer());
    @endif

    // Delete modal
    let deleteBlockId = null;
    function openDeleteBlockModal(id, name) {
      deleteBlockId = id;
      document.getElementById('delete-block-name').textContent = name;
      document.getElementById('delete-block-form').action = '/blocks/' + id;
      document.getElementById('modal-delete-block').classList.remove('hidden');
    }
    function closeDeleteBlockModal() {
      document.getElementById('modal-delete-block').classList.add('hidden');
    }
  </script>

  {{-- Delete Confirmation Modal --}}
  <div id="modal-delete-block"
    class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-900 rounded-xl shadow-2xl w-full max-w-sm p-6">
      <div class="flex items-center gap-3 mb-4">
        <div
          class="w-10 h-10 rounded-full bg-rose-100 dark:bg-rose-900/30 flex items-center justify-center flex-shrink-0">
          <span class="material-icons text-rose-600 dark:text-rose-400">delete_outline</span>
        </div>
        <h3 class="text-lg font-bold text-slate-900 dark:text-white">Delete Block</h3>
      </div>
      <p class="text-sm text-slate-600 dark:text-slate-400 mb-6">
        Are you sure you want to delete <span class="font-semibold text-slate-900 dark:text-white"
          id="delete-block-name"></span>?
        This action cannot be undone.
      </p>
      <div class="flex gap-3">
        <button type="button" onclick="closeDeleteBlockModal()"
          class="flex-1 px-4 py-2 rounded-lg border border-slate-200 dark:border-slate-700 text-sm font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition-all">
          Cancel
        </button>
        <form id="delete-block-form" method="POST" action="" class="flex-1">
          @csrf @method('DELETE')
          <button type="submit"
            class="w-full px-4 py-2 rounded-lg bg-rose-600 hover:bg-rose-700 text-sm font-bold text-white transition-all">
            Delete
          </button>
        </form>
      </div>
    </div>
  </div>

</x-layouts.app>