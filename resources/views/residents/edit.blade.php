{{-- Household Edit Page — Section A: Household Info | Section B: Family Members --}}
<x-layouts.app :title="'Edit Household — ' . $resident->block->name . ' · ' . $resident->unit_number">

  <x-nav.sidebar active="residents" />

  <div class="lg:pl-64 min-h-screen bg-background-light dark:bg-background-dark flex flex-col">

    {{-- Page Header --}}
    <header class="h-16 bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between px-6 lg:px-8 shrink-0">
      <div class="flex items-center gap-3">
        <button class="lg:hidden p-2 rounded-lg border border-slate-200 dark:border-slate-800" onclick="toggleSidebar()">
          <span class="material-icons text-slate-500">menu</span>
        </button>
        <a href="{{ route('residents.index') }}"
          class="p-2 rounded-lg text-slate-400 hover:text-primary hover:bg-primary/5 transition-all">
          <span class="material-icons">arrow_back</span>
        </a>
        <div>
          <h1 class="text-xl font-bold text-slate-900 dark:text-white leading-tight">Edit Household</h1>
          <p class="text-xs text-slate-400">{{ $resident->block->name }} &middot; Unit {{ $resident->unit_number }}</p>
        </div>
      </div>
      <div class="flex items-center gap-3">
        <span class="hidden sm:inline px-2.5 py-1 rounded-lg text-xs font-bold {{ $resident->is_active ? 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400' : 'bg-slate-100 dark:bg-slate-800 text-slate-500' }}">
          {{ $resident->is_active ? 'Active' : 'Inactive' }}
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
            <h2 class="text-lg font-bold text-slate-900 dark:text-white">Household Information</h2>
            <p class="text-xs text-slate-400">Unit details, contact, classification and billing.</p>
          </div>
        </div>

        @php
          $ib = 'w-full px-3 py-2.5 bg-slate-50 dark:bg-slate-800 border rounded-lg text-sm focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none transition-all dark:text-white';
          $in = 'border-slate-200 dark:border-slate-700';
          $ie = 'border-rose-400';
        @endphp

        <form method="POST" action="{{ route('residents.update', $resident) }}" class="space-y-5">
          @csrf @method('PATCH')

          {{-- Unit Details --}}
          <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-6 space-y-5">
            <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Unit Details</h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
              {{-- Block --}}
              <div>
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                  Block <span class="text-rose-500">*</span>
                </label>
                <div class="relative">
                  <select name="block_id" class="{{ $ib }} {{ $errors->has('block_id') ? $ie : $in }} appearance-none pl-3 pr-9">
                    @foreach($blocks as $block)
                      <option value="{{ $block->id }}" {{ old('block_id', $resident->block_id) == $block->id ? 'selected' : '' }}>
                        {{ $block->name }}
                      </option>
                    @endforeach
                  </select>
                  <span class="material-icons absolute right-2.5 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400 text-[18px]">expand_more</span>
                </div>
                @error('block_id') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
              </div>

              {{-- Unit Number --}}
              <div>
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                  Unit Number <span class="text-rose-500">*</span>
                </label>
                <input type="text" name="unit_number"
                  value="{{ old('unit_number', $resident->unit_number) }}"
                  placeholder="e.g. A-101"
                  class="{{ $ib }} {{ $errors->has('unit_number') ? $ie : $in }}">
                @error('unit_number') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
              </div>

              {{-- Owner / Contact Name --}}
              <div>
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                  Owner / Contact Name <span class="text-rose-500">*</span>
                </label>
                <input type="text" name="fullname"
                  value="{{ old('fullname', $resident->fullname) }}"
                  placeholder="Full name of owner or primary contact"
                  class="{{ $ib }} {{ $errors->has('fullname') ? $ie : $in }}">
                <p class="text-xs text-slate-400 mt-1">Used as fallback when no Head of Family is set.</p>
                @error('fullname') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
              </div>

              {{-- Family Card Number --}}
              <div>
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                  Family Card Number <span class="text-xs font-normal text-slate-400">(No. KK)</span>
                </label>
                <input type="text" name="family_card_number"
                  value="{{ old('family_card_number') }}"
                  placeholder="{{ $resident->family_card_number ? $resident->maskedFamilyCardNumber() : 'e.g. 3174012345678901' }}"
                  maxlength="20"
                  class="{{ $ib }} {{ $errors->has('family_card_number') ? $ie : $in }}">
                @if($resident->family_card_number)
                  <p class="text-xs text-slate-400 mt-1">Leave blank to keep existing value.</p>
                @endif
                @error('family_card_number') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
              </div>

              {{-- Phone --}}
              <div>
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Phone Number</label>
                <input type="tel" name="phone"
                  value="{{ old('phone', $resident->phone) }}"
                  placeholder="+62 812 xxxx xxxx"
                  class="{{ $ib }} {{ $errors->has('phone') ? $ie : $in }}">
                @error('phone') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
              </div>

              {{-- Email --}}
              <div>
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                  Email Address
                  <span class="text-xs font-normal text-slate-400">(links to user account)</span>
                </label>
                <input type="email" name="email"
                  value="{{ old('email', $resident->email) }}"
                  placeholder="household@example.com"
                  class="{{ $ib }} {{ $errors->has('email') ? $ie : $in }}">
                @error('email') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
              </div>
            </div>
          </div>

          {{-- Classification --}}
          <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-6 space-y-5">
            <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Classification</h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
              {{-- House Status --}}
              <div>
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                  House Status <span class="text-rose-500">*</span>
                </label>
                <div class="relative">
                  <select name="house_status" class="{{ $ib }} {{ $errors->has('house_status') ? $ie : $in }} appearance-none pl-3 pr-9">
                    @foreach(['owner_occupied' => 'Owner Occupied', 'vacant' => 'Vacant', 'rented' => 'Rented'] as $val => $label)
                      <option value="{{ $val }}" {{ old('house_status', $resident->house_status ?? 'owner_occupied') === $val ? 'selected' : '' }}>
                        {{ $label }}
                      </option>
                    @endforeach
                  </select>
                  <span class="material-icons absolute right-2.5 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400 text-[18px]">expand_more</span>
                </div>
                @error('house_status') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
              </div>

              {{-- Active Status toggle --}}
              <div class="flex items-center">
                <label class="flex items-center gap-3 cursor-pointer p-3 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors w-full border border-slate-200 dark:border-slate-700">
                  <input type="checkbox" name="is_active" value="1"
                    {{ old('is_active', $resident->is_active) ? 'checked' : '' }}
                    class="w-4 h-4 text-primary rounded border-slate-300 focus:ring-primary/20">
                  <div>
                    <span class="text-sm font-semibold text-slate-700 dark:text-slate-300">Active Household</span>
                    <p class="text-xs text-slate-400">Uncheck to deactivate this unit.</p>
                  </div>
                </label>
              </div>
            </div>

            {{-- Notes --}}
            <div>
              <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                Notes <span class="text-xs font-normal text-slate-400">(optional)</span>
              </label>
              <textarea name="notes" rows="3"
                placeholder="Any additional notes about this household or unit..."
                class="{{ $ib }} {{ $errors->has('notes') ? $ie : $in }} resize-y"
              >{{ old('notes', $resident->notes) }}</textarea>
              @error('notes') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
            </div>
          </div>

          {{-- Fee Management --}}
          <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-6 space-y-4">
            <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Fee Management</h3>

            @php $currentFeeRecord = $resident->feeHistories->first(); @endphp
            @if($currentFeeRecord)
              <div class="flex items-center gap-4 p-3 bg-primary/5 dark:bg-primary/10 rounded-lg border border-primary/20">
                <span class="material-icons text-primary">payments</span>
                <div>
                  <p class="text-sm font-bold text-slate-900 dark:text-white">
                    {{ $currency }} {{ number_format($currentFeeRecord->amount, 0, ',', '.') }} / month
                  </p>
                  <p class="text-xs text-slate-500">
                    Effective from {{ $currentFeeRecord->effective_from->format('F Y') }}
                    @if($currentFeeRecord->notes) &middot; {{ $currentFeeRecord->notes }} @endif
                  </p>
                </div>
              </div>
            @else
              <p class="text-sm text-slate-400 italic">No fee history yet.</p>
            @endif

            <div class="rounded-xl bg-amber-50 dark:bg-amber-900/10 border border-amber-200 dark:border-amber-700/30 p-4 space-y-4">
              <p class="text-xs font-semibold text-amber-700 dark:text-amber-400 flex items-center gap-1.5">
                <span class="material-icons text-sm">info</span>
                To update the fee, fill the fields below. Leave blank to keep the current fee.
              </p>
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                    New Monthly Fee ({{ $currency }})
                  </label>
                  <input type="number" name="new_monthly_fee" min="0" step="1000"
                    value="{{ old('new_monthly_fee') }}"
                    placeholder="Leave blank to keep current"
                    class="{{ $ib }} {{ $in }}">
                </div>
                <div>
                  <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                    Effective From
                  </label>
                  <input type="month" name="new_fee_start"
                    value="{{ old('new_fee_start', now()->format('Y-m')) }}"
                    class="{{ $ib }} {{ $in }} dark:[color-scheme:dark]">
                </div>
              </div>
            </div>
          </div>

          {{-- Save Button --}}
          <div class="flex justify-end">
            <button type="submit"
              class="flex items-center gap-2 bg-primary hover:bg-primary/90 text-white px-6 py-2.5 rounded-lg font-semibold transition-all shadow-sm shadow-primary/20 text-sm">
              <span class="material-icons text-sm">save</span>
              Save Household
            </button>
          </div>
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
                Family Members
                @if($resident->familyMembers->count() > 0)
                  <span class="ml-2 px-2 py-0.5 text-xs font-bold bg-slate-100 dark:bg-slate-800 text-slate-500 rounded-full">
                    {{ $resident->familyMembers->count() }}
                  </span>
                @endif
              </h2>
              <p class="text-xs text-slate-400">People living in this household.</p>
            </div>
          </div>
          @if(auth()->user()->can('residents.edit'))
            <button onclick="openMemberModal()"
              class="flex items-center gap-2 text-sm font-semibold px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition-all shadow-sm">
              <span class="material-icons text-sm">person_add</span>
              <span class="hidden sm:inline">Add Member</span>
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
          @if($resident->familyMembers->isEmpty())
            <div class="flex flex-col items-center gap-3 py-16 text-slate-400">
              <span class="material-icons text-5xl">group_off</span>
              <p class="text-sm font-medium">No family members added yet.</p>
              @if(auth()->user()->can('residents.edit'))
                <button onclick="openMemberModal()"
                  class="text-primary text-sm hover:underline font-semibold">
                  + Add the first member
                </button>
              @endif
            </div>
          @else
            <div class="overflow-x-auto">
              <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-800">
                  <tr>
                    <th class="px-5 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wider">Name</th>
                    <th class="px-5 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wider">Relationship</th>
                    <th class="px-5 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wider hidden md:table-cell">NIK</th>
                    <th class="px-5 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wider hidden lg:table-cell">Birth Date</th>
                    <th class="px-5 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wider hidden lg:table-cell">Gender</th>
                    <th class="px-5 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wider hidden xl:table-cell">Education</th>
                    <th class="px-5 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wider hidden xl:table-cell">Occupation</th>
                    @if(auth()->user()->can('residents.edit'))
                      <th class="px-5 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wider text-center">Actions</th>
                    @endif
                  </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                  @foreach($resident->familyMembers as $member)
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition-colors">
                      <td class="px-5 py-4">
                        <div class="flex items-center gap-2">
                          <span class="font-semibold text-slate-900 dark:text-white">{{ $member->fullname }}</span>
                          @if($member->is_head)
                            <span class="px-1.5 py-0.5 text-[10px] font-bold bg-primary/10 text-primary rounded-full uppercase tracking-wider">Head</span>
                          @endif
                        </div>
                      </td>
                      <td class="px-5 py-4 text-slate-600 dark:text-slate-400">{{ $member->relationshipLabel() }}</td>
                      <td class="px-5 py-4 text-slate-500 font-mono text-xs hidden md:table-cell">{{ $member->maskedNik() }}</td>
                      <td class="px-5 py-4 text-slate-500 hidden lg:table-cell">
                        {{ $member->birth_date ? $member->birth_date->format('d M Y') . ' (' . $member->birth_date->age . ' yrs)' : '—' }}
                      </td>
                      <td class="px-5 py-4 text-slate-500 hidden lg:table-cell capitalize">{{ $member->gender ?? '—' }}</td>
                      <td class="px-5 py-4 text-slate-500 text-xs hidden xl:table-cell">{{ $member->educationLabel() }}</td>
                      <td class="px-5 py-4 text-slate-500 hidden xl:table-cell">{{ $member->occupation ?? '—' }}</td>
                      @if(auth()->user()->can('residents.edit'))
                        <td class="px-5 py-4">
                          <div class="flex items-center justify-center gap-1">
                            {{-- Edit --}}
                            <button type="button"
                              onclick="openMemberModal({{ json_encode(['id' => $member->id, 'fullname' => $member->fullname, 'relationship' => $member->relationship, 'nik_masked' => $member->maskedNik(), 'birth_date' => $member->birth_date?->format('Y-m-d'), 'gender' => $member->gender, 'education' => $member->education, 'occupation' => $member->occupation]) }})"
                              class="p-1.5 text-slate-400 hover:text-primary hover:bg-primary/5 rounded-lg transition-colors"
                              title="Edit member">
                              <span class="material-icons text-lg">edit</span>
                            </button>
                            {{-- Set as Head --}}
                            @if(!$member->is_head)
                              <form method="POST" action="{{ route('residents.family-members.set-head', [$resident, $member]) }}">
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

        @if($resident->house_status === 'rented')
          <p class="mt-3 text-xs text-amber-600 dark:text-amber-400 flex items-start gap-1.5">
            <span class="material-icons text-sm shrink-0 mt-0.5">info</span>
            <span>This unit is <strong>Rented</strong>. The owner's data is preserved. Use the <strong>Set as Head (★)</strong> button to assign the current tenant as Head of Family.</span>
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
        <h2 id="member-modal-title" class="text-xl font-extrabold text-slate-900 dark:text-white">Add Family Member</h2>
        <button onclick="closeMemberModal()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors">
          <span class="material-icons">close</span>
        </button>
      </div>

      {{-- Body --}}
      <div class="flex-1 overflow-y-auto px-8 py-6">
        <form id="member-form" method="POST" action="{{ route('residents.family-members.store', $resident) }}" class="space-y-4">
          @csrf
          <input type="hidden" name="_method" id="member-method" value="POST">
          <input type="hidden" name="_member_form" value="1">

          @php
            $ib = 'w-full px-3 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none transition-all dark:text-white';
          @endphp

          {{-- Full Name --}}
          <div>
            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
              Full Name <span class="text-rose-500">*</span>
            </label>
            <input type="text" name="fullname" id="mf-fullname" placeholder="Enter full name"
              class="{{ $ib }} @error('fullname') border-rose-400 @enderror">
            @error('fullname') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
          </div>

          <div class="grid grid-cols-2 gap-4">
            {{-- Relationship --}}
            <div>
              <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                Relationship <span class="text-rose-500">*</span>
              </label>
              <div class="relative">
                <select name="relationship" id="mf-relationship" class="{{ $ib }} appearance-none pr-9">
                  @foreach(\App\Models\FamilyMember::$relationships as $val => $label)
                    <option value="{{ $val }}">{{ $label }}</option>
                  @endforeach
                </select>
                <span class="material-icons absolute right-2.5 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400 text-[18px]">expand_more</span>
              </div>
              @error('relationship') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- NIK --}}
            <div>
              <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">NIK / KTP</label>
              <input type="text" name="nik" id="mf-nik" placeholder="16-digit NIK" maxlength="20"
                class="{{ $ib }}">
              <p id="mf-nik-hint" class="text-xs text-slate-400 mt-1 hidden">Leave blank to keep existing value.</p>
            </div>
          </div>

          <div class="grid grid-cols-2 gap-4">
            {{-- Birth Date --}}
            <div>
              <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Birth Date</label>
              <input type="date" name="birth_date" id="mf-birth_date" max="{{ now()->format('Y-m-d') }}"
                class="{{ $ib }} dark:[color-scheme:dark]">
            </div>

            {{-- Gender --}}
            <div>
              <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Gender</label>
              <div class="relative">
                <select name="gender" id="mf-gender" class="{{ $ib }} appearance-none pr-9">
                  <option value="">— Select —</option>
                  <option value="male">Male</option>
                  <option value="female">Female</option>
                </select>
                <span class="material-icons absolute right-2.5 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400 text-[18px]">expand_more</span>
              </div>
            </div>
          </div>

          <div class="grid grid-cols-2 gap-4">
            {{-- Education --}}
            <div>
              <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Education</label>
              <div class="relative">
                <select name="education" id="mf-education" class="{{ $ib }} appearance-none pr-9">
                  <option value="">— Select —</option>
                  @foreach(\App\Models\FamilyMember::$educationLevels as $val => $label)
                    <option value="{{ $val }}">{{ $label }}</option>
                  @endforeach
                </select>
                <span class="material-icons absolute right-2.5 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400 text-[18px]">expand_more</span>
              </div>
            </div>

            {{-- Occupation --}}
            <div>
              <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Occupation</label>
              <input type="text" name="occupation" id="mf-occupation" placeholder="e.g. Teacher, Engineer"
                class="{{ $ib }}">
            </div>
          </div>

          {{-- Footer --}}
          <div class="flex gap-3 pt-3">
            <button type="button" onclick="closeMemberModal()"
              class="flex-1 py-2.5 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-bold text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
              Cancel
            </button>
            <button type="submit"
              class="flex-1 py-2.5 bg-indigo-600 text-white rounded-xl text-sm font-bold hover:bg-indigo-700 shadow-sm transition-all flex items-center justify-center gap-2">
              <span class="material-icons text-sm">save</span>
              <span id="member-submit-label">Add Member</span>
            </button>
          </div>
        </form>
      </div>
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
      <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-2">Remove Member?</h3>
      <p id="delete-member-body" class="text-sm text-slate-500 mb-6 leading-relaxed"></p>
      <div class="flex gap-3">
        <button onclick="closeDeleteMemberModal()"
          class="flex-1 py-2.5 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-bold text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
          Cancel
        </button>
        <form id="delete-member-form" method="POST" action="" class="flex-1">
          @csrf @method('DELETE')
          <button type="submit"
            class="w-full py-2.5 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-sm font-bold transition-all">
            Remove
          </button>
        </form>
      </div>
    </div>
  </div>

  <script>
    const residentId   = '{{ $resident->id }}';
    const membersBase  = '{{ url("/residents/{$resident->id}/family-members") }}';

    // ── Member Modal ─────────────────────────────────────────────────
    function openMemberModal(data) {
      const modal = document.getElementById('member-modal');
      const form  = document.getElementById('member-form');

      // Reset
      form.reset();
      document.getElementById('member-method').value = 'POST';
      form.action = membersBase;
      document.getElementById('member-modal-title').textContent  = 'Add Family Member';
      document.getElementById('member-submit-label').textContent = 'Add Member';
      document.getElementById('mf-nik').placeholder = '16-digit NIK';
      document.getElementById('mf-nik-hint').classList.add('hidden');

      if (data) {
        // Edit mode
        document.getElementById('member-method').value = 'PUT';
        form.action = membersBase + '/' + data.id;
        document.getElementById('member-modal-title').textContent  = 'Edit Family Member';
        document.getElementById('member-submit-label').textContent = 'Save Changes';

        document.getElementById('mf-fullname').value      = data.fullname   || '';
        document.getElementById('mf-relationship').value  = data.relationship || '';
        document.getElementById('mf-nik').value           = '';
        document.getElementById('mf-nik').placeholder     = data.nik_masked && data.nik_masked !== '—' ? data.nik_masked : '16-digit NIK';
        document.getElementById('mf-nik-hint').classList.toggle('hidden', !data.nik_masked || data.nik_masked === '—');
        document.getElementById('mf-birth_date').value    = data.birth_date  || '';
        document.getElementById('mf-gender').value        = data.gender      || '';
        document.getElementById('mf-education').value     = data.education   || '';
        document.getElementById('mf-occupation').value    = data.occupation  || '';
      }

      modal.classList.remove('hidden'); modal.classList.add('flex');
      document.body.classList.add('overflow-hidden');
    }

    function closeMemberModal() {
      const modal = document.getElementById('member-modal');
      modal.classList.add('hidden'); modal.classList.remove('flex');
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
      if (e.key === 'Escape') { closeMemberModal(); closeDeleteMemberModal(); }
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
        });
      });
    @endif
  </script>

</x-layouts.app>
