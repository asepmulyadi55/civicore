{{-- homepage/_metadata.blade.php --}}
<div class="p-6 space-y-8">

  {{-- Section header --}}
  <div class="flex items-start gap-4">
    <div class="flex-shrink-0 w-11 h-11 rounded-xl bg-violet-100 dark:bg-violet-900/30 flex items-center justify-center">
      <span class="material-icons text-violet-500 text-[22px]">manage_search</span>
    </div>
    <div>
      <h2 class="text-base font-semibold text-slate-800 dark:text-slate-100">{{ __('app.hp_section_metadata') }}</h2>
      <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">{{ __('app.hp_section_metadata_desc') }}</p>
    </div>
  </div>

  <form method="POST" action="{{ route('homepage.metadata') }}" enctype="multipart/form-data" class="space-y-8">
    @csrf

    {{-- ── Basic SEO ────────────────────────────────────────────────────── --}}
    <div class="bg-slate-50 dark:bg-slate-800/50 rounded-xl p-5 border border-slate-200 dark:border-slate-700 space-y-5">
      <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-300 flex items-center gap-2">
        <span class="material-icons text-[16px] text-slate-400">search</span>
        Search Engine Optimisation (SEO)
      </h3>

      {{-- Page Title --}}
      <div>
        <label for="meta-page-title" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
          {{ __('app.hp_meta_page_title') }}
        </label>
        <input type="text" id="meta-page-title" name="page_title" maxlength="120"
          value="{{ $metadata['page_title'] ?? '' }}"
          placeholder="e.g. Dwipapuri – Residential Community"
          class="w-full text-sm rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-700 dark:text-slate-300 px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-primary/30 placeholder-slate-400"
          oninput="updateCharCount(this, 'meta-page-title-count', 60)">
        <div class="flex items-center justify-between mt-1">
          <p class="text-xs text-slate-400 dark:text-slate-500">{{ __('app.hp_meta_page_title_hint') }}</p>
          <span id="meta-page-title-count" class="text-xs text-slate-400 dark:text-slate-500 tabular-nums"></span>
        </div>
      </div>

      {{-- Meta Description --}}
      <div>
        <label for="meta-description" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
          {{ __('app.hp_meta_description') }}
        </label>
        <textarea id="meta-description" name="meta_description" rows="3" maxlength="300"
          placeholder="e.g. Official portal of Dwipapuri Residential Community – payments, events, announcements and more."
          class="w-full text-sm rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-700 dark:text-slate-300 px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-primary/30 placeholder-slate-400 resize-none"
          oninput="updateCharCount(this, 'meta-description-count', 160)">{{ $metadata['meta_description'] ?? '' }}</textarea>
        <div class="flex items-center justify-between mt-1">
          <p class="text-xs text-slate-400 dark:text-slate-500">{{ __('app.hp_meta_description_hint') }}</p>
          <span id="meta-description-count" class="text-xs text-slate-400 dark:text-slate-500 tabular-nums"></span>
        </div>
      </div>

      {{-- Keywords --}}
      <div>
        <label for="meta-keywords" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
          {{ __('app.hp_meta_keywords') }}
          <span class="text-slate-400 font-normal">({{ __('app.optional') }})</span>
        </label>
        <input type="text" id="meta-keywords" name="meta_keywords" maxlength="500"
          value="{{ $metadata['meta_keywords'] ?? '' }}"
          placeholder="e.g. perumahan, iuran warga, komunitas, dwipapuri"
          class="w-full text-sm rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-700 dark:text-slate-300 px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-primary/30 placeholder-slate-400">
        <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">{{ __('app.hp_meta_keywords_hint') }}</p>
      </div>
    </div>

    {{-- ── Open Graph / Social Share ────────────────────────────────────── --}}
    <div class="bg-slate-50 dark:bg-slate-800/50 rounded-xl p-5 border border-slate-200 dark:border-slate-700 space-y-5">
      <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-300 flex items-center gap-2">
        <span class="material-icons text-[16px] text-slate-400">share</span>
        Open Graph — Social Share Preview
      </h3>

      {{-- Social Preview Card --}}
      <div class="rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden bg-white dark:bg-slate-900 max-w-sm">
        @if(!empty($metadata['og_image']))
          <img src="{{ Storage::url($metadata['og_image']) }}" alt="OG Image" class="w-full h-36 object-cover">
        @else
          <div class="w-full h-36 bg-slate-100 dark:bg-slate-800 flex items-center justify-center">
            <span class="material-icons text-slate-300 dark:text-slate-600 text-5xl">image</span>
          </div>
        @endif
        <div class="p-3">
          <p id="og-preview-domain" class="text-[10px] text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1">{{ parse_url(config('app.url'), PHP_URL_HOST) }}</p>
          <p id="og-preview-title" class="text-sm font-semibold text-slate-800 dark:text-slate-100 leading-snug">
            {{ $metadata['og_title'] ?? $metadata['page_title'] ?? config('app.name') }}
          </p>
          <p id="og-preview-desc" class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 line-clamp-2">
            {{ $metadata['og_description'] ?? $metadata['meta_description'] ?? '' }}
          </p>
        </div>
      </div>

      {{-- OG Title --}}
      <div>
        <label for="og-title" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
          {{ __('app.hp_meta_og_title') }}
          <span class="text-slate-400 font-normal">({{ __('app.optional') }})</span>
        </label>
        <input type="text" id="og-title" name="og_title" maxlength="120"
          value="{{ $metadata['og_title'] ?? '' }}"
          placeholder="{{ $metadata['page_title'] ?? config('app.name') }}"
          class="w-full text-sm rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-700 dark:text-slate-300 px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-primary/30 placeholder-slate-400"
          oninput="document.getElementById('og-preview-title').textContent = this.value || this.placeholder">
        <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">{{ __('app.hp_meta_og_title_hint') }}</p>
      </div>

      {{-- OG Description --}}
      <div>
        <label for="og-description" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
          {{ __('app.hp_meta_og_description') }}
          <span class="text-slate-400 font-normal">({{ __('app.optional') }})</span>
        </label>
        <textarea id="og-description" name="og_description" rows="2" maxlength="300"
          placeholder="{{ $metadata['meta_description'] ?? '' }}"
          class="w-full text-sm rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-700 dark:text-slate-300 px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-primary/30 placeholder-slate-400 resize-none"
          oninput="document.getElementById('og-preview-desc').textContent = this.value || this.placeholder">{{ $metadata['og_description'] ?? '' }}</textarea>
        <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">{{ __('app.hp_meta_og_description_hint') }}</p>
      </div>

      {{-- OG Image --}}
      <div>
        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
          {{ __('app.hp_meta_og_image') }}
          <span class="text-slate-400 font-normal">({{ __('app.optional') }})</span>
        </label>
        @if(!empty($metadata['og_image']))
          <div class="mb-3 flex items-center gap-3">
            <img src="{{ Storage::url($metadata['og_image']) }}" alt="Current OG image"
              class="h-16 w-28 object-cover rounded-lg border border-slate-200 dark:border-slate-700">
            <span class="text-xs text-slate-500 dark:text-slate-400">{{ __('app.current_image') }}</span>
          </div>
        @endif
        <label for="og-image-upload"
          class="flex items-center gap-3 cursor-pointer p-3 rounded-lg border-2 border-dashed border-slate-200 dark:border-slate-600 hover:border-primary/50 hover:bg-primary/5 transition-colors">
          <span class="material-icons text-slate-400 text-[22px]">add_photo_alternate</span>
          <div>
            <p class="text-sm font-medium text-slate-600 dark:text-slate-400" id="og-image-label">
              {{ __('app.change_image') }}
            </p>
            <p class="text-xs text-slate-400">{{ __('app.hp_meta_og_image_hint') }}</p>
          </div>
        </label>
        <input type="file" id="og-image-upload" name="og_image" accept="image/*" class="sr-only"
          onchange="document.getElementById('og-image-label').textContent = this.files[0]?.name ?? '{{ __('app.change_image') }}'">
      </div>
    </div>

    {{-- Save button --}}
    <div class="flex justify-end">
      <button type="submit"
        class="inline-flex items-center gap-2 px-6 py-2.5 text-sm font-semibold bg-primary text-white rounded-xl hover:opacity-90 transition-opacity shadow-sm">
        <span class="material-icons text-[18px]">save</span>
        {{ __('app.hp_save_metadata') }}
      </button>
    </div>
  </form>
</div>

<script>
function updateCharCount(el, countId, recommended) {
  const len = el.value.length;
  const counter = document.getElementById(countId);
  if (!counter) return;
  counter.textContent = len + ' / ' + recommended + ' recommended';
  counter.className = 'text-xs tabular-nums ' + (len > recommended
    ? 'text-amber-500 dark:text-amber-400'
    : 'text-slate-400 dark:text-slate-500');
}

// Init counts on page load
(function() {
  const fields = [
    { id: 'meta-page-title',  countId: 'meta-page-title-count',   recommended: 60  },
    { id: 'meta-description', countId: 'meta-description-count',  recommended: 160 },
  ];
  fields.forEach(function(f) {
    var el = document.getElementById(f.id);
    if (el) updateCharCount(el, f.countId, f.recommended);
  });
})();
</script>
