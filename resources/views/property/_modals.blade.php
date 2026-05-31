{{-- Property Listings Modals (Create / Edit + Delete) --}}

{{-- ── Create / Edit Modal ──────────────────────────────────────────────────── --}}
<div id="property-modal" class="fixed inset-0 z-50 hidden">
  <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="closePropertyModal()"></div>
  <div class="relative z-10 flex items-start justify-center min-h-screen p-4 pt-10">
    <div class="w-full max-w-2xl bg-white dark:bg-slate-900 rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-800 max-h-[90vh] flex flex-col">

      {{-- Modal header --}}
      <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex-shrink-0">
        <h2 id="modal-title" class="text-lg font-bold text-slate-900 dark:text-white">{{ __('app.property_add') }}</h2>
        <button onclick="closePropertyModal()" class="p-1.5 text-slate-400 hover:text-slate-600 dark:hover:text-white rounded-lg transition-colors">
          <span class="material-icons">close</span>
        </button>
      </div>

      {{-- Scrollable form body --}}
      <div class="overflow-y-auto flex-1">
        <form id="property-form" method="POST" action="{{ route('property.store') }}" enctype="multipart/form-data" class="p-6 space-y-6" novalidate>
          @csrf
          <input type="hidden" name="_method" id="form-method" value="POST">

          {{-- Title + Type --}}
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="md:col-span-2 space-y-1.5">
              <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">
                {{ __('app.property_field_title') }} <span class="text-rose-500">*</span>
              </label>
              <input type="text" name="title" id="prop-title" required
                class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all"
                placeholder="{{ __('app.property_field_title') }}...">
            </div>
            <div class="space-y-1.5">
              <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">
                {{ __('app.property_field_type') }} <span class="text-rose-500">*</span>
              </label>
              <select name="type" id="prop-type" required
                class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all">
                <option value="sell">{{ __('app.property_type_sell') }}</option>
                <option value="rent">{{ __('app.property_type_rent') }}</option>
              </select>
            </div>
          </div>

          {{-- Price + Status --}}
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="space-y-1.5">
              <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">
                {{ __('app.property_field_price') }}
              </label>
              <div class="relative">
                <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-sm text-slate-400 font-medium">{{ \App\Models\Setting::get('currency_symbol', 'Rp') }}</span>
                <input type="number" name="price" id="prop-price" min="0" step="1"
                  class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all"
                  placeholder="0">
              </div>
              <p id="prop-price-hint" class="text-xs text-slate-400">{{ __('app.property_field_price_hint_sell') }}</p>
            </div>
            <div class="space-y-1.5">
              <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">
                {{ __('app.property_field_status') }} <span class="text-rose-500">*</span>
              </label>
              <select name="status" id="prop-status"
                class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all">
                <option value="available">{{ __('app.property_status_available') }}</option>
                <option value="sold">{{ __('app.property_status_sold') }}</option>
                <option value="rented">{{ __('app.property_status_rented') }}</option>
              </select>
            </div>
          </div>

          {{-- Block + Unit --}}
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="space-y-1.5">
              <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">{{ __('app.block') }}</label>
              <select name="block_id" id="prop-block-id" onchange="loadPropUnits(this.value)"
                class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all">
                <option value="">{{ __('app.select_block') }}</option>
                @foreach($blocks as $block)
                  <option value="{{ $block->id }}">{{ $block->name }}</option>
                @endforeach
              </select>
            </div>
            <div class="space-y-1.5">
              <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">{{ __('app.unit') }}</label>
              <select name="unit_id" id="prop-unit-id" onchange="updatePropLocation()"
                class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all">
                <option value="">{{ __('app.select_block') }}</option>
              </select>
            </div>
          </div>

          {{-- Location label --}}
          <div class="space-y-1.5">
            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">
              {{ __('app.property_field_location') }}
            </label>
            <input type="text" name="location_label" id="prop-location"
              class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all"
              placeholder="{{ __('app.property_field_location_hint') }}">
            <p class="text-xs text-slate-400">{{ __('app.property_field_location_hint') }}</p>
          </div>

          {{-- Bedrooms · Bathrooms · Land · Building --}}
          <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="space-y-1.5">
              <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">{{ __('app.property_field_bedrooms') }}</label>
              <div class="relative">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 material-icons text-slate-400 text-[16px]">bed</span>
                <input type="number" name="bedrooms" id="prop-bedrooms" min="0" max="99"
                  class="w-full pl-8 pr-3 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all"
                  placeholder="—">
              </div>
            </div>
            <div class="space-y-1.5">
              <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">{{ __('app.property_field_bathrooms') }}</label>
              <div class="relative">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 material-icons text-slate-400 text-[16px]">bathroom</span>
                <input type="number" name="bathrooms" id="prop-bathrooms" min="0" max="99"
                  class="w-full pl-8 pr-3 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all"
                  placeholder="—">
              </div>
            </div>
            <div class="space-y-1.5">
              <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">{{ __('app.property_field_land_area') }}</label>
              <input type="number" name="land_area" id="prop-land-area" min="0" step="0.01"
                class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all"
                placeholder="—">
            </div>
            <div class="space-y-1.5">
              <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">{{ __('app.property_field_building_area') }}</label>
              <input type="number" name="building_area" id="prop-building-area" min="0" step="0.01"
                class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all"
                placeholder="—">
            </div>
          </div>

          {{-- Description --}}
          <div class="space-y-1.5">
            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">{{ __('app.property_field_description') }}</label>
            <textarea name="description" id="prop-description" rows="3"
              class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all resize-none"
              placeholder="{{ __('app.property_field_description') }}..."></textarea>
          </div>

          {{-- Contact --}}
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="space-y-1.5">
              <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">{{ __('app.property_field_contact_name') }}</label>
              <input type="text" name="contact_name" id="prop-contact-name"
                class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all"
                placeholder="...">
            </div>
            <div class="space-y-1.5">
              <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">{{ __('app.property_field_contact_phone') }}</label>
              <input type="text" name="contact_phone" id="prop-contact-phone"
                class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all"
                placeholder="08xx...">
            </div>
          </div>

          {{-- Photos --}}
          <div class="space-y-3">
            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">
              {{ __('app.property_field_images') }}
            </label>
            <p class="text-xs text-slate-400">{{ __('app.property_field_images_hint') }}</p>

            {{-- Existing images (edit mode) --}}
            <div id="existing-images-grid" class="hidden grid grid-cols-3 sm:grid-cols-4 gap-3"></div>

            {{-- Upload trigger --}}
            <label for="prop-images"
              class="flex items-center justify-center gap-3 px-4 py-6 rounded-xl border-2 border-dashed border-slate-300 dark:border-slate-600 hover:border-primary dark:hover:border-primary cursor-pointer transition-colors bg-slate-50/50 dark:bg-slate-800/50">
              <span class="material-icons text-slate-400 text-2xl">add_photo_alternate</span>
              <span class="text-sm text-slate-500">{{ __('app.property_field_images') }}</span>
            </label>
            <input type="file" name="images[]" id="prop-images" accept="image/*" multiple class="hidden" onchange="previewNewImages(event)">

            {{-- New image previews --}}
            <div id="new-images-preview" class="grid grid-cols-3 sm:grid-cols-4 gap-3 mt-2"></div>
          </div>

          {{-- Active toggle --}}
          <div class="flex items-center justify-between py-3 border-t border-slate-100 dark:border-slate-800">
            <div>
              <p class="text-sm font-semibold text-slate-700 dark:text-slate-300">{{ __('app.property_field_active') }}</p>
              <p class="text-xs text-slate-400">{{ __('app.property_field_active_hint') }}</p>
            </div>
            <label class="relative inline-flex items-center cursor-pointer">
              <input type="checkbox" name="is_active" id="prop-is-active" value="1" class="sr-only peer" checked>
              <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary dark:bg-slate-700"></div>
            </label>
          </div>

        </form>
      </div>

      {{-- Modal footer --}}
      <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-slate-200 dark:border-slate-800 flex-shrink-0">
        <button type="button" onclick="closePropertyModal()"
          class="px-5 py-2.5 text-sm font-semibold text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 transition-all">
          {{ __('app.btn_cancel') }}
        </button>
        <button type="submit" form="property-form"
          class="px-5 py-2.5 text-sm font-bold bg-primary hover:bg-primary/90 text-white rounded-xl transition-all shadow-sm">
          {{ __('app.btn_save_changes') }}
        </button>
      </div>
    </div>
  </div>
