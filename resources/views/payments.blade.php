<x-layouts.app title="Payment Management"
  class="font-display bg-background-light dark:bg-background-dark text-slate-800 dark:text-slate-200 antialiased min-h-screen">

  {{-- Sidebar --}}
  <x-nav.sidebar active="payments" />

  {{-- ── Main Content ────────────────────────────────────────── --}}
  <main class="lg:ml-64 flex flex-col min-h-screen">

    @include('payments._header')

    <div class="flex-1 p-4 lg:p-8 space-y-6">

      {{-- Flash messages --}}
      @if (session('success'))
        <div
          class="p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-900/30 rounded-xl flex items-center space-x-3">
          <span class="material-icons text-green-500">check_circle</span>
          <p class="text-sm text-green-700 dark:text-green-400">{{ session('success') }}</p>
        </div>
      @endif

      @include('payments._stats')
      @include('payments._filters')
      @include('payments._table')

    </div>
  </main>

  {{-- Record / Verify Payment Modal --}}
  <x-modals.record-payment />

  <script>
    // ── Shared modal helpers (reused across all pages) ──────────
    function openModal(id) {
      document.getElementById(id).classList.remove('hidden');
      document.body.style.overflow = 'hidden';
    }
    function closeModal(id) {
      document.getElementById(id).classList.add('hidden');
      document.body.style.overflow = '';
    }
    function closeModalOnBackdrop(event, id) {
      if (event.target === event.currentTarget) closeModal(id);
    }

    // ── Sidebar toggle ──────────────────────────────────────────
    function toggleSidebar() {
      document.getElementById('sidebar').classList.toggle('-translate-x-full');
      document.getElementById('sidebar-overlay').classList.toggle('hidden');
    }
  </script>

</x-layouts.app>