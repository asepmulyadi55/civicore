{{-- Section 3 — Events --}}
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
        {{ __('app.showing') }} {{ ($pagination['current_page'] - 1) * $pagination['per_page'] + 1 }}–{{ min($pagination['current_page'] * $pagination['per_page'], $pagination['total']) }}
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
