<?php

namespace App\Http\Controllers;

use App\Models\Meeting;
use App\Models\MeetingAttendance;
use App\Models\MeetingImage;
use App\Models\Resident;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class MeetingController extends Controller
{
    // ── Listing ───────────────────────────────────────────────────────────────

    /**
     * GET /meetings
     * Visible to any role with meetings.view (including resident roles).
     */
    public function index(Request $request): View
    {
        $query = Meeting::withCount([
            'attendances',
            'attendances as present_count' => fn ($q) => $q->where('present', true),
            'images',
        ])->with('images')->orderByDesc('meeting_date')->orderByDesc('meeting_time');

        // Filter: topic search
        if ($search = trim($request->get('q', ''))) {
            $query->where('topic', 'like', "%{$search}%");
        }

        // Filter: month (1–12)
        if ($month = $request->get('month')) {
            $query->whereMonth('meeting_date', $month);
        }

        // Filter: year
        if ($year = $request->get('year')) {
            $query->whereYear('meeting_date', $year);
        }

        $meetings = $query->paginate(15)->withQueryString();

        // Distinct years for the year dropdown
        $availableYears = Meeting::selectRaw('YEAR(meeting_date) as yr')
            ->distinct()
            ->orderByDesc('yr')
            ->pluck('yr')
            ->filter()
            ->values();

        return view('meetings', compact('meetings', 'availableYears'));
    }

    // ── CRUD ──────────────────────────────────────────────────────────────────

    /**
     * POST /meetings
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorizeAction('meetings.create');

        $data = $request->validate([
            'topic'        => 'required|string|max:200',
            'meeting_date' => 'required|date',
            'meeting_time' => 'required|date_format:H:i',
            'location'     => 'nullable|string|max:150',
            'notes'        => 'nullable|string',
        ]);

        Meeting::create(array_merge($data, ['created_by' => auth()->id()]));

        return back()->with('success', __('app.meeting_created'));
    }

    /**
     * PUT /meetings/{meeting}
     */
    public function update(Request $request, Meeting $meeting): RedirectResponse
    {
        $this->authorizeAction('meetings.edit');

        $data = $request->validate([
            'topic'        => 'required|string|max:200',
            'meeting_date' => 'required|date',
            'meeting_time' => 'required|date_format:H:i',
            'location'     => 'nullable|string|max:150',
            'notes'        => 'nullable|string',
        ]);

        $meeting->update($data);

        return back()->with('success', __('app.meeting_updated'));
    }

    /**
     * DELETE /meetings/{meeting}
     */
    public function destroy(Meeting $meeting): RedirectResponse
    {
        $this->authorizeAction('meetings.delete');

        $meeting->delete(); // cascades to meeting_attendances

        return back()->with('success', __('app.meeting_deleted'));
    }

    // ── Attendance ────────────────────────────────────────────────────────────

    /**
     * POST /meetings/{meeting}/attendance
     * Bulk-saves attendance for a meeting. Replaces all existing records for this meeting.
     * Expects: attendees[resident_id] = ['present' => 0|1, 'remarks' => '...']
     */
    public function storeAttendance(Request $request, Meeting $meeting): RedirectResponse
    {
        $this->authorizeAction('meetings.edit');

        $request->validate([
            'attendees'                    => 'nullable|array',
            'attendees.*.present'          => 'nullable|boolean',
            'attendees.*.remarks'          => 'nullable|string|max:255',
        ]);

        $attendees = $request->input('attendees', []);

        DB::transaction(function () use ($meeting, $attendees) {
            // Delete all existing attendance records for this meeting
            $meeting->attendances()->delete();

            $rows = [];
            foreach ($attendees as $residentId => $data) {
                // Validate that the resident actually exists
                if (!Resident::where('id', $residentId)->exists()) {
                    continue;
                }
                $rows[] = [
                    'id'          => \Illuminate\Support\Str::uuid()->toString(),
                    'meeting_id'  => $meeting->id,
                    'resident_id' => $residentId,
                    'present'     => (bool) ($data['present'] ?? true),
                    'remarks'     => $data['remarks'] ?? null,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ];
            }

            if (!empty($rows)) {
                MeetingAttendance::insert($rows);
            }
        });

        return back()->with('success', __('app.meeting_attendance_saved'));
    }

    // ── AJAX ──────────────────────────────────────────────────────────────────

    /**
     * GET /meetings/search-residents?q=
     * If ?_all=1 is passed, returns all residents (used by attendance modal).
     * Otherwise performs a name search typeahead (min 2 chars).
     */
    public function searchResidents(Request $request): JsonResponse
    {
        $all = $request->boolean('_all');
        $q   = trim($request->get('q', ''));

        $query = Resident::with(['householder.block', 'householder.unit'])
            ->orderBy('fullname');

        if (!$all) {
            if (strlen($q) < 2) {
                return response()->json([]);
            }
            $query->where('fullname', 'like', "%{$q}%")->limit(20);
        }

        $results = $query->get()->map(fn ($r) => [
            'id'       => $r->id,
            'name'     => $r->fullname,
            'location' => trim(
                ($r->householder?->block?->name ?? '') . ' ' .
                ($r->householder?->unit_number ?? '')
            ),
            'photo'    => $r->photoUrl(),
        ]);

        return response()->json($results);
    }

    /**
     * GET /meetings/{meeting}/attendance-data
     * Returns existing attendance records as a map: resident_id => {present, remarks}
     */
    public function attendanceData(Meeting $meeting): JsonResponse
    {
        $map = $meeting->attendances()
            ->get()
            ->keyBy('resident_id')
            ->map(fn ($a) => ['present' => (bool) $a->present, 'remarks' => $a->remarks]);

        return response()->json($map);
    }

    /**
     * GET /meetings/{meeting}/attendance-summary
     * Returns a small HTML snippet with the list of attendees for the expand panel.
     */
    public function attendanceSummary(Meeting $meeting): string
    {
        $attendance = $meeting->attendances()
            ->with('resident')
            ->orderBy('present', 'desc')
            ->get();

        if ($attendance->isEmpty()) {
            return '<span class="text-slate-400 dark:text-slate-500 italic text-sm">'
                . e(__('app.meeting_no_attendance_yet'))
                . '</span>';
        }

        $html = '<div class="flex flex-wrap gap-2">';
        foreach ($attendance as $att) {
            $name = e($att->resident?->fullname ?? '—');
            $class = $att->present
                ? 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400'
                : 'bg-red-100 dark:bg-red-900/30 text-red-500 dark:text-red-400 line-through opacity-70';
            $html .= "<span class=\"text-xs px-2 py-0.5 rounded-full font-medium {$class}\">{$name}</span>";
        }
        $html .= '</div>';

        return $html;
    }


    // ── Helpers ───────────────────────────────────────────────────────────────

    /**
     * Abort 403 if the authenticated user lacks the given permission.
     */
    private function authorizeAction(string $permission): void
    {
        if (!auth()->user()->can($permission)) {
            abort(403, 'Unauthorized.');
        }
    }

    // ── Meeting Images ────────────────────────────────────────────────────────

    /**
     * POST /meetings/{meeting}/images
     * Upload one or more evidence images for a meeting.
     */
    public function storeImage(Request $request, Meeting $meeting): JsonResponse
    {
        $request->validate([
            'images'   => ['required', 'array', 'max:10'],
            'images.*' => ['required', 'file', 'image', 'mimes:jpeg,jpg,png,webp', 'max:5120'], // 5 MB each
        ]);

        $stored = [];

        foreach ($request->file('images') as $file) {
            $ext      = $file->getClientOriginalExtension();
            $filename = (string) \Illuminate\Support\Str::uuid() . '.' . strtolower($ext);
            $path     = 'meetings/' . $meeting->id . '/' . $filename;

            Storage::disk('local')->put($path, file_get_contents($file->getRealPath()));

            $image = MeetingImage::create([
                'meeting_id'    => $meeting->id,
                'path'          => $path,
                'original_name' => $file->getClientOriginalName(),
                'size'          => $file->getSize(),
                'mime_type'     => $file->getMimeType() ?? 'image/jpeg',
            ]);

            $stored[] = [
                'id'  => $image->id,
                'url' => $image->url(),
                'original_name' => $image->original_name,
            ];
        }

        return response()->json(['images' => $stored]);
    }

    /**
     * DELETE /meetings/{meeting}/images/{image}
     * Delete a single evidence image.
     */
    public function deleteImage(Meeting $meeting, MeetingImage $image): JsonResponse
    {
        if ($image->meeting_id !== $meeting->id) {
            abort(403, 'Image does not belong to this meeting.');
        }

        $image->deleteFile();
        $image->delete();

        return response()->json(['success' => true]);
    }
}