</div>

{{-- ── Delete Confirmation Modal ────────────────────────────────────────────── --}}
<div id="delete-property-modal" class="fixed inset-0 z-50 hidden">
  <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="closeDeletePropertyModal()"></div>
  <div class="relative z-10 flex items-center justify-center min-h-screen p-4">
    <div class="w-full max-w-sm bg-white dark:bg-slate-900 rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-800 p-6 space-y-4">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-rose-100 dark:bg-rose-900/30 flex items-center justify-center flex-shrink-0">
          <span class="material-icons text-rose-500">delete</span>
        </div>
        <div>
          <p class="font-bold text-slate-900 dark:text-white">{{ __('app.confirm_delete_title') }}</p>
          <p id="delete-property-name" class="text-sm text-slate-500 mt-0.5"></p>
        </div>
      </div>
      <form id="delete-property-form" method="POST">
        @csrf @method('DELETE')
        <div class="flex gap-3 pt-2">
          <button type="button" onclick="closeDeletePropertyModal()"
            class="flex-1 py-2.5 text-sm font-semibold border border-slate-200 dark:border-slate-700 rounded-xl text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition-all">
            {{ __('app.btn_cancel') }}
          </button>
          <button type="submit"
            class="flex-1 py-2.5 text-sm font-bold bg-rose-500 hover:bg-rose-600 text-white rounded-xl transition-all">
            {{ __('app.btn_delete') }}
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

