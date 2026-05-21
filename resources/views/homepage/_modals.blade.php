{{-- Event Edit & Delete Modals + JS --}}

{{-- ── Event Edit Modal ──────────────────────────────────────────────────── --}}
<div id="event-edit-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4"
  style="background:rgba(0,0,0,0.5);backdrop-filter:blur(4px)">
  <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-2xl w-full max-w-lg">

    {{-- Header --}}
    <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 dark:border-slate-800">
      <div class="flex items-center gap-2">
        <span class="material-icons text-blue-500 text-[20px]">edit_calendar</span>
        <h3 class="font-bold text-slate-900 dark:text-white text-base">{{ __('app.hp_edit_event_title') }}</h3>
      </div>
      <button type="button" onclick="closeEventEditModal()"
        class="p-1.5 text-slate-400 hover:text-slate-700 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg transition-colors">
        <span class="material-icons text-[20px]">close</span>
      </button>
    </div>

    {{-- Form --}}
    <form id="event-edit-form" method="POST" action="" class="p-6 space-y-4" enctype="multipart/form-data" novalidate>
      @csrf @method('PUT')
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div class="sm:col-span-2 space-y-1.5">
          <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">
            {{ __('app.hp_col_title') }} <span class="text-rose-500">*</span>
          </label>
          <input type="text" id="edit-event-title" name="title"
            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all" oninput="clearHpErr('err-hp-edit-title')">
          <p id="err-hp-edit-title" class="hidden mt-1 text-sm text-rose-500"></p>
        </div>
        <div class="space-y-1.5">
          <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">{{ __('app.hp_col_date') }}</label>
          <input type="date" id="edit-event-date" name="date"
            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all dark:[color-scheme:dark]">
        </div>
        <div class="space-y-1.5">
          <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">{{ __('app.hp_event_cat_label') }}</label>
          <select id="edit-event-category" name="category"
            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all">
            <option value="">{{ __('app.hp_none_option') }}</option>
            <option value="wellness">{{ __('app.hp_cat_wellness') }}</option>
            <option value="meetings">{{ __('app.hp_cat_meetings') }}</option>
            <option value="education">{{ __('app.hp_cat_education') }}</option>
            <option value="cultural">{{ __('app.hp_cat_cultural') }}</option>
            <option value="sports">{{ __('app.hp_cat_sports') }}</option>
            <option value="other">{{ __('app.hp_cat_other') }}</option>
          </select>
        </div>
        <div class="sm:col-span-2 space-y-1.5">
          <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">{{ __('app.hp_desc_label') }}</label>
          <input type="text" id="edit-event-description" name="description"
            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all"
            placeholder="Short description... (optional)">
        </div>
        <div class="sm:col-span-2 space-y-1.5">
          <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">{{ __('app.hp_url_label') }} <span class="text-slate-400 font-normal text-xs">{{ __('app.hp_url_hint') }}</span></label>
          <input type="url" id="edit-event-url" name="url"
            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all"
            placeholder="https://... (optional)">
        </div>
        <div class="sm:col-span-2 space-y-1.5">
          <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">{{ __('app.hp_event_image') }}</label>
          <div id="edit-event-img-current" class="hidden items-center gap-4 p-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 mb-2">
            <img id="edit-event-img-current-thumb" src="" alt="Current" class="w-20 h-14 object-cover rounded-lg border border-slate-200 dark:border-slate-700 flex-shrink-0">
            <div class="flex-1 min-w-0">
              <p class="text-xs font-semibold text-slate-600 dark:text-slate-400">{{ __('app.hp_current_image') }}</p>
              <p class="text-xs text-slate-400 truncate" id="edit-event-img-current-url"></p>
            </div>
          </div>
          <label id="edit-event-img-label" class="flex flex-col items-center justify-center gap-2 w-full h-24 rounded-xl border-2 border-dashed border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-800/50 hover:border-primary/60 hover:bg-primary/5 transition-all cursor-pointer">
            <span class="material-icons text-slate-400 text-2xl">cloud_upload</span>
            <span class="text-xs font-semibold text-slate-500">{{ __('app.hp_upload_new_image') }} <span class="text-slate-400 font-normal">{{ __('app.hp_upload_optional_hint') }}</span></span>
            <input type="file" name="image_file" id="edit-event-img-input" accept="image/*" class="sr-only"
              onchange="previewImage(this,'edit-event-img-preview','edit-event-img-label')">
          </label>
          <div id="edit-event-img-preview" class="hidden items-center gap-3 p-3 rounded-xl border border-primary/30 bg-primary/5">
            <img src="" alt="Preview" class="w-16 h-12 object-cover rounded-lg flex-shrink-0">
            <div class="flex-1 min-w-0">
              <p class="text-xs font-semibold text-primary">{{ __('app.hp_ready_to_upload') }}</p>
              <p class="text-xs text-slate-400 truncate"></p>
            </div>
            <button type="button" onclick="clearImageInput('edit-event-img-input','edit-event-img-preview','edit-event-img-label')" class="text-slate-400 hover:text-rose-500 transition-colors">
              <span class="material-icons text-lg">close</span>
            </button>
          </div>
        </div>
      </div>
      <div class="flex justify-end gap-3 pt-2">
        <button type="button" onclick="closeEventEditModal()"
          class="px-4 py-2 text-sm font-semibold text-slate-600 dark:text-slate-400 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 rounded-xl transition-colors">
          {{ __('app.btn_cancel') }}
        </button>
        <button type="submit"
          class="inline-flex items-center gap-2 px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold rounded-xl transition-all">
          <span class="material-icons text-base">save</span>
          {{ __('app.btn_save_changes') }}
        </button>
      </div>
    </form>
  </div>
