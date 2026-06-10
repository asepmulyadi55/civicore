{{-- meetings/_modals.blade.php --}}

{{-- ═══════════════════════════════════════════════════════════════════════════
     ADD MEETING MODAL
══════════════════════════════════════════════════════════════════════════════ --}}
<div id="modal-add-meeting"
  class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm hidden items-center justify-center p-4"
  onclick="if(event.target===this) closeModal('modal-add-meeting')">
  <div class="bg-white dark:bg-slate-900 w-full max-w-lg rounded-2xl shadow-2xl flex flex-col max-h-[90vh] overflow-hidden">

    {{-- Header --}}
    <div class="px-5 sm:px-8 py-5 sm:py-6 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center">
      <div>
        <h2 class="text-2xl font-extrabold text-slate-900 dark:text-slate-100">{{ __('app.meeting_add') }}</h2>
        <p class="text-sm text-slate-400 mt-0.5">{{ __('app.meeting_add_desc') }}</p>
      </div>
      <button type="button" onclick="closeModal('modal-add-meeting')"
        class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors p-1">
        <span class="material-icons">close</span>
      </button>
    </div>

    {{-- Body --}}
    <div class="flex-1 overflow-y-auto px-5 sm:px-8 py-5 sm:py-6">
      <form id="form-add-meeting" method="POST" action="{{ route('meetings.store') }}" class="space-y-5" novalidate>
        @csrf

        {{-- Topic --}}
        <div class="flex flex-col gap-2">
          <label class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">
            {{ __('app.meeting_topic') }} <span class="text-red-500">*</span>
          </label>
          <div class="relative">
            <span class="material-icons absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-lg">title</span>
            <input id="add-topic" type="text" name="topic" maxlength="200"
              placeholder="{{ __('app.meeting_topic_ph') }}"
              oninput="clearMErr('js-am-topic')"
              class="w-full pl-10 pr-4 py-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none dark:text-white dark:placeholder-slate-500" />
          </div>
          <p id="js-am-topic" class="hidden text-xs text-red-500 items-center gap-1">
            <span class="material-icons text-xs">error_outline</span> {{ __('app.meeting_err_topic') }}
          </p>
        </div>

        {{-- Date + Time --}}
        <div class="grid grid-cols-2 gap-4">
          <div class="flex flex-col gap-2">
            <label class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">
              {{ __('app.meeting_date_label') }} <span class="text-red-500">*</span>
            </label>
            <div class="relative">
              <span class="material-icons absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-lg">calendar_today</span>
              <input id="add-date" type="date" name="meeting_date"
                oninput="clearMErr('js-am-date')"
                class="w-full pl-10 pr-4 py-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none dark:text-white" />
            </div>
            <p id="js-am-date" class="hidden text-xs text-red-500 items-center gap-1">
              <span class="material-icons text-xs">error_outline</span> {{ __('app.meeting_err_date') }}
            </p>
          </div>
          <div class="flex flex-col gap-2">
            <label class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">
              {{ __('app.meeting_time_label') }} <span class="text-red-500">*</span>
            </label>
            <div class="relative">
              <span class="material-icons absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-lg">schedule</span>
              <input id="add-time" type="time" name="meeting_time"
                oninput="clearMErr('js-am-time')"
                class="w-full pl-10 pr-4 py-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none dark:text-white" />
            </div>
            <p id="js-am-time" class="hidden text-xs text-red-500 items-center gap-1">
              <span class="material-icons text-xs">error_outline</span> {{ __('app.meeting_err_time') }}
            </p>
          </div>
        </div>

        {{-- Location --}}
        <div class="flex flex-col gap-2">
          <label class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">
            {{ __('app.meeting_location') }}
            <span class="font-normal text-slate-400 normal-case">({{ __('app.optional') }})</span>
          </label>
          <div class="relative">
            <span class="material-icons absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-lg">place</span>
            <input id="add-location" type="text" name="location" maxlength="150"
              placeholder="{{ __('app.meeting_location_ph') }}"
              class="w-full pl-10 pr-4 py-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none dark:text-white dark:placeholder-slate-500" />
          </div>
        </div>

        {{-- Notes --}}
        <div class="flex flex-col gap-2">
          <label class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">
            {{ __('app.meeting_notes_label') }}
            <span class="font-normal text-slate-400 normal-case">({{ __('app.optional') }})</span>
          </label>
          <textarea id="add-notes" name="notes" rows="4"
            placeholder="{{ __('app.meeting_notes_ph') }}"
            class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none dark:text-white dark:placeholder-slate-500 resize-none"></textarea>
        </div>

        {{-- Footer --}}
        <div class="flex gap-3 pt-1">
          <button type="button" onclick="closeModal('modal-add-meeting')"
            class="flex-1 py-3 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-bold text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
            {{ __('app.btn_cancel') }}
          </button>
          <button type="submit"
            class="flex-1 py-3 bg-primary text-white rounded-xl text-sm font-bold hover:bg-primary/90 shadow-lg shadow-primary/20 transition-all flex items-center justify-center gap-2 active:scale-95">
            <span class="material-icons text-sm">event_note</span>
            {{ __('app.meeting_save') }}
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

