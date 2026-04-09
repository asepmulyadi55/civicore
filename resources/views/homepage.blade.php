{{-- Homepage CMS Page — Orchestrator --}}
<x-layouts.app :title="__('app.nav_homepage')"
  class="font-display bg-background-light dark:bg-background-dark text-slate-800 dark:text-slate-200 antialiased min-h-screen">

  <x-nav.sidebar active="homepage" />

  <div class="lg:pl-64 min-h-screen bg-background-light dark:bg-background-dark flex flex-col">

    @include('homepage._header')

    <main class="flex-1 p-6 lg:p-8 space-y-8">

      {{-- Flash Messages --}}
      @if(session('success'))
        <div class="p-4 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 rounded-xl flex items-center gap-3">
          <span class="material-icons text-emerald-500">check_circle</span>
          <p class="text-sm text-emerald-700 dark:text-emerald-400">{{ session('success') }}</p>
        </div>
      @endif
      @if(session('error'))
        <div class="p-4 bg-rose-50 dark:bg-rose-900/20 border border-rose-200 dark:border-rose-800 rounded-xl flex items-center gap-3">
          <span class="material-icons text-rose-500">error</span>
          <p class="text-sm text-rose-700 dark:text-rose-400">{{ session('error') }}</p>
        </div>
      @endif
      @if($errors->any())
        <div class="p-4 bg-rose-50 dark:bg-rose-900/20 border border-rose-200 dark:border-rose-800 rounded-xl">
          <div class="flex items-center gap-3 mb-2">
            <span class="material-icons text-rose-500">warning</span>
            <p class="text-sm font-semibold text-rose-700 dark:text-rose-400">Please fix the following errors:</p>
          </div>
          <ul class="list-disc list-inside text-sm text-rose-600 dark:text-rose-400 space-y-1 ml-7">
            @foreach($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
        </div>
      @endif

      @include('homepage._hero')
      @include('homepage._featured')
      @include('homepage._events')
      @include('homepage._about')

    </main>
  </div>

  @include('homepage._modals')

</x-layouts.app>
