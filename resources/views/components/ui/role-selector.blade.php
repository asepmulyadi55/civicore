@props([
  'name'     => 'role',        // radio group name
  'selected' => 'coordinator', // 'admin' | 'treasurer' | 'coordinator'
])

@php
$roles = [
  'admin' => [
    'label'   => 'Admin',
    'desc'    => 'Full system access &amp; configuration rights.',
    'icon'    => 'security',
    'bg'      => 'bg-purple-100 dark:bg-purple-500/10',
    'text'    => 'text-purple-600',
  ],
  'treasurer' => [
    'label'   => 'Treasurer',
    'desc'    => 'Financials, collections &amp; budget access.',
    'icon'    => 'account_balance',
    'bg'      => 'bg-amber-100 dark:bg-amber-500/10',
    'text'    => 'text-amber-600',
  ],
  'coordinator' => [
    'label'   => 'Coordinator',
    'desc'    => 'Zone management &amp; resident support.',
    'icon'    => 'supervised_user_circle',
    'bg'      => 'bg-indigo-100 dark:bg-indigo-500/10',
    'text'    => 'text-indigo-600',
  ],
];
@endphp

<div class="grid grid-cols-1 md:grid-cols-3 gap-3 md:gap-4">
  @foreach ($roles as $key => $role)
    <label class="cursor-pointer group">
      <input class="peer sr-only" name="{{ $name }}" type="radio"
        data-key="{{ $key }}"
        {{ $selected === $key ? 'checked' : '' }} />
      <div class="relative p-4 rounded-xl border-2 border-slate-200 dark:border-slate-700
        hover:border-primary/50 peer-checked:border-primary peer-checked:bg-primary/5
        transition-all h-full">
        <div class="absolute top-3 right-3 opacity-0 peer-checked:opacity-100 text-primary transition-opacity">
          <span class="material-icons text-base">check_circle</span>
        </div>
        <div class="w-10 h-10 rounded-lg {{ $role['bg'] }} {{ $role['text'] }}
          flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
          <span class="material-icons">{{ $role['icon'] }}</span>
        </div>
        <div class="font-bold text-slate-900 dark:text-white text-sm">{{ $role['label'] }}</div>
        <div class="text-xs text-slate-500 mt-1 leading-relaxed">{!! $role['desc'] !!}</div>
      </div>
    </label>
  @endforeach
</div>