{{-- ═══════════════════════════════════════════════════════════════════════════
     EDIT MEETING MODAL
══════════════════════════════════════════════════════════════════════════════ --}}
<div id="modal-edit-meeting"
  class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm hidden items-center justify-center p-4"
  onclick="if(event.target===this) closeModal('modal-edit-meeting')">
  <div class="bg-white dark:bg-slate-900 w-full max-w-lg rounded-2xl shadow-2xl flex flex-col max-h-[90vh] overflow-hidden">

    {{-- Header --}}
    <div class="px-5 sm:px-8 py-5 sm:py-6 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center">
      <div>
        <h2 class="text-2xl font-extrabold text-slate-900 dark:text-slate-100">{{ __('app.meeting_edit') }}</h2>
        <span id="edit-meeting-badge" class="px-2 py-0.5 bg-primary/10 text-primary rounded-lg text-xs font-bold mt-1 inline-block truncate max-w-xs"></span>
      </div>
      <button type="button" onclick="closeModal('modal-edit-meeting')"
        class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors p-1">
        <span class="material-icons">close</span>
      </button>
    </div>

    {{-- Body --}}
    <div class="flex-1 overflow-y-auto px-5 sm:px-8 py-5 sm:py-6">
      <form id="form-edit-meeting" method="POST" action="" class="space-y-5" novalidate>
        @csrf
        @method('PUT')

        <div class="flex flex-col gap-2">
          <label class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">
            {{ __('app.meeting_topic') }} <span class="text-red-500">*</span>
          </label>
          <div class="relative">
            <span class="material-icons absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-lg">title</span>
            <input id="edit-topic" type="text" name="topic" maxlength="200"
              oninput="clearMErr('js-em-topic')"
              class="w-full pl-10 pr-4 py-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none dark:text-white" />
          </div>
          <p id="js-em-topic" class="hidden text-xs text-red-500 items-center gap-1">
            <span class="material-icons text-xs">error_outline</span> {{ __('app.meeting_err_topic') }}
          </p>
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div class="flex flex-col gap-2">
            <label class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">
              {{ __('app.meeting_date_label') }} <span class="text-red-500">*</span>
            </label>
            <div class="relative">
              <span class="material-icons absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-lg">calendar_today</span>
              <input id="edit-date" type="date" name="meeting_date"
                oninput="clearMErr('js-em-date')"
                class="w-full pl-10 pr-4 py-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none dark:text-white" />
            </div>
            <p id="js-em-date" class="hidden text-xs text-red-500 items-center gap-1">
              <span class="material-icons text-xs">error_outline</span> {{ __('app.meeting_err_date') }}
            </p>
          </div>
          <div class="flex flex-col gap-2">
            <label class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">
              {{ __('app.meeting_time_label') }} <span class="text-red-500">*</span>
            </label>
            <div class="relative">
              <span class="material-icons absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-lg">schedule</span>
              <input id="edit-time" type="time" name="meeting_time"
                oninput="clearMErr('js-em-time')"
                class="w-full pl-10 pr-4 py-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none dark:text-white" />
            </div>
            <p id="js-em-time" class="hidden text-xs text-red-500 items-center gap-1">
              <span class="material-icons text-xs">error_outline</span> {{ __('app.meeting_err_time') }}
            </p>
          </div>
        </div>

        <div class="flex flex-col gap-2">
          <label class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">
            {{ __('app.meeting_location') }}
            <span class="font-normal text-slate-400 normal-case">({{ __('app.optional') }})</span>
          </label>
          <div class="relative">
            <span class="material-icons absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-lg">place</span>
            <input id="edit-location" type="text" name="location" maxlength="150"
              placeholder="{{ __('app.meeting_location_ph') }}"
              class="w-full pl-10 pr-4 py-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none dark:text-white dark:placeholder-slate-500" />
          </div>
        </div>

        <div class="flex flex-col gap-2">
          <label class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">
            {{ __('app.meeting_notes_label') }}
            <span class="font-normal text-slate-400 normal-case">({{ __('app.optional') }})</span>
          </label>
          <textarea id="edit-notes" name="notes" rows="4"
            placeholder="{{ __('app.meeting_notes_ph') }}"
            class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none dark:text-white dark:placeholder-slate-500 resize-none"></textarea>
        </div>

        <div class="flex gap-3 pt-1">
          <button type="button" onclick="closeModal('modal-edit-meeting')"
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

