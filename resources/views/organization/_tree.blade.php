{{-- organization/_tree.blade.php — Hybrid org chart layout --}}
<div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">

  {{-- Period label bar --}}
  @if($selectedPeriod)
    <div class="px-5 py-3 border-b border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/40 flex items-center justify-between">
      <div class="flex items-center gap-2">
        <span class="material-icons text-primary dark:text-secondary text-lg">account_tree</span>
        <span class="font-semibold text-slate-800 dark:text-white text-sm">{{ $selectedPeriod->name }}</span>
        @if($selectedPeriod->is_active)
          <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400">
            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 inline-block"></span>
            {{ __('app.org_active_badge') }}
          </span>
        @endif
      </div>
      <span class="text-xs text-slate-400">{{ trans_choice('app.org_positions_count', $positions->count(), ['count' => $positions->count()]) }}</span>
    </div>
  @endif

  {{-- Chart body --}}
  <div class="p-4 sm:p-6 lg:p-8">

    @if(!$selectedPeriod)
      {{-- No period --}}
      <div class="flex flex-col items-center justify-center py-16 text-center">
        <div class="w-16 h-16 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center mb-4">
          <span class="material-icons text-slate-400 text-3xl">account_tree</span>
        </div>
        <p class="text-slate-500 dark:text-slate-400 font-medium">{{ __('app.org_no_periods') }}</p>
        @if(auth()->user()->can('organization.create'))
          <p class="text-xs text-slate-400 mt-1">{{ __('app.org_add_period') }}</p>
        @endif
      </div>

    @elseif(empty($tree))
      {{-- Period exists but no positions --}}
      <div class="flex flex-col items-center justify-center py-16 text-center">
        <div class="w-16 h-16 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center mb-4">
          <span class="material-icons text-slate-400 text-3xl">people_outline</span>
        </div>
        <p class="text-slate-500 dark:text-slate-400 font-medium">{{ __('app.org_no_positions') }}</p>
        @if(auth()->user()->can('organization.create'))
          <button onclick="openOrgPositionModal()"
            class="mt-3 flex items-center gap-1.5 text-sm text-primary dark:text-secondary font-semibold hover:underline">
            <span class="material-icons text-sm">add</span>{{ __('app.org_add_position') }}
          </button>
        @endif
      </div>

    @else
      @php
        $isAdmin      = auth()->user()->can('organization.edit') || auth()->user()->can('organization.delete');
        $isSingleRoot = count($tree) === 1;

        if ($isSingleRoot) {
          $rootItem     = $tree[0];
          $rootChildren = $rootItem['children'];
          // Officers = leaf direct children (no sub-children)
          $officerItems = array_values(array_filter($rootChildren, fn($c) => empty($c['children'])));
          // Sections  = branch direct children (have sub-children)
          $sectionItems = array_values(array_filter($rootChildren, fn($c) => !empty($c['children'])));
        }
      @endphp

      @if($isSingleRoot)
        {{-- ─────────────────────────────────────────────────────────
             ROOT — Centered hero card
             ───────────────────────────────────────────────────── --}}
        @php
          $rPos      = $rootItem['node'];
          $rName     = $rPos->personName();
          $rPhoto    = $rPos->personPhotoUrl();
          $rLocation = $rPos->personLocation();
          $rPhone    = $rPos->personPhone();
          $rInitials = $rPos->personInitials();
        @endphp

        <div class="flex justify-center mb-3">
          <div class="group relative">
            @if($isAdmin)
              <div class="absolute -top-1 -right-1 flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity z-10">
                <button onclick="openEditPositionModal({{ json_encode(['id' => $rPos->id, 'position_name' => $rPos->position_name, 'parent_id' => $rPos->parent_id, 'resident_id' => $rPos->resident_id, 'family_member_id' => $rPos->family_member_id, 'sort_order' => $rPos->sort_order, 'person_name' => $rName, 'person_location' => $rLocation, 'period_id' => $rPos->organization_period_id]) }})"
                  class="w-7 h-7 rounded-lg bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 flex items-center justify-center hover:border-primary/50 shadow-sm transition-all"
                  title="{{ __('app.org_edit_position') }}">
                  <span class="material-icons text-slate-400 hover:text-primary dark:hover:text-secondary" style="font-size:14px">edit</span>
                </button>
                <button onclick="confirmDeletePosition('{{ $rPos->id }}', '{{ addslashes($rPos->position_name) }}')"
                  class="w-7 h-7 rounded-lg bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 flex items-center justify-center hover:border-red-400 shadow-sm transition-all"
                  title="{{ __('app.org_delete_position') }}">
                  <span class="material-icons text-slate-400 hover:text-red-500" style="font-size:14px">close</span>
                </button>
              </div>
            @endif
            <div class="w-56 sm:w-64 text-center px-5 py-6 rounded-2xl bg-gradient-to-b from-primary/5 to-transparent dark:from-secondary/5 border-2 border-primary/20 dark:border-secondary/20 shadow-md hover:shadow-lg transition-shadow">
              @if($rPhoto)
                <img src="{{ $rPhoto }}" alt="{{ $rName }}"
                  class="w-20 h-20 rounded-full object-cover border-4 border-white dark:border-slate-900 shadow-md mx-auto mb-3">
              @else
                <div class="w-20 h-20 rounded-full bg-primary/10 dark:bg-secondary/10 text-primary dark:text-secondary flex items-center justify-center font-bold text-2xl shadow-sm mx-auto mb-3 border-4 border-white dark:border-slate-900">
                  {{ $rInitials ?: '?' }}
                </div>
              @endif
              <span class="inline-block px-3 py-1 rounded-full text-xs font-bold bg-primary/10 text-primary dark:bg-secondary/10 dark:text-secondary mb-2 leading-5">
                {{ $rPos->position_name }}
              </span>
              @if($rName)
                <p class="font-bold text-base text-slate-900 dark:text-white leading-tight">{{ $rName }}</p>
              @else
                <p class="text-sm text-slate-400 italic">{{ __('app.org_vacant') }}</p>
              @endif
              @if($rLocation || $rPhone)
                <div class="mt-2 space-y-0.5">
                  @if($rLocation)
                    <div class="flex items-center justify-center gap-1 text-xs text-slate-500 dark:text-slate-400">
                      <span class="material-icons" style="font-size:12px">location_on</span>
                      <span>{{ $rLocation }}</span>
                    </div>
                  @endif
                  @if($rPhone)
                    <div class="flex items-center justify-center gap-1 text-xs text-slate-500 dark:text-slate-400">
                      <span class="material-icons" style="font-size:12px">phone</span>
                      <span>{{ $rPhone }}</span>
                    </div>
                  @endif
                </div>
              @endif
            </div>
          </div>
        </div>

        {{-- Connector root → officers/sections --}}
        @if(!empty($rootChildren))
          <div class="flex justify-center mb-3">
            <div class="w-px h-8 bg-slate-200 dark:bg-slate-700"></div>
          </div>
        @endif

        {{-- ─────────────────────────────────────────────────────────
             OFFICERS — Leaf direct children (centered row)
             ───────────────────────────────────────────────────── --}}
        @if(!empty($officerItems))
          <div class="flex justify-center mb-4">
            <div class="flex flex-wrap justify-center gap-3">
              @foreach($officerItems as $officerItem)
                @php
                  $oPos      = $officerItem['node'];
                  $oName     = $oPos->personName();
                  $oPhoto    = $oPos->personPhotoUrl();
                  $oLocation = $oPos->personLocation();
                  $oPhone    = $oPos->personPhone();
                  $oInitials = $oPos->personInitials();
                @endphp
                <div class="group relative">
                  @if($isAdmin)
                    <div class="absolute -top-1 -right-1 flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity z-10">
                      <button onclick="openEditPositionModal({{ json_encode(['id' => $oPos->id, 'position_name' => $oPos->position_name, 'parent_id' => $oPos->parent_id, 'resident_id' => $oPos->resident_id, 'family_member_id' => $oPos->family_member_id, 'sort_order' => $oPos->sort_order, 'person_name' => $oName, 'person_location' => $oLocation, 'period_id' => $oPos->organization_period_id]) }})"
                        class="w-6 h-6 rounded-md bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 flex items-center justify-center hover:border-primary/50 shadow-sm transition-all"
                        title="{{ __('app.org_edit_position') }}">
                        <span class="material-icons text-slate-400 hover:text-primary dark:hover:text-secondary" style="font-size:13px">edit</span>
                      </button>
                      <button onclick="confirmDeletePosition('{{ $oPos->id }}', '{{ addslashes($oPos->position_name) }}')"
                        class="w-6 h-6 rounded-md bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 flex items-center justify-center hover:border-red-400 shadow-sm transition-all"
                        title="{{ __('app.org_delete_position') }}">
                        <span class="material-icons text-slate-400 hover:text-red-500" style="font-size:13px">close</span>
                      </button>
                    </div>
                  @endif
                  <div class="w-44 sm:w-48 text-center px-4 py-4 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 shadow-sm hover:shadow-md hover:border-primary/30 dark:hover:border-secondary/30 transition-all">
                    @if($oPhoto)
                      <img src="{{ $oPhoto }}" alt="{{ $oName }}"
                        class="w-14 h-14 rounded-full object-cover border-2 border-white dark:border-slate-800 shadow-sm mx-auto mb-2">
                    @else
                      <div class="w-14 h-14 rounded-full bg-violet-100 dark:bg-violet-900/30 text-violet-600 dark:text-violet-400 flex items-center justify-center font-bold text-base shadow-sm mx-auto mb-2 border-2 border-white dark:border-slate-800">
                        {{ $oInitials ?: '?' }}
                      </div>
                    @endif
                    <span class="inline-block px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-violet-100 text-violet-700 dark:bg-violet-900/30 dark:text-violet-400 mb-1 leading-5">
                      {{ $oPos->position_name }}
                    </span>
                    @if($oName)
                      <p class="font-semibold text-sm text-slate-900 dark:text-white leading-tight truncate px-1" title="{{ $oName }}">{{ $oName }}</p>
                    @else
                      <p class="text-xs text-slate-400 italic">{{ __('app.org_vacant') }}</p>
                    @endif
                    @if($oLocation || $oPhone)
                      <div class="mt-1.5 space-y-0.5">
                        @if($oLocation)
                          <div class="flex items-center justify-center gap-1 text-[11px] text-slate-400">
                            <span class="material-icons" style="font-size:11px">location_on</span>
                            <span>{{ $oLocation }}</span>
                          </div>
                        @endif
                        @if($oPhone)
                          <div class="flex items-center justify-center gap-1 text-[11px] text-slate-400">
                            <span class="material-icons" style="font-size:11px">phone</span>
                            <span>{{ $oPhone }}</span>
                          </div>
                        @endif
                      </div>
                    @endif
                  </div>
                </div>
              @endforeach
            </div>
          </div>

          {{-- Connector officers → sections --}}
          @if(!empty($sectionItems))
            <div class="flex justify-center mb-5">
              <div class="w-px h-8 bg-slate-200 dark:bg-slate-700"></div>
            </div>
          @endif
        @endif

        {{-- ─────────────────────────────────────────────────────────
             SECTIONS — Branch direct children (responsive 2-col grid)
             ───────────────────────────────────────────────────── --}}
        @if(!empty($sectionItems))
          <div class="flex items-center gap-3 mb-4">
            <div class="flex-1 h-px bg-slate-100 dark:bg-slate-800"></div>
            <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-widest px-2">
              {{ __('app.org_divisions_heading') }}
            </span>
            <div class="flex-1 h-px bg-slate-100 dark:bg-slate-800"></div>
          </div>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach($sectionItems as $sectionItem)
              @include('organization._node', ['nodes' => [$sectionItem], 'depth' => 0])
            @endforeach
          </div>
        @endif

      @else
        {{-- ─────────────────────────────────────────────────────────
             MULTIPLE ROOTS — flat grid
             ───────────────────────────────────────────────────── --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          @foreach($tree as $treeItem)
            @if(!empty($treeItem['children']))
              @include('organization._node', ['nodes' => [$treeItem], 'depth' => 0])
            @else
              @php
                $sPos      = $treeItem['node'];
                $sName     = $sPos->personName();
                $sPhoto    = $sPos->personPhotoUrl();
                $sLocation = $sPos->personLocation();
                $sInitials = $sPos->personInitials();
              @endphp
              <div class="group relative flex items-center gap-4 px-4 py-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl shadow-sm hover:shadow-md transition-shadow">
                @if($isAdmin)
                  <div class="absolute top-2 right-2 flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity z-10">
                    <button onclick="openEditPositionModal({{ json_encode(['id' => $sPos->id, 'position_name' => $sPos->position_name, 'parent_id' => $sPos->parent_id, 'resident_id' => $sPos->resident_id, 'family_member_id' => $sPos->family_member_id, 'sort_order' => $sPos->sort_order, 'person_name' => $sName, 'person_location' => $sLocation, 'period_id' => $sPos->organization_period_id]) }})"
                      class="w-6 h-6 rounded-md bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 flex items-center justify-center hover:border-primary/50 transition-all"
                      title="{{ __('app.org_edit_position') }}">
                      <span class="material-icons text-slate-400 hover:text-primary" style="font-size:13px">edit</span>
                    </button>
                    <button onclick="confirmDeletePosition('{{ $sPos->id }}', '{{ addslashes($sPos->position_name) }}')"
                      class="w-6 h-6 rounded-md bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 flex items-center justify-center hover:border-red-400 transition-all"
                      title="{{ __('app.org_delete_position') }}">
                      <span class="material-icons text-slate-400 hover:text-red-500" style="font-size:13px">close</span>
                    </button>
                  </div>
                @endif
                @if($sPhoto)
                  <img src="{{ $sPhoto }}" alt="{{ $sName }}" class="w-12 h-12 rounded-full object-cover border-2 border-slate-200 dark:border-slate-700 flex-shrink-0">
                @else
                  <div class="w-12 h-12 rounded-full bg-primary/10 dark:bg-secondary/10 text-primary dark:text-secondary flex items-center justify-center font-bold flex-shrink-0">
                    {{ $sInitials ?: '?' }}
                  </div>
                @endif
                <div class="flex-1 min-w-0">
                  <span class="inline-block px-2 py-0.5 rounded-full text-[11px] font-bold bg-primary/10 text-primary dark:bg-secondary/10 dark:text-secondary mb-0.5">{{ $sPos->position_name }}</span>
                  <p class="font-semibold text-sm text-slate-900 dark:text-white truncate">{{ $sName ?: __('app.org_vacant') }}</p>
                  @if($sLocation)
                    <p class="text-xs text-slate-400 truncate">{{ $sLocation }}</p>
                  @endif
                </div>
              </div>
            @endif
          @endforeach
        </div>
      @endif

    @endif
  </div>
</div>