</div>

{{-- ── Event Delete Modal ────────────────────────────────────────────────── --}}
<div id="event-delete-modal"
  class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
  <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-2xl w-full max-w-sm overflow-hidden transform transition-all duration-200">
    <div class="flex flex-col items-center pt-8 pb-5 px-6 text-center">
      <div class="w-16 h-16 rounded-full bg-rose-100 dark:bg-rose-900/30 flex items-center justify-center mb-4">
        <span class="material-icons text-3xl text-rose-600">delete_outline</span>
      </div>
      <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-2">{{ __('app.hp_remove_event_title') }}</h3>
      <p id="event-delete-body" class="text-sm text-slate-500 dark:text-slate-400 leading-relaxed"></p>
    </div>
    <div class="flex gap-3 px-6 pb-6">
      <button onclick="closeEventDeleteModal()"
        class="flex-1 px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 text-sm font-semibold
          text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition-all duration-150">
        {{ __('app.btn_cancel') }}
      </button>
      <form id="event-delete-form" method="POST" action="" class="flex-1">
        @csrf @method('DELETE')
        <button type="submit"
          class="w-full px-4 py-2.5 rounded-xl text-sm font-bold text-white
            bg-rose-600 hover:bg-rose-700 active:bg-rose-800 transition-all duration-150">
          {{ __('app.hp_btn_yes_remove') }}
        </button>
      </form>
    </div>
  </div>
</div>