{{-- ═══════════════════════════════════════════════════════════════════════════
     DELETE MEETING MODAL
══════════════════════════════════════════════════════════════════════════════ --}}
<div id="modal-delete-meeting"
  class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm hidden items-center justify-center p-4"
  onclick="if(event.target===this) closeModal('modal-delete-meeting')">
  <div class="bg-white dark:bg-slate-900 w-full max-w-sm rounded-2xl shadow-2xl overflow-hidden
    transform transition-all duration-200 scale-95 opacity-0" id="delete-meeting-card">
    <div class="flex flex-col items-center pt-8 pb-5 px-6 text-center">
      <div class="w-16 h-16 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center mb-4">
        <span class="material-icons text-red-500 text-3xl">delete_forever</span>
      </div>
      <h2 class="text-xl font-bold text-slate-900 dark:text-white mb-2">{{ __('app.meeting_delete_title') }}</h2>
      <p class="text-sm text-slate-500 dark:text-slate-400 leading-relaxed" id="delete-meeting-body"></p>
    </div>
    <div class="flex gap-3 px-6 pb-6">
      <button type="button" onclick="closeModal('modal-delete-meeting')"
        class="flex-1 px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 text-sm font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition-all">
        {{ __('app.btn_cancel') }}
      </button>
      <form id="delete-meeting-form" method="POST" action="" class="flex-1">
        @csrf @method('DELETE')
        <button type="submit"
          class="w-full px-4 py-2.5 rounded-xl text-sm font-bold text-white bg-red-600 hover:bg-red-700 transition-all">
          {{ __('app.btn_yes_delete') }}
        </button>
      </form>
    </div>
  </div>
</div>

