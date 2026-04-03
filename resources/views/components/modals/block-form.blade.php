{{-- ============================================================
components/modals/block-form.blade.php
Add Block Modal + Edit Block Modal
Trigger: openAddBlockModal() / openEditBlockModal(id, name, desc, isActive)
============================================================ --}}
@props(['blocksCount' => 0])

{{-- ════════════════════════════════════════════════════════════════ --}}
{{-- ADD BLOCK MODAL --}}
{{-- ════════════════════════════════════════════════════════════════ --}}
<div id="add-block-modal"
  class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm hidden items-center justify-center p-4"
  onclick="if(event.target===this) closeAddBlockModal()">

  <div class="bg-white dark:bg-slate-900 w-full max-w-md rounded-2xl shadow-2xl flex flex-col overflow-hidden">

    {{-- Header --}}
    <div class="px-8 py-6 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center">
      <div>
        <h2 class="text-2xl font-extrabold text-slate-900 dark:text-slate-100">{{ __('app.add_new_block') }}</h2>
        <p class="text-sm text-slate-400 mt-0.5">{{ __('app.add_block_desc') }}</p>
      </div>
      <button onclick="closeAddBlockModal()"
        class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors p-1">
        <span class="material-icons">close</span>
      </button>
    </div>

    {{-- Body --}}
    <div class="px-8 py-6">
      <form id="form-add-block" method="POST" action="{{ route('blocks.store') }}" class="space-y-5" novalidate>
        @csrf

        {{-- Block Name --}}
        <div class="flex flex-col gap-2">
          <label class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">
            {{ __('app.block_name') }} <span class="text-red-500">*</span>
          </label>
          <div class="relative">
            <span
              class="material-icons absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-lg">apartment</span>
            <input id="add-block-name" type="text" name="name" value="{{ old('name') }}"
              placeholder="{{ __('app.eg_block_name') }}"
              class="w-full pl-10 pr-4 py-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none dark:text-white @error('name') border-red-500 @enderror"
              oninput="clearBErr('js-bm-name')" />
          </div>
          @error('name')
            <p class="text-xs text-red-500 flex items-center gap-1">
              <span class="material-icons text-xs">error_outline</span> {{ $message }}
            </p>
          @enderror
          <p id="js-bm-name" class="hidden text-xs text-red-500 items-center gap-1">
            <span class="material-icons text-xs">error_outline</span> {{ __('app.err_block_name') }}
          </p>
        </div>

        {{-- Description --}}
        <div class="flex flex-col gap-2">
          <label class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">
            {{ __('app.description') }} <span
              class="font-normal text-slate-400 normal-case">{{ __('app.description_optional') }}</span>
          </label>
          <textarea name="description" rows="3" placeholder="{{ __('app.eg_block_desc') }}"
            class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none dark:text-white resize-none">{{ old('description') }}</textarea>
        </div>

        {{-- Footer --}}
        <div class="flex gap-3 pt-1">
          <button type="button" onclick="closeAddBlockModal()"
            class="flex-1 py-3 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-bold text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
            {{ __('app.btn_cancel') }}
          </button>
          <button type="submit"
            class="flex-1 py-3 bg-primary text-white rounded-xl text-sm font-bold hover:bg-primary/90 shadow-lg shadow-primary/20 transition-all flex items-center justify-center gap-2 active:scale-95">
            <span class="material-icons text-sm">add_home</span>
            {{ __('app.btn_save_block') }}
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

