<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dwipapuri - Community Events</title>
  <meta name="description"
    content="Dwipapuri Community Events — bringing neighbors together through curated gatherings, cultural celebrations, and digital experiences.">
  {{-- API key injected at render-time so it is never in static source files --}}
  <meta name="api-key" content="{{ config('civicore.api_key') }}">
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
</head>

<body>
  <div id="root"></div>
</body>

</html>