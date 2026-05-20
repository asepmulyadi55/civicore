{{-- File grid and pagination --}}

{{-- File Grid --}}
@forelse($files as $file)
  @if($loop->first)
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4" id="file-grid">
  @endif

  <div class="group relative bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 overflow-hidden shadow-sm hover:shadow-md hover:border-primary/30 transition-all"
    data-id="{{ $file->id }}">

    {{-- Checkbox overlay (only shown when user has delete permission AND folder is not read-only) --}}
    @unless($readOnly ?? false)
    @if(auth()->user()->can('media.delete'))
    <div class="absolute top-2 left-2 z-10">
      <input type="checkbox" class="file-checkbox w-5 h-5 rounded accent-primary opacity-0 group-hover:opacity-100 transition-opacity cursor-pointer"
        data-id="{{ $file->id }}" onchange="updateBulkBar()">
    </div>
    @endif
    @endunless

    {{-- Thumbnail --}}
    <div class="aspect-square bg-slate-100 dark:bg-slate-800 flex items-center justify-center overflow-hidden relative">
      @if($file->disk === 'local')
        <span class="absolute top-1.5 right-1.5 z-10 inline-flex items-center gap-0.5 px-1.5 py-0.5 bg-slate-800/70 text-white text-[10px] font-bold rounded-md backdrop-blur-sm">
          <span class="material-icons text-[11px]">lock</span> Private
        </span>
      @endif
      @if($file->is_image)
        <img src="{{ $file->url }}" alt="{{ $file->original_name }}"
          class="w-full h-full object-cover transition-transform duration-200 group-hover:scale-105"
          loading="lazy"
          onerror="this.parentElement.innerHTML='<span class=\'material-icons text-4xl text-slate-300\'>broken_image</span>'">
      @else
        <span class="material-icons text-4xl text-slate-400">insert_drive_file</span>
      @endif
    </div>

    {{-- Info --}}
    <div class="p-2.5">
      <p class="text-xs font-semibold text-slate-700 dark:text-slate-300 truncate" title="{{ $file->original_name }}">
        {{ $file->original_name }}</p>
      <p class="text-[11px] text-slate-400 mt-0.5">{{ $file->human_size }}</p>
      <p class="text-[10px] text-slate-300 dark:text-slate-600 mt-0.5">{{ $file->created_at->format('d M Y') }}</p>
    </div>

    {{-- Actions overlay --}}
    <div class="absolute inset-0 bg-black/0 group-hover:bg-black/40 transition-all flex items-center justify-center gap-2 opacity-0 group-hover:opacity-100">
      @if($file->is_image)
        <a href="{{ $file->url }}" target="_blank"
          class="p-2 bg-white rounded-full shadow-md text-slate-700 hover:bg-primary hover:text-white transition-all"
          title="View">
          <span class="material-icons text-lg">open_in_new</span>
        </a>
      @endif
      @unless($readOnly ?? false)
        @if(auth()->user()->can('media.delete'))
          <button onclick="confirmDelete('{{ $file->id }}', '{{ addslashes($file->original_name) }}')"
            class="p-2 bg-white rounded-full shadow-md text-slate-700 hover:bg-rose-500 hover:text-white transition-all"
            title="{{ __('app.btn_delete') }}">
            <span class="material-icons text-lg">delete_outline</span>
          </button>
        @endif
      @else
        <span class="p-2 bg-white/80 rounded-full shadow-md text-slate-400 cursor-default" title="Manage via resident/member profile">
          <span class="material-icons text-lg">lock_outline</span>
        </span>
      @endunless
    </div>
  </div>

  @if($loop->last)
    </div>
  @endif
@empty
  <div class="flex flex-col items-center justify-center py-24 text-center">
    <span class="material-icons text-5xl text-slate-300 dark:text-slate-700 mb-4">perm_media</span>
    <p class="text-slate-500 font-semibold">{{ __('app.no_media_found') }}</p>
    @if(request('search') || request('type'))
      <a href="{{ route('media.index') }}" class="mt-2 text-sm text-primary hover:underline">{{ __('app.clear_filters') }}</a>
    @endif
  </div>
@endforelse

{{-- Pagination --}}
@if($files->hasPages())
  <div class="flex items-center justify-between pt-2">
    <p class="text-sm text-slate-500">
      {{ __('app.showing') }} {{ $files->firstItem() }}–{{ $files->lastItem() }}
      {{ __('app.of') }} {{ $files->total() }} {{ __('app.media_files') }}
    </p>
    <div class="flex items-center gap-1">
      @if($files->onFirstPage())
        <span class="px-3 py-1.5 text-sm text-slate-300 dark:text-slate-600 border border-slate-200 dark:border-slate-700 rounded-lg cursor-not-allowed">
          <span class="material-icons text-sm align-middle">chevron_left</span>
        </span>
      @else
        <a href="{{ $files->previousPageUrl() }}"
          class="px-3 py-1.5 text-sm text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-700 rounded-lg hover:border-primary/50 hover:text-primary transition-all">
          <span class="material-icons text-sm align-middle">chevron_left</span>
        </a>
      @endif

      @foreach($files->getUrlRange(max(1, $files->currentPage() - 2), min($files->lastPage(), $files->currentPage() + 2)) as $page => $url)
        @if($page === $files->currentPage())
          <span class="px-3 py-1.5 text-sm font-bold text-white bg-primary border border-primary rounded-lg">{{ $page }}</span>
        @else
          <a href="{{ $url }}"
            class="px-3 py-1.5 text-sm text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-700 rounded-lg hover:border-primary/50 hover:text-primary transition-all">{{ $page }}</a>
        @endif
      @endforeach

      @if($files->hasMorePages())
        <a href="{{ $files->nextPageUrl() }}"
          class="px-3 py-1.5 text-sm text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-700 rounded-lg hover:border-primary/50 hover:text-primary transition-all">
          <span class="material-icons text-sm align-middle">chevron_right</span>
        </a>
      @else
        <span class="px-3 py-1.5 text-sm text-slate-300 dark:text-slate-600 border border-slate-200 dark:border-slate-700 rounded-lg cursor-not-allowed">
          <span class="material-icons text-sm align-middle">chevron_right</span>
        </span>
      @endif
    </div>
  </div>
@endif
