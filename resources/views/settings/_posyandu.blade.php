{{-- settings/_posyandu.blade.php — Admin-only Posyandu category range manager --}}
@php
  use App\Http\Controllers\PosyanduController;
  $limits  = PosyanduController::categoryLimits();
  $defs    = PosyanduController::DEFAULT_LIMITS;

  $rows = [
    ['key' => 'baby_max_months',    'label' => 'Bayi',   'icon' => 'child_care',        'color' => 'text-pink-500',    'hint' => 'Batas atas usia Bayi (dalam bulan). Default: 12 bln = 0–11 bln.'],
    ['key' => 'toddler_max_months', 'label' => 'Balita', 'icon' => 'escalator_warning',  'color' => 'text-purple-500',  'hint' => 'Batas atas usia Balita (dalam bulan). Default: 60 bln = 1–4 thn.'],
    ['key' => 'child_max_months',   'label' => 'Anak',   'icon' => 'face',               'color' => 'text-blue-500',    'hint' => 'Batas atas usia Anak (dalam bulan). Default: 144 bln = 5–11 thn.'],
    ['key' => 'teen_max_months',    'label' => 'Remaja', 'icon' => 'school',             'color' => 'text-indigo-500',  'hint' => 'Batas atas usia Remaja (dalam bulan). Default: 216 bln = 12–17 thn.'],
    ['key' => 'adult_max_months',   'label' => 'Dewasa', 'icon' => 'person',             'color' => 'text-emerald-500', 'hint' => 'Batas atas usia Dewasa (dalam bulan). Default: 720 bln = 18–59 thn. Lansia = di atas nilai ini.'],
  ];
@endphp

<div id="tab-posyandu" class="hidden space-y-6">
  <form method="POST" action="{{ route('settings.posyandu') }}"
    class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-6 space-y-6">
    @csrf

    {{-- Header --}}
    <div class="flex items-center gap-3">
      <div class="w-9 h-9 rounded-lg bg-teal-100 dark:bg-teal-900/30 flex items-center justify-center">
        <span class="material-icons text-teal-500 text-lg">health_and_safety</span>
      </div>
      <div>
        <h2 class="font-bold text-slate-900 dark:text-white">Kategori Usia Posyandu</h2>
        <p class="text-xs text-slate-500">Atur batas usia maksimal tiap kategori. Nilai dalam <strong>bulan</strong>.</p>
      </div>
    </div>

    {{-- Info note --}}
    <div class="flex items-start gap-2 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg px-4 py-3 text-xs text-blue-700 dark:text-blue-300">
      <span class="material-icons text-sm mt-0.5 shrink-0">info</span>
      <span>
        Kategori diurut dari terkecil ke terbesar. Setiap kategori dimulai dari batas atas kategori sebelumnya.
        Anggota dengan usia <strong>di atas batas Dewasa</strong> otomatis masuk kategori <strong>Lansia</strong>.
      </span>
    </div>

    {{-- Fields --}}
    <div class="space-y-4">
      @foreach($rows as $row)
        @php
          $fieldKey = 'posyandu_' . $row['key'];
          $current  = $limits[$row['key']];
          $default  = $defs[$row['key']];
          $years    = round($current / 12, 1);
        @endphp
        <div class="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-4 p-4 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60">
          {{-- Category label --}}
          <div class="flex items-center gap-2 w-32 shrink-0">
            <span class="material-icons text-lg {{ $row['color'] }}">{{ $row['icon'] }}</span>
            <span class="text-sm font-bold text-slate-800 dark:text-white">{{ $row['label'] }}</span>
          </div>

          {{-- Input --}}
          <div class="flex items-center gap-3 flex-1">
            <div class="relative">
              <input type="number" name="{{ $row['key'] }}" id="posyandu-{{ $row['key'] }}"
                value="{{ old($row['key'], $current) }}"
                min="1" max="840" step="1"
                class="w-24 px-3 py-2 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-600 rounded-lg text-sm text-center font-mono font-bold focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none transition-all dark:text-white"
                onchange="updateYearsHint(this)">
              <span class="absolute right-2 top-1/2 -translate-y-1/2 text-xs text-slate-400 pointer-events-none">bln</span>
            </div>
            <span id="hint-{{ $row['key'] }}" class="text-xs text-slate-500">≈ {{ $years }} thn</span>
          </div>

          {{-- Hint text --}}
          <p class="text-xs text-slate-400 italic hidden sm:block flex-1">{{ $row['hint'] }}</p>

          @error($row['key'])
            <p class="text-xs text-rose-500">{{ $message }}</p>
          @enderror
        </div>
      @endforeach
    </div>

    <div class="flex items-center justify-between pt-2">
      <p class="text-xs text-slate-400">Perubahan berlaku segera setelah disimpan dan cache dikosongkan.</p>
      <button type="submit"
        class="flex items-center gap-2 bg-teal-600 hover:bg-teal-700 text-white px-6 py-2.5 rounded-lg font-semibold transition-all shadow-sm text-sm">
        <span class="material-icons text-sm">save</span>
        Simpan Kategori
      </button>
    </div>
  </form>
</div>

<script>
  function updateYearsHint(input) {
    const key   = input.id.replace('posyandu-', '');
    const hint  = document.getElementById('hint-' + key);
    if (!hint) return;
    const months = parseInt(input.value, 10);
    if (!isNaN(months) && months > 0) {
      hint.textContent = '\u2248 ' + (months / 12).toFixed(1) + ' thn';
    }
  }
</script>
