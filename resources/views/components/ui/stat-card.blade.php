@props([
  'icon'    => 'info',             // Material icon name
  'iconBg'  => 'bg-primary/10',   // icon container background
  'iconText'=> 'text-primary',    // icon color
  'label'   => '',                 // stat label e.g. "Total Collections"
  'value'   => '',                 // stat value e.g. "$42,500.00"
  'badge'   => null,               // optional badge text e.g. "12.5%"
  'badgeStyle' => 'emerald',       // 'emerald' | 'rose' | 'amber'
])

@php
$badgeColors = [
  'emerald' => 'text-emerald-500 bg-emerald-50 dark:bg-emerald-500/10',
  'rose'    => 'text-rose-500 bg-rose-50 dark:bg-rose-500/10',
  'amber'   => 'text-amber-500 bg-amber-50 dark:bg-amber-500/10',
];
$badgeClass = $badgeColors[$badgeStyle] ?? $badgeColors['emerald'];
@endphp

<div class="bg-white dark:bg-slate-900 p-6 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm">
  <div class="flex justify-between items-start mb-4">
    <div class="p-3 {{ $iconBg }} rounded-lg">
      <span class="material-icons {{ $iconText }}">{{ $icon }}</span>
    </div>
    @if ($badge)
      <span class="flex items-center text-xs font-bold {{ $badgeClass }} px-2 py-1 rounded-full">
        @if ($badgeStyle === 'emerald')
          <span class="material-icons text-[12px] mr-1">trending_up</span>
        @endif
        {{ $badge }}
      </span>
    @endif
  </div>
  <h3 class="text-slate-500 text-sm font-medium">{{ $label }}</h3>
  <p class="text-2xl font-extrabold text-slate-900 dark:text-white mt-1">{{ $value }}</p>
</div>
