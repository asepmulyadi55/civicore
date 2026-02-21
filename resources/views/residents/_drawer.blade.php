{{-- residents/_drawer.blade.php --}}
{{-- Slide-in drawer for Add / Edit resident --}}

{{-- Overlay --}}
<div id="drawer-overlay" onclick="closeResidentDrawer()"
  class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-40 hidden transition-opacity"></div>

{{-- Drawer panel --}}
<aside id="resident-drawer"
  class="fixed right-0 top-0 h-full w-full max-w-[460px] bg-white dark:bg-slate-900 shadow-2xl z-50 transform translate-x-full transition-transform duration-300 flex flex-col">

  {{-- Header --}}
  <div class="p-6 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
    <h3 id="drawer-title" class="text-xl font-bold text-slate-900 dark:text-white">Add New Resident</h3>
    <button onclick="closeResidentDrawer()"
      class="p-2 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-full transition-colors text-slate-400 hover:text-slate-600">
      <span class="material-icons">close</span>
    </button>
  </div>

  {{-- Body --}}
  <div class="flex-1 overflow-y-auto p-6">

    {{-- ADD FORM --}}
    <form id="form-add-resident" method="POST" action="{{ route('residents.store') }}" class="space-y-5">
      @csrf

      <div>
        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
          Full Name <span class="text-red-500">*</span>
        </label>
        <input type="text" name="fullname" value="{{ old('fullname') }}" placeholder="e.g. Ahmad Fauzi"
          class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all dark:text-slate-100" />
      </div>

      <div>
        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Phone Number</label>
        <input type="tel" name="phone" value="{{ old('phone') }}" placeholder="e.g. 081234567890"
          class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all dark:text-slate-100" />
      </div>

      <div class="h-px bg-slate-100 dark:bg-slate-800"></div>

      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
            Block <span class="text-red-500">*</span>
          </label>
          <select name="block_id"
            class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 dark:text-slate-100">
            <option value="">Select block</option>
            @foreach ($blocks as $block)
              <option value="{{ $block->id }}" {{ old('block_id') == $block->id ? 'selected' : '' }}>
                {{ $block->name }}
              </option>
            @endforeach
          </select>
        </div>
        <div>
          <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
            Unit No. <span class="text-red-500">*</span>
          </label>
          <input type="text" name="unit_number" value="{{ old('unit_number') }}" placeholder="e.g. A-101"
            class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all dark:text-slate-100" />
        </div>
      </div>

      <div class="h-px bg-slate-100 dark:bg-slate-800"></div>

      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
            Monthly Fee ({{ $currency }}) <span class="text-red-500">*</span>
          </label>
          <input type="number" name="monthly_fee" value="{{ old('monthly_fee') }}" placeholder="500000" min="0"
            step="1000"
            class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all dark:text-slate-100" />
        </div>
        <div>
          <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
            Fee Start Month <span class="text-red-500">*</span>
          </label>
          <input type="month" name="fee_start" value="{{ old('fee_start', now()->format('Y-m')) }}"
            class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all dark:text-slate-100" />
        </div>
      </div>

      <input type="hidden" name="_form" value="add" />
    </form>

    {{-- EDIT FORM --}}
    <form id="form-edit-resident" method="POST" action="" class="space-y-5 hidden">
      @csrf
      @method('PUT')

      <div>
        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
          Full Name <span class="text-red-500">*</span>
        </label>
        <input type="text" id="edit-fullname" name="fullname"
          class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 dark:text-slate-100" />
      </div>

      <div>
        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Phone Number</label>
        <input type="tel" id="edit-phone" name="phone"
          class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 dark:text-slate-100" />
      </div>

      <div class="h-px bg-slate-100 dark:bg-slate-800"></div>

      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Block <span
              class="text-red-500">*</span></label>
          <select id="edit-block_id" name="block_id"
            class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 dark:text-slate-100">
            @foreach ($blocks as $block)
              <option value="{{ $block->id }}">{{ $block->name }}</option>
            @endforeach
          </select>
        </div>
        <div>
          <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Unit No. <span
              class="text-red-500">*</span></label>
          <input type="text" id="edit-unit_number" name="unit_number"
            class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 dark:text-slate-100" />
        </div>
      </div>

      <div>
        <label class="flex items-center gap-3 cursor-pointer">
          <input type="checkbox" id="edit-is_active" name="is_active" value="1"
            class="w-4 h-4 text-primary rounded border-slate-300 focus:ring-primary/20" />
          <span class="text-sm font-semibold text-slate-700 dark:text-slate-300">Active Resident</span>
        </label>
      </div>

      <input type="hidden" name="_form" value="edit" />
    </form>
  </div>

  {{-- Footer actions --}}
  <div class="p-6 border-t border-slate-200 dark:border-slate-800 flex items-center gap-3">
    <button onclick="closeResidentDrawer()"
      class="flex-1 py-2.5 rounded-lg text-sm font-bold text-slate-600 dark:text-slate-300 bg-slate-50 dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 border border-slate-200 dark:border-slate-700 transition-colors">
      Cancel
    </button>
    <button id="drawer-submit-btn" onclick="submitDrawerForm()"
      class="flex-1 py-2.5 rounded-lg text-sm font-bold text-white bg-primary hover:bg-primary/90 transition-all shadow-lg shadow-primary/20">
      Save Resident
    </button>
  </div>
