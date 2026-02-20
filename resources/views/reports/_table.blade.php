{{-- Yearly Payment Grid Table --}}
<div
  class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden flex flex-col">
  <div class="overflow-x-auto" style="-webkit-overflow-scrolling: touch;">
    <table class="w-full text-left border-collapse min-w-[1200px]">
      <thead>
        <tr class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-800">
          <th
            class="sticky left-0 z-20 bg-slate-50 dark:bg-slate-800 px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider border-r border-slate-200 dark:border-slate-800">
            Unit &amp; Resident
          </th>
          @foreach(['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'] as $m)
            <th class="px-3 py-4 text-center text-xs font-bold text-slate-500 uppercase tracking-wider">{{ $m }}</th>
          @endforeach
          <th class="px-6 py-4 text-right text-xs font-bold text-primary uppercase tracking-wider">Annual Total</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100 dark:divide-slate-800">

        {{-- A-101: Jonathan Wick — 10 paid, 2 unpaid --}}
        @php
          $rows = [
            [
              'unit' => 'A-101',
              'name' => 'Jonathan Wick',
              'total' => '$1,200',
              'months' => ['05/01', '03/02', '01/03', '08/04', '02/05', null, '12/07', '04/08', '15/09', null, '10/11', '05/12']
            ],
            ['unit' => 'A-102', 'name' => 'Sarah Jenkins', 'total' => '$1,440', 'annual' => true],
            [
              'unit' => 'A-103',
              'name' => 'Robert Chen',
              'total' => '$960',
              'months' => ['10/01', null, null, null, null, '15/06', '20/07', '22/08', '02/09', '10/10', '12/11', '05/12']
            ],
            [
              'unit' => 'A-104',
              'name' => 'Maria Rodriguez',
              'total' => '$1,440',
              'months' => ['04/01', '02/02', '05/03', '10/04', '12/05', '15/06', '20/07', '22/08', '02/09', '10/10', '12/11', '05/12']
            ],
            [
              'unit' => 'A-105',
              'name' => 'David Miller',
              'total' => '$360',
              'months' => ['05/01', '05/02', '05/03', null, null, null, null, null, null, null, null, null]
            ],
          ];
        @endphp

        @foreach($rows as $row)
          <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors">
            <td
              class="sticky left-0 z-10 bg-white dark:bg-slate-900 px-6 py-4 border-r border-slate-200 dark:border-slate-800">
              <div class="flex flex-col">
                <span class="text-sm font-bold text-slate-900 dark:text-white">{{ $row['unit'] }}</span>
                <span class="text-[11px] text-slate-400 font-medium">{{ $row['name'] }}</span>
              </div>
            </td>

            @if (!empty($row['annual']))
              {{-- Annual advance payment row --}}
              <td class="p-1" colspan="12">
                <div
                  class="bg-emerald-50/50 dark:bg-emerald-900/10 text-emerald-600/70 dark:text-emerald-400/70 py-3 rounded text-center font-bold text-xs">
                  ANNUAL ADVANCE PAYMENT — RECEIVED 01 JAN
                </div>
              </td>
            @else
              @foreach($row['months'] as $date)
                <td class="p-1">
                  @if($date)
                    <div
                      class="bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600 dark:text-emerald-400 py-3 rounded text-center">
                      <span class="text-[10px] font-bold block">{{ $date }}</span>
                      <span class="material-icons text-sm">check_circle</span>
                    </div>
                  @else
                    <div
                      class="bg-red-50 dark:bg-red-900/20 text-red-500 py-3 rounded text-center flex flex-col items-center justify-center min-h-[44px]">
                      <span class="material-icons text-sm">priority_high</span>
                    </div>
                  @endif
                </td>
              @endforeach
            @endif

            <td class="px-6 py-4 text-right">
              <span class="text-sm font-bold text-slate-900 dark:text-white">{{ $row['total'] }}</span>
            </td>
          </tr>
        @endforeach

      </tbody>
      <tfoot>
        <tr class="bg-slate-50 dark:bg-slate-800/80 border-t-2 border-slate-200 dark:border-slate-700">
          <td
            class="sticky left-0 z-10 bg-slate-100 dark:bg-slate-800 px-6 py-4 font-bold text-slate-900 dark:text-white border-r border-slate-200 dark:border-slate-800">
            Column Totals
          </td>
          @foreach(['$12,400', '$11,200', '$10,800', '$12,000', '$11,900', '$9,500', '$13,200', '$12,800', '$12,100', '$10,400', '$11,600', '$12,400'] as $col)
            <td class="px-3 py-4 text-center text-xs font-bold text-slate-700 dark:text-slate-300">{{ $col }}</td>
          @endforeach
          <td class="px-6 py-4 text-right font-black text-primary text-sm">$140,300</td>
        </tr>
      </tfoot>
    </table>
  </div>

  {{-- Legend + Pagination --}}
  <div
    class="p-4 flex flex-col md:flex-row items-center justify-between border-t border-slate-200 dark:border-slate-800 gap-4">
    <div class="flex items-center gap-6">
      <div class="flex items-center gap-2">
        <div class="w-3 h-3 bg-emerald-500 rounded-sm"></div>
        <span class="text-xs font-medium text-slate-500">Paid / Up-to-date</span>
      </div>
      <div class="flex items-center gap-2">
        <div class="w-3 h-3 bg-red-400 rounded-sm"></div>
        <span class="text-xs font-medium text-slate-500">Unpaid / Arrears</span>
      </div>
      <div class="flex items-center gap-2">
        <div class="w-3 h-3 bg-slate-200 dark:bg-slate-700 rounded-sm"></div>
        <span class="text-xs font-medium text-slate-500">N/A / Vacant</span>
      </div>
    </div>
    <div class="flex items-center gap-2 no-print">
      <button class="p-2 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg text-slate-400 transition-colors">
        <span class="material-icons">chevron_left</span>
      </button>
      <span class="text-xs font-bold text-slate-600 dark:text-slate-300 px-4">Page 1 of 6</span>
      <button class="p-2 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg text-slate-400 transition-colors">
        <span class="material-icons">chevron_right</span>
      </button>
    </div>
  </div>
</div>