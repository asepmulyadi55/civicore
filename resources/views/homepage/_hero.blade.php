{{-- Section 1 — Hero --}}
<section class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
  <div class="flex items-center gap-3 px-6 py-4 border-b border-slate-100 dark:border-slate-800">
    <div class="w-9 h-9 rounded-xl bg-primary/10 flex items-center justify-center">
      <span class="material-icons text-primary text-[20px]">image</span>
    </div>
    <div>
      <h2 class="font-bold text-slate-900 dark:text-white text-base">{{ __('app.hp_section_hero') }}</h2>
      <p class="text-xs text-slate-500">{{ __('app.hp_section_hero_desc') }}</p>
    </div>
  </div>
  <form id="form-hp-hero" method="POST" action="{{ route('homepage.hero') }}" class="p-6 space-y-5" enctype="multipart/form-data" novalidate>
    @csrf
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
      <div class="space-y-1.5">
        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">Title <span class="text-rose-500">*</span></label>
        <input type="text" id="hp-hero-title" name="title" value="{{ old('title', $hero['title'] ?? '') }}"
          class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all"
          placeholder="e.g. Welcome to Dwipapuri" oninput="clearHpErr('err-hp-hero-title')">
        <p id="err-hp-hero-title" class="hidden mt-1 text-sm text-rose-500"></p>
      </div>
      <div class="space-y-1.5">
        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">{{ __('app.hp_subtitle_label') }}</label>
        <input type="text" name="subtitle" value="{{ old('subtitle', $hero['subtitle'] ?? '') }}"
          class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all"
          placeholder="e.g. A vibrant community hub">
      </div>
      <div class="space-y-1.5">
        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">{{ __('app.hp_cta_text') }}</label>
        <input type="text" name="cta_text" value="{{ old('cta_text', $hero['cta_text'] ?? '') }}"
          class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all"
          placeholder="e.g. Explore Events">
      </div>
      <div class="space-y-1.5">
        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">{{ __('app.hp_cta_url') }}</label>
        <input type="text" name="cta_url" value="{{ old('cta_url', $hero['cta_url'] ?? '') }}"
          class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all"
          placeholder="e.g. /events or https://...">
      </div>
    </div>
    <div class="space-y-1.5">
      <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">{{ __('app.hp_bg_image') }}</label>
      @if(!empty($hero['bg_image']))
        <div class="flex items-center gap-4 p-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50">
          <img src="{{ $hero['bg_image'] }}" alt="Current hero background"
            class="w-24 h-16 object-cover rounded-lg border border-slate-200 dark:border-slate-700 flex-shrink-0">
          <div class="flex-1 min-w-0">
            <p class="text-xs font-semibold text-slate-600 dark:text-slate-400">{{ __('app.current_image') }}</p>
            <p class="text-xs text-slate-400 truncate">{{ $hero['bg_image'] }}</p>
          </div>
        </div>
      @endif
      <label class="flex flex-col items-center justify-center gap-2 w-full h-28 rounded-xl border-2 border-dashed border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-800/50 hover:border-primary/60 hover:bg-primary/5 transition-all cursor-pointer"
        id="hero-bg-label">
        <span class="material-icons text-slate-400 text-3xl">cloud_upload</span>
        <span class="text-sm font-semibold text-slate-500">{{ __('app.upload_image') }}</span>
        <span class="text-xs text-slate-400">{{ __('app.image_hint') }}</span>
        <input type="file" name="bg_image" id="hero-bg-input" accept="image/*" class="sr-only"
          onchange="previewImage(this, 'hero-bg-preview', 'hero-bg-label')">
      </label>
      <div id="hero-bg-preview" class="hidden items-center gap-3 p-3 rounded-xl border border-primary/30 bg-primary/5">
        <img src="" alt="Preview" class="w-20 h-14 object-cover rounded-lg flex-shrink-0">
        <div class="flex-1 min-w-0">
          <p class="text-xs font-semibold text-primary">{{ __('app.hp_ready_to_upload') }}</p>
          <p class="text-xs text-slate-400 truncate" id="hero-bg-filename"></p>
        </div>
        <button type="button" onclick="clearImageInput('hero-bg-input','hero-bg-preview','hero-bg-label')"
          class="text-slate-400 hover:text-rose-500 transition-colors">
          <span class="material-icons text-lg">close</span>
        </button>
      </div>
      <p class="text-xs text-slate-400">{{ __('app.hp_image_keep_hint') }}</p>
    </div>
    <div class="flex justify-end pt-2">
      <button type="submit"
        class="inline-flex items-center gap-2 px-5 py-2.5 bg-primary hover:bg-primary/90 text-white text-sm font-bold rounded-xl transition-all shadow-sm shadow-primary/20">
        <span class="material-icons text-base">save</span>
        {{ __('app.hp_save_hero') }}
      </button>
    </div>
  </form>
</section>