{{-- ═══════════════════════════════════════════════════════════════════════════
     ATTENDANCE MODAL
══════════════════════════════════════════════════════════════════════════════ --}}
<div id="modal-attendance"
  class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm hidden items-center justify-center p-4"
  onclick="if(event.target===this) closeModal('modal-attendance')">
  <div class="bg-white dark:bg-slate-900 w-full max-w-2xl rounded-2xl shadow-2xl flex flex-col max-h-[90vh]">

    {{-- Header --}}
    <div class="px-5 sm:px-8 py-5 sm:py-6 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center flex-shrink-0">
      <div class="min-w-0">
        <h2 class="text-2xl font-extrabold text-slate-900 dark:text-slate-100">{{ __('app.meeting_attendance_title') }}</h2>
        <p id="modal-attendance-subtitle" class="text-sm text-slate-400 mt-0.5 truncate max-w-xs"></p>
      </div>
      <button type="button" onclick="closeModal('modal-attendance')"
        class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors p-1 flex-shrink-0">
        <span class="material-icons">close</span>
      </button>
    </div>

    {{-- Search + quick actions --}}
    <div class="px-5 sm:px-8 pt-4 pb-3 flex-shrink-0 space-y-3">
      <div class="relative">
        <span class="material-icons absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm z-10">search</span>
        <input type="text" id="attendance-search"
          placeholder="{{ __('app.meeting_attendance_search_ph') }}"
          oninput="filterAttendanceRows(this.value)"
          onblur="hideDropdownDelayed()"
          autocomplete="off"
          class="w-full pl-9 pr-4 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all dark:text-slate-100 dark:placeholder-slate-500 outline-none" />
        {{-- Search results dropdown --}}
        <div id="attendance-dropdown"
          class="hidden absolute top-full left-0 right-0 z-50 mt-1 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl shadow-xl max-h-52 overflow-y-auto divide-y divide-slate-100 dark:divide-slate-800">
        </div>
      </div>
      <div class="flex items-center gap-3">
        <span class="text-xs text-slate-500 dark:text-slate-400 font-semibold">{{ __('app.meeting_mark_all') }}:</span>
        <button type="button" onclick="clearAllHadir()"
          class="text-xs px-3 py-1 rounded-full bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400 font-semibold hover:opacity-80 transition">
          {{ __('app.meeting_all_absent') }}
        </button>
      </div>
    </div>

    {{-- Resident list --}}
    <form id="attendance-form" method="POST" action="" class="flex-1 overflow-y-auto px-5 sm:px-8 pb-2">
      @csrf
      <div id="attendance-list" class="divide-y divide-slate-100 dark:divide-slate-800"></div>
      <div id="attendance-loading" class="flex items-center justify-center py-12 text-slate-400 gap-2">
        <span class="material-icons animate-spin text-xl">refresh</span>
        {{ __('app.meeting_attendance_loading') }}
      </div>
      <div id="attendance-empty" class="hidden py-8 text-center text-slate-400 dark:text-slate-500 text-sm">
        {{ __('app.meeting_attendance_no_residents') }}
      </div>
    </form>

    {{-- Footer --}}
    <div class="px-5 sm:px-8 py-4 border-t border-slate-100 dark:border-slate-800 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 flex-shrink-0">
      <span id="attendance-count-label" class="text-sm text-slate-500 dark:text-slate-400 font-medium self-start sm:self-center"></span>
      <div class="flex gap-3 w-full sm:w-auto">
        <button type="button" onclick="closeModal('modal-attendance')"
          class="flex-1 sm:flex-none px-4 py-2.5 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-bold text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
          {{ __('app.btn_cancel') }}
        </button>
        <button type="button" onclick="submitAttendance()"
          class="flex-1 sm:flex-none px-5 py-2.5 bg-primary text-white rounded-xl text-sm font-bold hover:bg-primary/90 shadow-lg shadow-primary/20 transition-all flex items-center justify-center gap-2 active:scale-95">
          <span class="material-icons text-sm">how_to_reg</span>
          <span class="hidden sm:inline">{{ __('app.meeting_save_attendance') }}</span>
          <span class="sm:hidden">{{ __('app.meeting_save') }}</span>
        </button>
      </div>
    </div>
  </div>
</div>

{{-- ═══════════════════════════════════════════════════════════════════════════
     JavaScript
══════════════════════════════════════════════════════════════════════════════ --}}
<script>
// ── Modal helpers ─────────────────────────────────────────────────────────────
function openModal(id) {
  const el = document.getElementById(id);
  el.classList.remove('hidden');
  el.classList.add('flex');
  document.body.classList.add('overflow-hidden');
}

function closeModal(id) {
  // Animate delete card out before hiding
  if (id === 'modal-delete-meeting') {
    const card = document.getElementById('delete-meeting-card');
    card.classList.remove('scale-100', 'opacity-100');
    card.classList.add('scale-95', 'opacity-0');
    setTimeout(() => {
      document.getElementById(id).classList.add('hidden');
      document.getElementById(id).classList.remove('flex');
      document.body.classList.remove('overflow-hidden');
    }, 150);
    return;
  }
  document.getElementById(id).classList.add('hidden');
  document.getElementById(id).classList.remove('flex');
  document.body.classList.remove('overflow-hidden');
}

