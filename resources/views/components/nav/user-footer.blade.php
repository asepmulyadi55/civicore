{{--
Global sidebar user-footer component.

Usage (no props needed — reads auth user automatically):
<x-nav.user-footer />

Automatically shows:
• Residents → Block name · unit number
• All others → Role label (falls back to email)
--}}
@php
  $user = auth()->user();
  $initials = strtoupper(substr($user->name, 0, 2));

  if ($user->isResident()) {
    // Try to find the resident record linked to this user account
    $residentRecord = \App\Models\Resident::where('user_id', $user->id)
      ->with('block')
      ->first();
    $subtext = $residentRecord
      ? ($residentRecord->block?->name . ' · ' . $residentRecord->unit_number)
      : ($user->role?->label ?? $user->email);
  } else {
    $subtext = $user->role?->label ?? $user->email;
  }
@endphp

<div class="p-6 border-t border-slate-100 dark:border-white/5 transition-colors duration-300">
  <div class="flex items-center space-x-3">

    {{-- Avatar: photo if uploaded, else initials --}}
    @if($user->avatar)
      <img src="{{ $user->avatarUrl() }}" alt="{{ $user->name }}"
        class="w-10 h-10 rounded-full object-cover flex-shrink-0 border border-slate-200 dark:border-white/10">
    @else
      <div
        class="w-10 h-10 rounded-full bg-primary/10 dark:bg-white/10 text-primary dark:text-white flex items-center justify-center font-semibold text-sm flex-shrink-0 font-headline">
        {{ $initials }}
      </div>
    @endif

    <div class="flex-1 overflow-hidden">
      <p class="text-sm font-semibold font-headline text-primary dark:text-white truncate transition-colors duration-300">{{ $user->name }}</p>
      <p class="text-xs text-slate-500 font-light truncate">{{ $subtext }}</p>
    </div>

    {{-- Logout --}}
    <form method="POST" action="{{ route('logout') }}">
      @csrf
      <button type="submit" class="text-slate-400 hover:text-primary dark:hover:text-white transition-colors" title="Logout">
        <span class="material-symbols-outlined text-sm">logout</span>
      </button>
    </form>

  </div>
</div>