{{-- organization/_node.blade.php — Section / Division card (recursive) --}}
{{-- Variables: $nodes (array of tree items), $depth (int) --}}
@php $isAdmin = auth()->user()->can('organization.edit') || auth()->user()->can('organization.delete'); @endphp

@foreach($nodes as $item)
  @php
    $pos         = $item['node'];
    $children    = $item['children'];
    $hasChildren = !empty($children);
    $name        = $pos->personName();
    $photo       = $pos->personPhotoUrl();
    $location    = $pos->personLocation();
    $phone       = $pos->personPhone();
    $initials    = $pos->personInitials();
    $sectionId   = 'org-section-' . $pos->id;
    $memberCount = count($children);

    // Depth-based color tokens (full class names so Tailwind CDN scans them)
    $stripColors = [
      'bg-primary/50 dark:bg-secondary/50',
      'bg-blue-400 dark:bg-blue-500',
      'bg-teal-400 dark:bg-teal-500',
      'bg-orange-400 dark:bg-orange-500',
    ];
    $headerBgs = [
      'bg-primary/[0.04] dark:bg-secondary/[0.04]',
      'bg-blue-50 dark:bg-blue-900/10',
      'bg-teal-50 dark:bg-teal-900/10',
      'bg-orange-50 dark:bg-orange-900/10',
    ];
    $avatarColors = [
      'bg-primary/10 text-primary dark:bg-secondary/10 dark:text-secondary',
      'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
      'bg-teal-100 text-teal-700 dark:bg-teal-900/30 dark:text-teal-400',
      'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400',
    ];

    $idx         = min($depth, 3);
    $stripClass  = $stripColors[$idx];
    $headerClass = $headerBgs[$idx];
    $avatarClass = $avatarColors[$idx];
  @endphp

  <div class="group flex rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden bg-white dark:bg-slate-900 {{ $depth > 0 ? 'border-slate-100 dark:border-slate-800 shadow-none' : '' }}"
    id="{{ $sectionId }}">

    {{-- Left accent strip --}}
    <div class="w-1 flex-shrink-0 {{ $stripClass }}"></div>

    {{-- Card content --}}
    <div class="flex-1 min-w-0 overflow-hidden">

      {{-- Section header --}}
      <div class="{{ $headerClass }} px-4 py-2.5 border-b border-slate-100 dark:border-slate-800 flex items-center gap-2">
        <span class="font-semibold text-slate-700 dark:text-slate-200 text-sm leading-snug flex-1 min-w-0 truncate">
          {{ $pos->position_name }}
        </span>

        @if($memberCount > 0)
          <span class="flex-shrink-0 inline-flex items-center justify-center min-w-[20px] h-5 px-1.5 rounded-full bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-300 text-[10px] font-bold">
            {{ $memberCount }}
          </span>
        @endif

        {{-- Admin edit/delete --}}
        @if($isAdmin)
          <div class="flex gap-0.5 flex-shrink-0 opacity-0 group-hover:opacity-100 transition-opacity">
            <button onclick="openEditPositionModal({{ json_encode(['id' => $pos->id, 'position_name' => $pos->position_name, 'parent_id' => $pos->parent_id, 'resident_id' => $pos->resident_id, 'family_member_id' => $pos->family_member_id, 'sort_order' => $pos->sort_order, 'person_name' => $name, 'person_location' => $location, 'period_id' => $pos->organization_period_id]) }})"
              class="w-6 h-6 rounded-md flex items-center justify-center hover:bg-slate-200/70 dark:hover:bg-slate-700/70 transition-colors"
              title="{{ __('app.org_edit_position') }}">
              <span class="material-icons text-slate-400 hover:text-primary dark:hover:text-secondary" style="font-size:13px">edit</span>
            </button>
            <button onclick="confirmDeletePosition('{{ $pos->id }}', '{{ addslashes($pos->position_name) }}')"
              class="w-6 h-6 rounded-md flex items-center justify-center hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors"
              title="{{ __('app.org_delete_position') }}">
              <span class="material-icons text-slate-400 hover:text-red-500" style="font-size:13px">close</span>
            </button>
          </div>
        @endif

        {{-- Collapse toggle --}}
        @if($hasChildren)
          <button onclick="toggleOrgSection('{{ $sectionId }}')"
            class="flex-shrink-0 w-6 h-6 rounded-md flex items-center justify-center hover:bg-slate-200/70 dark:hover:bg-slate-700/70 transition-colors"
            title="Expand / Collapse">
            <span class="material-icons text-slate-400" id="{{ $sectionId }}-chevron" style="font-size:16px;transition:transform .2s">expand_more</span>
          </button>
        @endif
      </div>

      {{-- Section body (collapsible) --}}
      <div id="{{ $sectionId }}-body">

        {{-- Leader row — the person assigned to this section position --}}
        <div class="flex items-center gap-3 px-4 py-3 {{ $hasChildren ? 'border-b border-slate-100 dark:border-slate-800' : '' }}">
          @if($photo)
            <img src="{{ $photo }}" alt="{{ $name }}"
              class="w-10 h-10 rounded-full object-cover border-2 border-white dark:border-slate-800 shadow-sm flex-shrink-0">
          @else
            <div class="w-10 h-10 rounded-full {{ $avatarClass }} flex items-center justify-center font-bold text-sm flex-shrink-0 border-2 border-white dark:border-slate-800">
              {{ $initials ?: '?' }}
            </div>
          @endif
          <div class="flex-1 min-w-0">
            @if($name)
              <p class="font-semibold text-sm text-slate-900 dark:text-white truncate">{{ $name }}</p>
            @else
              <p class="text-xs text-slate-400 italic">{{ __('app.org_vacant') }}</p>
            @endif
            <div class="flex flex-wrap items-center gap-x-2 mt-0.5">
              @if($location)
                <span class="flex items-center gap-0.5 text-[11px] text-slate-400">
                  <span class="material-icons" style="font-size:11px">location_on</span>{{ $location }}
                </span>
              @endif
              @if($phone)
                <span class="flex items-center gap-0.5 text-[11px] text-slate-400">
                  <span class="material-icons" style="font-size:11px">phone</span>{{ $phone }}
                </span>
              @endif
            </div>
          </div>
        </div>

        {{-- Children: members or nested sub-sections --}}
        @foreach($children as $child)
          @php
            $cPos     = $child['node'];
            $cKids    = $child['children'];
            $cHasKids = !empty($cKids);
            $cName    = $cPos->personName();
            $cPhoto   = $cPos->personPhotoUrl();
            $cLoc     = $cPos->personLocation();
            $cPhone   = $cPos->personPhone();
            $cInit    = $cPos->personInitials();
          @endphp

          @if($cHasKids)
            {{-- Sub-section: recurse into a nested card --}}
            <div class="px-3 py-2.5 border-t border-slate-50 dark:border-slate-800/50 bg-slate-50/50 dark:bg-slate-800/20">
              @include('organization._node', ['nodes' => [$child], 'depth' => $depth + 1])
            </div>
          @else
            {{-- Member row --}}
            <div class="group/member flex items-center gap-3 px-4 py-2.5 border-t border-slate-50 dark:border-slate-800/50 hover:bg-slate-50/80 dark:hover:bg-slate-800/30 transition-colors">
              @if($cPhoto)
                <img src="{{ $cPhoto }}" alt="{{ $cName }}"
                  class="w-9 h-9 rounded-full object-cover border border-slate-200 dark:border-slate-700 flex-shrink-0">
              @else
                <div class="w-9 h-9 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 flex items-center justify-center font-bold text-xs flex-shrink-0">
                  {{ $cInit ?: '?' }}
                </div>
              @endif
              <div class="flex-1 min-w-0">
                @if($cName)
                  <p class="font-medium text-sm text-slate-800 dark:text-slate-200 truncate">{{ $cName }}</p>
                @else
                  <p class="text-xs text-slate-400 italic">{{ __('app.org_vacant') }}</p>
                @endif
                <div class="flex flex-wrap items-center gap-x-2 mt-0.5">
                  <span class="text-[11px] font-medium text-slate-400 dark:text-slate-500">{{ $cPos->position_name }}</span>
                  @if($cLoc)
                    <span class="flex items-center gap-0.5 text-[11px] text-slate-400">
                      <span class="material-icons" style="font-size:11px">location_on</span>{{ $cLoc }}
                    </span>
                  @endif
                  @if($cPhone)
                    <span class="flex items-center gap-0.5 text-[11px] text-slate-400">
                      <span class="material-icons" style="font-size:11px">phone</span>{{ $cPhone }}
                    </span>
                  @endif
                </div>
              </div>
              {{-- Admin actions (revealed on hover) --}}
              @if($isAdmin)
                <div class="flex gap-0.5 flex-shrink-0 opacity-0 group-hover/member:opacity-100 transition-opacity">
                  <button onclick="openEditPositionModal({{ json_encode(['id' => $cPos->id, 'position_name' => $cPos->position_name, 'parent_id' => $cPos->parent_id, 'resident_id' => $cPos->resident_id, 'family_member_id' => $cPos->family_member_id, 'sort_order' => $cPos->sort_order, 'person_name' => $cName, 'person_location' => $cLoc, 'period_id' => $cPos->organization_period_id]) }})"
                    class="w-6 h-6 rounded-md flex items-center justify-center hover:bg-slate-200/70 dark:hover:bg-slate-700/70 transition-colors"
                    title="{{ __('app.org_edit_position') }}">
                    <span class="material-icons text-slate-400 hover:text-primary" style="font-size:13px">edit</span>
                  </button>
                  <button onclick="confirmDeletePosition('{{ $cPos->id }}', '{{ addslashes($cPos->position_name) }}')"
                    class="w-6 h-6 rounded-md flex items-center justify-center hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors"
                    title="{{ __('app.org_delete_position') }}">
                    <span class="material-icons text-slate-400 hover:text-red-500" style="font-size:13px">close</span>
                  </button>
                </div>
              @endif
            </div>
          @endif
        @endforeach

      </div>{{-- /section body --}}
    </div>{{-- /card content --}}
  </div>{{-- /section card --}}
@endforeach
