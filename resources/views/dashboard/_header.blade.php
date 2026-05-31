{{-- Dashboard Page Header --}}
<header class="flex flex-col md:flex-row md:items-center justify-between gap-4">
  <div class="flex items-center space-x-4">
    <button class="lg:hidden p-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg"
      onclick="toggleSidebar()">
      <span class="material-icons text-slate-500">menu</span>
    </button>
    <div>
      <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white">{{ __('app.dashboard_overview') }}</h1>
      <p class="text-slate-500 text-sm">{{ __('app.dashboard_welcome', ['name' => Auth::user()->name]) }}</p>
    </div>
  </div>
  <div class="flex items-center space-x-3">

    {{-- Notification Bell --}}
    <div class="relative" id="notif-wrapper">
      <button id="notif-btn"
        class="relative p-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg hover:border-primary/50 transition-all"
        onclick="toggleNotif()" aria-label="Notifications">
        <span class="material-icons text-slate-500">notifications</span>
        @if($notifBadge > 0)
          <span id="notif-badge" class="absolute -top-1 -right-1 min-w-[18px] h-[18px] px-1 bg-red-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center border-2 border-white dark:border-slate-900 leading-none">
            {{ $notifBadge > 9 ? '9+' : $notifBadge }}
          </span>
        @endif
      </button>

      {{-- Dropdown panel — responsive: full-width on mobile, fixed 320px on sm+ --}}
      <div id="notif-panel"
        class="hidden absolute right-0 top-full mt-2 w-[calc(100vw-1.5rem)] sm:w-80 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-xl z-50 overflow-hidden">

        <div class="flex items-center justify-between px-4 py-3 border-b border-slate-100 dark:border-slate-800">
          <span class="text-sm font-bold text-slate-800 dark:text-white">Notifications</span>
          @if($notifTotal > 0)
            <span class="text-[10px] font-bold bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-400 px-1.5 py-0.5 rounded-full">{{ $notifTotal }} pending</span>
          @endif
        </div>

        <div class="max-h-80 overflow-y-auto divide-y divide-slate-50 dark:divide-slate-800">

          {{-- Pending payment batches --}}
          @forelse($notifPayments as $payment)
            <a href="{{ route('payments.index', ['status' => 'pending']) }}"
              class="flex items-start gap-3 px-4 py-3 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
              <span class="material-icons text-amber-500 mt-0.5 text-base flex-shrink-0">payments</span>
              <div class="flex-1 min-w-0">
                <p class="text-sm text-slate-800 dark:text-white font-medium truncate">
                  {{ $payment->householder?->fullname ?? 'Unknown' }}
                </p>
                <p class="text-xs text-slate-500">
                  Submitted {{ $payment->notif_month_count > 1 ? $payment->notif_month_count . ' month payment' : 'payment for ' . \Carbon\Carbon::parse($payment->payment_month)->format('M Y') }}
                </p>
                <p class="text-[10px] text-slate-400 mt-0.5">{{ $payment->created_at->diffForHumans() }}</p>
              </div>
            </a>
          @empty
          @endforelse

          {{-- Pending user registrations (admin only) --}}
          @foreach($notifUsers as $pendingUser)
            <a href="{{ route('users.index', ['status' => 'pending']) }}"
              class="flex items-start gap-3 px-4 py-3 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
              <span class="material-icons text-primary mt-0.5 text-base flex-shrink-0">person_add</span>
              <div class="flex-1 min-w-0">
                <p class="text-sm text-slate-800 dark:text-white font-medium truncate">{{ $pendingUser->name }}</p>
                <p class="text-xs text-slate-500 truncate">Pending account approval</p>
                <p class="text-[10px] text-slate-400 mt-0.5">{{ $pendingUser->created_at->diffForHumans() }}</p>
              </div>
            </a>
          @endforeach

          {{-- Empty state --}}
          @if($notifTotal === 0)
            <div class="flex flex-col items-center justify-center py-8 text-center">
              <span class="material-icons text-slate-300 dark:text-slate-600 text-3xl mb-2">notifications_none</span>
              <p class="text-sm text-slate-400">You're all caught up!</p>
            </div>
          @endif

        </div>

        @if($notifTotal > 0)
          <div class="px-4 py-2.5 border-t border-slate-100 dark:border-slate-800 flex gap-3">
            @if($notifPayments->count() > 0)
              <a href="{{ route('payments.index', ['status' => 'pending']) }}"
                class="text-xs text-primary hover:underline font-medium">View payments</a>
            @endif
            @if($notifUsers->count() > 0)
              <a href="{{ route('users.index', ['status' => 'pending']) }}"
                class="text-xs text-primary hover:underline font-medium">View users</a>
            @endif
          </div>
        @endif
      </div>
    </div>

    {{-- Dark mode toggle --}}
    <button
      class="p-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg hover:border-primary/50 transition-all"
      onclick="toggleDark()" title="Toggle dark mode">
      <span class="material-icons text-slate-500">dark_mode</span>
    </button>
  </div>
</header>

<script>
  function toggleNotif() {
    const panel = document.getElementById('notif-panel');
    const isOpening = panel.classList.contains('hidden');
    panel.classList.toggle('hidden');

    if (isOpening) {
      // Mark notifications as read — hide badge immediately, persist on server
      const badge = document.getElementById('notif-badge');
      if (badge) badge.remove();

      fetch('{{ route("notifications.read") }}', {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': '{{ csrf_token() }}',
          'Content-Type': 'application/json'
        }
      });
    }
  }
  document.addEventListener('click', function (e) {
    var wrapper = document.getElementById('notif-wrapper');
    if (wrapper && !wrapper.contains(e.target)) {
      document.getElementById('notif-panel').classList.add('hidden');
    }
  });
</script>
