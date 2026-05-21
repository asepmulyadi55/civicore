{{-- Admin memo tab (admin only) --}}
<div id="tab-memo" class="hidden space-y-6">
  <form method="POST" action="{{ route('settings.memo') }}"
    class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-6 space-y-5">
    @csrf
    <div class="flex items-center gap-3 mb-2">
      <div class="w-9 h-9 rounded-lg bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center">
        <span class="material-icons text-amber-500 text-lg">sticky_note_2</span>
      </div>
      <div>
        <h2 class="font-bold text-slate-900 dark:text-white">Admin Memo</h2>
        <p class="text-xs text-slate-500">Shown on the dashboard sidebar and on each resident's Overview page.</p>
      </div>
    </div>

    <div>
      <textarea name="admin_memo" rows="6"
        placeholder="Write a memo or announcement..."
        class="w-full px-3 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none transition-all resize-y"
        >{{ old('admin_memo', \App\Models\Setting::get('admin_memo', '')) }}</textarea>
      <p class="text-xs text-slate-400 mt-1">Max 1,000 characters. Leave blank to hide the notice.</p>
      @error('admin_memo') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
    </div>

    <div class="flex justify-end pt-1">
      <button type="submit"
        class="flex items-center gap-2 bg-primary hover:bg-primary/90 text-white px-6 py-2.5 rounded-lg font-semibold transition-all shadow-sm shadow-primary/20 text-sm">
        <span class="material-icons text-sm">save</span>
        Save Memo
      </button>
    </div>
  </form>
</div>
