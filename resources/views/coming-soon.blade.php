<x-layouts.app :title="$feature . ' — Coming Soon'" class="bg-background-light dark:bg-background-dark min-h-screen">

  <div class="flex flex-col items-center justify-center min-h-[70vh] px-4 text-center">

    {{-- Animated icon --}}
    <div class="relative mb-8">
      <div class="w-28 h-28 rounded-3xl bg-primary/10 flex items-center justify-center animate-pulse">
        <span class="material-icons text-primary text-5xl">{{ $icon }}</span>
      </div>
      {{-- Badge --}}
      <span
        class="absolute -top-2 -right-2 bg-amber-400 text-white text-[10px] font-extrabold px-2 py-0.5 rounded-full uppercase tracking-widest shadow">
        Soon
      </span>
    </div>

    {{-- Heading --}}
    <h1 class="text-3xl font-extrabold text-slate-900 dark:text-white mb-3 tracking-tight">
      {{ $feature }} is Coming Soon
    </h1>
    <p class="text-slate-500 dark:text-slate-400 text-base max-w-md leading-relaxed">
      {{ $description }}
    </p>

    {{-- Divider --}}
    <div class="mt-10 mb-6 w-16 h-1 rounded-full bg-primary/20"></div>

    {{-- Back button --}}
    <a href="{{ route('dashboard') }}"
      class="inline-flex items-center gap-2 px-6 py-3 rounded-xl text-sm font-bold text-white bg-primary hover:bg-primary/90 transition-all shadow-lg shadow-primary/20">
      <span class="material-icons text-base">arrow_back</span>
      Back to Dashboard
    </a>

  </div>

</x-layouts.app>