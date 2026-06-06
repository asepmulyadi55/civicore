{{-- Section — Buletin --}}
<section class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
  <div class="flex items-center gap-3 px-6 py-4 border-b border-slate-100 dark:border-slate-800">
    <div class="w-9 h-9 rounded-xl bg-sky-100 dark:bg-sky-900/30 flex items-center justify-center">
      <span class="material-icons text-sky-500 text-[20px]">article</span>
    </div>
    <div class="flex-1">
      <h2 class="font-bold text-slate-900 dark:text-white text-base">{{ __('app.hp_section_buletin') }}</h2>
      <p class="text-xs text-slate-500">{{ __('app.hp_section_buletin_desc') }}</p>
    </div>
    <span class="px-2.5 py-1 text-xs font-bold bg-sky-100 dark:bg-sky-900/30 text-sky-700 dark:text-sky-400 rounded-full">
      {{ $totalBuletin }}
    </span>
  </div>

  {{-- Display Settings form --}}
  <div class="p-6 border-b border-slate-100 dark:border-slate-800 bg-slate-50/60 dark:bg-slate-800/30">
    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-4">{{ __('app.hp_display_settings') }}</p>
    <form method="POST" action="{{ route('homepage.section-labels') }}" class="space-y-3">
      @csrf
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="space-y-1.5">
          <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">{{ __('app.hp_eyebrow_label') }} <span class="text-slate-400 font-normal text-xs">{{ __('app.hp_eyebrow_hint') }}</span></label>
          <input type="text" name="buletin_eyebrow" value="{{ $sectionLabels['buletin_eyebrow'] ?? 'Informasi' }}"
            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all"
            placeholder="e.g. Informasi">
        </div>
        <div class="space-y-1.5">
          <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">{{ __('app.hp_section_heading_label') }}</label>
          <input type="text" name="buletin_heading" value="{{ $sectionLabels['buletin_heading'] ?? 'Buletin' }}"
            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all"
            placeholder="e.g. Buletin">
        </div>
      </div>
      <div class="flex justify-end">
        <button type="submit"
          class="inline-flex items-center gap-2 px-4 py-2 bg-slate-700 hover:bg-slate-800 text-white text-sm font-bold rounded-xl transition-all">
          <span class="material-icons text-base">save</span>
          {{ __('app.hp_save_display') }}
        </button>
      </div>
    </form>
  </div>

  {{-- Add Buletin form --}}
  <div class="p-6 border-b border-slate-100 dark:border-slate-800">
    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-4">{{ __('app.hp_add_buletin') }}</p>
    <form id="form-hp-buletin-add" method="POST" action="{{ route('homepage.buletin.store') }}" class="space-y-4" enctype="multipart/form-data" novalidate>
      @csrf
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="space-y-1.5">
          <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">{{ __('app.hp_buletin_title_label') }} <span class="text-rose-500">*</span></label>
          <input type="text" id="hp-buletin-title" name="title"
            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all"
            placeholder="{{ __('app.hp_buletin_title_label') }}..." oninput="clearHpErr('err-hp-buletin-title')">
          <p id="err-hp-buletin-title" class="hidden mt-1 text-sm text-rose-500"></p>
        </div>
        <div class="space-y-1.5">
          <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">{{ __('app.hp_col_date') }}</label>
          <input type="date" name="date"
            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all dark:[color-scheme:dark]">
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
        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">{{ __('app.hp_buletin_image') }}</label>
        <label id="hp-buletin-img-label" class="flex flex-col items-center justify-center gap-2 w-full h-24 rounded-xl border-2 border-dashed border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-800/50 hover:border-primary/60 hover:bg-primary/5 transition-all cursor-pointer">
          <span class="material-icons text-slate-400 text-2xl">cloud_upload</span>
          <span class="text-xs font-semibold text-slate-500">{{ __('app.upload_image') }} <span class="text-slate-400 font-normal">{{ __('app.hp_upload_optional_hint') }}</span></span>
          <input type="file" name="image_file" id="hp-buletin-img-input" accept="image/*" class="sr-only"
            onchange="previewImage(this,'hp-buletin-img-preview','hp-buletin-img-label')">
        </label>
        <div id="hp-buletin-img-preview" class="hidden items-center gap-3 p-3 rounded-xl border border-primary/30 bg-primary/5">
          <img src="" alt="Preview" class="w-16 h-12 object-cover rounded-lg flex-shrink-0">
          <div class="flex-1 min-w-0">
            <p class="text-xs font-semibold text-primary">{{ __('app.hp_ready_to_upload') }}</p>
            <p class="text-xs text-slate-400 truncate"></p>
          </div>
          <button type="button" onclick="clearImageInput('hp-buletin-img-input','hp-buletin-img-preview','hp-buletin-img-label')" class="text-slate-400 hover:text-rose-500 transition-colors">
            <span class="material-icons text-lg">close</span>
          </button>
        </div>
      </div>
      <div class="flex justify-end">
        <button type="submit"
          class="inline-flex items-center gap-2 px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white text-sm font-bold rounded-xl transition-all">
          <span class="material-icons text-base">add</span>
          {{ __('app.hp_add_buletin') }}
        </button>
      </div>
    </form>
  </div>

  {{-- Search bar --}}
  <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800">
    <form method="GET" action="{{ route('homepage.index') }}" class="flex flex-wrap gap-3 items-center">
      <div class="relative flex-1 min-w-48">
        <span class="material-icons absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-[18px]">search</span>
        <input type="text" name="buletin_search" value="{{ $buletinPagination['search'] }}"
          placeholder="Search bulletins..."
          class="w-full pl-9 pr-4 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all">
      </div>
      {{-- keep current event filters when submitting buletin search --}}
      <input type="hidden" name="event_search" value="{{ $pagination['search'] }}">
      <input type="hidden" name="event_category" value="{{ $pagination['category'] }}">
      <button type="submit"
        class="px-4 py-2 bg-primary hover:bg-primary/90 text-white text-sm font-bold rounded-xl transition-all">
        Search
      </button>
      @if($buletinPagination['search'] !== '')
        <a href="{{ route('homepage.index') }}"
          class="px-4 py-2 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 text-sm font-medium rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 transition-all">
          Clear
        </a>
      @endif
    </form>
  </div>

  {{-- Buletin Table --}}
  <div class="overflow-x-auto">
    <table class="w-full text-sm">
      <thead>
        <tr class="bg-slate-50 dark:bg-slate-800/50">
          <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">{{ __('app.hp_col_title') }}</th>
          <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">{{ __('app.hp_col_date') }}</th>
          <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wide">{{ __('app.table_actions') }}</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
        @forelse($buletin as $item)
          @php
            $itemDate = !empty($item['date']) ? \Carbon\Carbon::parse($item['date']) : null;
          @endphp
          <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition-colors">
            <td class="px-6 py-3.5">
              <div class="flex items-center gap-3">
                @if(!empty($item['image_url']))
                  <img src="{{ $item['image_url'] }}" alt="" class="w-9 h-9 rounded-lg object-cover flex-shrink-0">
                @else
                  <div class="w-9 h-9 rounded-lg bg-sky-100 dark:bg-sky-900/30 flex items-center justify-center flex-shrink-0">
                    <span class="material-icons text-sky-500 text-[15px]">article</span>
                  </div>
                @endif
                <div class="min-w-0">
                  <span class="font-semibold text-slate-800 dark:text-slate-200 truncate max-w-xs block">{{ $item['title'] }}</span>
                  @if(!empty($item['description']))
                    <span class="text-xs text-slate-400 truncate max-w-xs block">{{ $item['description'] }}</span>
                  @endif
                </div>
              </div>
            </td>
            <td class="px-4 py-3.5 text-slate-500 text-xs whitespace-nowrap">
              {{ $itemDate ? $itemDate->format('d M Y') : '—' }}
            </td>
            <td class="px-4 py-3.5">
              <div class="flex items-center justify-end gap-1">
                @if(auth()->user()->can('homepage.edit'))
                <button type="button"
                  data-id="{{ $item['id'] }}"
                  data-title="{{ $item['title'] }}"
                  data-date="{{ $item['date'] ?? '' }}"
                  data-description="{{ $item['description'] ?? '' }}"
                  data-url="{{ $item['url'] ?? '' }}"
                  data-image-url="{{ $item['image_url'] ?? '' }}"
                  onclick="openBuletinEditModal(this)"
                  class="p-1.5 text-slate-400 hover:text-blue-500 hover:bg-blue-50 dark:hover:bg-blue-950/20 rounded-lg transition-colors"
                  title="Edit buletin">
                  <span class="material-icons text-[18px]">edit</span>
                </button>
                @endif
                @if(auth()->user()->can('homepage.delete'))
                <button type="button"
                  onclick="openBuletinDeleteModal('{{ $item['id'] }}', '{{ addslashes($item['title']) }}')"
                  class="p-1.5 text-slate-400 hover:text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-950/20 rounded-lg transition-colors"
                  title="Remove buletin">
                  <span class="material-icons text-[18px]">delete_outline</span>
                </button>
                @endif
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="3" class="px-6 py-10 text-center">
              <span class="material-icons text-3xl text-slate-300 dark:text-slate-600 block mb-2">article</span>
              <p class="text-sm text-slate-400">
                {{ $buletinPagination['search'] !== '' ? __('app.hp_no_buletin_search') : __('app.hp_no_buletin_yet') }}
              </p>
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  {{-- Pagination --}}
  @if($buletinPagination['last_page'] > 1)
    @php
      $baseParams = array_filter(['buletin_search' => $buletinPagination['search']]);
    @endphp
    <div class="px-6 py-4 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
      <p class="text-xs text-slate-500">
        {{ __('app.showing') }} {{ ($buletinPagination['current_page'] - 1) * $buletinPagination['per_page'] + 1 }}–{{ min($buletinPagination['current_page'] * $buletinPagination['per_page'], $buletinPagination['total']) }}
        {{ __('app.of') }} {{ $buletinPagination['total'] }} {{ __('app.hp_buletin_count') }}
      </p>
      <div class="flex items-center gap-1">
        @if($buletinPagination['current_page'] > 1)
          <a href="{{ route('homepage.index', array_merge($baseParams, ['buletin_page' => $buletinPagination['current_page'] - 1])) }}"
            class="px-3 py-1.5 text-sm text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-700 rounded-lg hover:border-primary/50 hover:text-primary transition-all">
            <span class="material-icons text-sm align-middle">chevron_left</span>
          </a>
        @else
          <span class="px-3 py-1.5 text-sm text-slate-300 dark:text-slate-600 border border-slate-200 dark:border-slate-700 rounded-lg cursor-not-allowed">
            <span class="material-icons text-sm align-middle">chevron_left</span>
          </span>
        @endif
        @for($p = max(1, $buletinPagination['current_page'] - 2); $p <= min($buletinPagination['last_page'], $buletinPagination['current_page'] + 2); $p++)
          @if($p === $buletinPagination['current_page'])
            <span class="px-3 py-1.5 text-sm font-bold text-white bg-primary border border-primary rounded-lg">{{ $p }}</span>
          @else
            <a href="{{ route('homepage.index', array_merge($baseParams, ['buletin_page' => $p])) }}"
              class="px-3 py-1.5 text-sm text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-700 rounded-lg hover:border-primary/50 hover:text-primary transition-all">{{ $p }}</a>
          @endif
        @endfor
        @if($buletinPagination['current_page'] < $buletinPagination['last_page'])
          <a href="{{ route('homepage.index', array_merge($baseParams, ['buletin_page' => $buletinPagination['current_page'] + 1])) }}"
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
