{{-- overview/_sidebar.blade.php --}}
{{-- Delegates to the shared sidebar component which handles all role-based nav --}}
{{-- Strip the route action suffix (e.g. 'household.show' → 'household') to match sidebar keys --}}
<x-nav.sidebar :active="\Illuminate\Support\Str::before(Route::currentRouteName() ?? '', '.')" />