{{-- ── JS ────────────────────────────────────────────────────────────────────── --}}
<script>
  const apiBlocksUrl      = "{{ url('/api/blocks') }}";
  const propPriceHints    = {
    sell: "{{ __('app.property_field_price_hint_sell') }}",
    rent: "{{ __('app.property_field_price_hint_rent') }}",
  };
  const propModeAddLabel       = "{{ __('app.property_add') }}";
  const propModeEditLabel      = "{{ __('app.property_edit') }}";
  const propSelectBlockLabel   = "{{ __('app.select_block') }}";
  const propSelectUnitLabel    = "{{ __('app.select_unit') }}";
  const propLoadingLabel       = "{{ __('app.units_loading') }}";

  let propUnitMap = {};

  // ── Open / Close ────────────────────────────────────────────────────────────
  function openPropertyModal(data = null) {
    const modal   = document.getElementById('property-modal');
    const form    = document.getElementById('property-form');
    const titleEl = document.getElementById('modal-title');

    form.reset();
    document.getElementById('new-images-preview').innerHTML = '';
    document.getElementById('existing-images-grid').classList.add('hidden');
    document.getElementById('existing-images-grid').innerHTML = '';

    if (data) {
      titleEl.textContent = propModeEditLabel;
      form.action = `/property-listings/${data.id}`;
      document.getElementById('form-method').value = 'PUT';

      document.getElementById('prop-title').value         = data.title         ?? '';
      document.getElementById('prop-type').value          = data.type          ?? 'sell';
      document.getElementById('prop-price').value         = data.price         ?? '';
      document.getElementById('prop-status').value        = data.status        ?? 'available';
      document.getElementById('prop-location').value      = data.location_label ?? '';
      document.getElementById('prop-bedrooms').value      = data.bedrooms      ?? '';
      document.getElementById('prop-bathrooms').value     = data.bathrooms     ?? '';
      document.getElementById('prop-land-area').value     = data.land_area     ?? '';
      document.getElementById('prop-building-area').value = data.building_area ?? '';
      document.getElementById('prop-description').value   = data.description   ?? '';
      document.getElementById('prop-contact-name').value  = data.contact_name  ?? '';
      document.getElementById('prop-contact-phone').value = data.contact_phone ?? '';
      document.getElementById('prop-is-active').checked   = !!data.is_active;

      const images     = data.image_urls  ?? [];
      const imagePaths = data.images      ?? [];
      if (images.length > 0) {
        const grid = document.getElementById('existing-images-grid');
        grid.classList.remove('hidden');
        grid.classList.add('grid');
        images.forEach((url, i) => {
          const path = imagePaths[i] ?? '';
          const div  = document.createElement('div');
          div.className = 'relative group';
          div.innerHTML = `
            <img src="${url}" alt="" class="w-full h-24 object-cover rounded-xl border border-slate-200 dark:border-slate-700">
            <label class="absolute inset-0 flex items-center justify-center bg-black/50 rounded-xl opacity-0 group-hover:opacity-100 transition-opacity cursor-pointer">
              <input type="checkbox" name="remove_images[]" value="${path}" class="hidden peer">
              <span class="material-icons text-white text-xl">delete</span>
            </label>
          `;
          const cb  = div.querySelector('input[type=checkbox]');
          const img = div.querySelector('img');
          cb.addEventListener('change', () => img.classList.toggle('opacity-30', cb.checked));
          grid.appendChild(div);
        });
      }

      if (data.block_id) {
        document.getElementById('prop-block-id').value = data.block_id;
        loadPropUnits(data.block_id, data.unit_id);
      }
    } else {
      titleEl.textContent = propModeAddLabel;
      form.action = "{{ route('property.store') }}";
      document.getElementById('form-method').value = 'POST';
    }

    updatePriceHint();
    modal.classList.remove('hidden');
  }

  function closePropertyModal() {
    document.getElementById('property-modal').classList.add('hidden');
  }

  // ── Price hint ──────────────────────────────────────────────────────────────
  document.getElementById('prop-type').addEventListener('change', updatePriceHint);
  function updatePriceHint() {
    const type = document.getElementById('prop-type').value;
    document.getElementById('prop-price-hint').textContent = propPriceHints[type] ?? '';
  }

  // ── Block → Unit cascade ────────────────────────────────────────────────────
  async function loadPropUnits(blockId, selectedUnitId = null) {
    const sel = document.getElementById('prop-unit-id');
    sel.innerHTML = `<option value="">${propLoadingLabel}</option>`;
    propUnitMap = {};
    if (!blockId) {
      sel.innerHTML = `<option value="">${propSelectBlockLabel}</option>`;
      return;
    }
    try {
      const res   = await fetch(`${apiBlocksUrl}/${blockId}/units`);
      const units = await res.json();
      sel.innerHTML = `<option value="">${propSelectUnitLabel}</option>`;
      units.forEach(u => {
        propUnitMap[u.id] = u.unit_number;
        const opt = document.createElement('option');
        opt.value       = u.id;
        opt.textContent = u.unit_number;
        if (u.id === selectedUnitId) opt.selected = true;
        sel.appendChild(opt);
      });
      if (selectedUnitId) updatePropLocation();
    } catch {
      sel.innerHTML = `<option value="">${propSelectUnitLabel}</option>`;
    }
  }

  function updatePropLocation() {
    const blockSel  = document.getElementById('prop-block-id');
    const unitSel   = document.getElementById('prop-unit-id');
    const locInput  = document.getElementById('prop-location');
    const blockText = blockSel.options[blockSel.selectedIndex]?.text ?? '';
    const unitId    = unitSel.value;
    const unitText  = propUnitMap[unitId] ?? '';
    if (blockText && unitText) {
      locInput.value = `${blockText} · ${unitText}`;
    }
  }

  // ── Image preview ───────────────────────────────────────────────────────────
  function previewNewImages(event) {
    const preview = document.getElementById('new-images-preview');
    Array.from(event.target.files).forEach(file => {
      const reader = new FileReader();
      reader.onload = (ev) => {
        const div = document.createElement('div');
        div.className = 'relative';
        div.innerHTML = `<img src="${ev.target.result}" alt="" class="w-full h-24 object-cover rounded-xl border border-slate-200 dark:border-slate-700">`;
        preview.appendChild(div);
      };
      reader.readAsDataURL(file);
    });
  }

  // ── Delete confirmation ─────────────────────────────────────────────────────
  function confirmDeleteProperty(id, name) {
    document.getElementById('delete-property-name').textContent = name;
    document.getElementById('delete-property-form').action = `/property-listings/${id}`;
    document.getElementById('delete-property-modal').classList.remove('hidden');
  }

  function closeDeletePropertyModal() {
    document.getElementById('delete-property-modal').classList.add('hidden');
  }
</script>
