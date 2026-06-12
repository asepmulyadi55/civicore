{{-- Household Edit Page — Section A: Household Info | Section B: Family Members --}}
<x-layouts.app :title="'Edit Household — ' . $householder->block->name . ' · ' . $householder->unit_number">

  @if($isOwnHousehold)
    @include('overview._sidebar')
  @else
    <x-nav.sidebar :active="($isOwnHousehold ?? false) ? 'household' : 'householders'" />
  @endif

  <div class="lg:pl-64 min-h-screen bg-background-light dark:bg-background-dark flex flex-col">

    {{-- Page Header --}}
    <header class="h-16 bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between px-6 lg:px-8 shrink-0">
      <div class="flex items-center gap-3">
        <button class="lg:hidden p-2 rounded-lg border border-slate-200 dark:border-slate-800" onclick="toggleSidebar()">
          <span class="material-icons text-slate-500">menu</span>
        </button>
        <a href="{{ $backRoute }}"
          class="p-2 rounded-lg text-slate-400 hover:text-primary hover:bg-primary/5 transition-all">
          <span class="material-icons">arrow_back</span>
        </a>
        <div>
          <h1 class="text-xl font-bold text-slate-900 dark:text-white leading-tight">{{ __('app.edit_household') }}</h1>
          <p class="text-xs text-slate-400">{{ $householder->block->name }} &middot; Unit {{ $householder->unit_number }}</p>
        </div>
      </div>
      <div class="flex items-center gap-3">
        <span class="hidden sm:inline px-2.5 py-1 rounded-lg text-xs font-bold {{ $householder->is_active ? 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400' : 'bg-slate-100 dark:bg-slate-800 text-slate-500' }}">
          {{ $householder->is_active ? 'Active' : 'Inactive' }}
        </span>
        <button class="p-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg hover:border-primary/50 transition-all"
          onclick="toggleDark()" title="Toggle dark mode">
          <span class="material-icons text-slate-500 text-[20px]">dark_mode</span>
        </button>
      </div>
    </header>

    <div class="flex-1 p-6 lg:p-8 space-y-8 max-w-5xl w-full">

      {{-- Flash --}}
      @if(session('success'))
        <div class="p-4 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 rounded-xl flex items-center gap-3">
          <span class="material-icons text-emerald-500">check_circle</span>
          <p class="text-sm text-emerald-700 dark:text-emerald-400">{{ session('success') }}</p>
        </div>
      @endif

      @if($errors->any() && !session('_member_form'))
        <div class="p-4 bg-rose-50 dark:bg-rose-900/20 border border-rose-200 dark:border-rose-800 rounded-xl text-sm text-rose-700 dark:text-rose-400 space-y-1">
          @foreach($errors->all() as $err)
            <p>{{ $err }}</p>
          @endforeach
        </div>
      @endif

      {{-- ════════════════════════════════════════════════════════════════ --}}
      {{-- SECTION A — Household Information                              --}}
      {{-- ════════════════════════════════════════════════════════════════ --}}
      <section>
        <div class="flex items-center gap-3 mb-5">
          <div class="w-9 h-9 bg-primary/10 rounded-lg flex items-center justify-center">
            <span class="material-icons text-primary text-lg">home</span>
          </div>
          <div>
            <h2 class="text-lg font-bold text-slate-900 dark:text-white">{{ __('app.household_info') }}</h2>
            <p class="text-xs text-slate-400">{{ __('app.household_info_desc') }}</p>
          </div>
        </div>

        @php
          $ib = 'w-full px-3 py-2.5 bg-slate-50 dark:bg-slate-800 border rounded-lg text-sm focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none transition-all dark:text-white';
          $in = 'border-slate-200 dark:border-slate-700';
          $ie = 'border-rose-400';
        @endphp

        @if(!$canManageInfo)
        {{-- Locked notice --}}
        <div class="bg-amber-50 dark:bg-amber-900/10 border border-amber-200 dark:border-amber-700/30 rounded-xl p-5 flex items-start gap-3 mb-5">
          <span class="material-icons text-amber-500 mt-0.5 shrink-0">lock</span>
          <div>
            <p class="text-sm font-semibold text-amber-800 dark:text-amber-300">{{ __('app.household_locked_title') }}</p>
            <p class="text-xs text-amber-700 dark:text-amber-400 mt-1">
              {!! __('app.household_locked_body') !!}
              {{ __('app.household_locked_you_can') }}
            </p>
          </div>
        </div>
        @endif

        {{-- Read-only unit info banner (resident self-service only) --}}
        @if($isOwnHousehold)
          <div class="bg-slate-50 dark:bg-slate-800/60 rounded-xl border border-slate-200 dark:border-slate-700 px-5 py-4 flex items-center gap-4 mb-1">
            <span class="material-icons text-primary text-2xl">home</span>
            <div>
              <p class="font-bold text-slate-900 dark:text-white">{{ $householder->block->name }} &middot; Unit {{ $householder->unit_number }}</p>
              <p class="text-xs text-slate-500">{{ __('app.house_status_' . ($householder->house_status ?? 'owner_occupied')) }}</p>
            </div>
          </div>
        @endif

        <form method="POST" action="{{ $updateRoute }}" enctype="multipart/form-data">
          @csrf @method('PATCH')
          <fieldset {{ !$canManageInfo ? 'disabled' : '' }} class="space-y-5">

          {{-- Household Photo --}}
          <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-6">
            <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4">{{ __('app.section_household_photo') }}</h3>
            <div class="flex items-center gap-5">
              <div class="w-20 h-20 rounded-2xl overflow-hidden flex-shrink-0 bg-slate-100 dark:bg-slate-800 border-2 border-slate-200 dark:border-slate-700 shadow-sm flex items-center justify-center">
                @if($householder->photoUrl())
                  <img id="resident-photo-preview" src="{{ $householder->photoUrl() }}" alt="{{ $householder->fullname }}"
                    class="w-full h-full object-cover">
                @else
                  <img id="resident-photo-preview" src="" alt="" class="w-full h-full object-cover hidden">
                  <span id="resident-photo-icon" class="material-icons text-slate-400 text-3xl">home</span>
                @endif
              </div>
              <div>
                <label for="resident-photo-upload"
                  class="inline-flex items-center gap-2 cursor-pointer px-4 py-2 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-lg text-sm font-semibold transition-all border border-slate-200 dark:border-slate-700">
                  <span class="material-icons text-sm">upload</span> Upload Photo
                </label>
                <input id="resident-photo-upload" type="file" name="photo" accept="image/*" class="hidden"
                  onchange="previewResidentPhoto(event)">
                <p class="text-xs text-slate-400 mt-2">{{ __('app.photo_compress_hint') }}</p>
                @error('photo') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
              </div>
            </div>
          </div>
          <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-6 space-y-5">
            <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider">{{ __('app.section_unit_details') }}</h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
              {{-- Block (admin-only) --}}
              @if(!$isOwnHousehold)
              <div>
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                  {{ __('app.table_block') }} <span class="text-rose-500">*</span>
                </label>
                <div class="relative">
                  <select name="block_id" id="edit-page-block_id" onchange="loadUnitsOnEdit(this.value, null)" class="{{ $ib }} {{ $errors->has('block_id') ? $ie : $in }} appearance-none pl-3 pr-9">
                    @foreach($blocks as $block)
                      <option value="{{ $block->id }}" {{ old('block_id', $householder->block_id) == $block->id ? 'selected' : '' }}>
                        {{ $block->name }}
                      </option>
                    @endforeach
                  </select>
                  <span class="material-icons absolute right-2.5 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400 text-[18px]">expand_more</span>
                </div>
                @error('block_id') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
              </div>

              {{-- Unit (admin-only) - AJAX-loaded select --}}
              <div>
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                  {{ __('app.unit_no') }} <span class="text-rose-500">*</span>
                </label>
                <div class="relative">
                  <select id="edit-page-unit_id" name="unit_id"
                    onchange="updateHouseStatusBadge(this.value)"
                    class="{{ $ib }} {{ $errors->has('unit_id') ? $ie : $in }} appearance-none pl-3 pr-9">
                    <option value="{{ $householder->unit_id }}">{{ $householder->unit_number }}</option>
                  </select>
                  <span class="material-icons absolute right-2.5 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400 text-[18px]">expand_more</span>
                </div>
                @error('unit_id') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
              </div>
              @endif {{-- /!$isOwnHousehold block+unit --}}

              {{-- Owner / Contact Name --}}
              <div>
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                  {{ __('app.owner_contact_name') }} <span class="text-rose-500">*</span>
                </label>
                <input type="text" name="fullname"
                  value="{{ old('fullname', $householder->fullname) }}"
                  placeholder="{{ __('app.owner_contact_placeholder') }}"
                  class="{{ $ib }} {{ $errors->has('fullname') ? $ie : $in }}">
                <p class="text-xs text-slate-400 mt-1">{{ __('app.owner_contact_hint') }}</p>
                @error('fullname') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
              </div>

              {{-- Family Card Number (Hidden for Data Privacy) --}}
              <div class="hidden">
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                  {{ __('app.fcn_label') }} <span class="text-xs font-normal text-slate-400">{{ __('app.fcn_abbrev') }}</span>
                </label>
                <div class="relative">
                  <input type="text" id="fcn-input" name="family_card_number"
                    value="{{ old('family_card_number') }}"
                    placeholder="{{ $householder->family_card_number ? $householder->maskedFamilyCardNumber() : 'e.g. 3174012345678901' }}"
                    maxlength="20"
                    class="{{ $ib }} {{ $errors->has('family_card_number') ? $ie : $in }} {{ $showRevealButtons && $householder->family_card_number ? 'pr-10' : '' }}">
                  @if($showRevealButtons && $householder->family_card_number)
                    <button type="button"
                      onclick="revealFCN('{{ route('householders.reveal-fcn', $householder) }}', this)"
                      class="absolute right-2.5 top-1/2 -translate-y-1/2 p-1 text-slate-400 hover:text-primary transition-colors"
                      title="Reveal full number (admin only)">
                      <span class="material-icons text-[18px]">visibility</span>
                    </button>
                  @endif
                </div>
                @if($householder->family_card_number)
                  <p class="text-xs text-slate-400 mt-1">{{ __('app.fcn_leave_blank') }}</p>
                @endif
                @if(($householder->house_status ?? 'owner_occupied') === 'rented')
                  <p id="fcn-rent-hint" class="text-xs text-amber-600 dark:text-amber-400 mt-1 flex items-center gap-1">
                    <span class="material-icons text-xs">info</span>
                    {{ __('app.fcn_rent_hint') }}
                  </p>
                @else
                  <p id="fcn-rent-hint" class="text-xs text-amber-600 dark:text-amber-400 mt-1 items-center gap-1 hidden">
                    <span class="material-icons text-xs">info</span>
                    {{ __('app.fcn_rent_hint') }}
                  </p>
                @endif
                @error('family_card_number') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
              </div>

              {{-- Phone --}}
              <div class="hidden">
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">{{ __('app.phone_number') }}</label>
                <input type="tel" name="phone"
                  value="{{ old('phone', $householder->phone) }}"
                  placeholder="+62 812 xxxx xxxx"
                  class="{{ $ib }} {{ $errors->has('phone') ? $ie : $in }}">
                @error('phone') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
              </div>

              {{-- Email --}}
              <div>
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                  {{ __('app.email_address') }}
                  <span class="text-xs font-normal text-slate-400">{{ __('app.email_links_user') }}</span>
                </label>
                <input type="email" name="email"
                  value="{{ old('email', $householder->email) }}"
                  placeholder="household@example.com"
                  class="{{ $ib }} {{ $errors->has('email') ? $ie : $in }}">
                @error('email') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
              </div>
            </div>
          </div>

          {{-- Classification --}}
          <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-6 space-y-5">
            <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider">{{ __('app.section_classification') }}</h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
              {{-- House Status read-only (managed on Unit) --}}
              <div>
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                  {{ __('app.house_status') }}
                </label>
                @php
                  $hsBadgeColors = ['owner_occupied'=>'bg-primary/10 text-primary','rented'=>'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400','vacant'=>'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400', 'public_facility'=>'bg-teal-100 text-teal-700 dark:bg-teal-900/30 dark:text-teal-400', 'developer'=>'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400'];
                  $hsColor = $hsBadgeColors[$householder->house_status ?? 'owner_occupied'] ?? $hsBadgeColors['owner_occupied'];
                  $hsLabel = __('app.house_status_' . ($householder->house_status ?? 'owner_occupied'));
                @endphp
                <div class="flex items-center gap-3 p-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800">
                  <span id="hs-badge-span" class="px-2.5 py-1 rounded-lg text-xs font-bold {{ $hsColor }}">{{ $hsLabel }}</span>
                  @if($householder->unit)
                  <a href="{{ route('blocks.units.index', $householder->unit->block_id) }}"
                    class="text-xs text-primary hover:underline flex items-center gap-1">
                    <span class="material-icons text-xs">open_in_new</span>{{ __('app.btn_go_unit_mgmt') }}
                  </a>
                  @endif
                </div>
                <p class="text-[11px] text-slate-400 mt-1">{{ __('app.unit_house_status_hint') }}</p>
              </div>

              {{-- Active Status toggle (admin-only) --}}
              @if(!$isOwnHousehold)
              <div class="flex items-center">
                <label class="flex items-center gap-3 cursor-pointer p-3 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors w-full border border-slate-200 dark:border-slate-700">
                  <input type="checkbox" name="is_active" value="1"
                    {{ old('is_active', $householder->is_active) ? 'checked' : '' }}
                    class="w-4 h-4 text-primary rounded border-slate-300 focus:ring-primary/20">
                  <div>
                    <span class="text-sm font-semibold text-slate-700 dark:text-slate-300">{{ __('app.active_household') }}</span>
                    <p class="text-xs text-slate-400">{{ __('app.active_household_hint') }}</p>
                  </div>
                </label>
              </div>
              @endif {{-- /!$isOwnHousehold is_active --}}
            </div>

            {{-- Rental Period (shown when rented) --}}
            <div id="rent-period-section" class="{{ ($householder->house_status ?? 'owner_occupied') === 'rented' ? '' : 'hidden' }}">
              <div class="border-t border-slate-100 dark:border-slate-800 pt-5">
                <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3 flex items-center gap-1.5">
                  <span class="material-icons text-sm text-amber-500">event</span>
                  {{ __('app.rent_period') }}
                </h4>
                <p class="text-xs text-slate-400 mb-3">{{ __('app.rent_period_hint') }}</p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">{{ __('app.rent_start') }}</label>
                    <input type="date" name="rent_start"
                      value="{{ old('rent_start', $householder->rent_start?->format('Y-m-d')) }}"
                      class="{{ $ib }} {{ $errors->has('rent_start') ? $ie : $in }} dark:[color-scheme:dark]">
                    @error('rent_start') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                  </div>
                  <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">{{ __('app.rent_end') }}</label>
                    <input type="date" name="rent_end"
                      value="{{ old('rent_end', $householder->rent_end?->format('Y-m-d')) }}"
                      class="{{ $ib }} {{ $errors->has('rent_end') ? $ie : $in }} dark:[color-scheme:dark]">
                    @error('rent_end') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                  </div>
                </div>
              </div>
            </div>

            {{-- Notes --}}
            <div>
              <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                {{ __('app.notes') }} <span class="text-xs font-normal text-slate-400">{{ __('app.optional') }}</span>
              </label>
              <textarea name="notes" rows="3"
                placeholder="{{ __('app.resident_notes_placeholder') }}"
                class="{{ $ib }} {{ $errors->has('notes') ? $ie : $in }} resize-y"
              >{{ old('notes', $householder->notes) }}</textarea>
              @error('notes') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
            </div>
          </div>

          {{-- Fee Management (admin-only) --}}
          @if(!$isOwnHousehold)
          <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-6 space-y-4">
            <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider">{{ __('app.section_fee_management') }}</h3>

            @php $currentFeeRecord = $householder->feeHistories->first(); @endphp
            @if($currentFeeRecord)
              <div class="flex items-center gap-4 p-3 bg-primary/5 dark:bg-primary/10 rounded-lg border border-primary/20">
                <span class="material-icons text-primary">payments</span>
                <div>
                  <p class="text-sm font-bold text-slate-900 dark:text-white">
                    {{ $currency }} {{ number_format($currentFeeRecord->amount, 0, ',', '.') }} {{ __('app.month_suffix') }}
                  </p>
                  <p class="text-xs text-slate-500">
                    {{ __('app.fee_effective_from') }} {{ $currentFeeRecord->effective_from->format('F Y') }}
                    @if($currentFeeRecord->notes) &middot; {{ $currentFeeRecord->notes }} @endif
                  </p>
                </div>
              </div>
            @else
              <p class="text-sm text-slate-400 italic">{{ __('app.no_fee_history') }}</p>
            @endif

            <div class="rounded-xl bg-amber-50 dark:bg-amber-900/10 border border-amber-200 dark:border-amber-700/30 p-4 space-y-4">
              <p class="text-xs font-semibold text-amber-700 dark:text-amber-400 flex items-center gap-1.5">
                <span class="material-icons text-sm">info</span>
                {{ __('app.fee_update_hint') }}
              </p>
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                    {{ __('app.new_monthly_fee') }} ({{ $currency }})
                  </label>
                  <input type="number" name="new_monthly_fee" min="0" step="1000"
                    value="{{ old('new_monthly_fee') }}"
                    placeholder="{{ __('app.leave_blank_keep_current') }}"
                    class="{{ $ib }} {{ $in }}">
                </div>
                <div>
                  <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                    {{ __('app.effective_from') }}
                  </label>
                  <input type="month" name="new_fee_start"
                    value="{{ old('new_fee_start', now()->format('Y-m')) }}"
                    class="{{ $ib }} {{ $in }} dark:[color-scheme:dark]">
                </div>
              </div>
            </div>
          </div>
          @endif {{-- /!$isOwnHousehold fee management --}}

          @if($canManageInfo)
          {{-- Save Button --}}
          <div class="flex justify-end">
            <button type="submit"
              class="flex items-center gap-2 bg-primary hover:bg-primary/90 text-white px-6 py-2.5 rounded-lg font-semibold transition-all shadow-sm shadow-primary/20 text-sm">
              <span class="material-icons text-sm">save</span>
              {{ __('app.save_household') }}
            </button>
          </div>
          @endif
          </fieldset>
        </form>

      </section>

      {{-- ════════════════════════════════════════════════════════════════ --}}
      {{-- SECTION B — Family Members                                     --}}
      {{-- ════════════════════════════════════════════════════════════════ --}}
      <section>
        <div class="flex items-center justify-between mb-5">
          <div class="flex items-center gap-3">
            <div class="w-9 h-9 bg-indigo-100 dark:bg-indigo-900/30 rounded-lg flex items-center justify-center">
              <span class="material-icons text-indigo-500 text-lg">group</span>
            </div>
            <div>
              <h2 class="text-lg font-bold text-slate-900 dark:text-white">
                {{ __('app.family_members') }}
                @if($householder->residents->count() > 0)
                  <span class="ml-2 px-2 py-0.5 text-xs font-bold bg-slate-100 dark:bg-slate-800 text-slate-500 rounded-full">
                    {{ $householder->residents->count() }}
                  </span>
                @endif
              </h2>
              <p class="text-xs text-slate-400">{{ __('app.people_living_hint') }}</p>
            </div>
          </div>
          @if($canManageResidents)
            <button onclick="openMemberModal()"
              class="flex items-center gap-2 text-sm font-semibold px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition-all shadow-sm">
              <span class="material-icons text-sm">person_add</span>
              <span class="hidden sm:inline">{{ __('app.btn_add_member') }}</span>
            </button>
          @endif
        </div>

        {{-- Member errors --}}
        @if($errors->any() && session('_member_form'))
          <div class="mb-4 p-4 bg-rose-50 dark:bg-rose-900/20 border border-rose-200 dark:border-rose-800 rounded-xl text-sm text-rose-700 dark:text-rose-400 space-y-1">
            @foreach($errors->all() as $err)
              <p>{{ $err }}</p>
            @endforeach
          </div>
        @endif

        <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 overflow-hidden shadow-sm">
          @if($householder->residents->isEmpty())
            <div class="flex flex-col items-center gap-3 py-16 text-slate-400">
              <span class="material-icons text-5xl">group_off</span>
              <p class="text-sm font-medium">{{ __('app.no_family_members') }}</p>
                @if($canManageResidents)
                <button onclick="openMemberModal()"
                  class="text-primary text-sm hover:underline font-semibold">
                  {{ __('app.add_first_member') }}
                </button>
              @endif
            </div>
          @else
            <div class="overflow-x-auto">
              <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-800">
                  <tr>
                    <th class="px-5 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wider">{{ __('app.col_name') }}</th>
                    <th class="px-5 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wider hidden sm:table-cell">{{ __('app.col_photo') }}</th>
                    <th class="px-5 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wider">{{ __('app.col_relationship') }}</th>
                    <th class="px-5 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wider hidden">{{ __('app.col_nik') }}</th>
                    <th class="px-5 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wider hidden lg:table-cell">{{ __('app.col_gender') }}</th>
                    <th class="px-5 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wider hidden xl:table-cell">{{ __('app.col_education') }}</th>
                    @if($canManageResidents)
                      <th class="px-5 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wider text-center">{{ __('app.table_actions') }}</th>
                    @endif
                  </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                  @foreach($householder->residents as $member)
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition-colors">
                      <td class="px-5 py-4">
                        <div class="flex items-center gap-2">
                          <span class="font-semibold text-slate-900 dark:text-white">{{ $member->fullname }}</span>
                          @if($member->is_head)
                            <span class="px-1.5 py-0.5 text-[10px] font-bold bg-primary/10 text-primary rounded-full uppercase tracking-wider">Head</span>
                          @endif
                        </div>
                      </td>
                      <td class="px-5 py-4 hidden sm:table-cell">
                        @if($member->photoUrl())
                          <img src="{{ $member->photoUrl() }}" alt="{{ $member->fullname }}"
                            class="w-9 h-9 rounded-full object-cover border-2 border-slate-200 dark:border-slate-700 cursor-pointer"
                            onclick="openPhotoLightbox('{{ $member->photoUrl() }}', '{{ addslashes($member->fullname) }}')">
                        @else
                          <div class="w-9 h-9 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center">
                            <span class="material-icons text-slate-400 text-lg">person</span>
                          </div>
                        @endif
                      </td>
                      <td class="px-5 py-4 text-slate-600 dark:text-slate-400">{{ $member->relationshipLabel() }}</td>
                      <td class="px-5 py-4 text-slate-500 font-mono text-xs hidden">
                        <span id="nik-{{ $member->id }}">{{ $member->maskedNik() }}</span>
                        @if($showRevealButtons && $member->nik)
                          <button type="button"
                            onclick="revealNIK('{{ route('householders.residents.reveal-nik', [$householder, $member]) }}', '{{ $member->id }}', this)"
                            class="ml-1 text-slate-400 hover:text-primary transition-colors align-middle"
                            title="Reveal full NIK (admin only)">
                            <span class="material-icons text-[14px]">visibility</span>
                          </button>
                        @endif
                      </td>
                      <td class="px-5 py-4 text-slate-500 hidden lg:table-cell capitalize">{{ $member->gender ? __('app.mf_gender_' . $member->gender) : '—' }}</td>
                      <td class="px-5 py-4 text-slate-500 text-xs hidden xl:table-cell">{{ $member->educationLabel() }}</td>
                      @if($canManageResidents)
                        <td class="px-5 py-4">
                          <div class="flex items-center justify-center gap-1">
                            {{-- Edit --}}
                            <button type="button"
                              onclick="openMemberModal({{ json_encode(['id' => $member->id, 'fullname' => $member->fullname, 'relationship' => $member->relationship, 'nik_masked' => $member->maskedNik(), 'birth_date' => is_string($member->birth_date) ? $member->birth_date : $member->birth_date?->format('Y-m-d'), 'gender' => $member->gender, 'education' => $member->education, 'occupation' => $member->occupation, 'phone' => $member->phone, 'photo_url' => $member->photoUrl()]) }})"
                              class="p-1.5 text-slate-400 hover:text-primary hover:bg-primary/5 rounded-lg transition-colors"
                              title="Edit member">
                              <span class="material-icons text-lg">edit</span>
                            </button>
                            {{-- Set as Head --}}
                            @if(!$member->is_head)
                              <form method="POST" action="{{ $residentsBase . '/' . $member->id . '/set-head' }}">
                                @csrf @method('PATCH')
                                <button type="submit"
                                  class="p-1.5 text-slate-400 hover:text-amber-500 hover:bg-amber-50 dark:hover:bg-amber-900/20 rounded-lg transition-colors"
                                  title="Set as Head of Family">
                                  <span class="material-icons text-lg">star</span>
                                </button>
                              </form>
                            @else
                              <span class="p-1.5 text-amber-400 rounded-lg" title="Current Head of Family">
                                <span class="material-icons text-lg">star</span>
                              </span>
                            @endif
                            {{-- Delete --}}
                            <button type="button"
                              onclick="openDeleteMemberModal('{{ $member->id }}', '{{ addslashes($member->fullname) }}')"
                              class="p-1.5 text-slate-400 hover:text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-900/20 rounded-lg transition-colors"
                              title="Remove member">
                              <span class="material-icons text-lg">delete_forever</span>
                            </button>
                          </div>
                        </td>
                      @endif
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          @endif
        </div>

        @if($householder->house_status === 'rented')
          <p class="mt-3 text-xs text-amber-600 dark:text-amber-400 flex items-start gap-1.5">
            <span class="material-icons text-sm shrink-0 mt-0.5">info</span>
            <span>{!! __('app.rented_info_note') !!}</span>
          </p>
        @endif
      </section>

    </div>
  </div>

  {{-- ════════════════════════════════════════════════════════════════ --}}
  {{-- ADD / EDIT FAMILY MEMBER MODAL                                  --}}
  {{-- ════════════════════════════════════════════════════════════════ --}}
  <div id="member-modal"
    class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm hidden items-center justify-center p-4"
    onclick="if(event.target===this) closeMemberModal()">
    <div class="bg-white dark:bg-slate-900 w-full max-w-xl rounded-2xl shadow-2xl flex flex-col max-h-[92vh] overflow-hidden">

      {{-- Header --}}
      <div class="px-8 py-5 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center shrink-0">
        <h2 id="member-modal-title" class="text-xl font-extrabold text-slate-900 dark:text-white">{{ __('app.add_family_member') }}</h2>
        <button onclick="closeMemberModal()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors">
          <span class="material-icons">close</span>
        </button>
      </div>

      {{-- Body --}}
      <div class="flex-1 overflow-y-auto px-8 py-6">
        <form id="member-form" method="POST" action="{{ route('householders.residents.store', $householder) }}" enctype="multipart/form-data" class="space-y-4">
          @csrf
          <input type="hidden" name="_method" id="member-method" value="POST">
          <input type="hidden" name="_member_form" value="1">

          @php
            $ib = 'w-full px-3 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none transition-all dark:text-white';
          @endphp

          {{-- Full Name --}}
          <div>
            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
              {{ __('app.mf_full_name') }} <span class="text-rose-500">*</span>
            </label>
            <input type="text" name="fullname" id="mf-fullname" placeholder="{{ __('app.mf_full_name') }}"
              class="{{ $ib }} @error('fullname') border-rose-400 @enderror">
            @error('fullname') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
          </div>

          {{-- Phone Number --}}
          <div class="hidden">
            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
              {{ __('app.mf_phone') }} <span class="text-xs font-normal text-slate-400">{{ __('app.optional') }}</span>
            </label>
            <input type="tel" name="phone" id="mf-phone" placeholder="{{ __('app.mf_phone_ph') }}"
              class="{{ $ib }}">
          </div>

          {{-- Photo --}}
          <div>
            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">{{ __('app.mf_photo') }}</label>
            <div class="flex items-center gap-4">
              <div id="mf-photo-preview-wrap" class="w-14 h-14 rounded-full overflow-hidden flex-shrink-0 bg-slate-100 dark:bg-slate-800 border-2 border-slate-200 dark:border-slate-700 flex items-center justify-center">
                <img id="mf-photo-preview" src="" alt="" class="w-full h-full object-cover hidden">
                <span id="mf-photo-icon" class="material-icons text-slate-400 text-2xl">person</span>
              </div>
              <div>
                <label for="mf-photo" class="inline-flex items-center gap-2 cursor-pointer px-3 py-1.5 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-lg text-sm font-semibold transition-all border border-slate-200 dark:border-slate-700">
                  <span class="material-icons text-sm">upload</span> {{ __('app.mf_upload_photo') }}
                </label>
                <input id="mf-photo" type="file" name="photo" accept="image/*" class="hidden"
                  onchange="previewMemberPhoto(event)">
                <p class="text-xs text-slate-400 mt-1">{{ __('app.mf_photo_hint') }}</p>
              </div>
            </div>
          </div>

          <div class="grid grid-cols-2 gap-4">
            {{-- Relationship --}}
            <div>
              <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                {{ __('app.mf_relationship') }} <span class="text-rose-500">*</span>
              </label>
              <div class="relative">
                <select name="relationship" id="mf-relationship" class="{{ $ib }} appearance-none pr-9">
                  <option value="head">{{ __('app.rel_head') }}</option>
                  <option value="spouse">{{ __('app.rel_spouse') }}</option>
                  <option value="child">{{ __('app.rel_child') }}</option>
                  <option value="parent">{{ __('app.rel_parent') }}</option>
                  <option value="tenant">{{ __('app.rel_tenant') }}</option>
                  <option value="other">{{ __('app.rel_other') }}</option>
                </select>
                <span class="material-icons absolute right-2.5 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400 text-[18px]">expand_more</span>
              </div>
              @error('relationship') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- NIK (Hidden for Data Privacy) --}}
            <div class="hidden">
              <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">{{ __('app.mf_nik') }}</label>
              <input type="text" name="nik" id="mf-nik" placeholder="{{ __('app.nik_placeholder') }}" maxlength="20"
                class="{{ $ib }}">
              <p id="mf-nik-hint" class="text-xs text-slate-400 mt-1 hidden">{{ __('app.mf_nik_blank_hint') }}</p>
            </div>
          </div>

          <div class="grid grid-cols-2 gap-4">
            {{-- Birth Date --}}
            <div>
              <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">{{ __('app.mf_birth_date') }}</label>
              <input type="date" name="birth_date" id="mf-birth_date" max="{{ now()->format('Y-m-d') }}"
                class="{{ $ib }} dark:[color-scheme:dark]">
            </div>

            {{-- Gender --}}
            <div>
              <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">{{ __('app.mf_gender') }}</label>
              <div class="relative">
                <select name="gender" id="mf-gender" class="{{ $ib }} appearance-none pr-9">
                  <option value="">{{ __('app.mf_gender_select') }}</option>
                  <option value="male">{{ __('app.mf_gender_male') }}</option>
                  <option value="female">{{ __('app.mf_gender_female') }}</option>
                </select>
                <span class="material-icons absolute right-2.5 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400 text-[18px]">expand_more</span>
              </div>
            </div>
          </div>

          <div class="grid grid-cols-2 gap-4">
            {{-- Education --}}
            <div>
              <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">{{ __('app.mf_education') }}</label>
              <div class="relative">
                <select name="education" id="mf-education" class="{{ $ib }} appearance-none pr-9">
                  <option value="">{{ __('app.mf_gender_select') }}</option>
                  <option value="none">{{ __('app.edu_none') }}</option>
                  <option value="elementary">{{ __('app.edu_elementary') }}</option>
                  <option value="junior_high">{{ __('app.edu_junior_high') }}</option>
                  <option value="senior_high">{{ __('app.edu_senior_high') }}</option>
                  <option value="associate">{{ __('app.edu_associate') }}</option>
                  <option value="bachelor">{{ __('app.edu_bachelor') }}</option>
                  <option value="master">{{ __('app.edu_master') }}</option>
                  <option value="doctorate">{{ __('app.edu_doctorate') }}</option>
                  <option value="other">{{ __('app.edu_other') }}</option>
                </select>
                <span class="material-icons absolute right-2.5 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400 text-[18px]">expand_more</span>
              </div>
            </div>

            {{-- Occupation --}}
            <div>
              <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">{{ __('app.mf_occupation') }}</label>
              <input type="text" name="occupation" id="mf-occupation" placeholder="{{ __('app.mf_occupation_ph') }}"
                class="{{ $ib }}">
            </div>
          </div>

          {{-- Footer --}}
          <div class="flex gap-3 pt-3">
            <button type="button" onclick="closeMemberModal()"
              class="flex-1 py-2.5 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-bold text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
              {{ __('app.mf_cancel') }}
            </button>
            <button type="submit"
              class="flex-1 py-2.5 bg-indigo-600 text-white rounded-xl text-sm font-bold hover:bg-indigo-700 shadow-sm transition-all flex items-center justify-center gap-2">
              <span class="material-icons text-sm">save</span>
              <span id="member-submit-label">{{ __('app.btn_add_member') }}</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  {{-- Photo Lightbox --}}
  <div id="photo-lightbox"
    class="fixed inset-0 z-50 bg-slate-900/80 backdrop-blur-sm hidden items-center justify-center p-4"
    onclick="if(event.target===this) closePhotoLightbox()">
    <div class="relative max-w-sm w-full">
      <button onclick="closePhotoLightbox()" class="absolute -top-3 -right-3 z-10 bg-white dark:bg-slate-800 rounded-full p-1.5 shadow-lg text-slate-500 hover:text-slate-800 dark:hover:text-white transition-colors">
        <span class="material-icons text-lg">close</span>
      </button>
      <img id="photo-lightbox-img" src="" alt="" class="w-full rounded-2xl shadow-2xl object-contain max-h-[70vh]">
      <p id="photo-lightbox-name" class="mt-3 text-center text-sm font-semibold text-white"></p>
    </div>
  </div>

  {{-- Delete Member Confirm --}}
  <div id="delete-member-modal"
    class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm hidden items-center justify-center p-4"
    onclick="if(event.target===this) closeDeleteMemberModal()">
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-2xl w-full max-w-sm p-8 text-center">
      <div class="w-14 h-14 rounded-full bg-rose-100 dark:bg-rose-900/30 flex items-center justify-center mx-auto mb-4">
        <span class="material-icons text-2xl text-rose-600">delete_forever</span>
      </div>
      <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-2">{{ __('app.mf_remove_member_title') }}</h3>
      <p id="delete-member-body" class="text-sm text-slate-500 mb-6 leading-relaxed"></p>
      <div class="flex gap-3">
        <button onclick="closeDeleteMemberModal()"
          class="flex-1 py-2.5 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-bold text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
          {{ __('app.mf_cancel') }}
        </button>
        <form id="delete-member-form" method="POST" action="" class="flex-1">
          @csrf @method('DELETE')
          <button type="submit"
            class="w-full py-2.5 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-sm font-bold transition-all">
            {{ __('app.mf_remove') }}
          </button>
        </form>
      </div>
    </div>
  </div>

  <script>
    const residentId   = '{{ $householder->id }}';
    const membersBase  = '{{ $residentsBase }}';
    const i18nAddFamilyMember  = '{{ __('app.add_family_member') }}';
    const i18nEditFamilyMember = '{{ __('app.edit_family_member') }}';
    const i18nAddMember        = '{{ __('app.btn_add_member') }}';
    const i18nSaveChanges      = '{{ __('app.btn_save_changes') }}';
    const i18nNikPlaceholder   = '{{ __('app.nik_placeholder') }}';

    // ── Unit AJAX loader ─────────────────────────────────────────────────────
    const editPageApiBlocksUrl = "{{ url('/api/blocks') }}";
    const editPageCurrentUnitId = "{{ $householder->unit_id }}";

    // unit id → { house_status, house_status_label } — populated by loadUnitsOnEdit
    let editPageUnitStatusMap = {};

    const hsBadgeClasses = {
      owner_occupied: 'bg-primary/10 text-primary',
      rented:         'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
      vacant:         'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400',
    };

    function updateHouseStatusBadge(unitId) {
      const badge = document.getElementById('hs-badge-span');
      if (!badge) return;
      const info = editPageUnitStatusMap[unitId];
      if (!info) return;
      // Swap colour classes
      Object.values(hsBadgeClasses).forEach(cls => cls.split(' ').forEach(c => badge.classList.remove(c)));
      (hsBadgeClasses[info.house_status] ?? hsBadgeClasses.owner_occupied)
        .split(' ').forEach(c => badge.classList.add(c));
      badge.textContent = info.house_status_label;

      // Show/hide rent period section and FCN rent hint based on house status
      const isRented = info.house_status === 'rented';
      const rentSection = document.getElementById('rent-period-section');
      const fcnHint     = document.getElementById('fcn-rent-hint');
      if (rentSection) rentSection.classList.toggle('hidden', !isRented);
      if (fcnHint) {
        fcnHint.classList.toggle('hidden', !isRented);
        fcnHint.classList.toggle('flex', isRented);
      }
    }

    async function loadUnitsOnEdit(blockId, selectedUnitId) {
      const sel = document.getElementById('edit-page-unit_id');
      if (!sel) return;
      const currentUnit = selectedUnitId ?? editPageCurrentUnitId;
      if (!blockId) {
        sel.innerHTML = '<option value="">{{ __('app.select_block') }}</option>';
        return;
      }
      sel.innerHTML = '<option value="">{{ __('app.units_loading') }}</option>';
      try {
        const qs = currentUnit ? '?current_unit_id=' + currentUnit : '';
        const res = await fetch(`${editPageApiBlocksUrl}/${blockId}/units${qs}`);
        const units = await res.json();
        if (!units.length) {
          sel.innerHTML = '<option value="">{{ __('app.no_units_in_block') }}</option>';
          return;
        }
        // Build status map for badge updates
        editPageUnitStatusMap = {};
        units.forEach(u => { editPageUnitStatusMap[u.id] = { house_status: u.house_status, house_status_label: u.house_status_label }; });
        sel.innerHTML = '<option value="">{{ __('app.select_unit') }}</option>';
        units.forEach(u => {
          const opt = document.createElement('option');
          opt.value = u.id;
          opt.textContent = u.unit_number + (u.is_occupied ? ' ({{ __('app.occupied_count') }})' : '');
          opt.disabled = u.is_occupied;
          if (u.id === currentUnit) opt.selected = true;
          sel.appendChild(opt);
        });
        // Update badge for the pre-selected unit
        updateHouseStatusBadge(sel.value);
      } catch {
        sel.innerHTML = '<option value="">{{ __('app.select_unit') }}</option>';
      }
    }

    // Load units on page ready (pre-select current unit)
    // The static <option> already shows the correct unit immediately.
    // We trigger an AJAX load so the user can switch units, but we
    // set the selected value AFTER the units load to avoid a flash.
    document.addEventListener('DOMContentLoaded', function () {
      const blockSel = document.getElementById('edit-page-block_id');
      const unitSel  = document.getElementById('edit-page-unit_id');
      if (blockSel && unitSel && blockSel.value) {
        // Keep current option visible while loading
        const currentUnitOption = unitSel.querySelector('option');
        const savedUnitId = editPageCurrentUnitId;
        loadUnitsOnEdit(blockSel.value, savedUnitId).then(() => {
          // Ensure the saved unit is selected after load completes
          if (savedUnitId && unitSel.querySelector(`option[value="${savedUnitId}"]`)) {
            unitSel.value = savedUnitId;
          }
        });
      }
    });
    function previewResidentPhoto(event) {
      const file = event.target.files[0];
      if (!file) return;
      const preview = document.getElementById('resident-photo-preview');
      const icon    = document.getElementById('resident-photo-icon');
      const reader  = new FileReader();
      reader.onload = e => {
        preview.src = e.target.result;
        preview.classList.remove('hidden');
        if (icon) icon.classList.add('hidden');
      };
      reader.readAsDataURL(file);
    }

    // ── Sensitive Data Reveal (admin-only) ──────────────────────────────────
    function revealFCN(url, btn) {
      const input = document.getElementById('fcn-input');
      const icon  = btn.querySelector('.material-icons');
      if (btn.dataset.revealed) {
        input.value = '';
        icon.textContent = 'visibility';
        delete btn.dataset.revealed;
        return;
      }
      fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '' } })
        .then(r => r.json())
        .then(d => {
          input.value = d.value;
          input.placeholder = '';
          icon.textContent  = 'visibility_off';
          btn.dataset.revealed = '1';
        })
        .catch(() => alert('Could not reveal value. Please try again.'));
    }

    function revealNIK(url, memberId, btn) {
      const span  = document.getElementById('nik-' + memberId);
      const icon  = btn.querySelector('.material-icons');
      if (btn.dataset.revealed) {
        span.textContent = btn.dataset.masked;
        icon.textContent = 'visibility';
        delete btn.dataset.revealed;
        return;
      }
      btn.dataset.masked = span.textContent;
      fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '' } })
        .then(r => r.json())
        .then(d => {
          span.textContent = d.value || '—';
          icon.textContent = 'visibility_off';
          btn.dataset.revealed = '1';
        })
        .catch(() => alert('Could not reveal value. Please try again.'));
    }

    // ── Member Modal ─────────────────────────────────────────────────
    function resetMemberPhotoPreview(photoUrl) {
      const img  = document.getElementById('mf-photo-preview');
      const icon = document.getElementById('mf-photo-icon');
      if (photoUrl) {
        img.src = photoUrl;
        img.classList.remove('hidden');
        icon.classList.add('hidden');
      } else {
        img.src = '';
        img.classList.add('hidden');
        icon.classList.remove('hidden');
      }
    }

    function previewMemberPhoto(event) {
      const file = event.target.files[0];
      if (!file) return;
      const reader = new FileReader();
      reader.onload = e => resetMemberPhotoPreview(e.target.result);
      reader.readAsDataURL(file);
    }

    function openMemberModal(data) {
      const modal = document.getElementById('member-modal');
      const form  = document.getElementById('member-form');

      // Reset
      form.reset();
      resetMemberPhotoPreview(null);
      document.getElementById('member-method').value = 'POST';
      form.action = membersBase;
      document.getElementById('member-modal-title').textContent  = i18nAddFamilyMember;
      document.getElementById('member-submit-label').textContent = i18nAddMember;
      document.getElementById('mf-nik').placeholder = i18nNikPlaceholder;
      document.getElementById('mf-nik-hint').classList.add('hidden');
      document.getElementById('mf-phone').value = '';

      if (data) {
        // Edit mode
        document.getElementById('member-method').value = 'PUT';
        form.action = membersBase + '/' + data.id;
        document.getElementById('member-modal-title').textContent  = i18nEditFamilyMember;
        document.getElementById('member-submit-label').textContent = i18nSaveChanges;

        document.getElementById('mf-fullname').value      = data.fullname    || '';
        document.getElementById('mf-relationship').value  = data.relationship || '';
        document.getElementById('mf-nik').value           = '';
        document.getElementById('mf-nik').placeholder     = data.nik_masked && data.nik_masked !== '—' ? data.nik_masked : i18nNikPlaceholder;
        document.getElementById('mf-nik-hint').classList.toggle('hidden', !data.nik_masked || data.nik_masked === '—');
        document.getElementById('mf-birth_date').value    = data.birth_date  || '';
        document.getElementById('mf-gender').value        = data.gender      || '';
        document.getElementById('mf-education').value     = data.education   || '';
        document.getElementById('mf-occupation').value    = data.occupation  || '';
        document.getElementById('mf-phone').value         = data.phone       || '';
        resetMemberPhotoPreview(data.photo_url || null);
      }

      modal.classList.remove('hidden'); modal.classList.add('flex');
      document.body.classList.add('overflow-hidden');
    }

    function closeMemberModal() {
      const modal = document.getElementById('member-modal');
      modal.classList.add('hidden'); modal.classList.remove('flex');
      document.body.classList.remove('overflow-hidden');
    }

    // ── Photo Lightbox ───────────────────────────────────────────────
    function openPhotoLightbox(url, name) {
      document.getElementById('photo-lightbox-img').src  = url;
      document.getElementById('photo-lightbox-name').textContent = name;
      const lb = document.getElementById('photo-lightbox');
      lb.classList.remove('hidden'); lb.classList.add('flex');
      document.body.classList.add('overflow-hidden');
    }
    function closePhotoLightbox() {
      const lb = document.getElementById('photo-lightbox');
      lb.classList.add('hidden'); lb.classList.remove('flex');
      document.body.classList.remove('overflow-hidden');
    }

    // ── Delete Member Modal ──────────────────────────────────────────
    function openDeleteMemberModal(memberId, memberName) {
      document.getElementById('delete-member-body').innerHTML =
        `<strong class="text-slate-800 dark:text-slate-200">${memberName}</strong> will be permanently removed from this household.`;
      document.getElementById('delete-member-form').action = membersBase + '/' + memberId;
      const modal = document.getElementById('delete-member-modal');
      modal.classList.remove('hidden'); modal.classList.add('flex');
      document.body.classList.add('overflow-hidden');
    }

    function closeDeleteMemberModal() {
      const modal = document.getElementById('delete-member-modal');
      modal.classList.add('hidden'); modal.classList.remove('flex');
      document.body.classList.remove('overflow-hidden');
    }

    document.addEventListener('keydown', e => {
      if (e.key === 'Escape') { closeMemberModal(); closeDeleteMemberModal(); closePhotoLightbox(); }
    });

    // ── Fetch-based member form submission ───────────────────────────────────
    // Using fetch instead of native form submission avoids the browser navigating
    // to the action URL (which would allow a refresh to re-send GET to a POST-only
    // route and cause a 405). Fetch also handles multipart/file uploads correctly.
    document.getElementById('member-form').addEventListener('submit', function (e) {
      e.preventDefault();
      const form = this;
      const submitBtn = form.querySelector('button[type="submit"]');
      const originalLabel = document.getElementById('member-submit-label').textContent;

      submitBtn.disabled = true;
      document.getElementById('member-submit-label').textContent = 'Saving…';

      fetch(form.action, {
        method: 'POST',
        body: new FormData(form),
        credentials: 'same-origin',
      }).then(async function (response) {
        if (!response.ok) {
            const html = await response.text();
            document.open(); document.write(html); document.close();
            return;
        }
        // response.url is the final URL after any redirects (success → edit page, error → back to edit page)
        window.location.href = response.url;
      }).catch(function () {
        submitBtn.disabled = false;
        document.getElementById('member-submit-label').textContent = originalLabel;
        alert('Submission failed. Please check your connection and try again.');
      });
    });

    // Re-open member modal on validation failure
    @if($errors->any() && old('_member_form'))
      document.addEventListener('DOMContentLoaded', () => {
        openMemberModal({
          id:           '{{ old("_member_id") }}',
          fullname:     '{{ old("fullname") }}',
          relationship: '{{ old("relationship") }}',
          nik:          '{{ old("nik") }}',
          birth_date:   '{{ old("birth_date") }}',
          gender:       '{{ old("gender") }}',
          education:    '{{ old("education") }}',
          occupation:   '{{ old("occupation") }}',
          phone:        '{{ old("phone") }}',
        });
      });
    @endif
  </script>

</x-layouts.app>



