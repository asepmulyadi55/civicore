{{-- Property Listings Admin Page --}}
<x-layouts.app :title="__('app.nav_property')"
  class="font-display bg-background-light dark:bg-background-dark text-slate-800 dark:text-slate-200 antialiased min-h-screen">

  <x-nav.sidebar active="property" />

  <div class="lg:pl-64 min-h-screen bg-background-light dark:bg-background-dark flex flex-col">

    {{-- Page Header --}}
    <header class="h-16 bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between px-4 lg:px-8">
      <div class="flex items-center gap-3">
        <button class="lg:hidden p-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg"
          onclick="toggleSidebar()">
          <span class="material-icons text-slate-500">menu</span>
        </button>
        <h1 class="text-xl font-bold">{{ __('app.property_title') }}</h1>
      </div>
      @can('property.create')
      <button onclick="openPropertyModal()"
        class="inline-flex items-center gap-2 px-4 py-2 bg-primary hover:bg-primary/90 text-white text-sm font-bold rounded-xl transition-all shadow-sm">
        <span class="material-icons text-base">add</span>
        {{ __('app.property_add') }}
      </button>
      @endcan
    </header>

    <main class="flex-1 p-6 lg:p-8 space-y-6">

      {{-- Flash Messages --}}
      @if(session('success'))
        <div class="p-4 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 rounded-xl flex items-center gap-3">
          <span class="material-icons text-emerald-500">check_circle</span>
          <p class="text-sm text-emerald-700 dark:text-emerald-400">{{ session('success') }}</p>
        </div>
      @endif
      @if($errors->any())
        <div class="p-4 bg-rose-50 dark:bg-rose-900/20 border border-rose-200 dark:border-rose-800 rounded-xl flex items-start gap-3">
          <span class="material-icons text-rose-500 mt-0.5">error</span>
          <ul class="text-sm text-rose-700 dark:text-rose-400 list-disc list-inside space-y-0.5">
            @foreach($errors->all() as $err) <li>{{ $err }}</li> @endforeach
          </ul>
        </div>
      @endif

      {{-- Filters --}}
      <form method="GET" action="{{ route('property.index') }}" class="flex flex-wrap gap-3">
        <div class="relative flex-1 min-w-[200px]">
          <span class="material-icons absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-[18px]">search</span>
          <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('app.search') }}..."
            class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all">
        </div>
        <select name="type"
          class="px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all">
          <option value="">{{ __('app.property_filter_all_types') }}</option>
          <option value="sell" {{ request('type') === 'sell' ? 'selected' : '' }}>{{ __('app.property_type_sell') }}</option>
          <option value="rent" {{ request('type') === 'rent' ? 'selected' : '' }}>{{ __('app.property_type_rent') }}</option>
        </select>
        <select name="status"
          class="px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all">
          <option value="">{{ __('app.property_filter_all_status') }}</option>
          <option value="available" {{ request('status') === 'available' ? 'selected' : '' }}>{{ __('app.property_status_available') }}</option>
          <option value="sold" {{ request('status') === 'sold' ? 'selected' : '' }}>{{ __('app.property_status_sold') }}</option>
          <option value="rented" {{ request('status') === 'rented' ? 'selected' : '' }}>{{ __('app.property_status_rented') }}</option>
        </select>
        <button type="submit"
          class="px-4 py-2.5 bg-primary hover:bg-primary/90 text-white text-sm font-semibold rounded-xl transition-all">
          {{ __('app.btn_filter') }}
        </button>
        @if(request()->hasAny(['search','type','status']))
          <a href="{{ route('property.index') }}"
            class="px-4 py-2.5 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400 text-sm font-semibold rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 transition-all">
            {{ __('app.clear_filters') }}
          </a>
        @endif
      </form>

      {{-- Table --}}
      <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
          <table class="w-full text-left">
            <thead class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-800">
              <tr>
                <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">{{ __('app.property_col_listing') }}</th>
                <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider hidden md:table-cell">{{ __('app.property_col_price') }}</th>
                <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider hidden lg:table-cell">{{ __('app.property_col_details') }}</th>
                <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider hidden sm:table-cell">{{ __('app.property_col_status') }}</th>
                <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">{{ __('app.property_col_active') }}</th>
                <th class="px-6 py-4"></th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
              @forelse($listings as $listing)
                @php
                  $typeColors = [
                    'sell' => 'bg-primary/10 text-primary dark:bg-primary/20',
                    'rent' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
                  ];
                  $statusColors = [
                    'available' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
                    'sold'      => 'bg-slate-200 text-slate-600 dark:bg-slate-700 dark:text-slate-300',
                    'rented'    => 'bg-slate-200 text-slate-600 dark:bg-slate-700 dark:text-slate-300',
                  ];
                  $firstImage = $listing->imageUrls()[0] ?? null;
                @endphp
                <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition-colors">

                  {{-- Listing info --}}
                  <td class="px-6 py-4">
                    <div class="flex items-center gap-3">
                      {{-- Thumbnail --}}
                      <div class="w-14 h-14 rounded-xl overflow-hidden flex-shrink-0 bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700">
                        @if($firstImage)
                          <img src="{{ $firstImage }}" alt="{{ $listing->title }}" class="w-full h-full object-cover">
                        @else
                          <div class="w-full h-full flex items-center justify-center">
                            <span class="material-icons text-slate-400 text-xl">home</span>
                          </div>
                        @endif
                      </div>
                      <div>
                        <p class="font-semibold text-slate-900 dark:text-white text-sm">{{ $listing->title }}</p>
                        @if($listing->location_label)
                          <p class="text-xs text-slate-500 mt-0.5">{{ $listing->location_label }}</p>
                        @endif
                        <span class="inline-block mt-1 px-2 py-0.5 text-[10px] font-bold rounded-full uppercase {{ $typeColors[$listing->type] ?? '' }}">
                          {{ $listing->typeLabel() }}
                        </span>
                      </div>
                    </div>
                  </td>

                  {{-- Price --}}
                  <td class="px-6 py-4 hidden md:table-cell">
                    <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ $listing->formattedPrice() }}</p>
                  </td>

                  {{-- Details --}}
                  <td class="px-6 py-4 hidden lg:table-cell">
                    <div class="flex items-center gap-3 text-xs text-slate-500">
                      @if($listing->bedrooms !== null)
                        <span class="flex items-center gap-1">
                          <span class="material-icons text-[13px]">bed</span> {{ $listing->bedrooms }}
                        </span>
                      @endif
                      @if($listing->bathrooms !== null)
                        <span class="flex items-center gap-1">
                          <span class="material-icons text-[13px]">bathroom</span> {{ $listing->bathrooms }}
                        </span>
                      @endif
                      @if($listing->land_area)
                        <span>LT {{ number_format($listing->land_area, 0) }}m²</span>
                      @endif
                      @if($listing->building_area)
                        <span>LB {{ number_format($listing->building_area, 0) }}m²</span>
                      @endif
                    </div>
                    @if($listing->contact_phone)
                      <p class="text-xs text-slate-400 mt-0.5">{{ $listing->contact_name }} · {{ $listing->contact_phone }}</p>
                    @endif
                  </td>

                  {{-- Status --}}
                  <td class="px-6 py-4 hidden sm:table-cell">
                    <span class="px-2.5 py-1 text-xs font-bold rounded-full {{ $statusColors[$listing->status] ?? '' }}">
                      {{ $listing->statusLabel() }}
                    </span>
                  </td>

                  {{-- Active toggle --}}
                  <td class="px-6 py-4">
                    @can('property.edit')
                    <form method="POST" action="{{ route('property.toggle-active', $listing) }}">
                      @csrf @method('PATCH')
                      <button type="submit"
                        class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none {{ $listing->is_active ? 'bg-primary' : 'bg-slate-200 dark:bg-slate-700' }}"
                        title="{{ $listing->is_active ? __('app.active') : __('app.inactive') }}">
                        <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform {{ $listing->is_active ? 'translate-x-6' : 'translate-x-1' }}"></span>
                      </button>
                    </form>
                    @else
                      <span class="px-2 py-1 text-xs rounded-full {{ $listing->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                        {{ $listing->is_active ? __('app.active') : __('app.inactive') }}
                      </span>
                    @endcan
                  </td>

                  {{-- Actions --}}
                  <td class="px-6 py-4">
                    <div class="flex items-center gap-2">
                      @can('property.edit')
                      <button type="button"
                        onclick="openPropertyModal(@json($listing))"
                        class="p-2 text-slate-400 hover:text-primary dark:hover:text-secondary transition-colors rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800"
                        title="{{ __('app.btn_edit') }}">
                        <span class="material-icons text-[18px]">edit</span>
                      </button>
                      @endcan
                      @can('property.delete')
                      <button type="button"
                        onclick="confirmDeleteProperty('{{ $listing->id }}', {{ json_encode($listing->title) }})"
                        class="p-2 text-slate-400 hover:text-rose-500 transition-colors rounded-lg hover:bg-rose-50 dark:hover:bg-rose-900/20"
                        title="{{ __('app.btn_delete') }}">
                        <span class="material-icons text-[18px]">delete</span>
                      </button>
                      @endcan
                    </div>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="6" class="px-6 py-16 text-center">
                    <div class="flex flex-col items-center gap-3 text-slate-400">
                      <span class="material-icons text-5xl">home_work</span>
                      <p class="text-sm font-medium">{{ __('app.property_empty') }}</p>
                    </div>
                  </td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>

        {{-- Pagination --}}
        @if($listings->hasPages())
          <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-800">
            {{ $listings->links() }}
          </div>
        @endif
      </div>

    </main>
  </div>

  {{-- ── Create / Edit Modal ──────────────────────────────────────────────── --}}
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

            {{-- Title + Type row --}}
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
                  <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-sm text-slate-400 font-medium">{{ \App\Models\Setting::get('currency_symbol','Rp') }}</span>
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

            {{-- Bedrooms, Bathrooms, Land, Building --}}
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

              {{-- New image upload --}}
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

  {{-- Delete confirmation modal --}}
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

  <script>
    const apiBlocksUrl = "{{ url('/api/blocks') }}";
    const propPriceHints = {
      sell: "{{ __('app.property_field_price_hint_sell') }}",
      rent: "{{ __('app.property_field_price_hint_rent') }}",
    };
    const propModeAddLabel  = "{{ __('app.property_add') }}";
    const propModeEditLabel = "{{ __('app.property_edit') }}";
    const propSelectBlockLabel = "{{ __('app.select_block') }}";
    const propSelectUnitLabel  = "{{ __('app.select_unit') }}";
    const propLoadingLabel     = "{{ __('app.units_loading') }}";

    let propUnitMap = {}; // unitId → { unit_number }

    // ── Modal open/close ──────────────────────────────────────────────────────

    function openPropertyModal(data = null) {
      const modal = document.getElementById('property-modal');
      const form  = document.getElementById('property-form');
      const titleEl = document.getElementById('modal-title');

      // Reset form
      form.reset();
      document.getElementById('new-images-preview').innerHTML = '';
      document.getElementById('existing-images-grid').classList.add('hidden');
      document.getElementById('existing-images-grid').innerHTML = '';

      if (data) {
        // Edit mode
        titleEl.textContent = propModeEditLabel;
        form.action = `/property-listings/${data.id}`;
        document.getElementById('form-method').value = 'PUT';

        // Fill basic fields
        document.getElementById('prop-title').value        = data.title ?? '';
        document.getElementById('prop-type').value         = data.type ?? 'sell';
        document.getElementById('prop-price').value        = data.price ?? '';
        document.getElementById('prop-status').value       = data.status ?? 'available';
        document.getElementById('prop-location').value     = data.location_label ?? '';
        document.getElementById('prop-bedrooms').value     = data.bedrooms ?? '';
        document.getElementById('prop-bathrooms').value    = data.bathrooms ?? '';
        document.getElementById('prop-land-area').value    = data.land_area ?? '';
        document.getElementById('prop-building-area').value= data.building_area ?? '';
        document.getElementById('prop-description').value  = data.description ?? '';
        document.getElementById('prop-contact-name').value = data.contact_name ?? '';
        document.getElementById('prop-contact-phone').value= data.contact_phone ?? '';
        document.getElementById('prop-is-active').checked  = !!data.is_active;

        // Render existing images
        const images = data.image_urls ?? [];
        const imagePaths = data.images ?? [];
        if (images.length > 0) {
          const grid = document.getElementById('existing-images-grid');
          grid.classList.remove('hidden');
          grid.classList.add('grid');
          images.forEach((url, i) => {
            const path = imagePaths[i] ?? '';
            const div = document.createElement('div');
            div.className = 'relative group';
            div.innerHTML = `
              <img src="${url}" alt="" class="w-full h-24 object-cover rounded-xl border border-slate-200 dark:border-slate-700">
              <label class="absolute inset-0 flex items-center justify-center bg-black/50 rounded-xl opacity-0 group-hover:opacity-100 transition-opacity cursor-pointer">
                <input type="checkbox" name="remove_images[]" value="${path}" class="hidden peer">
                <span class="material-icons text-white text-xl peer-checked:text-rose-400">delete</span>
              </label>
            `;
            // Toggle overlay on checkbox change
            const cb = div.querySelector('input[type=checkbox]');
            const img = div.querySelector('img');
            cb.addEventListener('change', () => {
              img.classList.toggle('opacity-30', cb.checked);
            });
            grid.appendChild(div);
          });
        }

        // Load block + unit
        if (data.block_id) {
          document.getElementById('prop-block-id').value = data.block_id;
          loadPropUnits(data.block_id, data.unit_id);
        }
      } else {
        // Create mode
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

    // ── Price hint update ─────────────────────────────────────────────────────
    document.getElementById('prop-type').addEventListener('change', updatePriceHint);
    function updatePriceHint() {
      const type = document.getElementById('prop-type').value;
      document.getElementById('prop-price-hint').textContent = propPriceHints[type] ?? '';
    }

    // ── Block → Unit cascade ──────────────────────────────────────────────────
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
          opt.value = u.id;
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

    // ── New image preview ─────────────────────────────────────────────────────
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

    // ── Delete confirmation ───────────────────────────────────────────────────
    function confirmDeleteProperty(id, name) {
      document.getElementById('delete-property-name').textContent = name;
      document.getElementById('delete-property-form').action = `/property-listings/${id}`;
      document.getElementById('delete-property-modal').classList.remove('hidden');
    }

    function closeDeletePropertyModal() {
      document.getElementById('delete-property-modal').classList.add('hidden');
    }
  </script>

</x-layouts.app>
