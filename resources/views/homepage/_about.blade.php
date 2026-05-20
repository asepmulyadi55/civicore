{{-- Section 4 — About --}}
<section class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
  <div class="flex items-center gap-3 px-6 py-4 border-b border-slate-100 dark:border-slate-800">
    <div class="w-9 h-9 rounded-xl bg-violet-100 dark:bg-violet-900/30 flex items-center justify-center">
      <span class="material-icons text-violet-500 text-[20px]">info</span>
    </div>
    <div>
      <h2 class="font-bold text-slate-900 dark:text-white text-base">{{ __('app.hp_section_about') }}</h2>
      <p class="text-xs text-slate-500">{{ __('app.hp_section_about_desc') }}</p>
    </div>
  </div>
  <form id="form-hp-about" method="POST" action="{{ route('homepage.about') }}" class="p-6 space-y-5" enctype="multipart/form-data" novalidate>
    @csrf
    {{-- Badge & Heading --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
      <div class="space-y-1.5">
        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">Badge Text</label>
        <input type="text" name="badge" maxlength="60"
          value="{{ old('badge', $about['badge'] ?? 'Our Identity') }}"
          class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all"
          placeholder="e.g. Our Identity">
        <p class="text-xs text-slate-400">Small label shown above the heading.</p>
      </div>
      <div class="space-y-1.5">
        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">Section Heading <span class="text-rose-500">*</span></label>
        <input type="text" name="heading" maxlength="120"
          value="{{ old('heading', $about['heading'] ?? 'Elevating Residential Living at Dwipapuri') }}"
          class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all"
          placeholder="e.g. Elevating Residential Living...">
      </div>
    </div>

    {{-- CTA Buttons --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
      <div class="space-y-1.5">
        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">Primary Button Label</label>
        <input type="text" name="btn1_label" maxlength="60"
          value="{{ old('btn1_label', $about['btn1_label'] ?? 'Explore Amenities') }}"
          class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all"
          placeholder="e.g. Explore Amenities">
        <input type="url" name="btn1_url" maxlength="500"
          value="{{ old('btn1_url', $about['btn1_url'] ?? '') }}"
          class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all"
          placeholder="https://... (leave blank = no link)">
      </div>
      <div class="space-y-1.5">
        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">Secondary Button Label</label>
        <input type="text" name="btn2_label" maxlength="60"
          value="{{ old('btn2_label', $about['btn2_label'] ?? 'Our History') }}"
          class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all"
          placeholder="e.g. Our History">
        <input type="url" name="btn2_url" maxlength="500"
          value="{{ old('btn2_url', $about['btn2_url'] ?? '') }}"
          class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all"
          placeholder="https://... (leave blank = no link)">
      </div>
    </div>

    {{-- About Content --}}
    <div class="space-y-1.5">
      <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">{{ __('app.hp_about_content') }} <span class="text-rose-500">*</span></label>
      <textarea id="hp-about-content" name="content" rows="6"
        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all resize-none"
        placeholder="Write about the community, its history, values, and vision..." oninput="clearHpErr('err-hp-about-content')">{{ old('content', $about['content'] ?? '') }}</textarea>
      <p id="err-hp-about-content" class="hidden mt-1 text-sm text-rose-500"></p>
      <p class="text-xs text-slate-400">{{ __('app.hp_about_hint') }}</p>
    </div>
    <div class="space-y-1.5">
      <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">{{ __('app.hp_about_image') }}</label>
      @if(!empty($about['image_url']))
        <div class="flex items-center gap-4 p-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50">
          <img src="{{ $about['image_url'] }}" alt="Current about image"
            class="w-24 h-16 object-cover rounded-lg border border-slate-200 dark:border-slate-700 flex-shrink-0">
          <div class="flex-1 min-w-0">
            <p class="text-xs font-semibold text-slate-600 dark:text-slate-400">{{ __('app.current_image') }}</p>
            <p class="text-xs text-slate-400 truncate">{{ $about['image_url'] }}</p>
          </div>
        </div>
      @endif
      <label class="flex flex-col items-center justify-center gap-2 w-full h-28 rounded-xl border-2 border-dashed border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-800/50 hover:border-primary/60 hover:bg-primary/5 transition-all cursor-pointer"
        id="about-img-label">
        <span class="material-icons text-slate-400 text-3xl">cloud_upload</span>
        <span class="text-sm font-semibold text-slate-500">{{ __('app.upload_image') }}</span>
        <span class="text-xs text-slate-400">{{ __('app.image_hint') }}</span>
        <input type="file" name="image_url" id="about-img-input" accept="image/*" class="sr-only"
          onchange="previewImage(this, 'about-img-preview', 'about-img-label')">
      </label>
      <div id="about-img-preview" class="hidden items-center gap-3 p-3 rounded-xl border border-primary/30 bg-primary/5">
        <img src="" alt="Preview" class="w-20 h-14 object-cover rounded-lg flex-shrink-0">
        <div class="flex-1 min-w-0">
          <p class="text-xs font-semibold text-primary">{{ __('app.hp_ready_to_upload') }}</p>
          <p class="text-xs text-slate-400 truncate" id="about-img-filename"></p>
        </div>
        <button type="button" onclick="clearImageInput('about-img-input','about-img-preview','about-img-label')"
          class="text-slate-400 hover:text-rose-500 transition-colors">
          <span class="material-icons text-lg">close</span>
        </button>
      </div>
      <p class="text-xs text-slate-400">{{ __('app.hp_about_image_hint') }}</p>
    </div>

    {{-- Stats Grid --}}
    @php
      $defaultStats = [
        ['value' => '500+',    'label' => 'Residents'],
        ['value' => '24/7',    'label' => 'Security'],
        ['value' => '12',      'label' => 'Parks'],
        ['value' => 'Monthly', 'label' => 'Events'],
      ];
      $savedStats = old('stats', $about['stats'] ?? $defaultStats);
      while (count($savedStats) < 4) { $savedStats[] = ['value' => '', 'label' => '']; }
    @endphp
    <div class="space-y-3">
      <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">{{ __('app.hp_stats_cards') }}</label>
      <p class="text-xs text-slate-400">{{ __('app.hp_stats_hint') }}</p>
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        @foreach($savedStats as $i => $stat)
          <div class="flex items-center gap-2 p-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50">
            <div class="flex-1 space-y-1.5">
              <input type="text" name="stats[{{ $i }}][value]"
                value="{{ $stat['value'] ?? '' }}"
                class="w-full px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm font-bold focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all"
                placeholder="e.g. 500+">
              <input type="text" name="stats[{{ $i }}][label]"
                value="{{ $stat['label'] ?? '' }}"
                class="w-full px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs text-slate-500 focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all"
                placeholder="e.g. Residents">
            </div>
          </div>
        @endforeach
      </div>
    </div>

    <div class="flex justify-end pt-2">
      <button type="submit"
        class="inline-flex items-center gap-2 px-5 py-2.5 bg-primary hover:bg-primary/90 text-white text-sm font-bold rounded-xl transition-all shadow-sm shadow-primary/20">
        <span class="material-icons text-base">save</span>
        {{ __('app.hp_save_about') }}
      </button>
    </div>
  </form>
</section>
