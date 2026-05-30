{{-- Section 2 — Featured Event --}}
<section class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
  <div class="flex items-center gap-3 px-6 py-4 border-b border-slate-100 dark:border-slate-800">
    <div class="w-9 h-9 rounded-xl bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center">
      <span class="material-icons text-amber-500 text-[20px]">star</span>
    </div>
    <div>
      <h2 class="font-bold text-slate-900 dark:text-white text-base">{{ __('app.hp_section_featured') }}</h2>
      <p class="text-xs text-slate-500">{{ __('app.hp_section_featured_desc') }}</p>
    </div>
  </div>

  {{-- Display Settings --}}
  <div class="px-6 py-5 border-b border-slate-100 dark:border-slate-800 bg-slate-50/60 dark:bg-slate-800/30">
    <form method="POST" action="{{ route('homepage.section-labels') }}">
      @csrf
      <p class="text-xs font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500 mb-3">Display Settings</p>
      <div class="flex items-end gap-3 flex-wrap">
        <div class="flex-1 min-w-48 space-y-1">
          <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400">Eyebrow Label</label>
          <input type="text" name="featured_eyebrow"
            value="{{ old('featured_eyebrow', $sectionLabels['featured_eyebrow'] ?? 'Featured Event') }}"
            class="w-full px-3 py-2 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all"
            placeholder="Featured Event">
        </div>
        <button type="submit" class="px-4 py-2 bg-primary hover:bg-primary/90 text-white text-xs font-bold rounded-lg transition-all shadow-sm">
          Save
        </button>
      </div>
    </form>
  </div>

  <form id="form-hp-featured" method="POST" action="{{ route('homepage.featured-event') }}" class="p-6 space-y-5" enctype="multipart/form-data" novalidate>
    @csrf
    @php $featuredType = old('type', $featuredEvent['type'] ?? 'full'); @endphp

    {{-- Display Type --}}
    <div class="space-y-2">
      <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">{{ __('app.hp_featured_type_label') }}</label>
      <div class="flex flex-wrap gap-3">
        <label class="flex items-center gap-2.5 px-4 py-2.5 rounded-xl border cursor-pointer transition-all
          {{ $featuredType === 'full' ? 'border-primary bg-primary/5 text-primary' : 'border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400 hover:border-primary/40' }}">
          <input type="radio" name="type" value="full" class="sr-only" onchange="toggleFeaturedType(this.value)"
            {{ $featuredType === 'full' ? 'checked' : '' }}>
          <span class="material-icons text-[18px]">play_circle</span>
          <span class="text-sm font-semibold">{{ __('app.hp_featured_type_full') }}</span>
        </label>
        <label class="flex items-center gap-2.5 px-4 py-2.5 rounded-xl border cursor-pointer transition-all
          {{ $featuredType === 'simple' ? 'border-primary bg-primary/5 text-primary' : 'border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400 hover:border-primary/40' }}">
          <input type="radio" name="type" value="simple" class="sr-only" onchange="toggleFeaturedType(this.value)"
            {{ $featuredType === 'simple' ? 'checked' : '' }}>
          <span class="material-icons text-[18px]">image</span>
          <span class="text-sm font-semibold">{{ __('app.hp_featured_type_simple') }}</span>
        </label>
      </div>
    </div>

    {{-- Title (always shown) --}}
    <div class="space-y-1.5">
      <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">{{ __('app.hp_event_title_label') }} <span class="text-rose-500">*</span></label>
      <input type="text" id="hp-featured-title" name="title" value="{{ old('title', $featuredEvent['title'] ?? '') }}"
        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all"
        placeholder="e.g. Dwipapuri Anniversary Gala" oninput="clearHpErr('err-hp-featured-title')">
      <p id="err-hp-featured-title" class="hidden mt-1 text-sm text-rose-500"></p>
    </div>

    {{-- Full-type fields: YouTube ID + Date --}}
    <div id="featured-full-fields" class="{{ $featuredType === 'simple' ? 'hidden' : '' }} grid grid-cols-1 md:grid-cols-2 gap-5">
      <div class="space-y-1.5">
        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">{{ __('app.hp_youtube_id') }}</label>
        <input type="text" name="youtube_id" value="{{ old('youtube_id', $featuredEvent['youtube_id'] ?? '') }}"
          class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all"
          placeholder="e.g. dQw4w9WgXcQ">
        <p class="text-xs text-slate-400">{{ __('app.hp_youtube_hint') }} youtube.com/watch?v=<strong>ID</strong></p>
      </div>
      <div class="space-y-1.5">
        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">{{ __('app.hp_event_date_label') }}</label>
        <input type="date" name="date" value="{{ old('date', $featuredEvent['date'] ?? '') }}"
          class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all dark:[color-scheme:dark]">
      </div>
    </div>

    {{-- Simple-type fields: Image upload --}}
    <div id="featured-simple-fields" class="{{ $featuredType === 'full' ? 'hidden' : '' }} space-y-1.5">
      <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">{{ __('app.hp_featured_image_label') }}</label>
      @if(!empty($featuredEvent['image_url']) && ($featuredType === 'simple'))
        <div class="flex items-center gap-4 p-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 mb-2">
          <img src="{{ $featuredEvent['image_url'] }}" alt="Current" class="w-20 h-14 object-cover rounded-lg border border-slate-200 dark:border-slate-700 flex-shrink-0">
          <div class="flex-1 min-w-0">
            <p class="text-xs font-semibold text-slate-600 dark:text-slate-400">{{ __('app.hp_current_image') }}</p>
            <p class="text-xs text-slate-400 truncate">{{ $featuredEvent['image_url'] }}</p>
          </div>
        </div>
      @endif
      <label id="featured-img-label" class="flex flex-col items-center justify-center gap-2 w-full h-24 rounded-xl border-2 border-dashed border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-800/50 hover:border-primary/60 hover:bg-primary/5 transition-all cursor-pointer">
        <span class="material-icons text-slate-400 text-2xl">cloud_upload</span>
        <span class="text-xs font-semibold text-slate-500">{{ __('app.hp_upload_new_image') }} <span class="text-slate-400 font-normal">{{ __('app.hp_upload_optional_hint') }}</span></span>
        <input type="file" name="image_file" id="featured-img-input" accept="image/*" class="sr-only"
          onchange="previewImage(this,'featured-img-preview','featured-img-label')">
      </label>
      <div id="featured-img-preview" class="hidden items-center gap-3 p-3 rounded-xl border border-primary/30 bg-primary/5">
        <img src="" alt="Preview" class="w-16 h-12 object-cover rounded-lg flex-shrink-0">
        <div class="flex-1 min-w-0">
          <p class="text-xs font-semibold text-primary">{{ __('app.hp_ready_to_upload') }}</p>
          <p class="text-xs text-slate-400 truncate"></p>
        </div>
        <button type="button" onclick="clearImageInput('featured-img-input','featured-img-preview','featured-img-label')" class="text-slate-400 hover:text-rose-500 transition-colors">
          <span class="material-icons text-lg">close</span>
        </button>
      </div>
    </div>

    <div class="flex justify-end pt-2">
      <button type="submit"
        class="inline-flex items-center gap-2 px-5 py-2.5 bg-primary hover:bg-primary/90 text-white text-sm font-bold rounded-xl transition-all shadow-sm shadow-primary/20">
        <span class="material-icons text-base">save</span>
        {{ __('app.hp_save_featured') }}
      </button>
    </div>
  </form>
</section>

<script>
  function toggleFeaturedType(type) {
    var fullFields   = document.getElementById('featured-full-fields');
    var simpleFields = document.getElementById('featured-simple-fields');
    if (type === 'simple') {
      fullFields.classList.add('hidden');
      simpleFields.classList.remove('hidden');
    } else {
      fullFields.classList.remove('hidden');
      simpleFields.classList.add('hidden');
    }
    // Update label styles
    document.querySelectorAll('#form-hp-featured input[name="type"]').forEach(function(radio) {
      var lbl = radio.closest('label');
      if (radio.value === type) {
        lbl.classList.add('border-primary', 'bg-primary/5', 'text-primary');
        lbl.classList.remove('border-slate-200', 'dark:border-slate-700', 'text-slate-600', 'dark:text-slate-400');
      } else {
        lbl.classList.remove('border-primary', 'bg-primary/5', 'text-primary');
        lbl.classList.add('border-slate-200', 'text-slate-600');
      }
    });
  }
</script>
