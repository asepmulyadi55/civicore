{{-- Users Table with confirmation modals and Edit button --}}

{{-- ── Flash Messages ─────────────────────────────────────── --}}
@if(session('success'))
  <div
    class="mb-4 flex items-center gap-3 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 text-emerald-700 dark:text-emerald-400 px-4 py-3 rounded-lg text-sm font-medium">
    <span class="material-icons text-base">check_circle</span>
    {{ session('success') }}
  </div>
@endif

@if(session('error'))
  <div
    class="mb-4 flex items-center gap-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-400 px-4 py-3 rounded-lg text-sm font-medium">
    <span class="material-icons text-base">error_outline</span>
    {{ session('error') }}
  </div>
@endif


{{-- ── Table ────────────────────────────────────────────────── --}}
<div
  class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
  <div class="overflow-x-auto">
    <table class="w-full text-left border-collapse">
      <thead>
        <tr class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-800">
          <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">User</th>
          <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Email</th>
          <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Role</th>
          <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Status</th>
          <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider text-right">Actions</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-200 dark:divide-slate-800">

        @forelse($users as $user)
          @php
            $initials = collect(explode(' ', $user->name))->map(fn($w) => strtoupper($w[0]))->take(2)->implode('');
            $avatarColors = ['bg-primary/10 text-primary', 'bg-blue-100 text-blue-600', 'bg-emerald-100 text-emerald-600', 'bg-amber-100 text-amber-600', 'bg-indigo-100 text-indigo-600'];
            $color = $avatarColors[$user->id % count($avatarColors)];
            $isPending = !$user->is_active;
            $isSelf = $user->id === auth()->id();
            $roleBadge = match ($user->role?->name) {
              'admin' => 'bg-purple-100 text-purple-700',
              'treasurer' => 'bg-amber-100 text-amber-700',
              'block_coordinator' => 'bg-indigo-100 text-indigo-700',
              'resident' => 'bg-sky-100 text-sky-700',
              default => 'bg-slate-100 text-slate-600',
            };
          @endphp
          <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/20 transition-colors">

            {{-- User Details --}}
            <td class="px-6 py-4">
              <div class="flex items-center gap-3">
                <div
                  class="w-10 h-10 rounded-full {{ $color }} flex items-center justify-center font-bold text-sm flex-shrink-0">
                  {{ $initials }}
                </div>
                <div>
                  <div class="font-bold text-slate-900 dark:text-white">{{ $user->name }}</div>
                  <div class="text-xs text-slate-400">&#64;{{ $user->username }}</div>
                </div>
              </div>
            </td>

            {{-- Email --}}
            <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-400">{{ $user->email }}</td>

            {{-- Role --}}
            <td class="px-6 py-4">
              @if($user->role)
                <span class="px-2 py-1 text-[10px] font-bold {{ $roleBadge }} rounded-lg uppercase">
                  {{ str_replace('_', ' ', $user->role->name) }}
                </span>
              @else
                <span class="text-xs text-slate-400 italic">No role</span>
              @endif
            </td>

            {{-- Status --}}
            <td class="px-6 py-4">
              @if($user->is_active)
                <span
                  class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400">
                  <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>Active
                </span>
              @else
                <span
                  class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400">
                  <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>Pending
                </span>
              @endif
            </td>

            {{-- Actions --}}
            <td class="px-6 py-4 text-right">
              <div class="flex justify-end gap-1.5 items-center">

                {{-- Edit (always shown) --}}
                <button onclick="openEditModal(
                          {{ $user->id }},
                          {{ json_encode($user->name) }},
                          {{ json_encode($user->username) }},
                          {{ json_encode($user->email) }},
                          {{ $user->role_id ?? 'null' }},
                          {{ $user->block_id ?? 'null' }}
                        )"
                  class="p-1.5 text-slate-400 hover:text-primary hover:bg-primary/10 rounded-lg transition-colors"
                  title="Edit user">
                  <span class="material-icons text-lg">edit</span>
                </button>

                @if($isPending)
                  {{-- Approve button → triggers modal --}}
                  <button onclick="openUserConfirmModal('approve', {{ $user->id }}, {{ json_encode($user->name) }})"
                    class="bg-primary text-white text-[10px] px-3 py-1.5 rounded font-bold uppercase tracking-wider hover:bg-primary/90 transition-colors flex items-center gap-1">
                    <span class="material-icons text-xs">verified</span>
                    Approve
                  </button>
                @else
                  @if(!$isSelf)
                    {{-- Deactivate button → triggers modal --}}
                    <button onclick="openUserConfirmModal('deactivate', {{ $user->id }}, {{ json_encode($user->name) }})"
                      class="p-1.5 text-slate-400 hover:text-amber-500 hover:bg-amber-50 dark:hover:bg-amber-900/20 rounded-lg transition-colors"
                      title="Deactivate">
                      <span class="material-icons text-lg">person_off</span>
                    </button>
                  @endif
                @endif

                {{-- Delete button → triggers modal --}}
                @if(!$isSelf)
                  <button onclick="openUserConfirmModal('delete', {{ $user->id }}, {{ json_encode($user->name) }})"
                    class="p-1.5 text-slate-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors"
                    title="Delete">
                    <span class="material-icons text-lg">delete_outline</span>
                  </button>
                @endif

              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="5" class="px-6 py-16 text-center">
              <div class="flex flex-col items-center gap-3 text-slate-400">
                <span class="material-icons text-5xl">manage_accounts</span>
                <p class="text-sm font-medium">No users found.</p>
                @if(request()->hasAny(['search', 'role_id', 'status']))
                  <a href="{{ route('users.index') }}" class="text-primary text-sm hover:underline">Clear filters</a>
                @endif
              </div>
            </td>
          </tr>
        @endforelse

      </tbody>
    </table>
  </div>

  {{-- Pagination --}}
  <div class="p-4 border-t border-slate-200 dark:border-slate-800 flex items-center justify-between flex-wrap gap-3">
    <span class="text-sm text-slate-500">
      Showing {{ $users->firstItem() ?? 0 }}–{{ $users->lastItem() ?? 0 }} of {{ $users->total() }} users
    </span>
    {{ $users->links() }}
  </div>
</div>