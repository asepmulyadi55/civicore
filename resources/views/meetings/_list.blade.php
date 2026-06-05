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

            {{-- Evidence images --}}
            <div>
              <div class="flex items-center justify-between mb-2">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">
                  {{ __('app.meeting_images_label') }}
                </p>
                 @if($canManage)
                  <label for="img-upload-{{ $meeting->id }}"
                    class="cursor-pointer inline-flex items-center gap-1.5 text-xs font-semibold px-3 py-1.5 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-lg border border-slate-200 dark:border-slate-700 transition-all">
                    <span class="material-icons text-sm">add_photo_alternate</span>
                    {{ __('app.meeting_upload_images') }}
                  </label>
                  <input type="file" id="img-upload-{{ $meeting->id }}"
                    multiple accept="image/jpeg,image/png,image/webp"
                    class="hidden"
                    onchange="uploadMeetingImages('{{ $meeting->id }}', this)">
                @endif
              </div>

              {{-- Image grid --}}
              <div id="img-grid-{{ $meeting->id }}" class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 gap-2">
                @forelse($meeting->images as $img)
                  <div id="img-item-{{ $img->id }}" class="relative group aspect-square rounded-lg overflow-hidden bg-slate-100 dark:bg-slate-800">
                    <img src="{{ $img->url() }}"
                      alt="{{ $img->original_name }}"
                      class="w-full h-full object-cover cursor-pointer hover:opacity-90 transition"
                      onclick="openLightbox('{{ $img->url() }}', '{{ e($img->original_name) }}')" />
                    @if($canManage)
                      <button type="button"
                        onclick="openDeleteImageModal('{{ $meeting->id }}', '{{ $img->id }}')"
                        class="absolute top-1.5 right-1.5 w-6 h-6 flex items-center justify-center bg-black/60 hover:bg-red-600 text-white rounded-lg opacity-0 group-hover:opacity-100 transition-all shadow">
                        <span class="material-icons" style="font-size:14px">close</span>
                      </button>
                    @endif
                  </div>
                @empty
                  <p id="img-empty-{{ $meeting->id }}" class="col-span-full text-xs text-slate-400 dark:text-slate-500 italic">
                    {{ __('app.meeting_no_images') }}
                  </p>
                @endforelse
              </div>

              {{-- Upload progress --}}
              <div id="img-progress-{{ $meeting->id }}" class="hidden mt-2 flex items-center gap-2 text-xs text-slate-400">
                <span class="material-icons animate-spin text-sm">refresh</span>
                {{ __('app.meeting_uploading') }}
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

{{-- Delete Image Confirm Modal --}}
<div id="modal-delete-image"
  class="fixed inset-0 z-[110] bg-slate-900/60 backdrop-blur-sm hidden items-center justify-center p-4"
  onclick="if(event.target===this) closeDeleteImageModal()">
  <div class="bg-white dark:bg-slate-900 w-full max-w-sm rounded-2xl shadow-2xl overflow-hidden">
    <div class="flex flex-col items-center pt-8 pb-5 px-6 text-center">
      <div class="w-14 h-14 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center mb-4">
        <span class="material-icons text-red-500 text-2xl">delete_forever</span>
      </div>
      <h2 class="text-lg font-bold text-slate-900 dark:text-white mb-2">{{ __('app.meeting_image_delete_title') }}</h2>
      <p class="text-sm text-slate-500 dark:text-slate-400 leading-relaxed">{{ __('app.meeting_image_delete_body') }}</p>
    </div>
    <div class="flex gap-3 px-6 pb-6">
      <button type="button" onclick="closeDeleteImageModal()"
        class="flex-1 px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 text-sm font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition-all">
        {{ __('app.btn_cancel') }}
      </button>
      <button type="button" id="delete-image-confirm-btn"
        class="flex-1 px-4 py-2.5 rounded-xl text-sm font-bold text-white bg-red-600 hover:bg-red-700 transition-all">
        {{ __('app.btn_yes_delete') }}
      </button>
    </div>
  </div>
</div>

{{-- Lightbox --}}
<div id="lightbox" class="fixed inset-0 z-[100] bg-black/80 backdrop-blur-sm hidden items-center justify-center p-4"
  onclick="if(event.target===this) closeLightbox()">
  <div class="relative max-w-3xl w-full">
    <button onclick="closeLightbox()" class="absolute -top-10 right-0 text-white/70 hover:text-white transition">
      <span class="material-icons text-2xl">close</span>
    </button>
    <img id="lightbox-img" src="" alt="" class="w-full max-h-[80vh] object-contain rounded-xl" />
    <p id="lightbox-caption" class="mt-2 text-center text-xs text-white/50"></p>
  </div>
</div>

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

// ── Image upload ────────────────────────────────────────────────────────────

/**
 * Compress a single File using canvas (JPEG, 85% quality, max 1920px).
 * Files already under 1 MB are returned as-is.
 * Mirrors the global compressFile() in app.blade.php but works with Promises
 * so it can be awaited for each file in a multi-file upload.
 */
