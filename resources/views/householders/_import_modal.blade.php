{{-- ── Import Residents, Fees & Payments from Excel ──────────────────────── --}}
<div id="modal-import-residents"
  class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 hidden items-center justify-center p-4"
  onclick="if(event.target===this){this.classList.add('hidden');this.classList.remove('flex')}">

  <div class="bg-white dark:bg-slate-900 w-full max-w-lg rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-800 overflow-hidden">

    {{-- Header --}}
    <div class="px-8 py-5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
      <div>
        <h2 class="text-lg font-bold text-slate-900 dark:text-white">Import Residents from Excel</h2>
        <p class="text-xs text-slate-500 mt-0.5">Creates residents, fees, and paid payment records in one step.</p>
      </div>
      <button onclick="document.getElementById('modal-import-residents').classList.add('hidden');document.getElementById('modal-import-residents').classList.remove('flex')"
        class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors">
        <span class="material-icons">close</span>
      </button>
    </div>

    {{-- Form --}}
    <form method="POST" action="{{ route('householders.import') }}" enctype="multipart/form-data" class="p-8 space-y-5">
      @csrf

      {{-- Info box --}}
      <div class="flex gap-3 p-4 rounded-xl bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800/50 text-sm">
        <span class="material-icons text-blue-500 shrink-0 mt-0.5 text-base">info</span>
        <div class="text-blue-700 dark:text-blue-300 space-y-1.5 text-xs">
          <p class="font-semibold text-sm">What gets imported</p>
          <ul class="space-y-1 list-disc list-inside text-blue-600 dark:text-blue-400">
            <li><strong>Col I</strong> → Resident full name</li>
            <li><strong>Col F + G</strong> → Linked to existing Block + Unit</li>
            <li><strong>Col K</strong> → Monthly fee (creates Fee History)</li>
            <li><strong>L/M, O/P, R/S … (12 months)</strong> → Payment records where status = "L" (Lunas)</li>
          </ul>
          <p class="mt-1">⚠️ Run <strong>Block &amp; Unit import</strong> first. Residents with no matching unit are skipped. Re-running is safe — existing records are never overwritten.</p>
        </div>
      </div>

      {{-- Year selector --}}
      <div class="space-y-2">
        <label class="text-xs font-bold text-slate-500 uppercase">Payment Year <span class="text-red-500">*</span></label>
        <div class="relative">
          <span class="material-icons absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-lg">calendar_today</span>
          <select name="year"
            class="w-full appearance-none pl-10 pr-9 py-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none dark:text-white">
            @for($y = now()->year; $y >= 2023; $y--)
              <option value="{{ $y }}" {{ $y === 2026 ? 'selected' : '' }}>{{ $y }}</option>
            @endfor
          </select>
          <span class="material-icons absolute right-2.5 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400 text-[18px]">expand_more</span>
        </div>
        <p class="text-[11px] text-slate-400">Sets the <code>effective_from</code> date for fees and maps months for payment records.</p>
      </div>

      {{-- File picker --}}
      <div class="space-y-2">
        <label class="text-xs font-bold text-slate-500 uppercase">Excel File <span class="text-red-500">*</span></label>
        <label
          class="flex flex-col items-center justify-center gap-2 border-2 border-dashed border-slate-300 dark:border-slate-600 rounded-xl p-6 cursor-pointer hover:border-primary dark:hover:border-secondary hover:bg-primary/5 dark:hover:bg-secondary/5 transition-all group">
          <span class="material-icons text-3xl text-slate-400 group-hover:text-primary dark:group-hover:text-secondary transition-colors">upload_file</span>
          <span id="resident-import-file-name" class="text-sm font-medium text-slate-500">Click to choose .xlsx file</span>
          <span class="text-[11px] text-slate-400 uppercase tracking-wider">xlsx · xls · max 10 MB</span>
          <input type="file" name="excel_file" accept=".xlsx,.xls" class="sr-only"
            onchange="document.getElementById('resident-import-file-name').textContent = this.files[0]?.name ?? 'Click to choose .xlsx file'" />
        </label>
        @error('excel_file')
          <p class="text-xs text-red-500 flex items-center gap-1">
            <span class="material-icons text-xs">error_outline</span> {{ $message }}
          </p>
        @enderror
      </div>

      {{-- Month/column reference --}}
      <details class="text-xs text-slate-500 cursor-pointer">
        <summary class="font-semibold hover:text-slate-700 dark:hover:text-slate-300">Month → column mapping</summary>
        <div class="mt-2 grid grid-cols-2 gap-1 text-[11px]">
          @foreach(['Jan→K/L/M','Feb→N/O/P','Mar→Q/R/S','Apr→T/U/V','May→W/X/Y','Jun→Z/AA/AB','Jul→AC/AD/AE','Aug→AF/AG/AH','Sep→AI/AJ/AK','Oct→AL/AM/AN','Nov→AO/AP/AQ','Dec→AR/AS/AT'] as $m)
            <span class="px-2 py-1 rounded bg-slate-100 dark:bg-slate-800 font-mono">{{ $m }}</span>
          @endforeach
          <p class="col-span-2 mt-1 text-slate-400">Fee col / Date col / Status col. "L"=Lunas (paid) → imported as Approved.</p>
        </div>
      </details>

      {{-- Actions --}}
      <div class="flex gap-3 pt-1">
        <button type="button"
          onclick="document.getElementById('modal-import-residents').classList.add('hidden');document.getElementById('modal-import-residents').classList.remove('flex')"
          class="flex-1 px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 text-sm font-bold text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
          Cancel
        </button>
        <button type="submit"
          class="flex-1 px-4 py-2.5 rounded-xl bg-primary text-white text-sm font-bold hover:bg-primary/90 shadow-lg shadow-primary/20 transition-all flex items-center justify-center gap-2 active:scale-95">
          <span class="material-icons text-sm">upload</span>
          Run Import
        </button>
      </div>
    </form>
  </div>
</div>


