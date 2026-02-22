<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
  <meta charset="utf-8" />
  <meta content="width=device-width, initial-scale=1.0" name="viewport" />
  <title>CiviCore | {{ $title ?? 'Dashboard' }}</title>

  {{-- Dark mode: restore saved preference BEFORE paint to avoid flash of wrong theme --}}
  <script>
    (function () {
      var saved = localStorage.getItem('theme');
      if (saved === 'dark' || (!saved && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
        document.documentElement.classList.add('dark');
      }
    })();
  </script>

  {{-- Tailwind CDN --}}
  <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>

  {{-- Google Fonts --}}
  <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@300;400;500;600;700;800&display=swap"
    rel="stylesheet" />
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap"
    rel="stylesheet" />

  {{--
  Tailwind config — defined once here for all pages.
  NOTE: This must be an inline
  <script> when using the CDN.
      If you switch to Tailwind CLI / Vite, move this to tailwind.config.js at the project root.
  --}}
    <script>
      tailwind.config = {
        darkMode: "class",
      theme: {
        extend: {
        colors: {
        "primary": "#137fec",
      "background-light": "#f6f7f8",
      "background-dark": "#101922",
          },
      fontFamily: {
        "display": ["Manrope"]
          },
      borderRadius: {
        "DEFAULT": "0.25rem",
      "lg": "0.5rem",
      "xl": "0.75rem",
      "full": "9999px"
          },
        },
      },
    }
  </script>

  {{-- Extra head content (per-page scripts, meta tags, etc.) --}}
  {{ $head ?? '' }}
</head>

<body {{ $attributes->merge(['class' => 'font-display antialiased']) }}>
  {{ $slot }}

  {{-- Global dark mode toggle — saves preference in localStorage so it persists across navigations --}}
  <script>
    function toggleDark() {
      var isDark = document.documentElement.classList.toggle('dark');
    localStorage.setItem('theme', isDark ? 'dark' : 'light');
    }
  </script>
</body>

</html>