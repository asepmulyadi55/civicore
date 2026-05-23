{{-- organization/_periods_panel.blade.php — Period management table (admin only) --}}
<div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
  <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
    <div class="flex items-center gap-2">
      <span class="material-icons text-primary dark:text-secondary text-lg">date_range</span>
      <h2 class="font-semibold text-slate-800 dark:text-white text-sm">{{ __('app.org_manage_periods') }}</h2>
    </div>
    <button onclick="openOrgPeriodModal()"
      class="flex items-center gap-1 px-3 py-1.5 rounded-lg bg-primary hover:bg-primary/90 dark:bg-secondary dark:hover:bg-secondary/90 text-white dark:text-primary font-semibold text-xs transition-all shadow-sm">
      <span class="material-icons text-sm">add</span>{{ __('app.org_add_period') }}
    </button>
  </div>

  @if($periods->isEmpty())
    <div class="p-8 text-center">
      <p class="text-sm text-slate-400">{{ __('app.org_no_periods') }}</p>
    </div>
  @else
    <div class="overflow-x-auto">
      <table class="w-full text-left">
        <thead class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-800">
          <tr>
            <th class="px-5 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider">{{ __('app.org_period_name') }}</th>
            <th class="px-5 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider hidden sm:table-cell">{{ __('app.org_start_year') }}</th>
            <th class="px-5 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider hidden sm:table-cell">{{ __('app.org_end_year') }}</th>
            <th class="px-5 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider">Status</th>
            <th class="px-5 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider text-center">{{ __('app.table_actions') }}</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
          @foreach($periods as $period)
            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition-colors">
              <td class="px-5 py-3 font-semibold text-sm text-slate-800 dark:text-white">{{ $period->name }}</td>
              <td class="px-5 py-3 text-sm text-slate-500 hidden sm:table-cell">{{ $period->start_year }}</td>
              <td class="px-5 py-3 text-sm text-slate-500 hidden sm:table-cell">{{ $period->end_year }}</td>
              <td class="px-5 py-3">
                @if($period->is_active)
                  <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 inline-block"></span>
                    {{ __('app.org_active_badge') }}
                  </span>
                @else
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400">
                    {{ __('app.org_inactive_badge') }}
                  </span>
                @endif
              </td>
              <td class="px-5 py-3">
                <div class="flex items-center justify-center gap-2">
                  {{-- Activate --}}
                  @if(!$period->is_active && auth()->user()->can('organization.edit'))
                    <form method="POST" action="{{ route('organization.periods.activate', $period) }}">
                      @csrf @method('PATCH')
                      <button type="submit"
                        class="text-xs font-semibold text-emerald-600 hover:text-emerald-700 dark:text-emerald-400 dark:hover:text-emerald-300 underline underline-offset-2 transition-colors"
                        title="{{ __('app.org_set_active') }}">
                        {{ __('app.org_set_active') }}
                      </button>
                    </form>
                  @endif

                  {{-- Edit --}}
                  @if(auth()->user()->can('organization.edit'))
                    <button
                      onclick="openEditPeriodModal({{ json_encode(['id' => $period->id, 'name' => $period->name, 'start_year' => $period->start_year, 'end_year' => $period->end_year]) }})"
                      class="p-1.5 rounded-lg border border-slate-200 dark:border-slate-700 hover:border-primary/50 dark:hover:border-secondary/50 transition-all"
                      title="{{ __('app.org_edit_period') }}">
                      <span class="material-icons text-slate-400 dark:text-slate-500" style="font-size:15px">edit</span>
                    </button>
                  @endif

                  {{-- Delete --}}
                  @if(!$period->is_active && auth()->user()->can('organization.delete'))
                    <button
                      onclick="confirmDeletePeriod('{{ $period->id }}', '{{ addslashes($period->name) }}')"
                      class="p-1.5 rounded-lg border border-slate-200 dark:border-slate-700 hover:border-red-400 transition-all"
                      title="{{ __('app.org_delete_period') }}">
                      <span class="material-icons text-slate-400 hover:text-red-500" style="font-size:15px">delete</span>
                    </button>
                  @endif
                </div>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  @endif
</div>