document.addEventListener('keydown', e => {
  if (e.key === 'Escape') {
    ['modal-add-meeting','modal-edit-meeting','modal-delete-meeting','modal-attendance']
      .forEach(id => {
        const el = document.getElementById(id);
        if (el && !el.classList.contains('hidden')) closeModal(id);
      });
  }
});

// ── Custom validation helpers ─────────────────────────────────────────────────
function clearMErr(id) {
  const el = document.getElementById(id);
  if (el) { el.classList.add('hidden'); el.classList.remove('flex'); }
}
function showMErr(id) {
  const el = document.getElementById(id);
  if (el) { el.classList.remove('hidden'); el.classList.add('flex'); }
}

// Validate ADD form — error IDs: js-am-topic / js-am-date / js-am-time
document.getElementById('form-add-meeting')?.addEventListener('submit', function (e) {
  let valid = true;
  const topic = document.getElementById('add-topic');
  const date  = document.getElementById('add-date');
  const time  = document.getElementById('add-time');

  if (!topic || topic.value.trim() === '') { showMErr('js-am-topic'); valid = false; }
  else clearMErr('js-am-topic');

  if (!date || date.value.trim() === '') { showMErr('js-am-date'); valid = false; }
  else clearMErr('js-am-date');

  if (!time || time.value.trim() === '') { showMErr('js-am-time'); valid = false; }
  else clearMErr('js-am-time');

  if (!valid) {
    e.preventDefault();
    // Scroll first error into view
    const firstErr = document.querySelector('#form-add-meeting .text-red-500:not(.hidden)');
    if (firstErr) firstErr.scrollIntoView({ behavior: 'smooth', block: 'center' });
  }
});

// Validate EDIT form — error IDs: js-em-topic / js-em-date / js-em-time
document.getElementById('form-edit-meeting')?.addEventListener('submit', function (e) {
  let valid = true;
  const topic = document.getElementById('edit-topic');
  const date  = document.getElementById('edit-date');
  const time  = document.getElementById('edit-time');

  if (!topic || topic.value.trim() === '') { showMErr('js-em-topic'); valid = false; }
  else clearMErr('js-em-topic');

  if (!date || date.value.trim() === '') { showMErr('js-em-date'); valid = false; }
  else clearMErr('js-em-date');

  if (!time || time.value.trim() === '') { showMErr('js-em-time'); valid = false; }
  else clearMErr('js-em-time');

  if (!valid) {
    e.preventDefault();
    const firstErr = document.querySelector('#form-edit-meeting .text-red-500:not(.hidden)');
    if (firstErr) firstErr.scrollIntoView({ behavior: 'smooth', block: 'center' });
  }
});

// ── Edit modal ────────────────────────────────────────────────────────────────
function openEditModal(data) {
  document.getElementById('edit-topic').value    = data.topic    || '';
  document.getElementById('edit-date').value     = data.meeting_date || '';
  document.getElementById('edit-time').value     = data.meeting_time || '';
  document.getElementById('edit-location').value = data.location || '';
  document.getElementById('edit-notes').value    = data.notes    || '';
  document.getElementById('edit-meeting-badge').textContent = data.topic || '';
  document.getElementById('form-edit-meeting').action = '{{ url("/meetings") }}/' + data.id;
  // Clear any previous errors
  ['js-em-topic','js-em-date','js-em-time'].forEach(clearMErr);
  openModal('modal-edit-meeting');
}

// ── Delete modal ──────────────────────────────────────────────────────────────
function openDeleteModal(id, topic) {
  document.getElementById('delete-meeting-body').innerHTML =
    '<strong class="text-slate-800 dark:text-slate-200">' + escHtml(topic) + '</strong> {{ __("app.meeting_delete_body_suffix") }}';
  document.getElementById('delete-meeting-form').action = '{{ url("/meetings") }}/' + id;

  const modal = document.getElementById('modal-delete-meeting');
  const card  = document.getElementById('delete-meeting-card');
  modal.classList.remove('hidden');
  modal.classList.add('flex');
  document.body.classList.add('overflow-hidden');
  requestAnimationFrame(() => {
    card.classList.remove('scale-95', 'opacity-0');
    card.classList.add('scale-100', 'opacity-100');
  });
}

