{{-- Delete single file modal, bulk delete modal, bulk delete form, and JS --}}

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

  const i18nBulkBefore = '{{ __('app.bulk_delete_about_to_delete') }}';
  const i18nBulkFile   = '{{ __('app.bulk_delete_file') }}';
  const i18nBulkFiles  = '{{ __('app.bulk_delete_files') }}';
  const i18nBulkAfter  = '{{ __('app.bulk_delete_cannot_undo') }}';

  function submitBulkDelete() {
    const checked = document.querySelectorAll('.file-checkbox:checked');
    if (checked.length === 0) return;
    document.getElementById('bulk-delete-body').textContent =
      i18nBulkBefore + ' ' + checked.length + ' ' + (checked.length > 1 ? i18nBulkFiles : i18nBulkFile) + '. ' + i18nBulkAfter;
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

  document.querySelectorAll('.file-checkbox').forEach(cb => {
    cb.addEventListener('change', updateBulkBar);
  });

  document.addEventListener('DOMContentLoaded', function () {
    ['delete-file-overlay', 'bulk-delete-overlay'].forEach(function (id) {
      document.getElementById(id).addEventListener('click', function (e) {
        if (e.target === this) hideOverlay(id);
      });
    });
  });
</script>