function compressMeetingImage(file) {
  const SIZE_LIMIT = 1 * 1024 * 1024; // 1 MB
  const MAX_SIDE   = 1920;
  const QUALITY    = 0.85;

  return new Promise(resolve => {
    if (!file.type.startsWith('image/') || file.size <= SIZE_LIMIT) {
      resolve(file);
      return;
    }
    const reader = new FileReader();
    reader.onload = e => {
      const img = new Image();
      img.onload = () => {
        let w = img.width, h = img.height;
        // If already within bounds, skip canvas step
        if (w <= MAX_SIDE && h <= MAX_SIDE) { resolve(file); return; }
        const ratio  = Math.min(MAX_SIDE / w, MAX_SIDE / h);
        const canvas = document.createElement('canvas');
        canvas.width  = Math.round(w * ratio);
        canvas.height = Math.round(h * ratio);
        canvas.getContext('2d').drawImage(img, 0, 0, canvas.width, canvas.height);
        canvas.toBlob(blob => {
          if (!blob || blob.size >= file.size) { resolve(file); return; }
          resolve(new File([blob], file.name.replace(/\.[^.]+$/, '.jpg'), {
            type: 'image/jpeg', lastModified: Date.now()
          }));
        }, 'image/jpeg', QUALITY);
      };
      img.src = e.target.result;
    };
    reader.readAsDataURL(file);
  });
}

async function uploadMeetingImages(meetingId, input) {
  if (!input.files.length) return;

  const progress = document.getElementById('img-progress-' + meetingId);
  const grid     = document.getElementById('img-grid-' + meetingId);
  const empty    = document.getElementById('img-empty-' + meetingId);
  progress.classList.remove('hidden');

  // Compress each file (>1 MB) before building FormData
  const files = await Promise.all([...input.files].map(compressMeetingImage));

  const form = new FormData();
  files.forEach(f => form.append('images[]', f));
  form.append('_token', '{{ csrf_token() }}');


  try {
    const res  = await fetch(`{{ url('/meetings') }}/${meetingId}/images`, { method: 'POST', body: form });
    const data = await res.json();

    if (!res.ok) {
      alert(data.message || 'Upload gagal.');
      return;
    }

    // Remove empty placeholder if present
    if (empty) empty.remove();

    // Append new thumbnails
    data.images.forEach(img => {
      const div = document.createElement('div');
      div.id        = 'img-item-' + img.id;
      div.className = 'relative group aspect-square rounded-lg overflow-hidden bg-slate-100 dark:bg-slate-800';
      div.innerHTML = `
        <img src="${img.url}" alt="${img.original_name}"
          class="w-full h-full object-cover cursor-pointer hover:opacity-90 transition"
          onclick="openLightbox('${img.url}', '${img.original_name.replace(/'/g,"\\'")}')" />
        <button type="button"
          onclick="openDeleteImageModal('${meetingId}', '${img.id}')"
          class="absolute top-1.5 right-1.5 w-6 h-6 flex items-center justify-center bg-black/60 hover:bg-red-600 text-white rounded-lg opacity-0 group-hover:opacity-100 transition-all shadow">
          <span class="material-icons" style="font-size:14px">close</span>
        </button>
      `;
      grid.appendChild(div);
    });
  } catch (e) {
    alert('Upload gagal. Coba lagi.');
  } finally {
    progress.classList.add('hidden');
    input.value = '';
  }
}

async function deleteMeetingImage(meetingId, imageId) {
  if (!confirm('{{ __("app.meeting_image_delete_confirm") }}')) return;

  try {
    const res = await fetch(`{{ url('/meetings') }}/${meetingId}/images/${imageId}`, {
      method: 'DELETE',
      headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'X-Requested-With': 'XMLHttpRequest' }
    });
    if (res.ok) {
      document.getElementById('img-item-' + imageId)?.remove();
    }
  } catch (e) {}
}

// ── Delete image modal ──────────────────────────────────────────────────────
let _delImgMeetingId = null;
let _delImgId        = null;

function openDeleteImageModal(meetingId, imageId) {
  _delImgMeetingId = meetingId;
  _delImgId        = imageId;
  const m = document.getElementById('modal-delete-image');
  m.classList.remove('hidden');
  m.classList.add('flex');
}
function closeDeleteImageModal() {
  const m = document.getElementById('modal-delete-image');
  m.classList.add('hidden');
  m.classList.remove('flex');
  _delImgMeetingId = null;
  _delImgId        = null;
}
document.getElementById('delete-image-confirm-btn').addEventListener('click', async () => {
  if (!_delImgMeetingId || !_delImgId) return;
  const meetingId = _delImgMeetingId;
  const imageId   = _delImgId;
  closeDeleteImageModal();
  await deleteMeetingImage(meetingId, imageId);
});

async function deleteMeetingImage(meetingId, imageId) {
  try {
    const res = await fetch(`{{ url('/meetings') }}/${meetingId}/images/${imageId}`, {
      method: 'DELETE',
      headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'X-Requested-With': 'XMLHttpRequest' }
    });
    if (res.ok) document.getElementById('img-item-' + imageId)?.remove();
  } catch (e) {}
}

// ── Lightbox ────────────────────────────────────────────────────────────────
function openLightbox(src, caption) {
  document.getElementById('lightbox-img').src = src;
  document.getElementById('lightbox-caption').textContent = caption;
  const lb = document.getElementById('lightbox');
  lb.classList.remove('hidden');
  lb.classList.add('flex');
  document.body.classList.add('overflow-hidden');
}
function closeLightbox() {
  const lb = document.getElementById('lightbox');
  lb.classList.add('hidden');
  lb.classList.remove('flex');
  document.body.classList.remove('overflow-hidden');
}
document.addEventListener('keydown', e => {
  if (e.key === 'Escape') { closeLightbox(); closeDeleteImageModal(); }
});
</script>
