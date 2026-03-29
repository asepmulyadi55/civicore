{{-- Homepage CMS Page --}}
<x-layouts.app :title="__('app.nav_homepage')"
  class="font-display bg-background-light dark:bg-background-dark text-slate-800 dark:text-slate-200 antialiased min-h-screen">

  <x-nav.sidebar active="homepage" />

  <div class="lg:pl-64 min-h-screen bg-background-light dark:bg-background-dark flex flex-col">

    {{-- Header --}}
    <header
      class="h-16 bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between px-6 lg:px-8">
      <div class="flex items-center gap-4">
        <button class="lg:hidden p-2 rounded-lg border border-slate-200 dark:border-slate-800"
          onclick="toggleSidebar()">
          <span class="material-icons text-slate-500">menu</span>
        </button>
        <h1 class="text-xl font-bold text-slate-900 dark:text-white">Homepage CMS</h1>
        <span
          class="hidden sm:inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-primary/10 text-primary">
          <span class="material-icons text-[14px]">public</span>
          React Frontend Content
        </span>
      </div>
      <button class="p-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg hover:border-primary/50 transition-all"
        onclick="toggleDark()" title="Toggle dark mode">
        <span class="material-icons text-slate-500 text-[20px]">dark_mode</span>
      </button>
    </header>

    {{-- Body --}}
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

      {{-- Validation Errors --}}
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

      {{-- ─────────────────────────────────────────────────────────────
           SECTION 1 — Hero
      ───────────────────────────────────────────────────────────────── --}}
      <section class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="flex items-center gap-3 px-6 py-4 border-b border-slate-100 dark:border-slate-800">
          <div class="w-9 h-9 rounded-xl bg-primary/10 flex items-center justify-center">
            <span class="material-icons text-primary text-[20px]">image</span>
          </div>
          <div>
            <h2 class="font-bold text-slate-900 dark:text-white text-base">Hero Section</h2>
            <p class="text-xs text-slate-500">Main banner shown at the top of the public homepage</p>
          </div>
        </div>
        <form method="POST" action="{{ route('homepage.hero') }}" class="p-6 space-y-5">
          @csrf
          <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div class="space-y-1.5">
              <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">Title <span class="text-rose-500">*</span></label>
              <input type="text" name="title" value="{{ old('title', $hero['title'] ?? '') }}"
                class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all"
                placeholder="e.g. Welcome to Dwipapuri" required>
            </div>
            <div class="space-y-1.5">
              <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">Subtitle</label>
              <input type="text" name="subtitle" value="{{ old('subtitle', $hero['subtitle'] ?? '') }}"
                class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all"
                placeholder="e.g. A vibrant community hub">
            </div>
            <div class="space-y-1.5">
              <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">CTA Button Text</label>
              <input type="text" name="cta_text" value="{{ old('cta_text', $hero['cta_text'] ?? '') }}"
                class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all"
                placeholder="e.g. Explore Events">
            </div>
            <div class="space-y-1.5">
              <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">CTA Button URL</label>
              <input type="text" name="cta_url" value="{{ old('cta_url', $hero['cta_url'] ?? '') }}"
                class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all"
                placeholder="e.g. /events or https://...">
            </div>
          </div>
          <div class="space-y-1.5">
            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">Background Image URL</label>
            <input type="text" name="bg_image" value="{{ old('bg_image', $hero['bg_image'] ?? '') }}"
              class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all"
              placeholder="e.g. https://cdn.example.com/hero.jpg">
            <p class="text-xs text-slate-400">Provide a public image URL. This will be used as the hero background on the React frontend.</p>
          </div>
          <div class="flex justify-end pt-2">
            <button type="submit"
              class="inline-flex items-center gap-2 px-5 py-2.5 bg-primary hover:bg-primary/90 text-white text-sm font-bold rounded-xl transition-all shadow-sm shadow-primary/20">
              <span class="material-icons text-base">save</span>
              Save Hero Section
            </button>
          </div>
        </form>
      </section>

      {{-- ─────────────────────────────────────────────────────────────
           SECTION 2 — Featured Event
      ───────────────────────────────────────────────────────────────── --}}
      <section class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="flex items-center gap-3 px-6 py-4 border-b border-slate-100 dark:border-slate-800">
          <div class="w-9 h-9 rounded-xl bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center">
            <span class="material-icons text-amber-500 text-[20px]">star</span>
          </div>
          <div>
            <h2 class="font-bold text-slate-900 dark:text-white text-base">Featured Event</h2>
            <p class="text-xs text-slate-500">Highlighted event shown prominently with a YouTube embed</p>
          </div>
        </div>
        <form method="POST" action="{{ route('homepage.featured-event') }}" class="p-6 space-y-5">
          @csrf
          <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div class="space-y-1.5">
              <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">Event Title <span class="text-rose-500">*</span></label>
              <input type="text" name="title" value="{{ old('title', $featuredEvent['title'] ?? '') }}"
                class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all"
                placeholder="e.g. Dwipapuri Anniversary Gala" required>
            </div>
            <div class="space-y-1.5">
              <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">YouTube Video ID</label>
              <input type="text" name="youtube_id" value="{{ old('youtube_id', $featuredEvent['youtube_id'] ?? '') }}"
                class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all"
                placeholder="e.g. dQw4w9WgXcQ">
              <p class="text-xs text-slate-400">The ID portion from a YouTube URL: youtube.com/watch?v=<strong>ID</strong></p>
            </div>
            <div class="space-y-1.5">
              <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">Event Date</label>
              <input type="date" name="date" value="{{ old('date', $featuredEvent['date'] ?? '') }}"
                class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all">
            </div>
            <div class="space-y-1.5">
              <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">Status</label>
              <select name="status"
                class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all">
                <option value="">— Select status —</option>
                @foreach(['upcoming' => 'Upcoming', 'ongoing' => 'Ongoing', 'past' => 'Past'] as $val => $label)
                  <option value="{{ $val }}" {{ old('status', $featuredEvent['status'] ?? '') === $val ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
              </select>
            </div>
          </div>
          <div class="space-y-1.5">
            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">Description</label>
            <textarea name="description" rows="3"
              class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all resize-none"
              placeholder="Brief description of the featured event...">{{ old('description', $featuredEvent['description'] ?? '') }}</textarea>
          </div>
          <div class="flex justify-end pt-2">
            <button type="submit"
              class="inline-flex items-center gap-2 px-5 py-2.5 bg-primary hover:bg-primary/90 text-white text-sm font-bold rounded-xl transition-all shadow-sm shadow-primary/20">
              <span class="material-icons text-base">save</span>
              Save Featured Event
            </button>
          </div>
        </form>
      </section>

      {{-- ─────────────────────────────────────────────────────────────
           SECTION 3 — Upcoming Events
      ───────────────────────────────────────────────────────────────── --}}
      <section class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="flex items-center gap-3 px-6 py-4 border-b border-slate-100 dark:border-slate-800">
          <div class="w-9 h-9 rounded-xl bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center">
            <span class="material-icons text-emerald-500 text-[20px]">event_upcoming</span>
          </div>
          <div class="flex-1">
            <h2 class="font-bold text-slate-900 dark:text-white text-base">Upcoming Events</h2>
            <p class="text-xs text-slate-500">Events listed in the "Upcoming" section of the public homepage</p>
          </div>
          <span class="px-2.5 py-1 text-xs font-bold bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 rounded-full">
            {{ count($upcomingEvents) }}
          </span>
        </div>

        {{-- Add Upcoming Event form --}}
        <div class="p-6 border-b border-slate-100 dark:border-slate-800">
          <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-4">Add Upcoming Event</p>
          <form method="POST" action="{{ route('homepage.events.store') }}" class="space-y-4">
            @csrf
            <input type="hidden" name="status" value="upcoming">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
              <div class="md:col-span-2 space-y-1.5">
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">Title <span class="text-rose-500">*</span></label>
                <input type="text" name="title"
                  class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all"
                  placeholder="Event title..." required>
              </div>
              <div class="space-y-1.5">
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">Date</label>
                <input type="date" name="date"
                  class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all">
              </div>
            </div>
            <div class="space-y-1.5">
              <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">Description</label>
              <input type="text" name="description"
                class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all"
                placeholder="Short description... (optional)">
            </div>
            <div class="space-y-1.5">
              <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">Event Image URL</label>
              <input type="url" name="image_url"
                class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all"
                placeholder="https://example.com/image.jpg (optional)">
              <p class="text-xs text-slate-400">Shown as the event card image on the frontend. Leave blank to use a default placeholder.</p>
            </div>
            <div class="flex justify-end">
              <button type="submit"
                class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold rounded-xl transition-all">
                <span class="material-icons text-base">add</span>
                Add Event
              </button>
            </div>
          </form>
        </div>

        {{-- Upcoming Events list --}}
        <div class="divide-y divide-slate-100 dark:divide-slate-800">
          @forelse($upcomingEvents as $event)
            <div class="flex items-center gap-4 px-6 py-4">
              <div class="w-8 h-8 rounded-lg bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center flex-shrink-0">
                <span class="material-icons text-emerald-500 text-[16px]">event</span>
              </div>
              <div class="flex-1 min-w-0">
                <p class="font-semibold text-slate-800 dark:text-slate-200 text-sm truncate">{{ $event['title'] }}</p>
                <p class="text-xs text-slate-400 mt-0.5">
                  {{ isset($event['date']) && $event['date'] ? \Carbon\Carbon::parse($event['date'])->format('d M Y') : 'No date set' }}
                  @if(!empty($event['description']))
                    · {{ Str::limit($event['description'], 60) }}
                  @endif
                </p>
              </div>
              <form method="POST" action="{{ route('homepage.events.destroy', $event['id']) }}">
                @csrf @method('DELETE')
                <button type="submit"
                  class="p-1.5 text-slate-400 hover:text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-950/20 rounded-lg transition-colors"
                  title="Remove event"
                  onclick="return confirm('Remove this event?')">
                  <span class="material-icons text-[18px]">delete_outline</span>
                </button>
              </form>
            </div>
          @empty
            <div class="px-6 py-10 text-center">
              <span class="material-icons text-3xl text-slate-300 dark:text-slate-600 block mb-2">event_busy</span>
              <p class="text-sm text-slate-400">No upcoming events yet. Add one above.</p>
            </div>
          @endforelse
        </div>
      </section>

      {{-- ─────────────────────────────────────────────────────────────
           SECTION 4 — Past Events
      ───────────────────────────────────────────────────────────────── --}}
      <section class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="flex items-center gap-3 px-6 py-4 border-b border-slate-100 dark:border-slate-800">
          <div class="w-9 h-9 rounded-xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center">
            <span class="material-icons text-slate-500 text-[20px]">history</span>
          </div>
          <div class="flex-1">
            <h2 class="font-bold text-slate-900 dark:text-white text-base">Past Events</h2>
            <p class="text-xs text-slate-500">Events listed in the "Past Events" section of the public homepage</p>
          </div>
          <span class="px-2.5 py-1 text-xs font-bold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 rounded-full">
            {{ count($pastEvents) }}
          </span>
        </div>

        {{-- Add Past Event form --}}
        <div class="p-6 border-b border-slate-100 dark:border-slate-800">
          <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-4">Add Past Event</p>
          <form method="POST" action="{{ route('homepage.events.store') }}" class="space-y-4">
            @csrf
            <input type="hidden" name="status" value="past">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
              <div class="md:col-span-2 space-y-1.5">
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">Title <span class="text-rose-500">*</span></label>
                <input type="text" name="title"
                  class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all"
                  placeholder="Event title..." required>
              </div>
              <div class="space-y-1.5">
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">Date</label>
                <input type="date" name="date"
                  class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all">
              </div>
            </div>
            <div class="space-y-1.5">
              <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">Description</label>
              <input type="text" name="description"
                class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all"
                placeholder="Short description... (optional)">
            </div>
            <div class="space-y-1.5">
              <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">Event Image URL</label>
              <input type="url" name="image_url"
                class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all"
                placeholder="https://example.com/image.jpg (optional)">
              <p class="text-xs text-slate-400">Shown in the Past Highlights gallery on the frontend. Leave blank to use a default placeholder.</p>
            </div>
            <div class="flex justify-end">
              <button type="submit"
                class="inline-flex items-center gap-2 px-4 py-2 bg-slate-600 hover:bg-slate-700 text-white text-sm font-bold rounded-xl transition-all">
                <span class="material-icons text-base">add</span>
                Add Event
              </button>
            </div>
          </form>
        </div>

        {{-- Past Events list --}}
        <div class="divide-y divide-slate-100 dark:divide-slate-800">
          @forelse($pastEvents as $event)
            <div class="flex items-center gap-4 px-6 py-4">
              <div class="w-8 h-8 rounded-lg bg-slate-100 dark:bg-slate-800 flex items-center justify-center flex-shrink-0">
                <span class="material-icons text-slate-400 text-[16px]">event</span>
              </div>
              <div class="flex-1 min-w-0">
                <p class="font-semibold text-slate-800 dark:text-slate-200 text-sm truncate">{{ $event['title'] }}</p>
                <p class="text-xs text-slate-400 mt-0.5">
                  {{ isset($event['date']) && $event['date'] ? \Carbon\Carbon::parse($event['date'])->format('d M Y') : 'No date set' }}
                  @if(!empty($event['description']))
                    · {{ Str::limit($event['description'], 60) }}
                  @endif
                </p>
              </div>
              <form method="POST" action="{{ route('homepage.events.destroy', $event['id']) }}">
                @csrf @method('DELETE')
                <button type="submit"
                  class="p-1.5 text-slate-400 hover:text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-950/20 rounded-lg transition-colors"
                  title="Remove event"
                  onclick="return confirm('Remove this event?')">
                  <span class="material-icons text-[18px]">delete_outline</span>
                </button>
              </form>
            </div>
          @empty
            <div class="px-6 py-10 text-center">
              <span class="material-icons text-3xl text-slate-300 dark:text-slate-600 block mb-2">history_toggle_off</span>
              <p class="text-sm text-slate-400">No past events yet. Add one above.</p>
            </div>
          @endforelse
        </div>
      </section>

      {{-- ─────────────────────────────────────────────────────────────
           SECTION 5 — About Section
      ───────────────────────────────────────────────────────────────── --}}
      <section class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="flex items-center gap-3 px-6 py-4 border-b border-slate-100 dark:border-slate-800">
          <div class="w-9 h-9 rounded-xl bg-violet-100 dark:bg-violet-900/30 flex items-center justify-center">
            <span class="material-icons text-violet-500 text-[20px]">info</span>
          </div>
          <div>
            <h2 class="font-bold text-slate-900 dark:text-white text-base">About Section</h2>
            <p class="text-xs text-slate-500">The community description shown in the "About" section of the public homepage</p>
          </div>
        </div>
        <form method="POST" action="{{ route('homepage.about') }}" class="p-6 space-y-5">
          @csrf
          <div class="space-y-1.5">
            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">About Content <span class="text-rose-500">*</span></label>
            <textarea name="content" rows="6"
              class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all resize-none"
              placeholder="Write about the community, its history, values, and vision..." required>{{ old('content', $about['content'] ?? '') }}</textarea>
            <p class="text-xs text-slate-400">Max 3,000 characters. Separate paragraphs with a blank line.</p>
          </div>
          <div class="space-y-1.5">
            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">Section Image URL</label>
            <input type="url" name="image_url" value="{{ old('image_url', $about['image_url'] ?? '') }}"
              class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all"
              placeholder="https://example.com/community-photo.jpg (optional)">
            <p class="text-xs text-slate-400">Optional image shown below the about text on the frontend. Leave blank to hide the image.</p>
          </div>

          {{-- Stats Grid --}}
          @php
            $defaultStats = [
              ['value' => '500+',    'label' => 'Residents'],
              ['value' => '24/7',    'label' => 'Security'],
              ['value' => '12',      'label' => 'Parks'],
              ['value' => 'Monthly', 'label' => 'Events'],
            ];
            $savedStats = old('stats', $about['stats'] ?? $defaultStats);
            // Always ensure 4 rows
            while (count($savedStats) < 4) { $savedStats[] = ['value' => '', 'label' => '']; }
          @endphp
          <div class="space-y-3">
            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">Stats Cards</label>
            <p class="text-xs text-slate-400">The 4 stat cards shown in the About section. Edit both the value and label for each.</p>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
              @foreach($savedStats as $i => $stat)
                <div class="flex items-center gap-2 p-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50">
                  <div class="flex-1 space-y-1.5">
                    <input type="text" name="stats[{{ $i }}][value]"
                      value="{{ $stat['value'] ?? '' }}"
                      class="w-full px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm font-bold focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all"
                      placeholder="e.g. 500+">
                    <input type="text" name="stats[{{ $i }}][label]"
                      value="{{ $stat['label'] ?? '' }}"
                      class="w-full px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs text-slate-500 focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all"
                      placeholder="e.g. Residents">
                  </div>
                </div>
              @endforeach
            </div>
          </div>

          <div class="flex justify-end pt-2">
            <button type="submit"
              class="inline-flex items-center gap-2 px-5 py-2.5 bg-primary hover:bg-primary/90 text-white text-sm font-bold rounded-xl transition-all shadow-sm shadow-primary/20">
              <span class="material-icons text-base">save</span>
              Save About Section
            </button>
          </div>
        </form>
      </section>

    </main>
  </div>

</x-layouts.app>
