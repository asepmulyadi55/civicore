{{-- Homepage CMS Page --}}
<x-layouts.app :title="__('app.nav_homepage')"
  class="font-display bg-background-light dark:bg-background-dark text-slate-800 dark:text-slate-200 antialiased min-h-screen">

  <x-nav.sidebar active="homepage" />

  <div class="lg:pl-64 min-h-screen bg-background-light dark:bg-background-dark flex flex-col">

    {{-- Header --}}
    <header
      class="h-16 bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between px-6 lg:px-8">
      <div class="flex items-center gap-4">
        <button class="lg:hidden p-2 rounded-lg border border-slate-200 dark:border-slate-800"
          onclick="toggleSidebar()">
          <span class="material-icons text-slate-500">menu</span>
        </button>
        <h1 class="text-xl font-bold text-slate-900 dark:text-white">{{ __('app.nav_homepage') }}</h1>
        <span class="hidden sm:inline px-2.5 py-1 text-xs font-semibold bg-primary/10 text-primary rounded-lg">{{ $totalEvents }} {{ __('app.hp_section_events') }}</span>
      </div>
      <button class="p-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg hover:border-primary/50 transition-all"
        onclick="toggleDark()" title="Toggle dark mode">
        <span class="material-icons text-slate-500 text-[20px]">dark_mode</span>
      </button>
    </header>

    {{-- Body --}}
    <main class="flex-1 p-6 lg:p-8 space-y-8">

      {{-- Flash Messages --}}
      @if(session('success'))
        <div class="p-4 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 rounded-xl flex items-center gap-3">
          <span class="material-icons text-emerald-500">check_circle</span>
          <p class="text-sm text-emerald-700 dark:text-emerald-400">{{ session('success') }}</p>
        </div>
      @endif
      @if(session('error'))
        <div class="p-4 bg-rose-50 dark:bg-rose-900/20 border border-rose-200 dark:border-rose-800 rounded-xl flex items-center gap-3">
          <span class="material-icons text-rose-500">error</span>
          <p class="text-sm text-rose-700 dark:text-rose-400">{{ session('error') }}</p>
        </div>
      @endif

      {{-- Validation Errors --}}
      @if($errors->any())
        <div class="p-4 bg-rose-50 dark:bg-rose-900/20 border border-rose-200 dark:border-rose-800 rounded-xl">
          <div class="flex items-center gap-3 mb-2">
            <span class="material-icons text-rose-500">warning</span>
            <p class="text-sm font-semibold text-rose-700 dark:text-rose-400">Please fix the following errors:</p>
          </div>
          <ul class="list-disc list-inside text-sm text-rose-600 dark:text-rose-400 space-y-1 ml-7">
            @foreach($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
        </div>
      @endif

      {{-- ─────────────────────────────────────────────────────────────
           SECTION 1 — Hero
      ───────────────────────────────────────────────────────────────── --}}
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

      {{-- ─────────────────────────────────────────────────────────────
           SECTION 2 — Featured Event
      ───────────────────────────────────────────────────────────────── --}}
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
        <form id="form-hp-featured" method="POST" action="{{ route('homepage.featured-event') }}" class="p-6 space-y-5" novalidate>
          @csrf
          <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div class="space-y-1.5">
              <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">{{ __('app.hp_event_title_label') }} <span class="text-rose-500">*</span></label>
              <input type="text" id="hp-featured-title" name="title" value="{{ old('title', $featuredEvent['title'] ?? '') }}"
                class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all"
                placeholder="e.g. Dwipapuri Anniversary Gala" oninput="clearHpErr('err-hp-featured-title')">
              <p id="err-hp-featured-title" class="hidden mt-1 text-sm text-rose-500"></p>
            </div>
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
            <div class="space-y-1.5">
              <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">{{ __('app.status') }}</label>
              <select name="status"
                class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all">
                <option value="">{{ __('app.hp_select_status') }}</option>
                @foreach(['upcoming' => __('app.hp_status_upcoming'), 'ongoing' => __('app.hp_status_ongoing'), 'past' => __('app.hp_status_past')] as $val => $label)
                  <option value="{{ $val }}" {{ old('status', $featuredEvent['status'] ?? '') === $val ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
              </select>
            </div>
          </div>
          <div class="space-y-1.5">
            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">{{ __('app.hp_desc_label') }}</label>
            <textarea name="description" rows="3"
              class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all resize-none"
              placeholder="{{ __('app.hp_desc_label') }}...">{{ old('description', $featuredEvent['description'] ?? '') }}</textarea>
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

      {{-- ─────────────────────────────────────────────────────────────
           SECTION 3 — Events
      ───────────────────────────────────────────────────────────────── --}}
      <section class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="flex items-center gap-3 px-6 py-4 border-b border-slate-100 dark:border-slate-800">
          <div class="w-9 h-9 rounded-xl bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center">
            <span class="material-icons text-emerald-500 text-[20px]">event</span>
          </div>
          <div class="flex-1">
            <h2 class="font-bold text-slate-900 dark:text-white text-base">{{ __('app.hp_section_events') }}</h2>
            <p class="text-xs text-slate-500">{{ __('app.hp_section_events_desc') }}</p>
          </div>
          <span class="px-2.5 py-1 text-xs font-bold bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 rounded-full">
            {{ $totalEvents }}
          </span>
        </div>

        {{-- Add Event form --}}
        <div class="p-6 border-b border-slate-100 dark:border-slate-800">
          <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-4">{{ __('app.hp_add_event') }}</p>
          <form id="form-hp-event-add" method="POST" action="{{ route('homepage.events.store') }}" class="space-y-4" enctype="multipart/form-data" novalidate>
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
              <div class="space-y-1.5">
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">Title <span class="text-rose-500">*</span></label>
                <input type="text" id="hp-event-title" name="title"
                  class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all"
                  placeholder="Event title..." oninput="clearHpErr('err-hp-event-title')">
                <p id="err-hp-event-title" class="hidden mt-1 text-sm text-rose-500"></p>
              </div>
              <div class="space-y-1.5">
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">Date</label>
                <input type="date" name="date"
                  class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all dark:[color-scheme:dark]">
              </div>
              <div class="space-y-1.5">
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">{{ __('app.hp_event_cat_label') }}</label>
                <select name="category"
                  class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all">
                  <option value="">{{ __('app.hp_none_option') }}</option>
                  @foreach(['wellness' => __('app.hp_cat_wellness'), 'meetings' => __('app.hp_cat_meetings'), 'education' => __('app.hp_cat_education'), 'cultural' => __('app.hp_cat_cultural'), 'sports' => __('app.hp_cat_sports'), 'other' => __('app.hp_cat_other')] as $val => $label)
                    <option value="{{ $val }}">{{ $label }}</option>
                  @endforeach
                </select>
              </div>
            </div>
            <div class="space-y-1.5">
              <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">{{ __('app.hp_desc_label') }}</label>
              <input type="text" name="description"
                class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all"
                placeholder="{{ __('app.hp_desc_label') }}... ({{ trim(__('app.optional'), '()') }})">
            </div>
            <div class="space-y-1.5">
              <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">{{ __('app.hp_url_label') }} <span class="text-slate-400 font-normal text-xs">{{ __('app.hp_url_hint') }}</span></label>
              <input type="url" name="url"
                class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all"
                placeholder="https://... {{ __('app.optional') }}">
            </div>
            <div class="space-y-1.5">
              <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">{{ __('app.hp_event_image') }}</label>
              <label id="hp-event-img-label" class="flex flex-col items-center justify-center gap-2 w-full h-24 rounded-xl border-2 border-dashed border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-800/50 hover:border-primary/60 hover:bg-primary/5 transition-all cursor-pointer">
                <span class="material-icons text-slate-400 text-2xl">cloud_upload</span>
                <span class="text-xs font-semibold text-slate-500">{{ __('app.upload_image') }} <span class="text-slate-400 font-normal">{{ __('app.hp_upload_optional_hint') }}</span></span>
                <input type="file" name="image_file" id="hp-event-img-input" accept="image/*" class="sr-only"
                  onchange="previewImage(this,'hp-event-img-preview','hp-event-img-label')">
              </label>
              <div id="hp-event-img-preview" class="hidden items-center gap-3 p-3 rounded-xl border border-primary/30 bg-primary/5">
                <img src="" alt="Preview" class="w-16 h-12 object-cover rounded-lg flex-shrink-0">
                <div class="flex-1 min-w-0">
                  <p class="text-xs font-semibold text-primary">{{ __('app.hp_ready_to_upload') }}</p>
                  <p class="text-xs text-slate-400 truncate"></p>
                </div>
                <button type="button" onclick="clearImageInput('hp-event-img-input','hp-event-img-preview','hp-event-img-label')" class="text-slate-400 hover:text-rose-500 transition-colors">
                  <span class="material-icons text-lg">close</span>
                </button>
              </div>
            </div>
            <div class="flex justify-end">
              <button type="submit"
                class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold rounded-xl transition-all">
                <span class="material-icons text-base">add</span>
                {{ __('app.hp_add_event') }}
              </button>
            </div>
          </form>
        </div>

        {{-- Search & Filter bar --}}
        <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800">
          <form method="GET" action="{{ route('homepage.index') }}" class="flex flex-wrap gap-3 items-center">
            <div class="relative flex-1 min-w-48">
              <span class="material-icons absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-[18px]">search</span>
              <input type="text" name="event_search" value="{{ $pagination['search'] }}"
                placeholder="{{ __('app.hp_search_events') }}"
                class="w-full pl-9 pr-4 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all">
            </div>
            <select name="event_category"
              class="px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all">
              <option value="">{{ __('app.hp_all_categories') }}</option>
              @foreach(['wellness' => __('app.hp_cat_wellness'), 'meetings' => __('app.hp_cat_meetings'), 'education' => __('app.hp_cat_education'), 'cultural' => __('app.hp_cat_cultural'), 'sports' => __('app.hp_cat_sports'), 'other' => __('app.hp_cat_other')] as $val => $label)
                <option value="{{ $val }}" {{ $pagination['category'] === $val ? 'selected' : '' }}>{{ $label }}</option>
              @endforeach
            </select>
            <button type="submit"
              class="px-4 py-2 bg-primary hover:bg-primary/90 text-white text-sm font-bold rounded-xl transition-all">
              Search
            </button>
            @if($pagination['search'] !== '' || $pagination['category'] !== '')
              <a href="{{ route('homepage.index') }}"
                class="px-4 py-2 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 text-sm font-medium rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 transition-all">
                Clear
              </a>
            @endif
          </form>
        </div>

        {{-- Events Table --}}
        <div class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead>
              <tr class="bg-slate-50 dark:bg-slate-800/50">
                <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">{{ __('app.hp_col_title') }}</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">{{ __('app.hp_event_cat_label') }}</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">{{ __('app.hp_col_date') }}</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">{{ __('app.status') }}</th>
                <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wide">{{ __('app.table_actions') }}</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
              @php
                $catLabels = ['wellness'=>'Wellness','meetings'=>'Meetings','education'=>'Education','cultural'=>'Cultural','sports'=>'Sports','other'=>'Other'];
              @endphp
              @forelse($events as $event)
                @php
                  $today      = \Carbon\Carbon::today();
                  $eventDate  = !empty($event['date']) ? \Carbon\Carbon::parse($event['date']) : null;
                  $autoStatus = $eventDate ? ($eventDate->lt($today) ? 'past' : 'upcoming') : 'upcoming';
                  if (($event['status'] ?? '') === 'ongoing') $autoStatus = 'ongoing';
                @endphp
                <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition-colors">
                  <td class="px-6 py-3.5">
                    <div class="flex items-center gap-3">
                      @if(!empty($event['image_url']))
                        <img src="{{ $event['image_url'] }}" alt="" class="w-9 h-9 rounded-lg object-cover flex-shrink-0">
                      @else
                        <div class="w-9 h-9 rounded-lg bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center flex-shrink-0">
                          <span class="material-icons text-emerald-500 text-[15px]">event</span>
                        </div>
                      @endif
                      <span class="font-semibold text-slate-800 dark:text-slate-200 truncate max-w-xs">{{ $event['title'] }}</span>
                    </div>
                  </td>
                  <td class="px-4 py-3.5">
                    @if(!empty($event['category']))
                      <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-primary/10 text-primary">
                        {{ $catLabels[$event['category']] ?? ucfirst($event['category']) }}
                      </span>
                    @else
                      <span class="text-slate-400 text-xs">—</span>
                    @endif
                  </td>
                  <td class="px-4 py-3.5 text-slate-500 text-xs whitespace-nowrap">
                    {{ $eventDate ? $eventDate->format('d M Y') : '—' }}
                  </td>
                  <td class="px-4 py-3.5">
                    @if($autoStatus === 'past')
                      <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400">{{ __('app.hp_status_past') }}</span>
                    @elseif($autoStatus === 'ongoing')
                      <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-amber-100 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400">{{ __('app.hp_status_ongoing') }}</span>
                    @else
                      <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400">{{ __('app.hp_status_upcoming') }}</span>
                    @endif
                  </td>
                  <td class="px-4 py-3.5">
                    <div class="flex items-center justify-end gap-1">
                      @if(auth()->user()->can('homepage.edit'))
                      <button type="button"
                        data-id="{{ $event['id'] }}"
                        data-title="{{ $event['title'] }}"
                        data-date="{{ $event['date'] ?? '' }}"
                        data-description="{{ $event['description'] ?? '' }}"
                        data-category="{{ $event['category'] ?? '' }}"
                        data-url="{{ $event['url'] ?? '' }}"
                        data-image-url="{{ $event['image_url'] ?? '' }}"
                        data-status="{{ $event['status'] ?? 'upcoming' }}"
                        onclick="openEventEditModal(this)"
                        class="p-1.5 text-slate-400 hover:text-blue-500 hover:bg-blue-50 dark:hover:bg-blue-950/20 rounded-lg transition-colors"
                        title="Edit event">
                        <span class="material-icons text-[18px]">edit</span>
                      </button>
                      @endif
                      @if(auth()->user()->can('homepage.delete'))
                      <button type="button"
                        onclick="openEventDeleteModal('{{ $event['id'] }}', '{{ addslashes($event['title']) }}')"
                        class="p-1.5 text-slate-400 hover:text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-950/20 rounded-lg transition-colors"
                        title="Remove event">
                        <span class="material-icons text-[18px]">delete_outline</span>
                      </button>
                      @endif
                    </div>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="5" class="px-6 py-10 text-center">
                    <span class="material-icons text-3xl text-slate-300 dark:text-slate-600 block mb-2">event_busy</span>
                    <p class="text-sm text-slate-400">
                      {{ ($pagination['search'] !== '' || $pagination['category'] !== '') ? __('app.hp_no_events_search') : __('app.hp_no_events_yet') }}
                    </p>
                  </td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>

        {{-- Pagination --}}
        @if($pagination['last_page'] > 1)
          @php
            $baseParams = array_filter([
              'event_search'   => $pagination['search'],
              'event_category' => $pagination['category'],
            ]);
          @endphp
          <div class="px-6 py-4 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
            <p class="text-xs text-slate-500">
              {{ __('app.showing') }} {{ ($pagination['current_page'] - 1) * $pagination['per_page'] + 1 }}–3{{ min($pagination['current_page'] * $pagination['per_page'], $pagination['total']) }}
              {{ __('app.of') }} {{ $pagination['total'] }} {{ __('app.hp_events_count') }}
            </p>
            <div class="flex items-center gap-1">
              @if($pagination['current_page'] > 1)
                <a href="{{ route('homepage.index', array_merge($baseParams, ['event_page' => $pagination['current_page'] - 1])) }}"
                  class="px-3 py-1.5 text-sm text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-700 rounded-lg hover:border-primary/50 hover:text-primary transition-all">
                  <span class="material-icons text-sm align-middle">chevron_left</span>
                </a>
              @else
                <span class="px-3 py-1.5 text-sm text-slate-300 dark:text-slate-600 border border-slate-200 dark:border-slate-700 rounded-lg cursor-not-allowed">
                  <span class="material-icons text-sm align-middle">chevron_left</span>
                </span>
              @endif
              @for($p = max(1, $pagination['current_page'] - 2); $p <= min($pagination['last_page'], $pagination['current_page'] + 2); $p++)
                @if($p === $pagination['current_page'])
                  <span class="px-3 py-1.5 text-sm font-bold text-white bg-primary border border-primary rounded-lg">{{ $p }}</span>
                @else
                  <a href="{{ route('homepage.index', array_merge($baseParams, ['event_page' => $p])) }}"
                    class="px-3 py-1.5 text-sm text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-700 rounded-lg hover:border-primary/50 hover:text-primary transition-all">{{ $p }}</a>
                @endif
              @endfor
              @if($pagination['current_page'] < $pagination['last_page'])
                <a href="{{ route('homepage.index', array_merge($baseParams, ['event_page' => $pagination['current_page'] + 1])) }}"
                  class="px-3 py-1.5 text-sm text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-700 rounded-lg hover:border-primary/50 hover:text-primary transition-all">
                  <span class="material-icons text-sm align-middle">chevron_right</span>
                </a>
              @else
                <span class="px-3 py-1.5 text-sm text-slate-300 dark:text-slate-600 border border-slate-200 dark:border-slate-700 rounded-lg cursor-not-allowed">
                  <span class="material-icons text-sm align-middle">chevron_right</span>
                </span>
              @endif
            </div>
          </div>
        @endif
      </section>

      {{-- ─────────────────────────────────────────────────────────────
           SECTION 4 — About Section
      ───────────────────────────────────────────────────────────────── --}}
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
            // Always ensure 4 rows
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

    </main>
  </div>

  <script>
    function clearHpErr(id) {
      const el = document.getElementById(id); if (el) el.classList.add('hidden');
    }
    function showHpErr(id, msg) {
      const el = document.getElementById(id); if (el) { el.textContent = msg; el.classList.remove('hidden'); }
    }
    function validateHpRequired(inputId, errId, label) {
      const el = document.getElementById(inputId);
      if (!el || !el.value.trim()) { showHpErr(errId, label + ' is required.'); return false; }
      clearHpErr(errId); return true;
    }
    document.addEventListener('DOMContentLoaded', function () {
      document.getElementById('form-hp-hero').addEventListener('submit', function(e) {
        if (!validateHpRequired('hp-hero-title', 'err-hp-hero-title', 'Title')) e.preventDefault();
      });
      document.getElementById('form-hp-featured').addEventListener('submit', function(e) {
        if (!validateHpRequired('hp-featured-title', 'err-hp-featured-title', 'Event title')) e.preventDefault();
      });
      document.getElementById('form-hp-event-add').addEventListener('submit', function(e) {
        if (!validateHpRequired('hp-event-title', 'err-hp-event-title', 'Title')) e.preventDefault();
      });
      document.getElementById('form-hp-about').addEventListener('submit', function(e) {
        if (!validateHpRequired('hp-about-content', 'err-hp-about-content', 'About content')) e.preventDefault();
      });
      document.getElementById('event-edit-form').addEventListener('submit', function(e) {
        if (!validateHpRequired('edit-event-title', 'err-hp-edit-title', 'Title')) e.preventDefault();
      });
      document.getElementById('event-edit-modal').addEventListener('click', function (e) {
        if (e.target === this) closeEventEditModal();
      });
      document.getElementById('event-delete-modal').addEventListener('click', function (e) {
        if (e.target === this) closeEventDeleteModal();
      });
    });

    function previewImage(input, previewContainerId, labelId) {
      const file = input.files[0];
      if (!file) return;

      const reader = new FileReader();
      reader.onload = function(e) {
        const container = document.getElementById(previewContainerId);
        container.querySelector('img').src = e.target.result;
        const filenameEl = container.querySelector('p.truncate');
        if (filenameEl) filenameEl.textContent = file.name;
        container.classList.remove('hidden');
        container.classList.add('flex');
        document.getElementById(labelId).classList.add('hidden');
      };
      reader.readAsDataURL(file);
    }

    function clearImageInput(inputId, previewContainerId, labelId) {
      document.getElementById(inputId).value = '';
      const container = document.getElementById(previewContainerId);
      container.classList.add('hidden');
      container.classList.remove('flex');
      document.getElementById(labelId).classList.remove('hidden');
    }

    // ── Event Edit Modal ────────────────────────────────────────────────────
    const eventUpdateUrlTpl = '{{ route('homepage.events.update', '__id__') }}';
    const eventDeleteUrlTpl = '{{ route('homepage.events.destroy', '__id__') }}';

    function openEventEditModal(btn) {
      const eventEditModal = document.getElementById('event-edit-modal');
      const eventEditForm  = document.getElementById('event-edit-form');
      const { id, title, date, description, category, url, imageUrl, status } = btn.dataset;
      eventEditForm.action = eventUpdateUrlTpl.replace('__id__', id);
      document.getElementById('edit-event-title').value       = title       || '';
      document.getElementById('edit-event-date').value        = date        || '';
      document.getElementById('edit-event-description').value = description || '';
      document.getElementById('edit-event-category').value    = category    || '';
      document.getElementById('edit-event-url').value         = url         || '';
      // status is now derived from date on the server
      // Show current image if present, reset file input
      clearImageInput('edit-event-img-input', 'edit-event-img-preview', 'edit-event-img-label');
      const currentWrap  = document.getElementById('edit-event-img-current');
      const currentThumb = document.getElementById('edit-event-img-current-thumb');
      const currentUrlEl = document.getElementById('edit-event-img-current-url');
      if (imageUrl) {
        currentThumb.src         = imageUrl;
        currentUrlEl.textContent = imageUrl;
        currentWrap.classList.remove('hidden');
        currentWrap.classList.add('flex');
      } else {
        currentWrap.classList.add('hidden');
        currentWrap.classList.remove('flex');
      }
      eventEditModal.classList.remove('hidden');
      document.body.style.overflow = 'hidden';
    }

    function closeEventEditModal() {
      document.getElementById('event-edit-modal').classList.add('hidden');
      document.body.style.overflow = '';
    }

    function openEventDeleteModal(id, title) {
      const modal = document.getElementById('event-delete-modal');
      document.getElementById('event-delete-body').textContent = '{{ __('app.hp_event_delete_body_before') }} "' + title + '" {{ __('app.hp_event_delete_body_after') }}';
      document.getElementById('event-delete-form').action = eventDeleteUrlTpl.replace('__id__', id);
      modal.classList.remove('hidden');
      document.body.style.overflow = 'hidden';
    }

    function closeEventDeleteModal() {
      document.getElementById('event-delete-modal').classList.add('hidden');
      document.body.style.overflow = '';
    }

  </script>

  {{-- ── Event Edit Modal ──────────────────────────────────────────────────── --}}
  <div id="event-edit-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4"
    style="background:rgba(0,0,0,0.5);backdrop-filter:blur(4px)">
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-2xl w-full max-w-lg">

      {{-- Header --}}
      <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 dark:border-slate-800">
        <div class="flex items-center gap-2">
          <span class="material-icons text-blue-500 text-[20px]">edit_calendar</span>
          <h3 class="font-bold text-slate-900 dark:text-white text-base">{{ __('app.hp_edit_event_title') }}</h3>
        </div>
        <button type="button" onclick="closeEventEditModal()"
          class="p-1.5 text-slate-400 hover:text-slate-700 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg transition-colors">
          <span class="material-icons text-[20px]">close</span>
        </button>
      </div>

      {{-- Form --}}
      <form id="event-edit-form" method="POST" action="" class="p-6 space-y-4" enctype="multipart/form-data" novalidate>
        @csrf @method('PUT')
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div class="sm:col-span-2 space-y-1.5">
            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">
              {{ __('app.hp_col_title') }} <span class="text-rose-500">*</span>
            </label>
            <input type="text" id="edit-event-title" name="title"
              class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all" oninput="clearHpErr('err-hp-edit-title')">
            <p id="err-hp-edit-title" class="hidden mt-1 text-sm text-rose-500"></p>
          </div>
          <div class="space-y-1.5">
            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">{{ __('app.hp_col_date') }}</label>
            <input type="date" id="edit-event-date" name="date"
              class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all dark:[color-scheme:dark]">
          </div>
          <div class="space-y-1.5">
            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">{{ __('app.hp_event_cat_label') }}</label>
            <select id="edit-event-category" name="category"
              class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all">
              <option value="">{{ __('app.hp_none_option') }}</option>
              <option value="wellness">{{ __('app.hp_cat_wellness') }}</option>
              <option value="meetings">{{ __('app.hp_cat_meetings') }}</option>
              <option value="education">{{ __('app.hp_cat_education') }}</option>
              <option value="cultural">{{ __('app.hp_cat_cultural') }}</option>
              <option value="sports">{{ __('app.hp_cat_sports') }}</option>
              <option value="other">{{ __('app.hp_cat_other') }}</option>
            </select>
          </div>
          <div class="sm:col-span-2 space-y-1.5">
            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">{{ __('app.hp_desc_label') }}</label>
            <input type="text" id="edit-event-description" name="description"
              class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all"
              placeholder="Short description... (optional)">
          </div>
          <div class="sm:col-span-2 space-y-1.5">
            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">{{ __('app.hp_url_label') }} <span class="text-slate-400 font-normal text-xs">{{ __('app.hp_url_hint') }}</span></label>
            <input type="url" id="edit-event-url" name="url"
              class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all"
              placeholder="https://... (optional)">
          </div>
          <div class="sm:col-span-2 space-y-1.5">
            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">{{ __('app.hp_event_image') }}</label>
            <div id="edit-event-img-current" class="hidden items-center gap-4 p-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 mb-2">
              <img id="edit-event-img-current-thumb" src="" alt="Current" class="w-20 h-14 object-cover rounded-lg border border-slate-200 dark:border-slate-700 flex-shrink-0">
              <div class="flex-1 min-w-0">
                <p class="text-xs font-semibold text-slate-600 dark:text-slate-400">{{ __('app.hp_current_image') }}</p>
                <p class="text-xs text-slate-400 truncate" id="edit-event-img-current-url"></p>
              </div>
            </div>
            <label id="edit-event-img-label" class="flex flex-col items-center justify-center gap-2 w-full h-24 rounded-xl border-2 border-dashed border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-800/50 hover:border-primary/60 hover:bg-primary/5 transition-all cursor-pointer">
              <span class="material-icons text-slate-400 text-2xl">cloud_upload</span>
              <span class="text-xs font-semibold text-slate-500">{{ __('app.hp_upload_new_image') }} <span class="text-slate-400 font-normal">{{ __('app.hp_upload_optional_hint') }}</span></span>
              <input type="file" name="image_file" id="edit-event-img-input" accept="image/*" class="sr-only"
                onchange="previewImage(this,'edit-event-img-preview','edit-event-img-label')">
            </label>
            <div id="edit-event-img-preview" class="hidden items-center gap-3 p-3 rounded-xl border border-primary/30 bg-primary/5">
              <img src="" alt="Preview" class="w-16 h-12 object-cover rounded-lg flex-shrink-0">
              <div class="flex-1 min-w-0">
                <p class="text-xs font-semibold text-primary">{{ __('app.hp_ready_to_upload') }}</p>
                <p class="text-xs text-slate-400 truncate"></p>
              </div>
              <button type="button" onclick="clearImageInput('edit-event-img-input','edit-event-img-preview','edit-event-img-label')" class="text-slate-400 hover:text-rose-500 transition-colors">
                <span class="material-icons text-lg">close</span>
              </button>
            </div>
          </div>
        </div>
        <div class="flex justify-end gap-3 pt-2">
          <button type="button" onclick="closeEventEditModal()"
            class="px-4 py-2 text-sm font-semibold text-slate-600 dark:text-slate-400 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 rounded-xl transition-colors">
            {{ __('app.btn_cancel') }}
          </button>
          <button type="submit"
            class="inline-flex items-center gap-2 px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold rounded-xl transition-all">
            <span class="material-icons text-base">save</span>
            {{ __('app.btn_save_changes') }}
          </button>
        </div>
      </form>
    </div>
  </div>

  {{-- ── Event Delete Modal ──────────────────────────────────────────────── --}}
  <div id="event-delete-modal"
    class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-2xl w-full max-w-sm overflow-hidden
      transform transition-all duration-200">
      <div class="flex flex-col items-center pt-8 pb-5 px-6 text-center">
        <div class="w-16 h-16 rounded-full bg-rose-100 dark:bg-rose-900/30 flex items-center justify-center mb-4">
          <span class="material-icons text-3xl text-rose-600">delete_outline</span>
        </div>
        <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-2">{{ __('app.hp_remove_event_title') }}</h3>
        <p id="event-delete-body" class="text-sm text-slate-500 dark:text-slate-400 leading-relaxed"></p>
      </div>
      <div class="flex gap-3 px-6 pb-6">
        <button onclick="closeEventDeleteModal()"
          class="flex-1 px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 text-sm font-semibold
            text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition-all duration-150">
          {{ __('app.btn_cancel') }}
        </button>
        <form id="event-delete-form" method="POST" action="" class="flex-1">
          @csrf @method('DELETE')
          <button type="submit"
            class="w-full px-4 py-2.5 rounded-xl text-sm font-bold text-white
              bg-rose-600 hover:bg-rose-700 active:bg-rose-800 transition-all duration-150">
            {{ __('app.hp_btn_yes_remove') }}
          </button>
        </form>
      </div>
    </div>
  </div>

</x-layouts.app>
