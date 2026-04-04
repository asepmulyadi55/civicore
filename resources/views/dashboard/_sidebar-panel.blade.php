{{-- Quick Actions + Community Status + Admin Memo --}}
<div
  class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm p-6 space-y-6">
  <h2 class="text-lg font-bold text-slate-900 dark:text-white">{{ __('app.quick_actions') }}</h2>
  <div class="grid grid-cols-2 gap-4">
    <a href="{{ route('residents.index') }}"
      class="flex flex-col items-center justify-center p-4 bg-primary text-white rounded-xl hover:bg-blue-600 transition-colors space-y-2 text-center group">
      <span class="material-icons text-2xl group-hover:scale-110 transition-transform">person_add</span>
      <span class="text-xs font-bold">{{ __('app.action_add_resident') }}</span>
    </a>
    <a href="{{ route('payments.index') }}"
      class="flex flex-col items-center justify-center p-4 bg-slate-100 dark:bg-slate-800 text-slate-900 dark:text-white rounded-xl hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors space-y-2 text-center group">
      <span class="material-icons text-2xl text-primary group-hover:scale-110 transition-transform">receipt_long</span>
      <span class="text-xs font-bold">{{ __('app.action_payments') }}</span>
    </a>
    <a href="{{ route('reports.index') }}"
      class="flex flex-col items-center justify-center p-4 bg-slate-100 dark:bg-slate-800 text-slate-900 dark:text-white rounded-xl hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors space-y-2 text-center group">
      <span
        class="material-icons text-2xl text-primary group-hover:scale-110 transition-transform">picture_as_pdf</span>
      <span class="text-xs font-bold">{{ __('app.action_generate_report') }}</span>
    </a>
    @if(auth()->user()->isAdmin() || auth()->user()->isTreasurer())
      <a href="{{ route('blocks.index') }}"
        class="flex flex-col items-center justify-center p-4 bg-slate-100 dark:bg-slate-800 text-slate-900 dark:text-white rounded-xl hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors space-y-2 text-center group">
        <span class="material-icons text-2xl text-primary group-hover:scale-110 transition-transform">domain</span>
        <span class="text-xs font-bold">{{ __('app.action_manage_blocks') }}</span>
      </a>
    @endif
  </div>

  <div class="p-4 bg-amber-50 dark:bg-amber-900/10 border border-amber-100 dark:border-amber-900/30 rounded-lg">
    <div class="flex items-center space-x-2 text-amber-700 dark:text-amber-400 mb-2">
      <span class="material-icons text-sm">sticky_note_2</span>
      <span class="text-xs font-bold uppercase tracking-wider">{{ __('app.admin_memo') }}</span>
    </div>
    @php $adminMemo = \App\Models\Setting::get('admin_memo', ''); @endphp
    @if($adminMemo)
      <p class="text-xs text-amber-800 dark:text-amber-300 leading-relaxed">{{ $adminMemo }}</p>
    @else
      <p class="text-xs text-amber-400 italic">No memo set. Add one in Settings → Admin Memo.</p>
    @endif
  </div>
</div>