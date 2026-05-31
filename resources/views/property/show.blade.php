{{-- property/show.blade.php — Property Listing Detail --}}
<x-layouts.app :title="$property->title"
  class="font-display bg-background-light dark:bg-background-dark text-slate-800 dark:text-slate-200 antialiased min-h-screen">

  <x-nav.sidebar active="property" />

  <main class="lg:ml-64 flex flex-col min-h-screen">

    {{-- Page Header --}}
    <header class="h-16 bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between px-6 lg:px-8 shrink-0">
      <div class="flex items-center gap-3">
        <button class="lg:hidden p-2 rounded-lg border border-slate-200 dark:border-slate-800" onclick="toggleSidebar()">
          <span class="material-icons text-slate-500">menu</span>
        </button>
        <a href="{{ route('property.index') }}"
          class="p-2 rounded-lg text-slate-400 hover:text-primary hover:bg-primary/5 transition-all"
          title="{{ __('app.btn_back') }}">
          <span class="material-icons">arrow_back</span>
        </a>
        <div>
          <h1 class="text-lg font-bold text-slate-900 dark:text-white leading-tight">{{ $property->title }}</h1>
          @if($property->location_label)
            <p class="text-xs text-slate-400">{{ $property->location_label }}</p>
          @endif
        </div>
      </div>
      <div class="flex items-center gap-3">
        {{-- Type badge --}}
        @php
          $typeColor = $property->type === 'sell'
            ? 'bg-primary/10 text-primary'
            : 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400';
          $statusColor = match($property->status) {
            'available' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
            default     => 'bg-slate-200 text-slate-600 dark:bg-slate-700 dark:text-slate-300',
          };
        @endphp
        <span class="hidden sm:inline px-2.5 py-1 text-xs font-bold rounded-full uppercase {{ $typeColor }}">
          {{ $property->typeLabel() }}
        </span>
        <span class="hidden sm:inline px-2.5 py-1 text-xs font-bold rounded-full {{ $statusColor }}">
          {{ $property->statusLabel() }}
        </span>
        @if(auth()->user()->can('property.edit'))
          <button onclick="openPropertyModal({{ Js::from($property) }})"
            class="flex items-center gap-1.5 px-3.5 py-2 text-sm font-semibold bg-primary hover:bg-primary/90 text-white rounded-xl transition-all shadow-sm shadow-primary/20">
            <span class="material-icons text-[16px]">edit</span>
            <span class="hidden sm:inline">{{ __('app.btn_edit') }}</span>
          </button>
        @endif
        <button class="p-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg hover:border-primary/50 transition-all"
          onclick="toggleDark()" title="{{ __('app.toggle_dark_mode') }}">
          <span class="material-icons text-slate-500 text-[20px]">dark_mode</span>
        </button>
      </div>
    </header>

    <div class="flex-1 p-6 lg:p-8">
      <div class="max-w-4xl mx-auto space-y-6">

        {{-- Image Gallery --}}
        @php $images = $property->imageUrls(); @endphp
        @if(count($images) > 0)
          <div class="space-y-3">
            {{-- Main image --}}
            <div class="w-full aspect-video rounded-2xl overflow-hidden bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700">
              <img id="gallery-main" src="{{ $images[0] }}" alt="{{ $property->title }}"
                class="w-full h-full object-cover">
            </div>
            {{-- Thumbnails --}}
            @if(count($images) > 1)
              <div class="grid grid-cols-5 sm:grid-cols-6 gap-2">
                @foreach($images as $i => $url)
                  <button onclick="setGalleryMain('{{ $url }}', this)"
                    class="aspect-square rounded-xl overflow-hidden border-2 transition-all {{ $i === 0 ? 'border-primary' : 'border-slate-200 dark:border-slate-700 hover:border-primary/50' }}">
                    <img src="{{ $url }}" alt="" class="w-full h-full object-cover">
                  </button>
                @endforeach
              </div>
            @endif
          </div>
        @else
          <div class="w-full aspect-video rounded-2xl bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 flex items-center justify-center">
            <span class="material-icons text-6xl text-slate-300 dark:text-slate-600">home</span>
          </div>
        @endif

        {{-- Main content grid --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

          {{-- Left: main info --}}
          <div class="lg:col-span-2 space-y-6">

            {{-- Price & quick stats --}}
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6">
              <div class="flex items-start justify-between gap-4 flex-wrap">
                <div>
                  <p class="text-3xl font-black text-slate-900 dark:text-white">{{ $property->formattedPrice() }}</p>
                  @if($property->type === 'rent')
                    <p class="text-xs text-slate-400 mt-0.5">{{ __('app.property_field_price_hint_rent') }}</p>
                  @endif
                </div>
                <div class="flex gap-2 flex-wrap">
                  <span class="px-3 py-1.5 text-xs font-bold rounded-full uppercase {{ $typeColor }}">
                    {{ $property->typeLabel() }}
                  </span>
                  <span class="px-3 py-1.5 text-xs font-bold rounded-full {{ $statusColor }}">
                    {{ $property->statusLabel() }}
                  </span>
                </div>
              </div>

              {{-- Quick stat chips --}}
              @if($property->bedrooms !== null || $property->bathrooms !== null || $property->land_area || $property->building_area)
                <div class="flex flex-wrap gap-4 mt-5 pt-5 border-t border-slate-100 dark:border-slate-800">
                  @if($property->bedrooms !== null)
                    <div class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-300">
                      <span class="material-icons text-slate-400 text-[20px]">bed</span>
                      <span><strong class="text-slate-900 dark:text-white">{{ $property->bedrooms }}</strong> {{ __('app.property_field_bedrooms') }}</span>
                    </div>
                  @endif
                  @if($property->bathrooms !== null)
                    <div class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-300">
                      <span class="material-icons text-slate-400 text-[20px]">bathroom</span>
                      <span><strong class="text-slate-900 dark:text-white">{{ $property->bathrooms }}</strong> {{ __('app.property_field_bathrooms') }}</span>
                    </div>
                  @endif
                  @if($property->land_area)
                    <div class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-300">
                      <span class="material-icons text-slate-400 text-[20px]">square_foot</span>
                      <span><strong class="text-slate-900 dark:text-white">{{ number_format($property->land_area, 0) }} m²</strong> LT</span>
                    </div>
                  @endif
                  @if($property->building_area)
                    <div class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-300">
                      <span class="material-icons text-slate-400 text-[20px]">home</span>
                      <span><strong class="text-slate-900 dark:text-white">{{ number_format($property->building_area, 0) }} m²</strong> LB</span>
                    </div>
                  @endif
                </div>
              @endif
            </div>

            {{-- Description --}}
            @if($property->description)
              <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6">
                <h2 class="text-sm font-bold text-slate-500 uppercase tracking-wider mb-3">{{ __('app.property_field_description') }}</h2>
                <p class="text-sm text-slate-700 dark:text-slate-300 leading-relaxed whitespace-pre-line">{{ $property->description }}</p>
              </div>
            @endif

          </div>

          {{-- Right: sidebar info --}}
          <div class="space-y-4">

            {{-- Location & block/unit --}}
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-5 space-y-3">
              <h2 class="text-sm font-bold text-slate-500 uppercase tracking-wider">{{ __('app.property_field_location') }}</h2>
              @if($property->location_label)
                <div class="flex items-start gap-2.5">
                  <span class="material-icons text-slate-400 text-[18px] mt-0.5">place</span>
                  <p class="text-sm text-slate-700 dark:text-slate-200">{{ $property->location_label }}</p>
                </div>
              @endif
              @if($property->block)
                <div class="flex items-center gap-2.5">
                  <span class="material-icons text-slate-400 text-[18px]">domain</span>
                  <p class="text-sm text-slate-700 dark:text-slate-200">{{ __('app.block') }}: <strong>{{ $property->block->name }}</strong></p>
                </div>
              @endif
              @if($property->unit)
                <div class="flex items-center gap-2.5">
                  <span class="material-icons text-slate-400 text-[18px]">door_front</span>
                  <p class="text-sm text-slate-700 dark:text-slate-200">{{ __('app.unit') }}: <strong>{{ $property->unit->unit_number }}</strong></p>
                </div>
              @endif
              @if(!$property->location_label && !$property->block)
                <p class="text-sm text-slate-400">—</p>
              @endif
            </div>

            {{-- Contact --}}
            @if($property->contact_name || $property->contact_phone)
              <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-5 space-y-3">
                <h2 class="text-sm font-bold text-slate-500 uppercase tracking-wider">{{ __('app.property_field_contact_name') }}</h2>
                @if($property->contact_name)
                  <div class="flex items-center gap-2.5">
                    <span class="material-icons text-slate-400 text-[18px]">person</span>
                    <p class="text-sm font-semibold text-slate-700 dark:text-slate-200">{{ $property->contact_name }}</p>
                  </div>
                @endif
                @if($property->contact_phone)
                  <div class="flex items-center gap-2.5">
                    <span class="material-icons text-slate-400 text-[18px]">phone</span>
                    <a href="https://wa.me/{{ preg_replace('/\D/', '', $property->contact_phone) }}"
                      target="_blank" rel="noopener"
                      class="text-sm text-primary hover:underline font-medium">{{ $property->contact_phone }}</a>
                  </div>
                @endif
              </div>
            @endif

            {{-- Meta --}}
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-5 space-y-2.5">
              <h2 class="text-sm font-bold text-slate-500 uppercase tracking-wider">{{ __('app.property_show_meta') }}</h2>
              <div class="flex items-center justify-between text-sm">
                <span class="text-slate-400">{{ __('app.property_col_active') }}</span>
                <span class="px-2 py-0.5 rounded-full text-xs font-bold {{ $property->is_active ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400' : 'bg-slate-100 text-slate-500 dark:bg-slate-700 dark:text-slate-400' }}">
                  {{ $property->is_active ? __('app.active') : __('app.inactive') }}
                </span>
              </div>
              @if($property->creator)
                <div class="flex items-center justify-between text-sm">
                  <span class="text-slate-400">{{ __('app.property_show_posted_by') }}</span>
                  <span class="text-slate-700 dark:text-slate-300 font-medium">{{ $property->creator->name }}</span>
                </div>
              @endif
              <div class="flex items-center justify-between text-sm">
                <span class="text-slate-400">{{ __('app.property_show_posted_on') }}</span>
                <span class="text-slate-700 dark:text-slate-300">{{ $property->created_at->format('d M Y') }}</span>
              </div>
              @if($property->updated_at->ne($property->created_at))
                <div class="flex items-center justify-between text-sm">
                  <span class="text-slate-400">{{ __('app.property_show_updated') }}</span>
                  <span class="text-slate-700 dark:text-slate-300">{{ $property->updated_at->format('d M Y') }}</span>
                </div>
              @endif
            </div>

            {{-- Actions --}}
            @if(auth()->user()->can('property.edit') || auth()->user()->can('property.delete'))
              <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-5 space-y-2">
                <h2 class="text-sm font-bold text-slate-500 uppercase tracking-wider mb-3">{{ __('app.property_show_actions') }}</h2>
                @if(auth()->user()->can('property.edit'))
                  <button onclick="openPropertyModal({{ Js::from($property) }})"
                    class="w-full flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-semibold bg-primary/10 hover:bg-primary/20 text-primary rounded-xl transition-all">
                    <span class="material-icons text-[16px]">edit</span>
                    {{ __('app.btn_edit') }}
                  </button>
                  <form method="POST" action="{{ route('property.toggle-active', $property) }}">
                    @csrf @method('PATCH')
                    <button type="submit"
                      class="w-full flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-semibold border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 rounded-xl transition-all">
                      <span class="material-icons text-[16px]">{{ $property->is_active ? 'visibility_off' : 'visibility' }}</span>
                      {{ $property->is_active ? __('app.property_hide') : __('app.property_show') }}
                    </button>
                  </form>
                @endif
                @if(auth()->user()->can('property.delete'))
                  <button onclick="confirmDeleteProperty('{{ $property->id }}', {{ json_encode($property->title) }})"
                    class="w-full flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-semibold bg-rose-50 dark:bg-rose-900/20 hover:bg-rose-100 dark:hover:bg-rose-900/30 text-rose-600 rounded-xl transition-all">
                    <span class="material-icons text-[16px]">delete</span>
                    {{ __('app.btn_delete') }}
                  </button>
                @endif
              </div>
            @endif

          </div>
        </div>

      </div>
    </div>
  </main>

  {{-- Reuse modals from the module (edit + delete) --}}
  @php $listings = collect([]); $blocks = \App\Models\Block::where('is_active', true)->orderBy('name')->get(); @endphp
  @include('property._modals')

  {{-- After a successful edit, redirect back to the show page --}}
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const origOpen = window.openPropertyModal;
      window.openPropertyModal = function(data) {
        origOpen(data);
        if (data) {
          const form = document.getElementById('property-form');
          if (!document.getElementById('prop-stay-on-show')) {
            const inp  = document.createElement('input');
            inp.type   = 'hidden';
            inp.name   = '_stay_on_show';
            inp.id     = 'prop-stay-on-show';
            inp.value  = '1';
            form.appendChild(inp);
          }
        }
      };
    });
  </script>

  {{-- Gallery switcher --}}
  <script>
    function setGalleryMain(url, btn) {
      document.getElementById('gallery-main').src = url;
      document.querySelectorAll('[onclick^="setGalleryMain"]').forEach(b => {
        b.classList.remove('border-primary');
        b.classList.add('border-slate-200', 'dark:border-slate-700');
      });
      btn.classList.add('border-primary');
      btn.classList.remove('border-slate-200', 'dark:border-slate-700');
    }
  </script>

</x-layouts.app>
