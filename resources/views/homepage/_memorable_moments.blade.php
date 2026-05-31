{{-- Section: Memorable Moments --}}
<section class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
  <div class="flex items-center gap-3 px-6 py-4 border-b border-slate-100 dark:border-slate-800">
    <div class="w-9 h-9 rounded-xl bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center">
      <span class="material-icons text-indigo-500 text-[20px]">photo_library</span>
    </div>
    <div>
      <h2 class="font-bold text-slate-900 dark:text-white text-base">{{ __('app.hp_section_moments') }}</h2>
      <p class="text-xs text-slate-500">{{ __('app.hp_section_moments_desc') }}</p>
    </div>
  </div>

  <form id="form-hp-moments" method="POST" action="{{ route('homepage.memorable-moments') }}"
    class="p-6 space-y-6" enctype="multipart/form-data" novalidate>
    @csrf

    {{-- Title / Subtitle / Archive URL --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
      <div class="space-y-1.5">
        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">Eyebrow Label</label>
        <input type="text" name="eyebrow" value="{{ old('eyebrow', $memorableMoments['eyebrow'] ?? 'The Gallery') }}"
          class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all"
          placeholder="e.g. The Gallery">
      </div>
      <div class="space-y-1.5">
        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">{{ __('app.hp_moments_title') }}</label>
        <input type="text" name="title" value="{{ old('title', $memorableMoments['title'] ?? '') }}"
          class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all"
          placeholder="e.g. Memorable Moments">
      </div>
      <div class="space-y-1.5 md:col-span-2">
        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">{{ __('app.hp_moments_subtitle') }}</label>
        <input type="text" name="subtitle" value="{{ old('subtitle', $memorableMoments['subtitle'] ?? '') }}"
          class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all"
          placeholder="e.g. A look back at the experiences that define our community.">
      </div>
      <div class="space-y-1.5 md:col-span-2">
        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">{{ __('app.hp_moments_archive_url') }}</label>
        <input type="url" name="archive_url" value="{{ old('archive_url', $memorableMoments['archive_url'] ?? '') }}"
          class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all"
          placeholder="https://...">
        <p class="text-xs text-slate-400">{{ __('app.hp_moments_archive_url_hint') }}</p>
      </div>
    </div>

    {{-- Gallery Images (4 slots) --}}
    <div class="space-y-3">
      <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">{{ __('app.hp_moments_images') }}</label>
      <p class="text-xs text-slate-400">{{ __('app.hp_moments_images_hint') }}</p>

      @php
        $slots = [
          ['label' => '1 — Large (left, spans 2 rows)', 'icon' => 'crop_landscape'],
          ['label' => '2 — Wide (top-right)',            'icon' => 'panorama_wide_angle'],
          ['label' => '3 — Small (bottom-right)',        'icon' => 'crop_square'],
          ['label' => '4 — Small (bottom-right)',        'icon' => 'crop_square'],
        ];
        $savedImages = $memorableMoments['images'] ?? [];
      @endphp

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        @foreach($slots as $i => $slot)
          @php $existing = $savedImages[$i] ?? null; @endphp
          <div class="rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 p-4 space-y-3">
            <div class="flex items-center gap-2">
              <span class="material-icons text-slate-400 text-[18px]">{{ $slot['icon'] }}</span>
              <span class="text-xs font-semibold text-slate-600 dark:text-slate-400">{{ $slot['label'] }}</span>
            </div>

            {{-- Current image preview --}}
            @if(!empty($existing['url']))
              <div class="flex items-center gap-3 p-2 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800">
                <img src="{{ $existing['url'] }}" alt="Current slot {{ $i + 1 }}"
                  class="w-16 h-12 object-cover rounded-lg flex-shrink-0">
                <div class="flex-1 min-w-0">
                  <p class="text-xs font-semibold text-slate-600 dark:text-slate-400">{{ __('app.current_image') }}</p>
                  <p class="text-xs text-slate-400 truncate">{{ $existing['url'] }}</p>
                </div>
              </div>
            @endif

            {{-- Upload --}}
            <label id="moments-img-label-{{ $i }}"
              class="flex flex-col items-center justify-center gap-1.5 w-full h-20 rounded-xl border-2 border-dashed border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 hover:border-primary/60 hover:bg-primary/5 transition-all cursor-pointer">
              <span class="material-icons text-slate-400 text-2xl">cloud_upload</span>
              <span class="text-xs font-semibold text-slate-500">{{ __('app.upload_image') }}</span>
              <input type="file" name="images[{{ $i }}]" id="moments-img-input-{{ $i }}" accept="image/*" class="sr-only"
                onchange="previewImage(this,'moments-img-preview-{{ $i }}','moments-img-label-{{ $i }}')">
            </label>
            <div id="moments-img-preview-{{ $i }}" class="hidden items-center gap-3 p-2 rounded-xl border border-primary/30 bg-primary/5">
              <img src="" alt="Preview" class="w-14 h-10 object-cover rounded-lg flex-shrink-0">
              <div class="flex-1 min-w-0">
                <p class="text-xs font-semibold text-primary">{{ __('app.hp_ready_to_upload') }}</p>
                <p class="text-xs text-slate-400 truncate"></p>
              </div>
              <button type="button"
                onclick="clearImageInput('moments-img-input-{{ $i }}','moments-img-preview-{{ $i }}','moments-img-label-{{ $i }}')"
                class="text-slate-400 hover:text-rose-500 transition-colors">
                <span class="material-icons text-lg">close</span>
              </button>
            </div>

            {{-- Caption --}}
            <input type="text" name="captions[{{ $i }}]"
              value="{{ old('captions.' . $i, $existing['caption'] ?? '') }}"
              class="w-full px-3 py-2 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all"
              placeholder="{{ __('app.hp_moments_image_caption') }} ({{ trim(__('app.optional'), '()') }})">
          </div>
        @endforeach
      </div>
    </div>

    <div class="flex justify-end pt-2">
      <button type="submit"
        class="inline-flex items-center gap-2 px-5 py-2.5 bg-primary hover:bg-primary/90 text-white text-sm font-bold rounded-xl transition-all shadow-sm shadow-primary/20">
        <span class="material-icons text-base">save</span>
        {{ __('app.hp_save_moments') }}
      </button>
    </div>
  </form>
</section>
