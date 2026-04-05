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
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  @vite(['resources/css/app.css', 'resources/js/app.jsx'])
</head>

<body>
  <div id="root"></div>
</body>

</html>