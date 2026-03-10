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

<div class="p-4 border-t border-slate-200 dark:border-slate-800">
  <div class="flex items-center gap-3 px-2">

    {{-- Avatar: photo if uploaded, else initials --}}
    @if($user->avatar)
      <img src="{{ $user->avatarUrl() }}" alt="{{ $user->name }}"
        class="w-9 h-9 rounded-full object-cover flex-shrink-0 border border-slate-200 dark:border-slate-700">
    @else
      <div
        class="w-9 h-9 rounded-full bg-primary/10 text-primary flex items-center justify-center font-bold text-sm flex-shrink-0">
        {{ $initials }}
      </div>
    @endif

    <div class="flex-1 min-w-0">
      <p class="text-sm font-bold truncate uppercase text-slate-900 dark:text-white">{{ $user->name }}</p>
      <p class="text-xs text-slate-400 truncate">{{ $subtext }}</p>
    </div>

    {{-- Logout --}}
    <form method="POST" action="{{ route('logout') }}">
      @csrf
      <button type="submit" class="text-slate-400 hover:text-primary transition-colors" title="Logout">
        <span class="material-icons text-lg">logout</span>
      </button>
    </form>

  </div>
</div>