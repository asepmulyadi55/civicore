{{-- overview/_sidebar.blade.php --}}
{{-- Delegates to the shared sidebar component which handles all role-based nav --}}
<x-nav.sidebar :active="Route::currentRouteName()" />