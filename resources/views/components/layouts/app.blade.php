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
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&family=Inter:wght@300;400;500;600&display=swap"
    rel="stylesheet" />
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL,GRAD,opsz@300,0,0,24&display=swap"
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
            "primary":    "#1C2D27",
            "secondary":  "#D4AF37",
            "surface":    "#F8F8F7",
            "dark-bg":    "#111815",
            "dark-card":  "#17221E",
            "background-light": "#F8F8F7",
            "background-dark":  "#111815",
          },
          fontFamily: {
            "headline": ["Plus Jakarta Sans", "sans-serif"],
            "body":     ["Inter", "sans-serif"],
            "display":  ["Plus Jakarta Sans", "sans-serif"],
          },
          boxShadow: {
            "elegant":      "0 4px 24px -12px rgba(28, 45, 39, 0.10)",
            "elegant-dark": "0 4px 24px -12px rgba(0, 0, 0, 0.50)",
          },
          borderRadius: {
            "DEFAULT": "0.25rem",
            "lg":  "0.5rem",
            "xl":  "0.75rem",
            "2xl": "1rem",
            "full": "9999px",
          },
        },
      },
    }
  </script>
  <style>
    body { font-family: 'Inter', sans-serif; }
    .font-headline { font-family: 'Plus Jakarta Sans', sans-serif; }
    .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 300, 'GRAD' 0, 'opsz' 24; }

    /* ── Dark Mode Normalization ──────────────────────────────
       Rebases legacy slate-800/900 card surfaces to dark-card (#17221E)
       and makes borders/dividers use the design token.
       Applied here (after Tailwind CDN) so these take precedence.  */
    .dark .dark\:bg-slate-900  { background-color: #17221E; }
    .dark .dark\:bg-slate-800  { background-color: rgba(255,255,255,0.04); }
    .dark .dark\:border-slate-800 { border-color: rgba(255,255,255,0.06); }
    .dark .dark\:border-slate-700 { border-color: rgba(255,255,255,0.08); }
    .dark .dark\:divide-slate-800 > :not([hidden]) ~ :not([hidden]) { border-color: rgba(255,255,255,0.05); }
    /* Primary color (#1C2D27) is invisible on dark backgrounds — show secondary gold instead */
    .dark .text-primary { color: #D4AF37; }
    /* hover:text-primary in dark mode: same fix — gold instead of near-black */
    .dark .hover\:text-primary:hover { color: #D4AF37; }
    /* group-hover:text-primary in dark mode */
    .dark .group:hover .group-hover\:text-primary { color: #D4AF37; }
    /* hover:bg-primary/5 in dark mode: use a subtle gold wash instead of dark-green tint */
    .dark .hover\:bg-primary\/5:hover { background-color: rgba(212,175,55,0.08); }
    /* focus:ring-primary/20 and focus:border-primary stay as-is (they already look fine) */

    /* ── Disabled / Readonly field distinction ───────────────────────
       Makes locked fields visually distinct from editable ones.
       Works for both :disabled attribute and .cursor-not-allowed helper. */
    input:disabled, select:disabled, textarea:disabled {
      background-color: #f1f5f9 !important;
      border-style: dashed !important;
      cursor: not-allowed !important;
      opacity: 0.65 !important;
    }
    .dark input:disabled, .dark select:disabled, .dark textarea:disabled {
      background-color: rgba(0, 0, 0, 0.35) !important;
      border-style: dashed !important;
      cursor: not-allowed !important;
      opacity: 0.55 !important;
    }

    /* ── Hide native browser picker icon (date / time / month) ──────────
       opacity:0 hides the icon visually; stretching it to 100% width/height
       keeps the click target alive so the picker still opens on click.      */
    input[type="date"],
    input[type="time"],
    input[type="month"] {
      position: relative;
    }
    input[type="date"]::-webkit-calendar-picker-indicator,
    input[type="time"]::-webkit-calendar-picker-indicator,
    input[type="month"]::-webkit-calendar-picker-indicator {
      opacity: 0;
      position: absolute;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      cursor: pointer;
    }
    /* Ensure the custom Material Icon is always visible above the input overlay */
    .relative > span.material-icons {
      z-index: 1;
      pointer-events: none;
    }
  </style>

  {{-- Extra head content (per-page scripts, meta tags, etc.) --}}
  {{ $head ?? '' }}
</head>

<body {{ $attributes->merge(['class' => 'font-body bg-surface dark:bg-dark-bg text-slate-800 dark:text-slate-300 antialiased min-h-screen transition-colors duration-500']) }}>
  {{ $slot }}

  {{-- Global dark mode toggle — saves preference in localStorage so it persists across navigations --}}
  <script>
    function toggleDark() {
      var isDark = document.documentElement.classList.toggle('dark');
      localStorage.setItem('theme', isDark ? 'dark' : 'light');
    }
  </script>

  @auth
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

    {{-- Global Bulk Delete Confirmation Modal --}}
    <div id="modal-bulk-delete" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
      <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-2xl w-full max-w-sm overflow-hidden transform transition-all duration-200 scale-95 opacity-0" id="bulk-delete-card">
        <div class="flex flex-col items-center pt-8 pb-5 px-6 text-center">
          <div class="w-16 h-16 rounded-full bg-rose-100 dark:bg-rose-900/30 flex items-center justify-center mb-4">
            <span class="material-icons text-rose-600 dark:text-rose-400 text-3xl">delete_sweep</span>
          </div>
          <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-2">{{ __('app.delete_title') ?? 'Confirm Deletion' }}</h3>
          <p id="bulk-delete-message" class="text-sm text-slate-500 dark:text-slate-400 leading-relaxed"></p>
        </div>
        <div class="flex gap-3 px-6 pb-6">
          <button type="button" onclick="closeBulkDeleteModal()"
            class="flex-1 px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 text-sm font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition-all">
            {{ __('app.btn_cancel') ?? 'Cancel' }}
          </button>
          <button type="button" id="bulk-delete-confirm-btn"
            class="flex-1 px-4 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-sm font-bold text-white transition-all">
            {{ __('app.btn_yes_delete') ?? 'Yes, Delete' }}
          </button>
        </div>
      </div>
    </div>

    <script>
      let currentBulkDeleteFormId = null;

      function confirmBulkDelete(event, formId, message) {
        event.preventDefault();
        currentBulkDeleteFormId = formId;
        document.getElementById('bulk-delete-message').textContent = message;

        const modal = document.getElementById('modal-bulk-delete');
        const card = document.getElementById('bulk-delete-card');
        modal.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
        
        requestAnimationFrame(() => {
          card.classList.remove('scale-95', 'opacity-0');
          card.classList.add('scale-100', 'opacity-100');
        });
      }

      function closeBulkDeleteModal() {
        const modal = document.getElementById('modal-bulk-delete');
        const card = document.getElementById('bulk-delete-card');
        card.classList.remove('scale-100', 'opacity-100');
        card.classList.add('scale-95', 'opacity-0');
        setTimeout(() => {
          modal.classList.add('hidden');
          document.body.classList.remove('overflow-hidden');
          currentBulkDeleteFormId = null;
        }, 150);
      }

      document.getElementById('bulk-delete-confirm-btn').addEventListener('click', function() {
        if (currentBulkDeleteFormId) {
          document.getElementById(currentBulkDeleteFormId).submit();
        }
      });

      document.addEventListener('keydown', e => {
        if (e.key === 'Escape' && currentBulkDeleteFormId) closeBulkDeleteModal();
      });
    </script>
  @endauth
</body>

</html>