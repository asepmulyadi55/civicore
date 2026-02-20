<x-layouts.app title="Reports – Yearly Block Report"
  class="font-display bg-background-light dark:bg-background-dark text-slate-800 dark:text-slate-200 antialiased min-h-screen">

  {{-- Sidebar --}}
  <x-nav.sidebar active="reports" />

  {{-- ── Main Content ────────────────────────────────────────── --}}
  <main class="lg:ml-64 flex flex-col min-h-screen">

    @include('reports._header')

    <div class="flex-1 p-4 lg:p-8 space-y-6">

      @include('reports._filters')
      @include('reports._stats')
      @include('reports._table')

      {{-- Footer --}}
      <footer
        class="pt-6 border-t border-slate-200 dark:border-slate-800 flex flex-col md:flex-row justify-between items-center gap-4 no-print">
        <p class="text-xs text-slate-400 font-medium">
          &copy; {{ date('Y') }} CiviCore Community Management. Generated on {{ now()->format('M d, Y \a\t g:i A') }}.
        </p>
        <div class="flex items-center gap-4">
          <a href="#" class="text-xs font-semibold text-primary hover:underline underline-offset-4">Privacy Policy</a>
          <a href="#" class="text-xs font-semibold text-primary hover:underline underline-offset-4">Support Center</a>
        </div>
      </footer>

    </div>
  </main>

  {{-- Mobile print FAB --}}
  <button
    class="lg:hidden fixed bottom-6 right-6 w-14 h-14 bg-primary text-white rounded-full shadow-2xl flex items-center justify-center z-[100] no-print active:scale-95 transition-transform"
    onclick="window.print()">
    <span class="material-icons">print</span>
  </button>

  <style>
    @media print {
      .no-print {
        display: none !important;
      }

      #sidebar,
      #sidebar-overlay {
        display: none !important;
      }

      main {
        margin-left: 0 !important;
      }

      body {
        background: white !important;
      }
    }
  </style>

  <script>
    function toggleSidebar() {
      document.getElementById('sidebar').classList.toggle('-translate-x-full');
      document.getElementById('sidebar-overlay').classList.toggle('hidden');
    }
  </script>

</x-layouts.app>