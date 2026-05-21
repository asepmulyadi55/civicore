@props([
  'icon'       => 'info',
  'iconBg'     => 'bg-primary/10 dark:bg-white/8',
  'iconText'   => 'text-primary dark:text-secondary',
  'label'      => '',
  'value'      => '',
  'badge'      => null,
  'badgeStyle' => 'emerald',
])

@php
$badgeColors = [
  'emerald' => 'text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-500/10',
  'rose'    => 'text-rose-600 dark:text-rose-400 bg-rose-50 dark:bg-rose-500/10',
  'amber'   => 'text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-500/10',
];
$badgeClass = $badgeColors[$badgeStyle] ?? $badgeColors['emerald'];
@endphp

<div class="bg-white dark:bg-dark-card p-6 rounded-xl border border-slate-200 dark:border-white/5 shadow-elegant dark:shadow-elegant-dark transition-colors duration-300">
  <div class="flex justify-between items-start">
    {{-- Icon --}}
    <div class="p-3 {{ $iconBg }} rounded-lg">
      <span class="material-icons {{ $iconText }}">{{ $icon }}</span>
    </div>
    {{-- Value + badge --}}
    <div class="text-right">
      @if ($badge)
        <span class="inline-flex items-center text-xs font-bold {{ $badgeClass }} px-2 py-1 rounded-full mb-1">
          @if ($badgeStyle === 'emerald')
            <span class="material-icons text-[12px] mr-1">trending_up</span>
          @endif
          {{ $badge }}
        </span>
      @endif
      <p class="text-2xl font-bold text-slate-900 dark:text-white leading-tight font-headline">{{ $value }}</p>
    </div>
  </div>
  <h3 class="text-slate-500 dark:text-slate-400 text-sm font-medium mt-4">{{ $label }}</h3>
</div>
