{{-- posyandu/_table.blade.php --}}
@php
  use App\Http\Controllers\PosyanduController;
  $categories = PosyanduController::translatedCategories();

  $colorMap = [
    'pink'    => 'bg-pink-100 dark:bg-pink-900/30 text-pink-700 dark:text-pink-300',
    'purple'  => 'bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300',
    'blue'    => 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300',
    'indigo'  => 'bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300',
    'emerald' => 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300',
    'amber'   => 'bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300',
    'slate'   => 'bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400',
  ];
@endphp

{{-- Flash --}}
@if(session('success'))
  <div class="mb-4 flex items-center gap-3 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800
              text-emerald-700 dark:text-emerald-400 px-4 py-3 rounded-lg text-sm font-medium">
    <span class="material-icons text-base">check_circle</span>
    {{ session('success') }}
  </div>
@endif

<div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 overflow-hidden shadow-sm">
  <div class="overflow-x-auto">
    <table class="w-full text-left">
      <thead class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-800">
        <tr>
          <x-ui.sort-th column="fullname" :label="__('app.posyandu_col_name')" />
          <x-ui.sort-th column="birth_date" :label="__('app.posyandu_col_dob')" class="hidden md:table-cell" />
          <x-ui.sort-th column="birth_date" :label="__('app.posyandu_col_age')" />
          <x-ui.sort-th column="age_category" :label="__('app.posyandu_col_cat')" />
          <x-ui.sort-th column="gender" :label="__('app.posyandu_col_gender')" class="hidden sm:table-cell" />
          <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider hidden lg:table-cell">{{ __('app.posyandu_col_rel') }}</th>
          <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider hidden lg:table-cell">{{ __('app.posyandu_col_block_unit') }}</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100 dark:divide-slate-800">

        @forelse($members as $member)
          @php
            $cat      = $categories[$member->age_category] ?? $categories['unknown'];
            $catColor = $colorMap[$cat['color']];
            $gender   = match($member->gender) {
              'male'   => __('app.mf_gender_male'),
              'female' => __('app.mf_gender_female'),
              default  => '—',
            };
            $genderIcon = $member->gender === 'male' ? 'male' : ($member->gender === 'female' ? 'female' : 'help_outline');
            $householder = $member->householder;
            $block       = $householder?->block?->name ?? '—';
            $unit        = $householder?->unit_number ?? '—';

            // Translated relationship label
            $relKey   = 'rel_' . ($member->relationship ?? 'other');
            $relLabel = __('app.' . $relKey);
            if ($relLabel === 'app.' . $relKey) {
              $relLabel = \App\Models\FamilyMember::$relationships[$member->relationship] ?? 'Other';
            }

            $initials = collect(preg_split('/\s+/', trim($member->fullname ?? '')))->filter()->map(fn($w) => strtoupper($w[0]))->take(2)->implode('') ?: '?';
          @endphp
          <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition-colors">

            {{-- Name + avatar --}}
            <td class="px-6 py-4">
              <div class="flex items-center gap-3">
                @if($member->photoUrl())
                  <img src="{{ $member->photoUrl() }}" alt="{{ $member->fullname }}"
                    class="w-9 h-9 rounded-full object-cover border-2 border-slate-200 dark:border-slate-700 flex-shrink-0">
                @else
                  <div class="w-9 h-9 rounded-full bg-teal-100 dark:bg-teal-900/30 text-teal-700 dark:text-teal-300
                              flex items-center justify-center text-xs font-bold flex-shrink-0">
                    {{ $initials }}
                  </div>
                @endif
                <div>
                  <span class="text-sm font-semibold text-slate-900 dark:text-white">{{ $member->fullname }}</span>
                  @if($member->is_head)
                    <span class="ml-1.5 text-[10px] font-bold text-primary bg-primary/10 px-1.5 py-0.5 rounded-full uppercase">Head</span>
                  @endif
                </div>
              </div>
            </td>

            {{-- Date of Birth --}}
            <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-400 hidden md:table-cell">
              {{ $member->birth_date?->format('d M Y') ?? '—' }}
            </td>

            {{-- Age --}}
            <td class="px-6 py-4">
              <span class="text-sm font-semibold text-slate-900 dark:text-white">{{ $member->age_label }}</span>
            </td>

            {{-- Category badge --}}
            <td class="px-6 py-4">
              <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold {{ $catColor }}">
                <span class="material-icons text-[12px]">{{ $cat['icon'] }}</span>
                {{ $cat['label'] }}
              </span>
            </td>

            {{-- Gender --}}
            <td class="px-6 py-4 hidden sm:table-cell">
              <span class="inline-flex items-center gap-1 text-sm text-slate-600 dark:text-slate-400">
                <span class="material-icons text-[16px]">{{ $genderIcon }}</span>
                {{ $gender }}
              </span>
            </td>

            {{-- Relationship --}}
            <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-400 hidden lg:table-cell">
              {{ $relLabel }}
            </td>

            {{-- Block · Unit --}}
            <td class="px-6 py-4 hidden lg:table-cell">
              @if($block !== '—')
                <span class="text-sm font-medium text-slate-900 dark:text-white">{{ $block }}</span>
                @if($unit !== '—')
                  <span class="text-xs text-slate-400 block mt-0.5">Unit {{ $unit }}</span>
                @endif
              @else
                <span class="text-slate-400 text-sm">—</span>
              @endif
            </td>

          </tr>
        @empty
          <tr>
            <td colspan="7" class="px-6 py-16 text-center">
              <div class="flex flex-col items-center gap-3 text-slate-400">
                <span class="material-icons text-5xl">health_and_safety</span>
                <p class="text-sm font-medium">{{ __('app.posyandu_no_members') }}</p>
                @if(request()->hasAny(['search', 'block_id', 'category']))
                  <a href="{{ route('posyandu.index') }}" class="text-primary text-sm hover:underline">{{ __('app.clear_filters') }}</a>
                @endif
              </div>
            </td>
          </tr>
        @endforelse

      </tbody>
    </table>
  </div>

  {{-- Pagination --}}
  @if($members->hasPages())
    <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-800 flex items-center justify-between">
      <p class="text-sm text-slate-500">
        {{ __('app.posyandu_showing') }} {{ $members->firstItem() }}–{{ $members->lastItem() }}
        {{ __('app.posyandu_of') }} {{ $members->total() }} {{ __('app.posyandu_members') }}
      </p>
      <div class="flex items-center gap-1">
        @if($members->onFirstPage())
          <button class="p-2 rounded-lg border border-slate-200 dark:border-slate-700 text-slate-300 dark:text-slate-600 cursor-not-allowed" disabled>
            <span class="material-icons text-sm">chevron_left</span>
          </button>
        @else
          <a href="{{ $members->previousPageUrl() }}"
            class="p-2 rounded-lg border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
            <span class="material-icons text-sm">chevron_left</span>
          </a>
        @endif

        @php
          $lastPage    = $members->lastPage();
          $currentPage = $members->currentPage();
          $start       = max(1, $currentPage - 2);
          $end         = min($members->lastPage(), $currentPage + 2);
        @endphp

        @if ($start > 1)
          <a href="{{ $members->url(1) }}" class="px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 text-sm text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">1</a>
          @if ($start > 2)
            <span class="px-1 text-slate-400 text-sm">&hellip;</span>
          @endif
        @endif

        @for ($p = $start; $p <= $end; $p++)
          @if ($p === $currentPage)
            <span class="px-3 py-1.5 rounded-lg bg-primary text-white text-sm font-semibold">{{ $p }}</span>
          @else
            <a href="{{ $members->url($p) }}" class="px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 text-sm text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">{{ $p }}</a>
          @endif
        @endfor

        @if ($end < $lastPage)
          @if ($end < $lastPage - 1)
            <span class="px-1 text-slate-400 text-sm">&hellip;</span>
          @endif
          <a href="{{ $members->url($lastPage) }}" class="px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 text-sm text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">{{ $lastPage }}</a>
        @endif

        @if($members->hasMorePages())
          <a href="{{ $members->nextPageUrl() }}"
            class="p-2 rounded-lg border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
            <span class="material-icons text-sm">chevron_right</span>
          </a>
        @else
          <button class="p-2 rounded-lg border border-slate-200 dark:border-slate-700 text-slate-300 dark:text-slate-600 cursor-not-allowed" disabled>
            <span class="material-icons text-sm">chevron_right</span>
          </button>
        @endif
      </div>
    </div>
  @endif

</div>
