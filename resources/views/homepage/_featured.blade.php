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
