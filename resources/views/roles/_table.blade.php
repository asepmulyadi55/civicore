{{-- Flash messages --}}
@foreach(['success', 'error'] as $type)
  @if(session($type))
    <div
      class="p-4 {{ $type === 'success' ? 'bg-emerald-50 dark:bg-emerald-900/20 border-emerald-200 text-emerald-700 dark:text-emerald-400' : 'bg-rose-50 dark:bg-rose-900/20 border-rose-200 text-rose-700 dark:text-rose-400' }} border rounded-xl flex items-center gap-3">
      <span class="material-icons text-sm">{{ $type === 'success' ? 'check_circle' : 'error' }}</span>
      <p class="text-sm">{{ session($type) }}</p>
    </div>
  @endif
@endforeach

{{-- Roles Table --}}
<div
  class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
  <div class="overflow-x-auto">
    <table class="w-full text-left border-collapse">
      <thead>
        <tr class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-800">
          <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">
            {{ __('app.table_role') }}</th>
          <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">
            {{ __('app.table_description') }}</th>
          <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">
            {{ __('app.table_users_count') }}</th>
          <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">
            {{ __('app.table_permissions') }}</th>
          @if(auth()->user()->isAdmin())
            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider text-right">
              {{ __('app.table_actions') }}</th>
          @endif
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
        @forelse($roles as $role)
          <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors">
            <td class="px-6 py-4">
              <div class="flex items-center gap-3">
                <div
                  class="w-9 h-9 rounded-xl {{ $role->bg_class ?? 'bg-slate-100 dark:bg-slate-800' }} flex items-center justify-center">
                  <span
                    class="material-icons text-lg {{ $role->text_class ?? 'text-slate-500' }}">{{ $role->icon ?? 'person' }}</span>
                </div>
                <div>
                  <p class="font-semibold text-sm">{{ $role->label }}</p>
                  <p class="text-xs text-slate-400 font-mono">{{ $role->name }}</p>
                </div>
              </div>
            </td>
            <td class="px-6 py-4 text-sm text-slate-500 max-w-xs">{{ $role->description ?? '—' }}</td>
            <td class="px-6 py-4">
              <span
                class="px-2.5 py-1 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 rounded-lg text-xs font-bold">{{ $role->users_count ?? 0 }}</span>
            </td>
            <td class="px-6 py-4">
              @if($role->name === 'admin')
                <span
                  class="inline-flex items-center gap-1 px-2.5 py-1 bg-primary/10 text-primary rounded-full text-xs font-bold">
                  <span class="material-icons text-xs">verified</span> {{ __('app.full_access') }}
                </span>
              @else
                @php
                  $count = collect($role->permissions ?? [])->filter()->count();
                  $total = collect(\App\Models\Role::$availablePermissions)->flatten()->count();
                @endphp
                <span class="text-sm text-slate-600 dark:text-slate-400">{{ $count }} / {{ $total }}
                  {{ __('app.permissions_lowercase') }}</span>
              @endif
            </td>
            @if(auth()->user()->isAdmin())
              <td class="px-6 py-4">
                <div class="flex items-center justify-end gap-1">
                  @if($role->name !== 'admin')
                    <button
                      onclick="openPermissionsModal('{{ $role->id }}', '{{ addslashes($role->label) }}', {{ json_encode($role->permissions ?? new stdClass()) }})"
                      class="p-1.5 text-slate-400 hover:text-primary hover:bg-primary/5 rounded-lg transition-colors"
                      title="{{ __('app.title_edit_permissions') }}">
                      <span class="material-icons text-lg">tune</span>
                    </button>
                    <button
                      onclick="openEditRoleModal('{{ $role->id }}', '{{ addslashes($role->label) }}', '{{ addslashes($role->description ?? '') }}')"
                      class="p-1.5 text-slate-400 hover:text-primary hover:bg-primary/5 rounded-lg transition-colors"
                      title="{{ __('app.title_edit_role') }}">
                      <span class="material-icons text-lg">edit</span>
                    </button>
                    <button onclick="openDeleteRoleModal('{{ $role->id }}', '{{ addslashes($role->label) }}')"
                      class="p-1.5 text-slate-400 hover:text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-900/20 rounded-lg transition-colors"
                      title="{{ __('app.title_delete_role') }}">
                      <span class="material-icons text-lg">delete_outline</span>
                    </button>
                  @endif
                </div>
              </td>
            @endif
          </tr>
        @empty
          <tr>
            <td colspan="5" class="px-6 py-16 text-center text-slate-400">{{ __('app.no_roles_found') }}</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
