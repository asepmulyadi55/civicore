{{-- organization/_modals.blade.php — All modals for org feature --}}

{{-- ═══════════════════════════════════════════════════════════
     1. ADD PERIOD MODAL
     ═══════════════════════════════════════════════════════════ --}}
<div id="modal-add-period"
  class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 hidden items-center justify-center p-4"
  onclick="if(event.target===this) closeOrgPeriodModal()">
  <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-2xl w-full max-w-md overflow-hidden">
    <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 dark:border-slate-800">
      <h3 class="font-bold text-slate-900 dark:text-white" id="period-modal-title">{{ __('app.org_add_period') }}</h3>
      <button onclick="closeOrgPeriodModal()" class="p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
        <span class="material-icons text-slate-400" style="font-size:18px">close</span>
      </button>
    </div>

    <form id="period-form" method="POST" action="{{ route('organization.periods.store') }}" class="p-6 space-y-4">
      @csrf
      <input type="hidden" name="_method" id="period-form-method" value="POST">

      <div>
        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">{{ __('app.org_period_name') }} *</label>
        <input type="text" name="name" id="period-name" required
          placeholder="{{ __('app.org_period_name_ph') }}"
          class="w-full px-3 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm dark:text-white focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all">
      </div>

      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">{{ __('app.org_start_year') }} *</label>
          <input type="number" name="start_year" id="period-start-year" required min="2000" max="2100"
            class="w-full px-3 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm dark:text-white focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all">
        </div>
        <div>
          <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">{{ __('app.org_end_year') }} *</label>
          <input type="number" name="end_year" id="period-end-year" required min="2000" max="2100"
            class="w-full px-3 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm dark:text-white focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all">
        </div>
      </div>

      <div class="flex gap-3 pt-2">
        <button type="button" onclick="closeOrgPeriodModal()"
          class="flex-1 px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 text-sm font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition-all">
          {{ __('app.btn_cancel') }}
        </button>
        <button type="submit"
          class="flex-1 px-4 py-2.5 rounded-xl text-sm font-bold text-white bg-primary hover:bg-primary/90 transition-all shadow-sm">
          {{ __('app.btn_save') }}
        </button>
      </div>
    </form>
  </div>
</div>


{{-- ═══════════════════════════════════════════════════════════
     2. ADD / EDIT POSITION MODAL
     ═══════════════════════════════════════════════════════════ --}}
