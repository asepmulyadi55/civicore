{{-- Blocks Management Page --}}
<x-layouts.app title="Blocks"
  class="font-display bg-background-light dark:bg-background-dark text-slate-800 dark:text-slate-200 antialiased min-h-screen">

  <x-nav.sidebar active="blocks" />

  <main class="lg:ml-64 p-4 lg:p-8 space-y-8">

    {{-- Header --}}
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight">Block Management</h1>
        <p class="text-slate-500 text-sm mt-1">{{ $blocks->count() }} blocks configured in the system.</p>
      </div>
      <button onclick="openBlockDrawer()"
        class="flex items-center gap-2 px-5 py-2.5 bg-primary text-white rounded-lg text-sm font-bold hover:bg-primary/90 shadow-lg shadow-primary/20 transition-all">
        <span class="material-icons text-lg">add</span>
        Add Block
      </button>
    </div>

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
                <span class="px-2 py-0.5 bg-slate-200 text-slate-500 text-[10px] font-bold uppercase rounded">Inactive</span>
              @endif
              <div class="flex gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                <button
                  onclick="openEditBlockDrawer({{ $block->id }}, '{{ addslashes($block->name) }}', '{{ addslashes($block->description ?? '') }}', {{ $block->is_active ? 'true' : 'false' }})"
                  class="p-2 text-slate-400 hover:text-primary hover:bg-primary/10 rounded-lg transition-colors"
                  title="Edit">
                  <span class="material-icons text-sm">edit</span>
                </button>
                <form method="POST" action="{{ route('blocks.destroy', $block) }}"
                  onsubmit="return confirm('Deactivate {{ addslashes($block->name) }}? This cannot be undone if residents are linked.')">
                  @csrf @method('DELETE')
                  <button type="submit"
                    class="p-2 text-slate-400 hover:text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-950/20 rounded-lg transition-colors"
                    title="Deactivate">
                    <span class="material-icons text-sm">block</span>
                  </button>
                </form>
              </div>
            </div>
          </div>
        @endforeach
      </div>
    @endif
  </main>

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
      <form id="form-add-block" method="POST" action="{{ route('blocks.store') }}" class="space-y-5">
        @csrf
        <div>
          <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Block Name <span
              class="text-rose-500">*</span></label>
          <input type="text" name="name" value="{{ old('name') }}" required
            class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:ring-2 focus:ring-primary/50 focus:border-primary"
            placeholder="e.g. Block A, Tower B" />
          @error('name')<p class="text-rose-500 text-xs mt-1">{{ $message }}</p>@enderror
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
      <form id="form-edit-block" method="POST" action="" class="space-y-5 hidden">
        @csrf @method('PUT')
        <div>
          <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Block Name <span
              class="text-rose-500">*</span></label>
          <input id="edit-block-name" type="text" name="name" required
            class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:ring-2 focus:ring-primary/50 focus:border-primary" />
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
  </script>

</x-layouts.app>