{{-- ════════════════════════════════════════════════════════════════ --}}
{{-- EDIT BLOCK MODAL --}}
{{-- ════════════════════════════════════════════════════════════════ --}}
<div id="edit-block-modal"
  class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm hidden items-center justify-center p-4"
  onclick="if(event.target===this) closeEditBlockModal()">

  <div class="bg-white dark:bg-slate-900 w-full max-w-md rounded-2xl shadow-2xl flex flex-col overflow-hidden">

    {{-- Header --}}
    <div class="px-8 py-6 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center">
      <div>
        <h2 class="text-2xl font-extrabold text-slate-900 dark:text-slate-100">{{ __('app.edit_block') }}</h2>
        <span id="ebm-name-badge"
          class="px-2 py-0.5 bg-primary/10 text-primary rounded-lg text-xs font-bold mt-1 inline-block"></span>
      </div>
      <button onclick="closeEditBlockModal()"
        class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors p-1">
        <span class="material-icons">close</span>
      </button>
    </div>

    {{-- Body --}}
    <div class="px-8 py-6">
      <form id="form-edit-block" method="POST" action="" class="space-y-5" novalidate>
        @csrf
        @method('PUT')

        {{-- Block Name --}}
        <div class="flex flex-col gap-2">
          <label class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">
            {{ __('app.block_name') }} <span class="text-red-500">*</span>
          </label>
          <div class="relative">
            <span
              class="material-icons absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-lg">apartment</span>
            <input type="text" id="edit-block-name" name="name"
              class="w-full pl-10 pr-4 py-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none dark:text-white"
              oninput="clearBErr('js-ebm-name')" />
          </div>
          <p id="js-ebm-name" class="hidden text-xs text-red-500 items-center gap-1">
            <span class="material-icons text-xs">error_outline</span> {{ __('app.err_block_name') }}
          </p>
        </div>

        {{-- Description --}}
        <div class="flex flex-col gap-2">
          <label class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">
            {{ __('app.description') }} <span
              class="font-normal text-slate-400 normal-case">{{ __('app.description_optional') }}</span>
          </label>
          <textarea id="edit-block-description" name="description" rows="3"
            class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none dark:text-white resize-none"></textarea>
        </div>

        {{-- Active status --}}
        <label
          class="flex items-center gap-3 cursor-pointer p-3 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
          <input type="checkbox" id="edit-block-active" name="is_active" value="1"
            class="w-4 h-4 text-primary rounded border-slate-300 focus:ring-primary/20" />
          <div>
            <span class="text-sm font-semibold text-slate-700 dark:text-slate-300">{{ __('app.active_block') }}</span>
            <p class="text-xs text-slate-400">{{ __('app.inactive_block_hint') }}</p>
          </div>
        </label>

        {{-- Footer --}}
        <div class="flex gap-3 pt-1">
          <button type="button" onclick="closeEditBlockModal()"
            class="flex-1 py-3 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-bold text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
            {{ __('app.btn_cancel') }}
          </button>
          <button type="submit"
            class="flex-1 py-3 bg-primary text-white rounded-xl text-sm font-bold hover:bg-primary/90 shadow-lg shadow-primary/20 transition-all flex items-center justify-center gap-2 active:scale-95">
            <span class="material-icons text-sm">save</span>
            {{ __('app.btn_save_changes') }}
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
  // ── Add Block Modal ───────────────────────────────────────────────
  function openAddBlockModal() {
    // Keep backward-compat alias for existing trigger buttons
    const el = document.getElementById('add-block-modal');
    el.classList.remove('hidden'); el.classList.add('flex');
    document.body.classList.add('overflow-hidden');
  }
  function closeAddBlockModal() {
    const el = document.getElementById('add-block-modal');
    el.classList.add('hidden'); el.classList.remove('flex');
    document.body.classList.remove('overflow-hidden');
  }
  // Old trigger name backward-compat
  function openBlockDrawer() { openAddBlockModal(); }

  // ── Edit Block Modal ──────────────────────────────────────────────
  function openEditBlockDrawer(id, name, description, isActive) {
    document.getElementById('edit-block-name').value = name;
    document.getElementById('edit-block-description').value = description;
    document.getElementById('edit-block-active').checked = isActive;
    document.getElementById('ebm-name-badge').textContent = name;
    document.getElementById('form-edit-block').action = `{{ url('/blocks') }}/${id}`;
    const el = document.getElementById('edit-block-modal');
    el.classList.remove('hidden'); el.classList.add('flex');
    document.body.classList.add('overflow-hidden');
  }
  function closeEditBlockModal() {
    const el = document.getElementById('edit-block-modal');
    el.classList.add('hidden'); el.classList.remove('flex');
    document.body.classList.remove('overflow-hidden');
  }

  document.addEventListener('keydown', e => {
    if (e.key === 'Escape') { closeAddBlockModal(); closeEditBlockModal(); }
  });

  // Re-open add modal on validation error
  @if($errors->any() && !old('_edit'))
    document.addEventListener('DOMContentLoaded', () => openAddBlockModal());
  @endif

    // ── Block modal: client-side validation ─────────────────────────────
    function clearBErr(id) {
      const el = document.getElementById(id);
      if (el) { el.classList.add('hidden'); el.classList.remove('flex'); }
    }
  function showBErr(id) {
    const el = document.getElementById(id);
    if (el) { el.classList.remove('hidden'); el.classList.add('flex'); }
  }
  document.getElementById('form-add-block')?.addEventListener('submit', function (e) {
    const name = document.getElementById('add-block-name');
    if (!name || name.value.trim() === '') { showBErr('js-bm-name'); e.preventDefault(); }
    else clearBErr('js-bm-name');
  });
  document.getElementById('form-edit-block')?.addEventListener('submit', function (e) {
    const name = document.getElementById('edit-block-name');
    if (!name || name.value.trim() === '') { showBErr('js-ebm-name'); e.preventDefault(); }
    else clearBErr('js-ebm-name');
  });
</script>