// ── Attendance modal ───────────────────────────────────────────────────────────
// attendanceState: { residentId: { present: bool, name: string, location: string } }
let currentMeetingId = null;
let attendanceState  = {};
let searchDebounce   = null;

async function openAttendanceModal(meeting) {
  currentMeetingId = meeting.id;
  attendanceState  = {};

  document.getElementById('modal-attendance-subtitle').textContent = meeting.topic;
  document.getElementById('attendance-search').value = '';
  document.getElementById('attendance-count-label').textContent = '';
  document.getElementById('attendance-form').action = '{{ url("/meetings") }}/' + meeting.id + '/attendance';
  document.getElementById('attendance-list').innerHTML = '';
  document.getElementById('attendance-loading').classList.add('hidden');
  document.getElementById('attendance-empty').classList.add('hidden');
  hideDropdown();

  openModal('modal-attendance');

  // Load existing attendance data + resolve names from all-residents list
  try {
    const [existingRes, residentsRes] = await Promise.all([
      fetch('{{ url("/meetings") }}/' + meeting.id + '/attendance-data',
        { headers: { 'X-Requested-With': 'XMLHttpRequest' } }),
      fetch('{{ url("/meetings/search-residents") }}?q=all&_all=1',
        { headers: { 'X-Requested-With': 'XMLHttpRequest' } }),
    ]);

    const existing  = existingRes.ok  ? await existingRes.json()  : {};
    const residents = residentsRes.ok ? await residentsRes.json() : [];

    // Build resident map for name/location lookup
    const resMap = {};
    residents.forEach(r => { resMap[r.id] = r; });

    // Only load Hadir records into state
    for (const [id, data] of Object.entries(existing)) {
      if (data.present && resMap[id]) {
        attendanceState[id] = { present: true, name: resMap[id].name, location: resMap[id].location || '' };
      }
    }
  } catch (e) {}

  renderHadirList();
  updateCountLabel();
}

// ── Hadir list ─────────────────────────────────────────────────────────────────
function renderHadirList() {
  const listEl = document.getElementById('attendance-list');
  const hadir  = Object.entries(attendanceState).filter(([, s]) => s.present);

  if (!hadir.length) {
    listEl.innerHTML =
      '<p class="py-10 text-center text-slate-400 dark:text-slate-500 text-sm">' +
      '<span class="material-icons text-3xl block mb-3 opacity-40">how_to_reg</span>' +
      'Belum ada yang hadir. Cari nama warga di atas.' +
      '</p>';
    return;
  }

  listEl.innerHTML = '';
  hadir.forEach(([id, state]) => {
    const row = document.createElement('div');
    row.className = 'attendance-row flex items-center gap-3 py-3';
    row.dataset.rid = id;
    row.innerHTML = `
      <span class="material-icons text-emerald-500 flex-shrink-0">check_circle</span>
      <div class="flex-1 min-w-0">
        <p class="text-sm font-semibold text-slate-800 dark:text-white leading-tight">${escHtml(state.name)}</p>
        ${state.location ? `<p class="text-xs text-slate-400 dark:text-slate-500">${escHtml(state.location)}</p>` : ''}
      </div>
      <span class="hidden sm:inline-block text-[10px] sm:text-xs px-2 py-0.5 rounded-full font-semibold bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400">
        {{ __('app.meeting_present') }}
      </span>
      <button type="button" onclick="removeFromHadir('${id}')"
        class="p-1 text-slate-300 hover:text-red-400 dark:text-slate-600 dark:hover:text-red-400 transition rounded-lg flex-shrink-0"
        title="Hapus">
        <span class="material-icons text-base">close</span>
      </button>
    `;
    listEl.appendChild(row);
  });
}

function addToHadir(resident) {
  attendanceState[resident.id] = { present: true, name: resident.name, location: resident.location || '' };
  renderHadirList();
  updateCountLabel();
  hideDropdown();
  document.getElementById('attendance-search').value = '';
}