</aside>

{{-- ── Delete / Deactivate Confirm Modal ─────────────────────────────── --}}
<div id="delete-overlay" class="fixed inset-0 flex items-center justify-center z-[60] p-4 hidden">
  <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" onclick="closeDeleteModal()"></div>
  <div class="relative bg-white dark:bg-slate-900 rounded-2xl max-w-sm w-full overflow-hidden shadow-2xl">
    <div class="p-6 text-center">
      <div
        class="w-16 h-16 bg-red-50 dark:bg-red-900/20 text-red-500 rounded-full flex items-center justify-center mx-auto mb-4">
        <span class="material-icons text-3xl">person_off</span>
      </div>
      <h4 class="text-xl font-bold text-slate-900 dark:text-white mb-2">Deactivate Resident?</h4>
      <p class="text-slate-500 dark:text-slate-400 text-sm">
        <span id="delete-resident-name" class="font-semibold text-slate-700 dark:text-slate-200"></span>
        will be marked as inactive. Their payment history will be preserved.
      </p>
    </div>
    <div class="flex gap-3 p-4 bg-slate-50 dark:bg-slate-800/50">
      <button onclick="closeDeleteModal()"
        class="flex-1 px-4 py-2.5 text-sm font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg transition-colors border border-slate-200 dark:border-slate-700">
        Cancel
      </button>
      <form id="form-delete-resident" method="POST" action="" class="flex-1">
        @csrf
        @method('DELETE')
        <button type="submit"
          class="w-full px-4 py-2.5 text-sm font-bold text-white bg-red-500 hover:bg-red-600 rounded-lg transition-all shadow-lg shadow-red-500/20">
          Yes, Deactivate
        </button>
      </form>
    </div>
  </div>
</div>

<script>
  const drawer = document.getElementById('resident-drawer');
  const drawerOverlay = document.getElementById('drawer-overlay');
  const formAdd = document.getElementById('form-add-resident');
  const formEdit = document.getElementById('form-edit-resident');
  const drawerTitle = document.getElementById('drawer-title');

  function openResidentDrawer() {
    drawerTitle.textContent = 'Add New Resident';
    formAdd.classList.remove('hidden');
    formEdit.classList.add('hidden');
    drawer.classList.remove('translate-x-full');
    drawerOverlay.classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
  }

  function openEditDrawer(id, data) {
    drawerTitle.textContent = 'Edit Resident';
    formAdd.classList.add('hidden');
    formEdit.classList.remove('hidden');

    // Populate edit form
    formEdit.action = `/residents/${id}`;
    document.getElementById('edit-fullname').value = data.fullname;
    document.getElementById('edit-phone').value = data.phone || '';
    document.getElementById('edit-block_id').value = data.block_id;
    document.getElementById('edit-unit_number').value = data.unit_number;
    document.getElementById('edit-is_active').checked = data.is_active;

    drawer.classList.remove('translate-x-full');
    drawerOverlay.classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
  }

  function closeResidentDrawer() {
    drawer.classList.add('translate-x-full');
    drawerOverlay.classList.add('hidden');
    document.body.classList.remove('overflow-hidden');
  }

  function submitDrawerForm() {
    if (!formAdd.classList.contains('hidden')) {
      formAdd.submit();
    } else {
      formEdit.submit();
    }
  }

  function openDeleteModal(id, name) {
    document.getElementById('delete-resident-name').textContent = name;
    document.getElementById('form-delete-resident').action = `/residents/${id}`;
    document.getElementById('delete-overlay').classList.remove('hidden');
  }

  function closeDeleteModal() {
    document.getElementById('delete-overlay').classList.add('hidden');
  }

  // Re-open drawer with validation errors if any
  @if ($errors->any() && old('_form') === 'add')
    openResidentDrawer();
  @elseif ($errors->any() && old('_form') === 'edit')
    // We can't easily restore the edit drawer without the resident ID, so just show the error banner
  @endif
</script>