<div id="modal-position"
  class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 hidden items-center justify-center p-4"
  onclick="if(event.target===this) closeOrgPositionModal()">
  <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden max-h-[90vh] flex flex-col">
    <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 dark:border-slate-800 flex-shrink-0">
      <h3 class="font-bold text-slate-900 dark:text-white" id="position-modal-title">{{ __('app.org_add_position') }}</h3>
      <button onclick="closeOrgPositionModal()" class="p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
        <span class="material-icons text-slate-400" style="font-size:18px">close</span>
      </button>
    </div>

    <div class="overflow-y-auto flex-1">
      <form id="position-form" method="POST" action="" class="p-6 space-y-5">
        @csrf
        <input type="hidden" name="_method" id="position-form-method" value="POST">

        {{-- Position name --}}
        <div>
          <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">{{ __('app.org_position_title') }} *</label>
          <input type="text" name="position_name" id="pos-position-name" required
            placeholder="{{ __('app.org_position_title_ph') }}"
            class="w-full px-3 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm dark:text-white focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all">
        </div>

        {{-- Parent position --}}
        <div>
          <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">{{ __('app.org_parent_position') }}</label>
          <div class="relative">
            <select name="parent_id" id="pos-parent-id"
              class="appearance-none w-full pl-3 pr-8 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm text-slate-700 dark:text-slate-300 focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all">
              <option value="">{{ __('app.org_no_parent') }}</option>
              @foreach($positions as $pos)
                <option value="{{ $pos->id }}" data-name="{{ $pos->position_name }}">{{ $pos->position_name }}</option>
              @endforeach
            </select>
            <span class="material-icons absolute right-2.5 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400 text-[18px]">expand_more</span>
          </div>
        </div>

        {{-- Sort order --}}
        <div>
          <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">{{ __('app.org_sort_order') }}</label>
          <input type="number" name="sort_order" id="pos-sort-order" min="0" value="0"
            class="w-28 px-3 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm dark:text-white focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all">
          <p class="text-xs text-slate-400 mt-1">{{ __('app.org_sort_order_hint') }}</p>
        </div>

        {{-- Person assignment --}}
        <div>
          <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">{{ __('app.org_assigned_person') }}</label>

          {{-- Hidden inputs for selected person --}}
          <input type="hidden" name="resident_id" id="pos-resident-id" value="">
          <input type="hidden" name="family_member_id" id="pos-family-member-id" value="">

          {{-- Currently assigned person chip --}}
          <div id="pos-person-chip" class="hidden mb-2">
            <div class="flex items-center gap-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg px-3 py-2">
              <div id="pos-chip-photo-wrap" class="w-8 h-8 rounded-full overflow-hidden flex-shrink-0 bg-primary/10 flex items-center justify-center text-xs font-bold text-primary">
                <span id="pos-chip-initials"></span>
                <img id="pos-chip-photo" src="" alt="" class="hidden w-8 h-8 rounded-full object-cover">
              </div>
              <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-slate-800 dark:text-white truncate" id="pos-chip-name"></p>
                <p class="text-xs text-slate-500 truncate" id="pos-chip-location"></p>
              </div>
              <button type="button" onclick="clearOrgPerson()"
                class="w-6 h-6 rounded-full flex items-center justify-center hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors flex-shrink-0"
                title="{{ __('app.org_clear_person') }}">
                <span class="material-icons text-slate-400" style="font-size:14px">close</span>
              </button>
            </div>
          </div>

          {{-- Search input --}}
          <div class="relative" id="pos-search-wrap">
            <span class="material-icons absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">search</span>
            <input type="text" id="pos-person-search"
              placeholder="{{ __('app.org_search_member_ph') }}"
              autocomplete="off"
              class="w-full pl-9 pr-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm dark:text-white focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all">

            {{-- Dropdown results --}}
            <div id="pos-search-results"
              class="hidden absolute z-20 top-full left-0 right-0 mt-1 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl shadow-lg overflow-hidden max-h-60 overflow-y-auto">
              <div id="pos-search-loading" class="hidden flex items-center gap-2 px-4 py-3 text-sm text-slate-400">
                <span class="material-icons animate-spin" style="font-size:16px">refresh</span>
                Searching…
              </div>
              <div id="pos-search-empty" class="hidden px-4 py-3 text-sm text-slate-400 text-center">No results found.</div>
              <ul id="pos-search-list" class="divide-y divide-slate-100 dark:divide-slate-800"></ul>
            </div>
          </div>
        </div>

        <div class="flex gap-3 pt-2">
          <button type="button" onclick="closeOrgPositionModal()"
            class="flex-1 px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 text-sm font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition-all">
            {{ __('app.btn_cancel') }}
          </button>
          <button type="submit"
            class="flex-1 px-4 py-2.5 rounded-xl text-sm font-bold text-white bg-primary hover:bg-primary/90 transition-all shadow-sm">
            {{ __('app.btn_save') }}
          </button>
        </div>
      </form>
    </div>
  </div>
</div>


{{-- ═══════════════════════════════════════════════════════════
     3. DELETE CONFIRM MODAL (period & position)
     ═══════════════════════════════════════════════════════════ --}}
<div id="modal-org-delete"
  class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 hidden items-center justify-center p-4"
  onclick="if(event.target===this) closeOrgDeleteModal()">
  <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-2xl w-full max-w-sm overflow-hidden">
    <div class="flex flex-col items-center pt-8 pb-5 px-6 text-center">
      <div class="w-14 h-14 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center mb-4">
        <span class="material-icons text-red-600 text-2xl">delete_forever</span>
      </div>
      <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-2" id="org-delete-title"></h3>
      <p class="text-sm text-slate-500 dark:text-slate-400" id="org-delete-body"></p>
    </div>
    <div class="flex gap-3 px-6 pb-6">
      <button onclick="closeOrgDeleteModal()"
        class="flex-1 px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 text-sm font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition-all">
        {{ __('app.btn_cancel') }}
      </button>
      <form id="org-delete-form" method="POST" action="" class="flex-1">
        @csrf @method('DELETE')
        <button type="submit"
          class="w-full px-4 py-2.5 rounded-xl text-sm font-bold text-white bg-red-600 hover:bg-red-700 transition-all">
          {{ __('app.btn_yes_delete') }}
        </button>
      </form>
    </div>
  </div>
</div>


{{-- ═══════════════════════════════════════════════════════════
     JavaScript
     ═══════════════════════════════════════════════════════════ --}}
