<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
  <meta charset="utf-8" />
  <meta content="width=device-width, initial-scale=1.0" name="viewport" />
  <title>Dwipapuri | {{ $title ?? 'Dashboard' }}</title>
  <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">

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

  @auth
    {{-- â”€â”€ Auto Image Compression â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ --}}
    {{-- Automatically compresses any image file selected in an image input   --}}
    {{-- before it is submitted. Max 1920px width, JPEG 0.85 quality.         --}}
    {{-- Files already under 300 KB or non-image types (PDF) are skipped.     --}}
    <script>
      (function () {
        var MAX_SIDE = 1920;
        var QUALITY  = 0.85;
        var SIZE_THRESHOLD = 300 * 1024; // 300 KB — skip if smaller

        function compressFile(file, callback) {
          if (!file.type.startsWith('image/') || file.size <= SIZE_THRESHOLD) {
            callback(file);
            return;
          }
          var reader = new FileReader();
          reader.onload = function (e) {
            var img = new Image();
            img.onload = function () {
              var w = img.width, h = img.height;
              if (w <= MAX_SIDE && h <= MAX_SIDE) { callback(file); return; }
              var ratio = Math.min(MAX_SIDE / w, MAX_SIDE / h);
              var canvas = document.createElement('canvas');
              canvas.width  = Math.round(w * ratio);
              canvas.height = Math.round(h * ratio);
              canvas.getContext('2d').drawImage(img, 0, 0, canvas.width, canvas.height);
              canvas.toBlob(function (blob) {
                if (!blob || blob.size >= file.size) { callback(file); return; }
                var compressed = new File([blob], file.name.replace(/\.[^.]+$/, '.jpg'), { type: 'image/jpeg', lastModified: Date.now() });
                callback(compressed);
              }, 'image/jpeg', QUALITY);
            };
            img.src = e.target.result;
          };
          reader.readAsDataURL(file);
        }

        function replaceFileInInput(input, file) {
          try {
            var dt = new DataTransfer();
            dt.items.add(file);
            input.files = dt.files;
          } catch (err) {
            // DataTransfer not supported (very old browsers) — skip silently
          }
        }

        document.addEventListener('change', function (e) {
          var input = e.target;
          if (input.tagName !== 'INPUT' || input.type !== 'file') return;
          var accept = (input.getAttribute('accept') || '');
          if (!accept.includes('image/') && !accept.includes('image/*')) return;
          var file = input.files[0];
          if (!file) return;
          compressFile(file, function (result) {
            if (result !== file) replaceFileInInput(input, result);
          });
        });
      })();
    </script>
    {{-- â”€â”€ Idle Session Timeout â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ --}}
    @php $timeoutMinutes = (int) \App\Models\Setting::get('session_timeout_minutes', 30); @endphp
    <div id="idle-warning" class="hidden fixed inset-0 z-50 bg-black/60 backdrop-blur-sm flex items-center justify-center p-6">
      <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-2xl p-8 max-w-sm w-full text-center space-y-4">
        <div class="w-14 h-14 rounded-2xl bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center mx-auto">
          <span class="material-icons text-amber-500 text-2xl">timer</span>
        </div>
        <h3 class="text-lg font-bold text-slate-900 dark:text-white">Session Expiring</h3>
        <p class="text-sm text-slate-500">You'll be logged out in <strong id="idle-countdown" class="text-rose-500">60</strong> seconds due to inactivity.</p>
        <button onclick="resetIdleTimer()"
          class="w-full bg-primary hover:bg-primary/90 text-white py-2.5 rounded-xl font-bold transition-all">
          Stay Logged In
        </button>
      </div>
    </div>
    <form id="auto-logout-form" method="POST" action="{{ route('logout') }}" class="hidden">
      @csrf
    </form>
    <script>
      (function() {
        var TIMEOUT_MS    = {{ $timeoutMinutes * 60 * 1000 }};
        var WARNING_MS    = 60000; // show warning 60 s before logout
        var idleTimer, countdownInterval;
        var warningEl    = document.getElementById('idle-warning');
        var countdownEl  = document.getElementById('idle-countdown');
        var logoutForm   = document.getElementById('auto-logout-form');

        function startLogoutCountdown() {
          var secs = 60;
          countdownEl.textContent = secs;
          warningEl.classList.remove('hidden');
          countdownInterval = setInterval(function() {
            secs--;
            countdownEl.textContent = secs;
            if (secs <= 0) {
              clearInterval(countdownInterval);
              logoutForm.submit();
            }
          }, 1000);
        }

        function resetIdleTimer() {
          clearTimeout(idleTimer);
          clearInterval(countdownInterval);
          warningEl.classList.add('hidden');
          idleTimer = setTimeout(startLogoutCountdown, TIMEOUT_MS - WARNING_MS);
        }

        window.resetIdleTimer = resetIdleTimer;

        ['mousemove', 'keydown', 'click', 'scroll', 'touchstart'].forEach(function(evt) {
          document.addEventListener(evt, resetIdleTimer, { passive: true });
        });

        resetIdleTimer();
      })();
    </script>
  @endauth
</body>

</html>