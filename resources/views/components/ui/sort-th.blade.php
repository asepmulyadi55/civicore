{{-- resources/views/components/ui/sort-th.blade.php
     Usage: <x-ui.sort-th column="fullname" :label="__('app.table_name')" />
            <x-ui.sort-th column="status" label="Status" class="text-right" />
--}}
@props(['column', 'label', 'class' => ''])

@php
    $currentSort = request('sort');
    $currentDir  = request('direction', 'asc');
    $isActive    = $currentSort === $column;
    $nextDir     = ($isActive && $currentDir === 'asc') ? 'desc' : 'asc';
    $url         = request()->fullUrlWithQuery(['sort' => $column, 'direction' => $nextDir]);
@endphp

<th class="px-6 py-4 text-xs font-bold uppercase tracking-wider whitespace-nowrap {{ $class }}">
    <a href="{{ $url }}"
       class="inline-flex items-center gap-1 transition-colors group
              {{ $isActive
                    ? 'text-primary dark:text-secondary'
                    : 'text-slate-500 hover:text-primary dark:hover:text-secondary' }}">
        {{ $label }}
        <span class="material-icons text-[14px] leading-none transition-all
                     {{ $isActive ? 'opacity-100' : 'opacity-20 group-hover:opacity-60' }}">
            {{ ($isActive && $currentDir === 'desc') ? 'arrow_downward' : 'arrow_upward' }}
        </span>
    </a>
</th>
