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
          <x-ui.sort-th column="name" :label="__('app.table_user')" />
          <x-ui.sort-th column="email" :label="__('app.table_email')" />
          <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">{{ __('app.table_role') }}</th>
          <x-ui.sort-th column="is_active" :label="__('app.table_status')" />
          <x-ui.sort-th column="last_login_at" :label="__('app.table_last_login')" />
          <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider text-right">
            {{ __('app.table_actions') }}
          </th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-200 dark:divide-slate-800">

        @forelse($users as $user)
          @php
            $initials = collect(explode(' ', $user->name))->map(fn($w) => strtoupper($w[0]))->take(2)->implode('');
            $avatarColors = ['bg-primary/10 text-primary', 'bg-blue-100 text-blue-600', 'bg-emerald-100 text-emerald-600', 'bg-amber-100 text-amber-600', 'bg-indigo-100 text-indigo-600'];
            $color = $avatarColors[abs(crc32($user->id)) % count($avatarColors)];
            $isPending = !$user->is_active && !$user->last_login_at;
            $isInactive = !$user->is_active && $user->last_login_at;
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
                @if($user->avatar)
                  <img src="{{ $user->avatarUrl() }}" alt="{{ $user->name }}"
                    class="w-10 h-10 rounded-full object-cover flex-shrink-0"
                    onerror="this.replaceWith(this.nextElementSibling)">
                  <div class="w-10 h-10 rounded-full {{ $color }} items-center justify-center font-bold text-sm flex-shrink-0 hidden">
                    {{ $initials }}
                  </div>
                @else
                  <div class="w-10 h-10 rounded-full {{ $color }} flex items-center justify-center font-bold text-sm flex-shrink-0">
                    {{ $initials }}
                  </div>
                @endif
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
                <span class="text-xs text-slate-400 italic">{{ __('app.no_role') }}</span>
              @endif
            </td>

            {{-- Status + Online badge --}}
            <td class="px-6 py-4">
              <div class="flex flex-col gap-1.5">
                @if($user->is_active)
                  <span
                    class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>{{ __('app.status_active') }}
                  </span>
                @elseif($isInactive)
                  <span
                    class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400">
                    <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>{{ __('app.status_inactive') }}
                  </span>
                @else
                  <span
                    class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>{{ __('app.status_pending') }}
                  </span>
                @endif
                @if($user->id === auth()->id() || $user->isOnline())
                  <span class="inline-flex items-center gap-1 text-[10px] font-bold text-emerald-600 dark:text-emerald-400">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>{{ __('app.status_online') }}
                  </span>
                @else
                  <span class="inline-flex items-center gap-1 text-[10px] font-medium text-slate-400">
                    <span
                      class="w-1.5 h-1.5 rounded-full bg-slate-300 dark:bg-slate-600"></span>{{ __('app.status_offline') }}
                  </span>
                @endif
              </div>
            </td>

            {{-- Last Login --}}
            <td class="px-6 py-4 text-sm text-slate-500">
              @if($user->last_login_at)
                <span title="{{ $user->last_login_at->format('d M Y H:i') }}">
                  {{ $user->last_login_at->diffForHumans() }}
                </span>
              @else
                <span class="text-slate-300 dark:text-slate-600 italic text-xs">{{ __('app.never_logged_in') }}</span>
              @endif
            </td>

            {{-- Actions --}}
            <td class="px-6 py-4 text-right">
              <div class="flex justify-end gap-1.5 items-center">

                {{-- Edit --}}
                @if(auth()->user()->can('users.edit'))
                <button onclick="openEditModal(
                                      '{{ $user->id }}',
                                      {{ json_encode($user->name) }},
                                      {{ json_encode($user->username) }},
                                      {{ json_encode($user->email) }},
                                      {{ $user->role_id ? "'{$user->role_id}'" : 'null' }},
                                      {{ $user->block_id ? "'{$user->block_id}'" : 'null' }},
                                      {{ json_encode($user->resident?->unit_number ?? $user->unit_number) }}
                                    )"
                  class="p-1.5 text-slate-400 hover:text-primary hover:bg-primary/10 rounded-lg transition-colors"
                  title="{{ __('app.title_edit_user') }}">
                  <span class="material-icons text-lg">edit</span>
                </button>
                @endif

                @if($isPending)
                  {{-- Approve button --}}
                  @if(auth()->user()->can('users.approve'))
                  <button
                    onclick="openApproveModal('{{ $user->id }}', {{ json_encode($user->name) }}, {{ json_encode($user->email) }}, {{ $user->block_id ? "'{$user->block_id}'" : 'null' }}, {{ json_encode($user->unit_number) }})"
                    class="bg-primary text-white text-[10px] px-3 py-1.5 rounded font-bold uppercase tracking-wider hover:bg-primary/90 transition-colors flex items-center gap-1">
                    <span class="material-icons text-xs">verified</span>
                    {{ __('app.btn_approve') }}
                  </button>
                  @endif
                @elseif($isInactive)
                  @if(!$isSelf && auth()->user()->can('users.edit'))
                    {{-- Reactivate button --}}
                    <button onclick="openUserConfirmModal('reactivate', '{{ $user->id }}', {{ json_encode($user->name) }})"
                      class="p-1.5 text-slate-400 hover:text-emerald-500 hover:bg-emerald-50 dark:hover:bg-emerald-900/20 rounded-lg transition-colors"
                      title="Reactivate user">
                      <span class="material-icons text-lg">person_add</span>
                    </button>
                  @endif
                @else
                  @if(!$isSelf && auth()->user()->can('users.edit'))
                    {{-- Deactivate button --}}
                    <button onclick="openUserConfirmModal('deactivate', '{{ $user->id }}', {{ json_encode($user->name) }})"
                      class="p-1.5 text-slate-400 hover:text-amber-500 hover:bg-amber-50 dark:hover:bg-amber-900/20 rounded-lg transition-colors"
                      title="{{ __('app.title_deactivate_user') }}">
                      <span class="material-icons text-lg">person_off</span>
                    </button>
                  @endif
                @endif

                {{-- Delete button --}}
                @if(!$isSelf && auth()->user()->can('users.delete'))
                  <button onclick="openUserConfirmModal('delete', '{{ $user->id }}', {{ json_encode($user->name) }})"
                    class="p-1.5 text-slate-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors"
                    title="{{ __('app.title_delete_user') }}">
                    <span class="material-icons text-lg">delete_outline</span>
                  </button>
                @endif

              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="6" class="px-6 py-16 text-center">
              <div class="flex flex-col items-center gap-3 text-slate-400">
                <span class="material-icons text-5xl">manage_accounts</span>
                <p class="text-sm font-medium">{{ __('app.no_users_found') }}</p>
                @if(request()->hasAny(['search', 'role_id', 'status']))
                  <a href="{{ route('users.index') }}"
                    class="text-primary text-sm hover:underline">{{ __('app.btn_clear') }}</a>
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
      {{ __('app.showing') }} {{ $users->firstItem() ?? 0 }}–{{ $users->lastItem() ?? 0 }} {{ __('app.of') }}
      {{ $users->total() }} {{ __('app.users_lowercase') }}
    </span>
    {{ $users->links() }}
  </div>
</div>