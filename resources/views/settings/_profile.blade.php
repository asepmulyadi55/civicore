{{-- Profile tab --}}
<div id="tab-profile">
  <form method="POST" action="{{ route('settings.profile') }}" enctype="multipart/form-data" class="space-y-6">
    @csrf

    {{-- Photo --}}
    <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-6">
      <h2 class="text-sm font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide mb-4">
        {{ __('app.settings_photo') }}
      </h2>
      <div class="flex items-center gap-5">
        <div class="w-20 h-20 rounded-2xl overflow-hidden flex-shrink-0 bg-primary/10 border-2 border-white dark:border-slate-800 shadow-md">
          <img id="avatar-preview" src="{{ $user->avatarUrl() }}" alt="{{ $user->name }}"
            class="w-full h-full object-cover">
        </div>
        <div class="flex-1">
          <label for="avatar-upload"
            class="inline-flex items-center gap-2 cursor-pointer px-4 py-2 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-lg text-sm font-semibold transition-all border border-slate-200 dark:border-slate-700">
            <span class="material-icons text-sm">upload</span>
            {{ __('app.settings_upload_photo') }}
          </label>
          <input id="avatar-upload" type="file" name="avatar" accept="image/*" class="hidden"
            onchange="previewAvatar(event)">
          <p class="text-xs text-slate-400 mt-2">{{ __('app.settings_photo_hint') }}</p>
          @error('avatar') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
        </div>
      </div>
    </div>

    {{-- Identity --}}
    <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-6 space-y-5">
      <h2 class="text-sm font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide">
        {{ __('app.settings_identity') }}
      </h2>
      <div>
        <label for="input-name" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
          {{ __('app.settings_full_name') }}
        </label>
        <input type="text" name="name" id="input-name" value="{{ old('name', $user->name) }}"
          class="w-full px-3 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none transition-all">
        @error('name') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
      </div>
      <div>
        <label for="input-email" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
          {{ __('app.settings_email') }}
        </label>
        <input type="email" id="input-email" value="{{ $user->email }}" disabled
          class="w-full px-3 py-2.5 bg-slate-100 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 rounded-lg text-sm text-slate-400 cursor-not-allowed">
        <p class="text-xs text-slate-400 mt-1">{{ __('app.settings_email_readonly') }}</p>
      </div>
      <div>
        <label for="input-username" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
          {{ __('app.settings_username') }}
        </label>
        <input type="text" id="input-username" value="{{ $user->username }}" disabled
          class="w-full px-3 py-2.5 bg-slate-100 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 rounded-lg text-sm text-slate-400 cursor-not-allowed">
      </div>
    </div>

    {{-- Language --}}
    <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-6">
      <h2 class="text-sm font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide mb-4">
        {{ __('app.settings_language') }}
      </h2>
      <div class="grid grid-cols-2 gap-3">
        @foreach(['en' => ['flag' => '🇬🇧', 'label' => 'English'], 'id' => ['flag' => '🇮🇩', 'label' => 'Indonesian']] as $langCode => $lang)
          @php $isActive = (old('language', $user->language ?? 'en') === $langCode); @endphp
          <label for="lang-{{ $langCode }}"
            class="flex items-center gap-3 p-4 rounded-xl border-2 cursor-pointer transition-all {{ $isActive ? 'border-primary bg-primary/5' : 'border-slate-200 dark:border-slate-700 hover:border-primary/40' }}">
            <input type="radio" name="language" id="lang-{{ $langCode }}" value="{{ $langCode }}"
              class="sr-only" {{ $isActive ? 'checked' : '' }}
              onchange="updateLangCards()">
            <span class="text-2xl">{{ $lang['flag'] }}</span>
            <span class="font-semibold text-sm text-slate-800 dark:text-slate-200">{{ $lang['label'] }}</span>
            @if($isActive)
              <span class="ml-auto material-icons text-primary text-base">check_circle</span>
            @else
              <span class="ml-auto material-icons text-slate-300 text-base">radio_button_unchecked</span>
            @endif
          </label>
        @endforeach
      </div>
      @error('language') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
      <p class="text-xs text-slate-400 mt-3">{{ __('app.settings_lang_hint') }}</p>
    </div>

    <div class="flex justify-end">
      <button type="submit"
        class="flex items-center gap-2 bg-primary hover:bg-primary/90 text-white px-6 py-2.5 rounded-lg font-semibold transition-all shadow-sm shadow-primary/20 text-sm">
        <span class="material-icons text-sm">save</span>
        {{ __('app.settings_save_profile') }}
      </button>
    </div>
  </form>
</div>
