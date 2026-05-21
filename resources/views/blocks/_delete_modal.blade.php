{{-- Delete Block Confirmation Modal --}}
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
  const blocksBaseUrl = "{{ url('/blocks') }}";
  function openDeleteBlockModal(id, name) {
    document.getElementById('delete-block-name').textContent = name;
    document.getElementById('delete-block-form').action = blocksBaseUrl + '/' + id;
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
