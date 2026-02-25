@php
  $roles = \App\Models\Role::orderBy('name')->get();
@endphp
{{-- ============================================================
Modal: Edit User — same design as Create User
Trigger: openEditModal(id, name, username, email, roleId, blockId)
============================================================ --}}
<div id="modal-edit"
  class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden"
  onclick="closeModalOnBackdrop(event, 'modal-edit')">
  <div
    class="bg-white dark:bg-slate-900 w-full max-w-xl rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-800 overflow-hidden">

    {{-- Header --}}
    <div class="px-8 py-6 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
      <div>
        <h2 class="text-xl font-bold text-slate-900 dark:text-white">Edit User</h2>
        <p id="edit-subtitle" class="text-sm text-slate-500 mt-0.5">Update user profile and permissions</p>
      </div>
      <button class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors"
        onclick="closeModal('modal-edit')">
        <span class="material-icons">close</span>
      </button>
    </div>

    {{-- Form --}}
    <form id="form-edit-user" method="POST" action="" class="p-8 pt-0 space-y-5 max-h-[80vh] overflow-y-auto"
      novalidate>
      @csrf
      @method('PATCH')
      <input type="hidden" name="_form_type" value="edit" />
      <input type="hidden" id="edit-user-id-field" name="_user_id" value="" />

      {{-- Name --}}
      <div class="space-y-1.5">
        <label class="text-xs font-bold text-slate-500 uppercase">Full Name <span class="text-red-500">*</span></label>
        <input id="edit-name" name="name" type="text"
          class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none dark:text-white @error('name') border-red-500 @enderror"
          placeholder="e.g. John Smith" />
        @error('name')
          <p class="text-xs text-red-500 flex items-center gap-1 mt-1">
            <span class="material-icons text-xs">error_outline</span> {{ $message }}
          </p>
        @enderror
      </div>

      {{-- Username --}}
      <div class="space-y-1.5">
        <label class="text-xs font-bold text-slate-500 uppercase">Username <span class="text-red-500">*</span></label>
        <input id="edit-username" name="username" type="text"
          class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none dark:text-white @error('username') border-red-500 @enderror"
          placeholder="e.g. jsmith" />
        @error('username')
          <p class="text-xs text-red-500 flex items-center gap-1 mt-1">
            <span class="material-icons text-xs">error_outline</span> {{ $message }}
          </p>
        @enderror
      </div>

      {{-- Email --}}
      <div class="space-y-1.5">
        <label class="text-xs font-bold text-slate-500 uppercase">Email Address <span
            class="text-red-500">*</span></label>
        <input id="edit-email" name="email" type="email"
          class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none dark:text-white @error('email') border-red-500 @enderror"
          placeholder="john.smith@example.com" />
        @error('email')
          <p class="text-xs text-red-500 flex items-center gap-1 mt-1">
            <span class="material-icons text-xs">error_outline</span> {{ $message }}
          </p>
        @enderror
      </div>

      {{-- New Password (optional) --}}
      <div class="space-y-1.5">
        <label class="text-xs font-bold text-slate-500 uppercase flex justify-between items-center">
          New Password
          <span class="text-[10px] text-slate-400 lowercase font-normal italic">Leave blank to keep current</span>
        </label>
        <div class="relative">
          <input id="edit-password" name="password" type="password" autocomplete="new-password"
            class="w-full px-4 py-2.5 pr-10 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none dark:text-white @error('password') border-red-500 @enderror"
            placeholder="Min. 8 characters" />
          <button type="button" onclick="togglePw('edit-password','edit-pw-icon')"
            class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition-colors">
            <span id="edit-pw-icon" class="material-icons text-lg">visibility_off</span>
          </button>
        </div>
        @error('password')
          <p class="text-xs text-red-500 flex items-center gap-1 mt-1">
            <span class="material-icons text-xs">error_outline</span> {{ $message }}
          </p>
        @enderror
      </div>

      {{-- Role Grid --}}
      <div class="space-y-2">
        <label class="text-xs font-bold text-slate-500 uppercase">System Role</label>

        <div class="grid grid-cols-2 gap-2">
          {{-- No Role card --}}
          <label class="cursor-pointer group col-span-2 sm:col-span-1">
            <input class="peer sr-only edit-role-radio" name="role_id" type="radio" value="" />
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
              <input class="peer sr-only edit-role-radio" name="role_id" type="radio" value="{{ $role->id }}" />
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
                  <div class="font-bold text-slate-900 dark:text-white text-xs">{{ $role->label }}</div>
                </div>
              </div>
            </label>
          @endforeach
        </div>
      </div>

      {{-- Actions --}}
      <div class="flex items-center gap-4 pt-2">
        <button type="button" onclick="closeModal('modal-edit')"
          class="flex-1 px-6 py-3 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-bold text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
          Cancel
        </button>
        <button type="submit"
          class="flex-1 px-6 py-3 bg-primary text-white rounded-xl text-sm font-bold hover:bg-primary/90 shadow-lg shadow-primary/20 transition-all flex items-center justify-center gap-2">
          <span class="material-icons text-sm">save</span>
          Save Changes
        </button>
      </div>

    </form>
  </div>
</div>