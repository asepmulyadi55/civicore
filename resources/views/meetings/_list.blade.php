{{-- meetings/_list.blade.php --}}

@if($meetings->isEmpty())
  <div class="flex flex-col items-center justify-center py-20 text-center bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800">
    <span class="material-icons text-6xl text-slate-300 dark:text-slate-600 mb-4">event_note</span>
    <p class="text-slate-500 dark:text-slate-400 font-medium">
      {{ request()->hasAny(['q','month','year']) ? __('app.meeting_no_results') : __('app.meeting_empty') }}
    </p>
    @if(!request()->hasAny(['q','month','year']) && auth()->user()->can('meetings.create'))
      <button type="button" onclick="openModal('modal-add-meeting')"
        class="mt-4 inline-flex items-center gap-2 px-4 py-2 bg-primary text-white text-sm font-semibold rounded-lg hover:bg-primary/90 transition shadow-sm shadow-primary/20">
        <span class="material-icons text-[18px]">add</span>{{ __('app.meeting_add') }}
      </button>
    @endif
  </div>
@else

  {{-- Showing count --}}
  <p class="text-xs text-slate-400 dark:text-slate-500 -mt-2">
    {{ __('app.showing') }} {{ $meetings->firstItem() }}–{{ $meetings->lastItem() }} {{ __('app.of') }} {{ $meetings->total() }}
  </p>

  {{-- Card grid --}}
  <div class="space-y-3">
    @foreach($meetings as $meeting)
      @php $canManage = auth()->user()->can('meetings.edit'); @endphp
      <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm overflow-hidden">

        {{-- Card header row --}}
        <div class="flex items-start gap-4 p-5">

          {{-- Month calendar badge --}}
          <div class="flex-shrink-0 w-14 text-center">
            <div class="bg-primary/10 dark:bg-primary/20 rounded-t-lg py-1">
              <span class="text-[10px] font-bold uppercase tracking-widest text-primary dark:text-secondary">
                {{ $meeting->meeting_date->format('M') }}
              </span>
            </div>
            <div class="bg-primary dark:bg-secondary rounded-b-lg py-1.5">
              <span class="text-lg font-extrabold text-white dark:text-primary leading-none">
                {{ $meeting->meeting_date->format('d') }}
              </span>
            </div>
          </div>

          {{-- Info --}}
          <div class="flex-1 min-w-0">
            <h3 class="font-bold text-slate-900 dark:text-white text-base leading-snug">{{ $meeting->topic }}</h3>
            <div class="flex flex-wrap items-center gap-x-4 gap-y-1 mt-1.5 text-sm text-slate-500 dark:text-slate-400">
              <span class="inline-flex items-center gap-1">
                <span class="material-icons text-[15px]">schedule</span>
                {{ substr($meeting->meeting_time, 0, 5) }}
              </span>
              <span class="inline-flex items-center gap-1">
                <span class="material-icons text-[15px]">calendar_today</span>
                {{ $meeting->meeting_date->format('D, d M Y') }}
              </span>
              @if($meeting->location)
                <span class="inline-flex items-center gap-1">
                  <span class="material-icons text-[15px]">place</span>
                  {{ $meeting->location }}
                </span>
              @endif
            </div>
            {{-- Attendance badge --}}
            @if(($meeting->attendances_count ?? 0) > 0)
            <div class="mt-2">
              <span class="inline-flex items-center gap-1 text-xs px-2.5 py-1 rounded-full font-semibold
                {{ ($meeting->present_count ?? 0) > 0
                   ? 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400'
                   : 'bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400' }}">
                <span class="material-icons text-[13px]">group</span>
                {{ $meeting->present_count ?? 0 }} {{ __('app.meeting_present_label') }}
              </span>
            </div>
            @endif
          </div>

          {{-- Actions --}}
          <div class="flex items-center gap-1 flex-shrink-0 pt-0.5">
            {{-- Expand toggle --}}
            <button type="button"
              onclick="toggleMeetingDetail('detail-{{ $meeting->id }}')"
              class="p-2 rounded-lg text-slate-400 hover:text-primary dark:hover:text-secondary hover:bg-slate-100 dark:hover:bg-slate-800 transition"
              title="{{ __('app.meeting_view_details') }}">
              <span class="material-icons text-[18px]" id="chevron-{{ $meeting->id }}">expand_more</span>
            </button>

            @if($canManage)
              {{-- Attendance --}}
              <button type="button"
                onclick="openAttendanceModal({{ json_encode(['id' => $meeting->id, 'topic' => $meeting->topic]) }})"
                class="p-2 rounded-lg text-slate-400 hover:text-primary dark:hover:text-secondary hover:bg-slate-100 dark:hover:bg-slate-800 transition"
                title="{{ __('app.meeting_manage_attendance') }}">
                <span class="material-icons text-[18px]">how_to_reg</span>
              </button>

              {{-- Edit --}}
              <button type="button"
                onclick="openEditModal({{ json_encode([
                  'id'           => $meeting->id,
                  'topic'        => $meeting->topic,
                  'meeting_date' => $meeting->meeting_date->format('Y-m-d'),
                  'meeting_time' => substr($meeting->meeting_time, 0, 5),
                  'location'     => $meeting->location,
                  'notes'        => $meeting->notes,
                ]) }})"
                class="p-2 rounded-lg text-slate-400 hover:text-primary dark:hover:text-secondary hover:bg-slate-100 dark:hover:bg-slate-800 transition"
                title="{{ __('app.btn_edit') }}">
                <span class="material-icons text-[18px]">edit</span>
              </button>

              {{-- Delete --}}
              <button type="button"
                onclick="openDeleteModal('{{ $meeting->id }}', {{ json_encode($meeting->topic) }})"
                class="p-2 rounded-lg text-slate-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 transition"
                title="{{ __('app.btn_delete') }}">
                <span class="material-icons text-[18px]">delete</span>
              </button>
            @endif
          </div>
        </div>

        {{-- Expandable detail --}}
        <div id="detail-{{ $meeting->id }}" class="hidden border-t border-slate-100 dark:border-slate-800">
          <div class="p-5 space-y-4">

            @if($meeting->notes)
              <div>
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-2">
                  {{ __('app.meeting_notes_label') }}
                </p>
                <div class="text-sm text-slate-700 dark:text-slate-300 whitespace-pre-wrap leading-relaxed bg-slate-50 dark:bg-slate-800 rounded-lg p-3">{{ $meeting->notes }}</div>
              </div>
            @endif

            <div>
              <p class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-2">
                {{ __('app.meeting_attendance_label') }}
              </p>
              <div id="attendance-summary-{{ $meeting->id }}">
                <div class="attendance-summary-content text-sm text-slate-400 dark:text-slate-500 italic">
                  {{ __('app.meeting_attendance_expand_hint') }}
                </div>
              </div>
            </div>

          </div>
        </div>

      </div>
    @endforeach
  </div>

  {{-- Pagination --}}
  @if($meetings->hasPages())
    <div class="mt-4">{{ $meetings->links() }}</div>
  @endif

@endif

<script>
function toggleMeetingDetail(id) {
  const panel = document.getElementById(id);
  const meetingId = id.replace('detail-', '');
  const chevron = document.getElementById('chevron-' + meetingId);
  const isHidden = panel.classList.toggle('hidden');
  if (chevron) chevron.textContent = isHidden ? 'expand_more' : 'expand_less';

  if (!isHidden) {
    const container = document.querySelector('#attendance-summary-' + meetingId + ' .attendance-summary-content');
    if (container && container.dataset.loaded !== '1') {
      loadAttendanceSummary(meetingId, container);
    }
  }
}

async function loadAttendanceSummary(meetingId, container) {
  container.dataset.loaded = '1';
  try {
    const res = await fetch(`{{ url('/meetings') }}/${meetingId}/attendance-summary`, {
      headers: { 'X-Requested-With': 'XMLHttpRequest' }
    });
    if (!res.ok) throw new Error();
    container.innerHTML = await res.text();
  } catch {
    container.innerHTML = '<span class="text-slate-400">—</span>';
  }
}
</script>
