{{-- ── Import Blocks & Units from Excel ─────────────────────────────────── --}}
<div id="modal-import-excel"
  class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 hidden items-center justify-center p-4"
  onclick="if(event.target===this){this.classList.add('hidden');this.classList.remove('flex')}">

  <div class="bg-white dark:bg-slate-900 w-full max-w-md rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-800 overflow-hidden">

    {{-- Header --}}
    <div class="px-8 py-5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
      <div>
        <h2 class="text-lg font-bold text-slate-900 dark:text-white">Import Blocks &amp; Units</h2>
        <p class="text-xs text-slate-500 mt-0.5">Upload your Excel file to import blocks and units in bulk.</p>
      </div>
      <button onclick="document.getElementById('modal-import-excel').classList.add('hidden');document.getElementById('modal-import-excel').classList.remove('flex')"
        class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors">
        <span class="material-icons">close</span>
      </button>
    </div>

    {{-- Form --}}
    <form method="POST" action="{{ route('blocks.import') }}" enctype="multipart/form-data" class="p-8 space-y-5">
      @csrf

      {{-- Info box --}}
      <div class="flex gap-3 p-4 rounded-xl bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800/50 text-sm">
        <span class="material-icons text-blue-500 shrink-0 mt-0.5">info</span>
        <div class="text-blue-700 dark:text-blue-300 space-y-1">
          <p class="font-semibold">Expected format (IuranWarga sheet)</p>
          <ul class="text-xs space-y-0.5 list-disc list-inside text-blue-600 dark:text-blue-400">
            <li>Column <strong>A</strong> — Block letter (A, B, C…)</li>
            <li>Column <strong>B</strong> — Unit number (1, 3, 5…)</li>
            <li>Column <strong>D</strong> — Status Warga (optional)</li>
            <li>Data starts at <strong>row 2</strong></li>
          </ul>
          <p class="text-xs mt-1">Existing blocks/units are <strong>updated</strong> to become active.</p>
        </div>
      </div>

      {{-- File picker --}}
      <div class="space-y-2">
        <label class="text-xs font-bold text-slate-500 uppercase">Excel File <span class="text-red-500">*</span></label>
        <label id="import-file-label"
          class="flex flex-col items-center justify-center gap-2 border-2 border-dashed border-slate-300 dark:border-slate-600 rounded-xl p-6 cursor-pointer hover:border-primary dark:hover:border-secondary hover:bg-primary/5 dark:hover:bg-secondary/5 transition-all group">
          <span class="material-icons text-3xl text-slate-400 group-hover:text-primary dark:group-hover:text-secondary transition-colors">upload_file</span>
          <span id="import-file-name" class="text-sm font-medium text-slate-500">Click to choose .xlsx file</span>
          <span class="text-[11px] text-slate-400 uppercase tracking-wider">xlsx · xls · max 10 MB</span>
          <input id="import-file-input" type="file" name="excel_file" accept=".xlsx,.xls"
            class="sr-only"
            onchange="document.getElementById('import-file-name').textContent = this.files[0]?.name ?? 'Click to choose .xlsx file'" />
        </label>
        @error('excel_file')
          <p class="text-xs text-red-500 flex items-center gap-1">
            <span class="material-icons text-xs">error_outline</span> {{ $message }}
          </p>
        @enderror
      </div>

      {{-- Status mapping reference --}}
      <details class="text-xs text-slate-500 cursor-pointer">
        <summary class="font-semibold hover:text-slate-700 dark:hover:text-slate-300">Status mapping reference</summary>
        <div class="mt-2 rounded-lg border border-slate-200 dark:border-slate-700 overflow-hidden">
          <table class="w-full text-xs">
            <thead class="bg-slate-50 dark:bg-slate-800">
              <tr>
                <th class="px-3 py-1.5 text-left font-bold text-slate-500">Excel Status</th>
                <th class="px-3 py-1.5 text-left font-bold text-slate-500">Imported As</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
              <tr><td class="px-3 py-1.5">Pemilik</td><td class="px-3 py-1.5 font-medium text-emerald-600">Owner Occupied</td></tr>
              <tr><td class="px-3 py-1.5">Pemilik Kosong</td><td class="px-3 py-1.5 font-medium text-amber-600">Vacant</td></tr>
              <tr><td class="px-3 py-1.5">Pemilik/Kosong</td><td class="px-3 py-1.5 font-medium text-amber-600">Vacant</td></tr>
              <tr><td class="px-3 py-1.5">Kavling</td><td class="px-3 py-1.5 font-medium text-amber-600">Vacant</td></tr>
              <tr><td class="px-3 py-1.5">Pengontrak</td><td class="px-3 py-1.5 font-medium text-blue-600">Rented</td></tr>
              <tr><td class="px-3 py-1.5">Developer / Warga</td><td class="px-3 py-1.5 font-medium text-emerald-600">Owner Occupied</td></tr>
              <tr><td class="px-3 py-1.5">FasUm / Fasilitas Umum</td><td class="px-3 py-1.5 font-medium text-teal-600">Public Facility</td></tr>
              <tr><td class="px-3 py-1.5 italic text-slate-400">(Empty / Unknown)</td><td class="px-3 py-1.5 font-medium text-amber-600">Vacant</td></tr>
            </tbody>
          </table>
        </div>
      </details>

      {{-- Actions --}}
      <div class="flex gap-3 pt-1">
        <button type="button"
          onclick="document.getElementById('modal-import-excel').classList.add('hidden');document.getElementById('modal-import-excel').classList.remove('flex')"
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