<script>
  var _orgSearchUrl    = '{{ route('organization.search-members') }}';
  var _orgPeriodId     = '{{ $selectedPeriod?->id ?? '' }}';
  var _orgPositionsUrl = '{{ $selectedPeriod ? route('organization.positions.store', $selectedPeriod) : '' }}';
  var _orgPositionBaseUrl = '{{ url('/organization/positions') }}';
  var _orgPeriodBaseUrl   = '{{ url('/organization/periods') }}';

  // ── Period modal ────────────────────────────────────────────
  function openOrgPeriodModal() {
    document.getElementById('period-modal-title').textContent = '{{ __('app.org_add_period') }}';
    document.getElementById('period-form').action = '{{ route('organization.periods.store') }}';
    document.getElementById('period-form-method').value = 'POST';
    document.getElementById('period-name').value = '';
    document.getElementById('period-start-year').value = '';
    document.getElementById('period-end-year').value = '';
    showModal('modal-add-period');
  }

  function openEditPeriodModal(data) {
    document.getElementById('period-modal-title').textContent = '{{ __('app.org_edit_period') }}';
    document.getElementById('period-form').action = _orgPeriodBaseUrl + '/' + data.id;
    document.getElementById('period-form-method').value = 'PUT';
    document.getElementById('period-name').value = data.name;
    document.getElementById('period-start-year').value = data.start_year;
    document.getElementById('period-end-year').value = data.end_year;
    showModal('modal-add-period');
  }

  function closeOrgPeriodModal() {
    hideModal('modal-add-period');
  }

  // ── Position modal ───────────────────────────────────────────
  function openOrgPositionModal() {
    if (!_orgPeriodId) {
      alert('{{ __('app.org_no_periods') }}');
      return;
    }
    document.getElementById('position-modal-title').textContent = '{{ __('app.org_add_position') }}';
    document.getElementById('position-form').action = _orgPositionsUrl;
    document.getElementById('position-form-method').value = 'POST';
    document.getElementById('pos-position-name').value = '';
    document.getElementById('pos-parent-id').value = '';
    document.getElementById('pos-sort-order').value = '0';
    clearOrgPerson();
    _currentEditPositionId = null;
    showModal('modal-position');
    setTimeout(() => document.getElementById('pos-position-name').focus(), 100);
  }

  var _currentEditPositionId = null;

  function openEditPositionModal(data) {
    _currentEditPositionId = data.id;
    document.getElementById('position-modal-title').textContent = '{{ __('app.org_edit_position') }}';
    document.getElementById('position-form').action = _orgPositionBaseUrl + '/' + data.id;
    document.getElementById('position-form-method').value = 'PUT';
    document.getElementById('pos-position-name').value = data.position_name;
    document.getElementById('pos-parent-id').value = data.parent_id || '';
    document.getElementById('pos-sort-order').value = data.sort_order || 0;

    // Remove the self from parent dropdown (can't be own parent)
    document.querySelectorAll('#pos-parent-id option').forEach(opt => {
      opt.disabled = (opt.value === data.id);
    });

    // Restore assigned person chip
    if (data.resident_id || data.family_member_id) {
      document.getElementById('pos-resident-id').value = data.resident_id || '';
      document.getElementById('pos-family-member-id').value = data.family_member_id || '';
      showPersonChip(data.person_name, data.person_location, null, null);
    } else {
      clearOrgPerson();
    }

    showModal('modal-position');
  }

  function closeOrgPositionModal() {
    hideModal('modal-position');
  }

  // ── Delete modal ─────────────────────────────────────────────
  function confirmDeletePeriod(id, name) {
    document.getElementById('org-delete-title').textContent = '{{ __('app.org_delete_period') }}';
    document.getElementById('org-delete-body').textContent =
      '{{ __('app.org_confirm_delete_period', ['name' => ':n']) }}'.replace(':n', name);
    document.getElementById('org-delete-form').action = _orgPeriodBaseUrl + '/' + id;
    showModal('modal-org-delete');
  }

  function confirmDeletePosition(id, name) {
    document.getElementById('org-delete-title').textContent = '{{ __('app.org_delete_position') }}';
    document.getElementById('org-delete-body').textContent =
      '{{ __('app.org_confirm_delete_position', ['name' => ':n']) }}'.replace(':n', name);
    document.getElementById('org-delete-form').action = _orgPositionBaseUrl + '/' + id;
    showModal('modal-org-delete');
  }

  function closeOrgDeleteModal() {
    hideModal('modal-org-delete');
  }

  // ── Modal helpers ─────────────────────────────────────────────
  function showModal(id) {
    var el = document.getElementById(id);
    el.classList.remove('hidden');
    el.classList.add('flex');
    document.body.style.overflow = 'hidden';
  }

  function hideModal(id) {
    var el = document.getElementById(id);
    el.classList.add('hidden');
    el.classList.remove('flex');
    document.body.style.overflow = '';
  }

  // ── Person search autocomplete ────────────────────────────────
  var _searchTimer = null;

  document.getElementById('pos-person-search').addEventListener('input', function() {
    clearTimeout(_searchTimer);
    var q = this.value.trim();
    if (q.length < 2) {
      document.getElementById('pos-search-results').classList.add('hidden');
      return;
    }
    _searchTimer = setTimeout(function() { runOrgSearch(q); }, 300);
  });

  document.getElementById('pos-person-search').addEventListener('blur', function() {
    setTimeout(function() {
      document.getElementById('pos-search-results').classList.add('hidden');
    }, 200);
  });

  function runOrgSearch(q) {
    var results = document.getElementById('pos-search-results');
    var loading = document.getElementById('pos-search-loading');
    var empty   = document.getElementById('pos-search-empty');
    var list    = document.getElementById('pos-search-list');

    results.classList.remove('hidden');
    loading.classList.remove('hidden');
    empty.classList.add('hidden');
    list.innerHTML = '';

    var url = _orgSearchUrl + '?q=' + encodeURIComponent(q) + '&period_id=' + encodeURIComponent(_orgPeriodId);
    if (_currentEditPositionId) url += '&exclude_position_id=' + encodeURIComponent(_currentEditPositionId);

    fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } })
      .then(function(r) { return r.json(); })
      .then(function(data) {
        loading.classList.add('hidden');
        if (!data || data.length === 0) {
          empty.classList.remove('hidden');
          return;
        }
        data.forEach(function(person) {
          var li = document.createElement('li');
          li.className = 'flex items-center gap-3 px-4 py-2.5 hover:bg-slate-50 dark:hover:bg-slate-800 cursor-pointer transition-colors';
          var typeBadge = person.type === 'resident'
            ? '<span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-primary/10 text-primary dark:bg-secondary/10 dark:text-secondary">{{ __('app.org_search_resident') }}</span>'
            : '<span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-violet-100 text-violet-700 dark:bg-violet-900/30 dark:text-violet-400">{{ __('app.org_search_family_member') }}</span>';

          var photoHtml = person.photo
            ? '<img src="' + person.photo + '" alt="" class="w-8 h-8 rounded-full object-cover flex-shrink-0">'
            : '<div class="w-8 h-8 rounded-full bg-primary/10 text-primary flex items-center justify-center text-xs font-bold flex-shrink-0">'
              + person.name.split(' ').map(function(w){ return w[0]||''; }).slice(0,2).join('').toUpperCase()
              + '</div>';

          li.innerHTML = photoHtml
            + '<div class="flex-1 min-w-0">'
            + '<div class="flex items-center gap-1.5 flex-wrap"><span class="font-semibold text-sm text-slate-800 dark:text-white truncate">' + person.name + '</span>' + typeBadge + '</div>'
            + '<span class="text-xs text-slate-400 truncate block">' + (person.location || '') + '</span>'
            + '</div>';

          li.addEventListener('click', function() {
            selectOrgPerson(person);
          });
          list.appendChild(li);
        });
      })
      .catch(function() {
        loading.classList.add('hidden');
        empty.classList.remove('hidden');
      });
  }

  function selectOrgPerson(person) {
    // Set hidden inputs
    document.getElementById('pos-resident-id').value       = person.type === 'resident'      ? person.id : '';
    document.getElementById('pos-family-member-id').value  = person.type === 'family_member' ? person.id : '';

    showPersonChip(person.name, person.location, person.photo, person.name);

    // Hide search
    document.getElementById('pos-search-results').classList.add('hidden');
    document.getElementById('pos-person-search').value = '';
  }

  function showPersonChip(name, location, photoUrl, initials) {
    var chip = document.getElementById('pos-person-chip');
    chip.classList.remove('hidden');
    document.getElementById('pos-chip-name').textContent = name || '{{ __('app.org_no_assigned') }}';
    document.getElementById('pos-chip-location').textContent = location || '';

    var initEl  = document.getElementById('pos-chip-initials');
    var photoEl = document.getElementById('pos-chip-photo');
    if (photoUrl) {
      photoEl.src = photoUrl;
      photoEl.classList.remove('hidden');
      initEl.classList.add('hidden');
    } else {
      photoEl.classList.add('hidden');
      initEl.classList.remove('hidden');
      initEl.textContent = (initials || name || '?').split(' ').map(function(w){ return (w[0]||'').toUpperCase(); }).slice(0,2).join('');
    }
  }

  function clearOrgPerson() {
    document.getElementById('pos-resident-id').value = '';
    document.getElementById('pos-family-member-id').value = '';
    document.getElementById('pos-person-chip').classList.add('hidden');
    document.getElementById('pos-person-search').value = '';
    document.getElementById('pos-search-results').classList.add('hidden');
  }

  // ── Expand/collapse section card body ───────────────────────
  function toggleOrgSection(sectionId) {
    var body    = document.getElementById(sectionId + '-body');
    var chevron = document.getElementById(sectionId + '-chevron');
    if (!body) return;
    var isHidden = body.style.display === 'none';
    body.style.display = isHidden ? '' : 'none';
    if (chevron) chevron.style.transform = isHidden ? '' : 'rotate(-90deg)';
  }
</script>
