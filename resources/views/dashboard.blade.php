<x-layouts.app title="Dashboard"
  class="font-display bg-background-light dark:bg-background-dark text-slate-800 dark:text-slate-200 antialiased min-h-screen">

  {{-- Mobile menu overlay --}}
  <div class="fixed inset-0 bg-black/50 z-40 lg:hidden hidden" id="sidebar-overlay" onclick="toggleSidebar()"></div>

  {{-- Sidebar --}}
  <aside
    class="fixed inset-y-0 left-0 w-64 bg-white dark:bg-slate-900 border-r border-slate-200 dark:border-slate-800 flex flex-col z-50 -translate-x-full lg:translate-x-0 transition-transform duration-300"
    id="sidebar">
    <div class="p-6 flex items-center space-x-3">
      <div class="bg-primary p-2 rounded-lg">
        <span class="material-icons text-white">apartment</span>
      </div>
      <span class="text-xl font-extrabold tracking-tight text-primary">CiviCore</span>
    </div>

    <nav class="flex-1 px-4 space-y-1 mt-4">
      <a class="flex items-center space-x-3 px-3 py-2.5 bg-primary/10 text-primary rounded-lg font-semibold group transition-all"
        href="{{ route('dashboard') }}">
        <span class="material-icons text-[20px]">dashboard</span>
        <span>Dashboard</span>
      </a>
      <a class="flex items-center space-x-3 px-3 py-2.5 text-slate-500 hover:text-primary hover:bg-primary/5 rounded-lg transition-all group"
        href="#">
        <span class="material-icons text-[20px]">payments</span>
        <span>Payments</span>
      </a>
      <a class="flex items-center space-x-3 px-3 py-2.5 text-slate-500 hover:text-primary hover:bg-primary/5 rounded-lg transition-all group"
        href="#">
        <span class="material-icons text-[20px]">people</span>
        <span>Residents</span>
      </a>
      <a class="flex items-center space-x-3 px-3 py-2.5 text-slate-500 hover:text-primary hover:bg-primary/5 rounded-lg transition-all group"
        href="#">
        <span class="material-icons text-[20px]">domain</span>
        <span>Blocks</span>
      </a>
      <a class="flex items-center space-x-3 px-3 py-2.5 text-slate-500 hover:text-primary hover:bg-primary/5 rounded-lg transition-all group"
        href="#">
        <span class="material-icons text-[20px]">manage_accounts</span>
        <span>User Management</span>
      </a>
      <a class="flex items-center space-x-3 px-3 py-2.5 text-slate-500 hover:text-primary hover:bg-primary/5 rounded-lg transition-all group"
        href="#">
        <span class="material-icons text-[20px]">bar_chart</span>
        <span>Reports</span>
      </a>
      <a class="flex items-center space-x-3 px-3 py-2.5 text-slate-500 hover:text-primary hover:bg-primary/5 rounded-lg transition-all group"
        href="#">
        <span class="material-icons text-[20px]">event</span>
        <span>Events</span>
      </a>
      <a class="flex items-center space-x-3 px-3 py-2.5 text-slate-500 hover:text-primary hover:bg-primary/5 rounded-lg transition-all group"
        href="#">
        <span class="material-icons text-[20px]">settings</span>
        <span>Settings</span>
      </a>
    </nav>

    {{-- User profile + logout --}}
    <div class="p-4 border-t border-slate-200 dark:border-slate-800">
      <div class="flex items-center space-x-3 p-2 bg-slate-50 dark:bg-slate-800/50 rounded-xl">
        <div class="w-10 h-10 rounded-lg bg-primary/10 flex items-center justify-center flex-shrink-0">
          <span class="material-icons text-primary">person</span>
        </div>
        <div class="flex-1 overflow-hidden">
          <p class="text-sm font-bold truncate">{{ Auth::user()->name }}</p>
          <p class="text-xs text-slate-500 truncate">{{ Auth::user()->email }}</p>
        </div>
        <form action="{{ route('logout') }}" method="POST">
          @csrf
          <button type="submit" class="text-slate-400 hover:text-primary transition-colors" title="Logout">
            <span class="material-icons text-sm">logout</span>
          </button>
        </form>
      </div>
    </div>
  </aside>

  {{-- Main content --}}
  <main class="lg:ml-64 p-4 lg:p-8 space-y-8">

    {{-- Header --}}
    <header class="flex flex-col md:flex-row md:items-center justify-between gap-4">
      <div class="flex items-center space-x-4">
        <button
          class="lg:hidden p-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg"
          onclick="toggleSidebar()">
          <span class="material-icons text-slate-500">menu</span>
        </button>
        <div>
          <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white">Dashboard Overview</h1>
          <p class="text-slate-500 text-sm">Welcome back, {{ Auth::user()->name }}! Here's what's happening today.</p>
        </div>
      </div>
      <div class="flex items-center space-x-4">
        <div class="relative flex-1 md:w-64">
          <span class="material-icons absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">search</span>
          <input
            class="w-full pl-10 pr-4 py-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/50 transition-all text-sm"
            placeholder="Search data..." type="text" />
        </div>
        <button
          class="relative p-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg hover:border-primary/50 transition-all">
          <span class="material-icons text-slate-500">notifications</span>
          <span
            class="absolute top-2 right-2.5 w-2 h-2 bg-red-500 rounded-full border-2 border-white dark:border-slate-900"></span>
        </button>
        <button
          class="p-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg hover:border-primary/50 transition-all"
          onclick="document.documentElement.classList.toggle('dark')" title="Toggle dark mode">
          <span class="material-icons text-slate-500">dark_mode</span>
        </button>
      </div>
    </header>

    {{-- Flash messages --}}
    @if (session('success'))
      <div
        class="p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-900/30 rounded-xl flex items-center space-x-3">
        <span class="material-icons text-green-500">check_circle</span>
        <p class="text-sm text-green-700 dark:text-green-400">{{ session('success') }}</p>
      </div>
    @endif

    {{-- Stats cards --}}
    <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
      <div class="bg-white dark:bg-slate-900 p-6 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm">
        <div class="flex justify-between items-start mb-4">
          <div class="p-3 bg-primary/10 rounded-lg">
            <span class="material-icons text-primary">account_balance_wallet</span>
          </div>
          <span
            class="flex items-center text-xs font-bold text-emerald-500 bg-emerald-50 dark:bg-emerald-500/10 px-2 py-1 rounded-full">
            <span class="material-icons text-[12px] mr-1">trending_up</span>
            12.5%
          </span>
        </div>
        <h3 class="text-slate-500 text-sm font-medium">Total Collections</h3>
        <p class="text-2xl font-extrabold text-slate-900 dark:text-white mt-1">$42,500.00</p>
      </div>

      <div class="bg-white dark:bg-slate-900 p-6 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm">
        <div class="flex justify-between items-start mb-4">
          <div class="p-3 bg-amber-100 dark:bg-amber-500/10 rounded-lg">
            <span class="material-icons text-amber-500">pending_actions</span>
          </div>
        </div>
        <h3 class="text-slate-500 text-sm font-medium">Pending Approvals</h3>
        <p class="text-2xl font-extrabold text-slate-900 dark:text-white mt-1">24</p>
      </div>

      <div class="bg-white dark:bg-slate-900 p-6 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm">
        <div class="flex justify-between items-start mb-4">
          <div class="p-3 bg-rose-100 dark:bg-rose-500/10 rounded-lg">
            <span class="material-icons text-rose-500">priority_high</span>
          </div>
          <span
            class="flex items-center text-xs font-bold text-rose-500 bg-rose-50 dark:bg-rose-500/10 px-2 py-1 rounded-full">
            Critical
          </span>
        </div>
        <h3 class="text-slate-500 text-sm font-medium">Unpaid Residents</h3>
        <p class="text-2xl font-extrabold text-slate-900 dark:text-white mt-1">12</p>
      </div>

      <div class="bg-white dark:bg-slate-900 p-6 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm">
        <div class="flex justify-between items-start mb-4">
          <div class="p-3 bg-indigo-100 dark:bg-indigo-500/10 rounded-lg">
            <span class="material-icons text-indigo-500">people_alt</span>
          </div>
        </div>
        <h3 class="text-slate-500 text-sm font-medium">Active Residents</h3>
        <p class="text-2xl font-extrabold text-slate-900 dark:text-white mt-1">1,248</p>
      </div>
    </section>

    {{-- Main grid: Activity table + Quick actions --}}
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">

      {{-- Recent Activity table --}}
      <div
        class="xl:col-span-2 bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden flex flex-col">
        <div class="p-6 border-b border-slate-200 dark:border-slate-800 flex justify-between items-center">
          <h2 class="text-lg font-bold text-slate-900 dark:text-white">Recent Activity</h2>
          <button class="text-sm font-semibold text-primary hover:underline">View All</button>
        </div>
        <div class="overflow-x-auto">
          <table class="w-full text-left">
            <thead>
              <tr class="bg-slate-50 dark:bg-slate-800/50 text-slate-500 text-xs uppercase tracking-wider font-bold">
                <th class="px-6 py-4">Resident</th>
                <th class="px-6 py-4">Activity Type</th>
                <th class="px-6 py-4">Unit/Block</th>
                <th class="px-6 py-4">Date</th>
                <th class="px-6 py-4 text-right">Status</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
              <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/20 transition-colors">
                <td class="px-6 py-4">
                  <div class="flex items-center space-x-3">
                    <div class="w-8 h-8 rounded-full bg-primary/10 flex items-center justify-center">
                      <span class="material-icons text-primary text-sm">person</span>
                    </div>
                    <span class="text-sm font-semibold">Mark Spencer</span>
                  </div>
                </td>
                <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-400">Monthly Fee Paid</td>
                <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-400">Block A - 402</td>
                <td class="px-6 py-4 text-sm text-slate-500">Oct 24, 2:45 PM</td>
                <td class="px-6 py-4 text-right">
                  <span
                    class="px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400">Success</span>
                </td>
              </tr>
              <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/20 transition-colors">
                <td class="px-6 py-4">
                  <div class="flex items-center space-x-3">
                    <div class="w-8 h-8 rounded-full bg-primary/10 flex items-center justify-center">
                      <span class="material-icons text-primary text-sm">person</span>
                    </div>
                    <span class="text-sm font-semibold">Sarah Jenkins</span>
                  </div>
                </td>
                <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-400">Registration Request</td>
                <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-400">Block C - 105</td>
                <td class="px-6 py-4 text-sm text-slate-500">Oct 24, 11:20 AM</td>
                <td class="px-6 py-4 text-right">
                  <span
                    class="px-2.5 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400">Pending</span>
                </td>
              </tr>
              <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/20 transition-colors">
                <td class="px-6 py-4">
                  <div class="flex items-center space-x-3">
                    <div class="w-8 h-8 rounded-full bg-primary/10 flex items-center justify-center">
                      <span class="material-icons text-primary text-sm">person</span>
                    </div>
                    <span class="text-sm font-semibold">David Chen</span>
                  </div>
                </td>
                <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-400">Amenity Booking</td>
                <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-400">Gym Facility</td>
                <td class="px-6 py-4 text-sm text-slate-500">Oct 23, 5:10 PM</td>
                <td class="px-6 py-4 text-right">
                  <span
                    class="px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400">Success</span>
                </td>
              </tr>
              <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/20 transition-colors">
                <td class="px-6 py-4">
                  <div class="flex items-center space-x-3">
                    <div class="w-8 h-8 rounded-full bg-primary/10 flex items-center justify-center">
                      <span class="material-icons text-primary text-sm">person</span>
                    </div>
                    <span class="text-sm font-semibold">Elena Rodriguez</span>
                  </div>
                </td>
                <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-400">Late Payment Alert</td>
                <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-400">Block B - 201</td>
                <td class="px-6 py-4 text-sm text-slate-500">Oct 23, 9:00 AM</td>
                <td class="px-6 py-4 text-right">
                  <span
                    class="px-2.5 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-400">Overdue</span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      {{-- Quick Actions + Community Status --}}
      <div
        class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm p-6 space-y-6">
        <h2 class="text-lg font-bold text-slate-900 dark:text-white">Quick Actions</h2>
        <div class="grid grid-cols-2 gap-4">
          <button
            class="flex flex-col items-center justify-center p-4 bg-primary text-white rounded-xl hover:bg-blue-600 transition-colors space-y-2 text-center group">
            <span class="material-icons text-2xl group-hover:scale-110 transition-transform">person_add</span>
            <span class="text-xs font-bold">Register Resident</span>
          </button>
          <button
            class="flex flex-col items-center justify-center p-4 bg-slate-100 dark:bg-slate-800 text-slate-900 dark:text-white rounded-xl hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors space-y-2 text-center group">
            <span
              class="material-icons text-2xl text-primary group-hover:scale-110 transition-transform">receipt_long</span>
            <span class="text-xs font-bold">New Payment</span>
          </button>
          <button
            class="flex flex-col items-center justify-center p-4 bg-slate-100 dark:bg-slate-800 text-slate-900 dark:text-white rounded-xl hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors space-y-2 text-center group">
            <span
              class="material-icons text-2xl text-primary group-hover:scale-110 transition-transform">campaign</span>
            <span class="text-xs font-bold">Broadcast</span>
          </button>
          <button
            class="flex flex-col items-center justify-center p-4 bg-slate-100 dark:bg-slate-800 text-slate-900 dark:text-white rounded-xl hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors space-y-2 text-center group">
            <span
              class="material-icons text-2xl text-primary group-hover:scale-110 transition-transform">picture_as_pdf</span>
            <span class="text-xs font-bold">Generate Report</span>
          </button>
        </div>

        <hr class="border-slate-100 dark:border-slate-800" />

        <div class="space-y-4">
          <h3 class="text-sm font-bold text-slate-500 uppercase tracking-wider">Community Status</h3>
          <div class="space-y-3">
            <div class="flex items-center justify-between">
              <span class="text-sm font-medium">Block A (Full)</span>
              <div class="w-32 h-2 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
                <div class="bg-primary h-full w-full"></div>
              </div>
            </div>
            <div class="flex items-center justify-between">
              <span class="text-sm font-medium">Block B</span>
              <div class="w-32 h-2 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
                <div class="bg-primary h-full w-3/4"></div>
              </div>
            </div>
            <div class="flex items-center justify-between">
              <span class="text-sm font-medium">Block C</span>
              <div class="w-32 h-2 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
                <div class="bg-primary h-full w-[45%]"></div>
              </div>
            </div>
          </div>
        </div>

        <div class="p-4 bg-amber-50 dark:bg-amber-900/10 border border-amber-100 dark:border-amber-900/30 rounded-lg">
          <div class="flex items-center space-x-2 text-amber-700 dark:text-amber-400 mb-2">
            <span class="material-icons text-sm">sticky_note_2</span>
            <span class="text-xs font-bold uppercase tracking-wider">Admin Memo</span>
          </div>
          <p class="text-xs text-amber-800 dark:text-amber-300 leading-relaxed">
            Upcoming maintenance for the Block A elevator scheduled for Oct 28. Notify all residents by tomorrow noon.
          </p>
        </div>
      </div>

    </div>
  </main>

  <script>
    function toggleSidebar() {
      const sidebar = document.getElementById('sidebar');
      const overlay = document.getElementById('sidebar-overlay');
      sidebar.classList.toggle('-translate-x-full');
      overlay.classList.toggle('hidden');
    }
  </script>

</x-layouts.app>