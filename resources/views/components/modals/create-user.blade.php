@php
  $roles = \App\Models\Role::orderBy('name')->get();
@endphp
{{-- ============================================================
Modal: Create User — wired to POST /users with real DB blocks
Trigger: openModal('modal-create')
============================================================ --}}
<div id="modal-create"
  class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden"
  onclick="closeModalOnBackdrop(event, 'modal-create')">
  <div
    class="bg-white dark:bg-slate-900 w-full max-w-xl rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-800 overflow-hidden">

    {{-- Header --}}
    <div class="px-8 py-6 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
      <div>
        <h2 class="text-xl font-bold text-slate-900 dark:text-white">Create New User</h2>
        <p class="text-sm text-slate-500 mt-0.5">Add staff and community administrators</p>
      </div>
      <button class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors"
        onclick="closeModal('modal-create')">
        <span class="material-icons">close</span>
      </button>
    </div>

    {{-- Form --}}
    <form id="form-create-user" method="POST" action="{{ route('users.store') }}"
      class="p-8 pt-0 space-y-5 max-h-[80vh] overflow-y-auto" novalidate>
      @csrf
      <input type="hidden" name="_form_type" value="create" />

      {{-- Name --}}
      <div class="space-y-1.5">
        <label class="text-xs font-bold text-slate-500 uppercase">Full Name <span class="text-red-500">*</span></label>
        <input id="cu-name" name="name" type="text" value="{{ old('name') }}"
          class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none dark:text-white @error('name') border-red-500 @enderror"
          placeholder="e.g. John Smith" oninput="clearUErr('js-cu-name')" />
        @error('name')
          <p class="text-xs text-red-500 flex items-center gap-1 mt-1">
            <span class="material-icons text-xs">error_outline</span> {{ $message }}
          </p>
        @enderror
        <p id="js-cu-name" class="hidden text-xs text-red-500 items-center gap-1 mt-1">
          <span class="material-icons text-xs">error_outline</span> Please enter the full name.
        </p>
      </div>

      {{-- Username --}}
      <div class="space-y-1.5">
        <label class="text-xs font-bold text-slate-500 uppercase">Username <span class="text-red-500">*</span></label>
        <input id="cu-username" name="username" type="text" value="{{ old('username') }}"
          class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none dark:text-white @error('username') border-red-500 @enderror"
          placeholder="e.g. jsmith" oninput="clearUErr('js-cu-username')" />
        @error('username')
          <p class="text-xs text-red-500 flex items-center gap-1 mt-1">
            <span class="material-icons text-xs">error_outline</span> {{ $message }}
          </p>
        @enderror
        <p id="js-cu-username" class="hidden text-xs text-red-500 items-center gap-1 mt-1">
          <span class="material-icons text-xs">error_outline</span> Please enter a username.
        </p>
      </div>

      {{-- Email --}}
      <div class="space-y-1.5">
        <label class="text-xs font-bold text-slate-500 uppercase">Email Address <span
            class="text-red-500">*</span></label>
        <input id="cu-email" name="email" type="email" value="{{ old('email') }}"
          class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none dark:text-white @error('email') border-red-500 @enderror"
          placeholder="john.smith@example.com" oninput="clearUErr('js-cu-email')" />
        @error('email')
          <p class="text-xs text-red-500 flex items-center gap-1 mt-1">
            <span class="material-icons text-xs">error_outline</span> {{ $message }}
          </p>
        @enderror
        <p id="js-cu-email" class="hidden text-xs text-red-500 items-center gap-1 mt-1">
          <span class="material-icons text-xs">error_outline</span> Please enter a valid email address.
        </p>
      </div>

      {{-- Password --}}
      <div class="space-y-1.5">
        <label class="text-xs font-bold text-slate-500 uppercase">Password <span class="text-red-500">*</span></label>
        <div class="relative">
          <input name="password" id="create-pw" type="password"
            class="w-full px-4 py-2.5 pr-10 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none dark:text-white @error('password') border-red-500 @enderror"
            placeholder="Min. 8 characters" oninput="clearUErr('js-cu-password')" />
          <button type="button" onclick="togglePw('create-pw','create-pw-icon')"
            class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition-colors">
            <span id="create-pw-icon" class="material-icons text-lg">visibility_off</span>
          </button>
        </div>
        @error('password')
          <p class="text-xs text-red-500 flex items-center gap-1 mt-1">
            <span class="material-icons text-xs">error_outline</span> {{ $message }}
          </p>
        @enderror
        <p id="js-cu-password" class="hidden text-xs text-red-500 items-center gap-1 mt-1">
          <span class="material-icons text-xs">error_outline</span> Please enter a password (min. 8 characters).
        </p>
      </div>

      {{-- Role Grid --}}
      <div class="space-y-2">
        <label class="text-xs font-bold text-slate-500 uppercase">System Role</label>

        <div class="grid grid-cols-2 gap-2">
          {{-- No Role card --}}
          <label class="cursor-pointer group col-span-2 sm:col-span-1">
            <input class="peer sr-only" name="role_id" type="radio" value="" {{ old('role_id', '') === '' ? 'checked' : '' }} />
            <div class="relative p-3 rounded-xl border-2 border-slate-200 dark:border-slate-700
              hover:border-primary/50 peer-checked:border-primary peer-checked:bg-primary/5
              transition-all h-full flex items-center gap-3">
              <div class="absolute top-2 right-2 opacity-0 peer-checked:opacity-100 text-primary transition-opacity">
                <span class="material-icons text-sm">check_circle</span>
              </div>
              <div class="w-9 h-9 rounded-lg bg-slate-100 dark:bg-slate-700 text-slate-400
                flex items-center justify-center flex-shrink-0">
                <span class="material-icons text-lg">block</span>
              </div>
              <div>
                <div class="font-bold text-slate-900 dark:text-white text-xs">No Role</div>
                <div class="text-[10px] text-slate-500 leading-snug">No system access</div>
              </div>
            </div>
          </label>
          @foreach($roles as $role)
            <label class="cursor-pointer group">
              <input class="peer sr-only" name="role_id" type="radio" value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'checked' : '' }} />
              <div class="relative p-3 rounded-xl border-2 border-slate-200 dark:border-slate-700
                                  hover:border-primary/50 peer-checked:border-primary peer-checked:bg-primary/5
                                  transition-all h-full flex items-center gap-3">
                <div class="absolute top-2 right-2 opacity-0 peer-checked:opacity-100 text-primary transition-opacity">
                  <span class="material-icons text-sm">check_circle</span>
                </div>
                <div
                  class="w-9 h-9 rounded-lg {{ $role->bg_class }} {{ $role->text_class }}
                                    flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-transform">
                  <span class="material-icons text-lg">{{ $role->icon }}</span>
                </div>
                <div>
                  <div class="font-bold text-slate-900 dark:text-white text-xs">
                    {{ $role->label }}
                  </div>
                </div>
              </div>
            </label>
          @endforeach
        </div>
      </div>

      {{-- Active toggle --}}
      <div class="flex items-center gap-3 py-1">
        <input type="checkbox" id="create-is-active" name="is_active" value="1"
          class="w-4 h-4 text-primary rounded border-slate-300 focus:ring-primary/20" checked />
        <label for="create-is-active" class="text-sm font-medium text-slate-700 dark:text-slate-300 cursor-pointer">
          Activate immediately <span class="text-slate-400 font-normal text-xs">(unchecked = Pending Approval)</span>
        </label>
      </div>

      {{-- Actions --}}
      <div class="flex items-center gap-4 pt-2">
        <button type="button" onclick="closeModal('modal-create')"
          class="flex-1 px-6 py-3 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-bold text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
          Cancel
        </button>
        <button type="submit"
          class="flex-1 px-6 py-3 bg-primary text-white rounded-xl text-sm font-bold hover:bg-primary/90 shadow-lg shadow-primary/20 transition-all flex items-center justify-center gap-2">
          <span class="material-icons text-sm">person_add</span>
          Create User
        </button>
      </div>

    </form>
  </div>
</div>

<script>
  // ── Create-User form: client-side validation ──────────────────────
  function clearUErr(id) {
    const el = document.getElementById(id);
    if (el) { el.classList.add('hidden'); el.classList.remove('flex'); }
  }
  function showUErr(id) {
    const el = document.getElementById(id);
    if (el) { el.classList.remove('hidden'); el.classList.add('flex'); }
  }

  document.getElementById('form-create-user')?.addEventListener('submit', function (e) {
    let valid = true;
    const checks = [
      ['cu-name', 'js-cu-name', v => v.trim().length > 0],
      ['cu-username', 'js-cu-username', v => v.trim().length > 0],
      ['cu-email', 'js-cu-email', v => v.trim().length > 0 && v.includes('@')],
      ['create-pw', 'js-cu-password', v => v.length >= 8],
    ];
    checks.forEach(([fieldId, errId, fn]) => {
      const el = document.getElementById(fieldId);
      if (el && !fn(el.value)) { showUErr(errId); valid = false; }
      else if (el) clearUErr(errId);
    });
    if (!valid) e.preventDefault();
  });
</script>