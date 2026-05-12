{{-- Media Manager Page - Orchestrator --}}
<x-layouts.app :title="__('app.media_manager')"
  class="font-display bg-background-light dark:bg-background-dark text-slate-800 dark:text-slate-200 antialiased min-h-screen">

  <x-nav.sidebar active="media" />

  <main class="lg:ml-64 flex flex-col h-screen overflow-hidden">

    @include('media._header')

    {{-- Two-column: folder sidebar + file grid --}}
    <div class="flex flex-1 overflow-hidden">

      {{-- Folder Sidebar --}}
      <aside class="hidden md:flex flex-col w-56 shrink-0 border-r border-slate-200 dark:border-slate-800
                    bg-white dark:bg-slate-900 overflow-y-auto">

        {{-- All Files --}}
        <a href="{{ route('media.index') }}"
           class="flex items-center gap-3 px-4 py-3 mx-2 my-1 rounded-xl text-sm font-semibold transition-all
                  {{ !$folder ? 'bg-primary/10 text-primary' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
          <span class="material-icons text-[20px]">perm_media</span>
          <span class="flex-1">All Files</span>
          <span class="text-xs font-bold px-2 py-0.5 rounded-full
                       {{ !$folder ? 'bg-primary text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-500' }}">
            {{ $folderCounts['_all'] }}
          </span>
        </a>

        <div class="mx-4 my-1 border-t border-slate-100 dark:border-slate-800"></div>
        <p class="px-4 pt-1 pb-1 text-[10px] font-bold uppercase tracking-widest text-slate-400">Folders</p>

        @php $folders = \App\Http\Controllers\MediaController::folders(); @endphp
        @foreach($folders as $key => $meta)
          <a href="{{ route('media.index', ['folder' => $key]) }}"
             class="flex items-center gap-3 px-4 py-3 mx-2 my-0.5 rounded-xl text-sm font-semibold transition-all
                    {{ $folder === $key ? 'bg-primary/10 text-primary' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
            <span class="material-icons text-[20px]">{{ $meta['icon'] }}</span>
            <span class="flex-1">{{ $meta['label'] }}</span>
            <span class="text-xs font-bold px-2 py-0.5 rounded-full
                         {{ $folder === $key ? 'bg-primary text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-500' }}">
              {{ $folderCounts[$key] ?? 0 }}
            </span>
          </a>
        @endforeach
      </aside>

      {{-- File Grid Area --}}
      <div class="flex-1 overflow-y-auto p-6 lg:p-8 space-y-6">
        @include('media._filters')
        @include('media._grid')
      </div>

    </div>

  </main>

  @include('media._modals')

</x-layouts.app>