<script>
  function clearHpErr(id) {
    const el = document.getElementById(id); if (el) el.classList.add('hidden');
  }
  function showHpErr(id, msg) {
    const el = document.getElementById(id); if (el) { el.textContent = msg; el.classList.remove('hidden'); }
  }
  function validateHpRequired(inputId, errId, label) {
    const el = document.getElementById(inputId);
    if (!el || !el.value.trim()) { showHpErr(errId, label + ' is required.'); return false; }
    clearHpErr(errId); return true;
  }
  document.addEventListener('DOMContentLoaded', function () {
    document.getElementById('form-hp-hero').addEventListener('submit', function(e) {
      if (!validateHpRequired('hp-hero-title', 'err-hp-hero-title', 'Title')) e.preventDefault();
    });
    document.getElementById('form-hp-featured').addEventListener('submit', function(e) {
      if (!validateHpRequired('hp-featured-title', 'err-hp-featured-title', 'Event title')) e.preventDefault();
    });
    document.getElementById('form-hp-event-add').addEventListener('submit', function(e) {
      if (!validateHpRequired('hp-event-title', 'err-hp-event-title', 'Title')) e.preventDefault();
    });
    document.getElementById('form-hp-about').addEventListener('submit', function(e) {
      if (!validateHpRequired('hp-about-content', 'err-hp-about-content', 'About content')) e.preventDefault();
    });
    document.getElementById('event-edit-form').addEventListener('submit', function(e) {
      if (!validateHpRequired('edit-event-title', 'err-hp-edit-title', 'Title')) e.preventDefault();
    });
    document.getElementById('event-edit-modal').addEventListener('click', function (e) {
      if (e.target === this) closeEventEditModal();
    });
    document.getElementById('event-delete-modal').addEventListener('click', function (e) {
      if (e.target === this) closeEventDeleteModal();
    });
  });

  function previewImage(input, previewContainerId, labelId) {
    const file = input.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = function(e) {
      const container = document.getElementById(previewContainerId);
      container.querySelector('img').src = e.target.result;
      const filenameEl = container.querySelector('p.truncate');
      if (filenameEl) filenameEl.textContent = file.name;
      container.classList.remove('hidden');
      container.classList.add('flex');
      document.getElementById(labelId).classList.add('hidden');
    };
    reader.readAsDataURL(file);
  }

  function clearImageInput(inputId, previewContainerId, labelId) {
    document.getElementById(inputId).value = '';
    const container = document.getElementById(previewContainerId);
    container.classList.add('hidden');
    container.classList.remove('flex');
    document.getElementById(labelId).classList.remove('hidden');
  }

  const eventUpdateUrlTpl = '{{ route('homepage.events.update', '__id__') }}';
  const eventDeleteUrlTpl = '{{ route('homepage.events.destroy', '__id__') }}';

  function openEventEditModal(btn) {
    const eventEditModal = document.getElementById('event-edit-modal');
    const eventEditForm  = document.getElementById('event-edit-form');
    const { id, title, date, description, category, url, imageUrl } = btn.dataset;
    eventEditForm.action = eventUpdateUrlTpl.replace('__id__', id);
    document.getElementById('edit-event-title').value       = title       || '';
    document.getElementById('edit-event-date').value        = date        || '';
    document.getElementById('edit-event-description').value = description || '';
    document.getElementById('edit-event-category').value    = category    || '';
    document.getElementById('edit-event-url').value         = url         || '';
    clearImageInput('edit-event-img-input', 'edit-event-img-preview', 'edit-event-img-label');
    const currentWrap  = document.getElementById('edit-event-img-current');
    const currentThumb = document.getElementById('edit-event-img-current-thumb');
    const currentUrlEl = document.getElementById('edit-event-img-current-url');
    if (imageUrl) {
      currentThumb.src         = imageUrl;
      currentUrlEl.textContent = imageUrl;
      currentWrap.classList.remove('hidden');
      currentWrap.classList.add('flex');
    } else {
      currentWrap.classList.add('hidden');
      currentWrap.classList.remove('flex');
    }
    eventEditModal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
  }

  function closeEventEditModal() {
    document.getElementById('event-edit-modal').classList.add('hidden');
    document.body.style.overflow = '';
  }

  function openEventDeleteModal(id, title) {
    const modal = document.getElementById('event-delete-modal');
    document.getElementById('event-delete-body').textContent = '{{ __('app.hp_event_delete_body_before') }} "' + title + '" {{ __('app.hp_event_delete_body_after') }}';
    document.getElementById('event-delete-form').action = eventDeleteUrlTpl.replace('__id__', id);
    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
  }

  function closeEventDeleteModal() {
    document.getElementById('event-delete-modal').classList.add('hidden');
    document.body.style.overflow = '';
  }
</script>