function removeFromHadir(residentId) {
  if (attendanceState[residentId]) attendanceState[residentId].present = false;
  renderHadirList();
  updateCountLabel();
}

function clearAllHadir() {
  Object.keys(attendanceState).forEach(id => { attendanceState[id].present = false; });
  renderHadirList();
  updateCountLabel();
}

// ── Search dropdown ─────────────────────────────────────────────────────────────
function filterAttendanceRows(q) {
  clearTimeout(searchDebounce);
  if (q.trim().length < 2) { hideDropdown(); return; }
  searchDebounce = setTimeout(() => doSearch(q.trim()), 250);
}

async function doSearch(q) {
  const dropdown = document.getElementById('attendance-dropdown');
  dropdown.innerHTML = '<p class="px-4 py-3 text-xs text-slate-400">Mencari...</p>';
  dropdown.classList.remove('hidden');
  try {
    const res = await fetch(
      '{{ url("/meetings/search-residents") }}?q=' + encodeURIComponent(q),
      { headers: { 'X-Requested-With': 'XMLHttpRequest' } }
    );
    showDropdown(res.ok ? await res.json() : []);
  } catch (e) { hideDropdown(); }
}

function showDropdown(residents) {
  const dropdown = document.getElementById('attendance-dropdown');
  if (!residents.length) {
    dropdown.innerHTML = '<p class="px-4 py-3 text-xs text-slate-400 text-center">{{ __("app.meeting_attendance_no_residents") }}</p>';
    dropdown.classList.remove('hidden');
    return;
  }

  dropdown.innerHTML = '';
  residents.forEach(r => {
    const alreadyHadir = attendanceState[r.id]?.present;
    const item = document.createElement('button');
    item.type = 'button';
    item.className = 'w-full flex items-center gap-3 px-4 py-2.5 text-left transition ' +
      (alreadyHadir
        ? 'opacity-60 cursor-default'
        : 'hover:bg-slate-50 dark:hover:bg-slate-800 cursor-pointer');
    item.innerHTML = `
      <span class="material-icons text-base flex-shrink-0 ${alreadyHadir ? 'text-emerald-400' : 'text-slate-300 dark:text-slate-600'}">
        ${alreadyHadir ? 'check_circle' : 'add_circle_outline'}
      </span>
      <div class="flex-1 min-w-0">
        <p class="text-sm font-semibold text-slate-800 dark:text-white leading-tight">${escHtml(r.name)}</p>
        ${r.location ? `<p class="text-xs text-slate-400">${escHtml(r.location)}</p>` : ''}
      </div>
      ${alreadyHadir ? '<span class="text-xs text-emerald-500 font-semibold flex-shrink-0">Hadir</span>' : ''}
    `;
    if (!alreadyHadir) item.onclick = () => addToHadir(r);
    dropdown.appendChild(item);
  });
  dropdown.classList.remove('hidden');
}

function hideDropdown() {
  const d = document.getElementById('attendance-dropdown');
  if (d) d.classList.add('hidden');
}

function hideDropdownDelayed() {
  setTimeout(hideDropdown, 200);
}

// ── Count + submit ──────────────────────────────────────────────────────────────
function updateCountLabel() {
  const n = Object.values(attendanceState).filter(s => s.present).length;
  document.getElementById('attendance-count-label').textContent =
    n + ' {{ __("app.meeting_present_label") }}';
}

function submitAttendance() {
  const form = document.getElementById('attendance-form');
  form.querySelectorAll('.state-hidden').forEach(el => el.remove());

  // Inject hidden inputs for all Hadir residents from state
  for (const [resId, data] of Object.entries(attendanceState)) {
    if (!data.present) continue;
    const inp = document.createElement('input');
    inp.type      = 'hidden';
    inp.name      = `attendees[${resId}][present]`;
    inp.value     = '1';
    inp.className = 'state-hidden';
    form.appendChild(inp);
  }

  form.submit();
}



// ── Utilities ─────────────────────────────────────────────────────────────────
function escHtml(str) {
  return String(str)
    .replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
function escAttr(str) { return String(str).replace(/"/g, '&quot;'); }
</script>
