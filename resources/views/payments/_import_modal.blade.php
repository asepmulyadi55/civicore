{{-- Import Excel Modal --}}
<div id="importModal" tabindex="-1" aria-hidden="true"
  class="hidden fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-sm transition-opacity">
  <div class="relative w-full max-w-md bg-white dark:bg-slate-900 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-800 m-4">
    <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 dark:border-slate-800">
      <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Import Payments from Excel</h3>
      <button type="button" onclick="document.getElementById('importModal').classList.add('hidden')"
        class="text-slate-400 hover:text-slate-500 dark:hover:text-slate-300">
        <span class="material-icons">close</span>
      </button>
    </div>
    
    <form action="{{ route('payments.import') }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-4">
      @csrf
      
      <div>
        <label for="excel_file" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Excel File (.xlsx, .xls)</label>
        <input type="file" name="excel_file" id="excel_file" accept=".xlsx,.xls" required
          class="w-full text-sm text-slate-500 dark:text-slate-400 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-primary/10 file:text-primary hover:file:bg-primary/20 transition-all border border-slate-200 dark:border-slate-700 rounded-lg p-1">
        <p class="text-xs text-slate-500 dark:text-slate-400 mt-2">
          Format must match Data IPL.xlsx (Col A: Block, Col B: Unit, Col E: Amount, Cols F-Q: Jan-Dec payments).
        </p>
      </div>

      <div>
        <label for="year" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Target Year</label>
        <input type="number" name="year" id="year" required min="2020" max="2035" value="{{ date('Y') }}"
          class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary text-slate-900 dark:text-white sm:text-sm">
        <p class="text-xs text-slate-500 dark:text-slate-400 mt-2">
          Specify the year for these payments. "L" cells will create Approved payments for this year.
        </p>
      </div>

      <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-100 dark:border-slate-800 mt-6">
        <button type="button" onclick="document.getElementById('importModal').classList.add('hidden')"
          class="px-4 py-2 text-sm font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 rounded-lg transition-colors">
          Cancel
        </button>
        <button type="submit"
          class="px-4 py-2 text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg shadow-sm shadow-emerald-600/20 transition-all flex items-center gap-2">
          <span class="material-icons text-[18px]">cloud_upload</span>
          Start Import
        </button>
      </div>
    </form>
  </div>
</div>
