<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

@php
  $spaMeta    = json_decode(\App\Models\Setting::get('homepage_metadata', '{}'), true) ?? [];
  $spaTitle   = $spaMeta['page_title']    ?? 'Dwipapuri - Community Events & Residential Living';
  $spaDesc    = $spaMeta['meta_description'] ?? 'Dwipapuri Residential Community in Bandung — discover upcoming events, connect with neighbors, and experience curated residential living.';
  $spaKeywords= $spaMeta['meta_keywords']  ?? '';
  $spaOgTitle = $spaMeta['og_title']       ?? $spaTitle;
  $spaOgDesc  = $spaMeta['og_description'] ?? $spaDesc;
  $spaOgImage = !empty($spaMeta['og_image']) ? Storage::url($spaMeta['og_image']) : null;
  $spaUrl     = rtrim(config('app.url'), '/') . (request()->getPathInfo() === '/' ? '' : request()->getPathInfo());
@endphp

  {{-- Primary SEO --}}
  <title>{{ $spaTitle }}</title>
  <meta name="title"       content="{{ $spaTitle }}">
  <meta name="description" content="{{ $spaDesc }}">
  @if($spaKeywords)
  <meta name="keywords"    content="{{ $spaKeywords }}">
  @endif
  <meta name="robots"      content="index, follow">
  <link rel="canonical"    href="{{ $spaUrl }}">

  {{-- Open Graph --}}
  <meta property="og:type"        content="website">
  <meta property="og:url"         content="{{ $spaUrl }}">
  <meta property="og:title"       content="{{ $spaOgTitle }}">
  <meta property="og:description" content="{{ $spaOgDesc }}">
  <meta property="og:locale"      content="id_ID">
  @if($spaOgImage)
  <meta property="og:image"       content="{{ $spaOgImage }}">
  @endif

  {{-- Twitter Card --}}
  <meta name="twitter:card"        content="summary_large_image">
  <meta name="twitter:title"       content="{{ $spaOgTitle }}">
  <meta name="twitter:description" content="{{ $spaOgDesc }}">
  @if($spaOgImage)
  <meta name="twitter:image"       content="{{ $spaOgImage }}">
  @endif

  {{-- API key injected at render-time so it is never in static source files --}}
  <meta name="api-key" content="{{ config('civicore.api_key') }}">
  <meta name="asset-url" content="{{ asset('') }}">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL,GRAD,opsz@300,0,0,24&display=swap" rel="stylesheet">
  <style>
    body { font-family: 'Inter', sans-serif; background: #FAF9F6; }
    .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 300, 'GRAD' 0, 'opsz' 24; }
  </style>
  @vite(['resources/css/app.css', 'resources/js/app.jsx'])
  @php $gaId = \App\Models\Setting::get('ga_measurement_id', ''); @endphp
  @if($gaId)
  {{-- Google Analytics --}}
  <script async src="https://www.googletagmanager.com/gtag/js?id={{ $gaId }}"></script>
  <script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());
    gtag('config', '{{ $gaId }}');
  </script>
  @endif
</head>

<body>
  <div id="root"></div>
  {{-- Static fallback text for crawlers — hidden visually, readable by Googlebot --}}
  <noscript>
    <h1>Dwipapuri Residential Community</h1>
    <p>Discover upcoming community events, connect with neighbors, and experience curated residential living in Bandung. Visit our events calendar and resident portal.</p>
  </noscript>
</body>

</html>