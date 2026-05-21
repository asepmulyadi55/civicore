{{-- Section — Footer --}}
<section class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
  <div class="flex items-center gap-3 px-6 py-4 border-b border-slate-100 dark:border-slate-800">
    <div class="w-9 h-9 rounded-xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center">
      <span class="material-icons text-slate-500 text-[20px]">web_asset</span>
    </div>
    <div>
      <h2 class="font-bold text-slate-900 dark:text-white text-base">{{ __('app.hp_section_footer') }}</h2>
      <p class="text-xs text-slate-500">{{ __('app.hp_section_footer_desc') }}</p>
    </div>
  </div>

  <form id="form-hp-footer" method="POST" action="{{ route('homepage.footer') }}" class="p-6 space-y-5" novalidate>
    @csrf

    {{-- Brand & Tagline --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
      <div class="space-y-1.5">
        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">{{ __('app.hp_footer_brand_name') }}</label>
        <input type="text" name="brand_name" maxlength="100"
          value="{{ old('brand_name', $footer['brand_name'] ?? '') }}"
          class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all"
          placeholder="e.g. Dwipapuri">
      </div>
      <div class="space-y-1.5">
        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">{{ __('app.hp_footer_tagline') }}</label>
        <input type="text" name="tagline" maxlength="300"
          value="{{ old('tagline', $footer['tagline'] ?? '') }}"
          class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all"
          placeholder="e.g. Cultivating a better lifestyle...">
      </div>
    </div>

    {{-- Quick Links --}}
    @php
      $defaultLinks = [
        ['label' => 'Resident Portal', 'url' => ''],
        ['label' => 'Event Calendar',  'url' => ''],
        ['label' => 'Amenities',       'url' => ''],
        ['label' => 'Privacy Policy',  'url' => ''],
      ];
      $savedLinks = old('links', $footer['links'] ?? $defaultLinks);
      while (count($savedLinks) < 4) { $savedLinks[] = ['label' => '', 'url' => '']; }
    @endphp
    <div class="space-y-3">
      <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">{{ __('app.hp_footer_quick_links') }}</label>
      <p class="text-xs text-slate-400">{{ __('app.hp_footer_quick_links_hint') }}</p>
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        @foreach($savedLinks as $i => $link)
          <div class="flex items-center gap-2 p-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50">
            <div class="flex-1 space-y-1.5">
              <input type="text" name="links[{{ $i }}][label]"
                value="{{ $link['label'] ?? '' }}"
                class="w-full px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all"
                placeholder="Label">
              <input type="url" name="links[{{ $i }}][url]"
                value="{{ $link['url'] ?? '' }}"
                class="w-full px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs text-slate-500 focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all"
                placeholder="https://...">
            </div>
          </div>
        @endforeach
      </div>
    </div>

    {{-- Contact --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
      <div class="space-y-1.5">
        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">{{ __('app.hp_footer_contact_email') }}</label>
        <input type="email" name="contact_email" maxlength="200"
          value="{{ old('contact_email', $footer['contact_email'] ?? '') }}"
          class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all"
          placeholder="e.g. hello@dwipapuri.com">
      </div>
      <div class="space-y-1.5">
        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">{{ __('app.hp_footer_contact_phone') }}</label>
        <input type="text" name="contact_phone" maxlength="50"
          value="{{ old('contact_phone', $footer['contact_phone'] ?? '') }}"
          class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all"
          placeholder="e.g. +62 123 4567 890">
      </div>
    </div>

    {{-- Social Links --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
      <div class="space-y-1.5">
        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">{{ __('app.hp_footer_facebook_url') }}</label>
        <input type="url" name="facebook_url" maxlength="500"
          value="{{ old('facebook_url', $footer['facebook_url'] ?? '') }}"
          class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all"
          placeholder="https://facebook.com/...">
      </div>
      <div class="space-y-1.5">
        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">{{ __('app.hp_footer_instagram_url') }}</label>
        <input type="url" name="instagram_url" maxlength="500"
          value="{{ old('instagram_url', $footer['instagram_url'] ?? '') }}"
          class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all"
          placeholder="https://instagram.com/...">
      </div>
    </div>

    {{-- Copyright & Bottom Note --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
      <div class="space-y-1.5">
        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">{{ __('app.hp_footer_copyright') }}</label>
        <input type="text" name="copyright" maxlength="300"
          value="{{ old('copyright', $footer['copyright'] ?? '') }}"
          class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all"
          placeholder="e.g. © 2025 Dwipapuri. All rights reserved.">
        <p class="text-xs text-slate-400">{{ __('app.hp_footer_copyright_hint') }}</p>
      </div>
      <div class="space-y-1.5">
        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">{{ __('app.hp_footer_bottom_note') }}</label>
        <input type="text" name="bottom_note" maxlength="300"
          value="{{ old('bottom_note', $footer['bottom_note'] ?? '') }}"
          class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all"
          placeholder="e.g. Built for a better community experience.">
        <p class="text-xs text-slate-400">{{ __('app.hp_footer_bottom_note_hint') }}</p>
      </div>
    </div>

    <div class="flex justify-end pt-2">
      <button type="submit"
        class="inline-flex items-center gap-2 px-5 py-2.5 bg-primary hover:bg-primary/90 text-white text-sm font-bold rounded-xl transition-all shadow-sm shadow-primary/20">
        <span class="material-icons text-base">save</span>
        {{ __('app.hp_save_footer') }}
      </button>
    </div>
  </form>
</section>
