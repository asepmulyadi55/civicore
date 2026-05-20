<?php

namespace App\Http\Controllers;

use App\Models\FamilyMember;
use App\Models\MediaFile;
use App\Models\Resident;
use App\Support\VirtualMediaFile;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;

class MediaController extends Controller
{
    /**
     * Virtual folder definitions.
     *
     * source='media_files' → query the media_files table (existing behaviour)
     * source='residents'   → query Resident.photo_path from local disk
     * source='members'     → query FamilyMember.photo_path from local disk
     *
     * readonly=true        → hide delete/bulk-delete buttons in the grid
     */
    private const FOLDERS = [
        'users'     => ['label' => 'Users',     'icon' => 'person',           'prefixes' => ['avatars/'],    'source' => 'media_files', 'readonly' => false],
        'payments'  => ['label' => 'Payments',  'icon' => 'receipt_long',     'prefixes' => ['proofs/'],     'source' => 'media_files', 'readonly' => false],
        'homepage'  => ['label' => 'Homepage',  'icon' => 'home',             'prefixes' => ['homepage/'],   'source' => 'media_files', 'readonly' => false],
        'residents' => ['label' => 'Residents', 'icon' => 'people',           'prefixes' => ['residents/'],  'source' => 'residents',   'readonly' => true],
        'members'   => ['label' => 'Members',   'icon' => 'family_restroom',  'prefixes' => ['members/'],    'source' => 'members',     'readonly' => true],
    ];

    /** Display paginated list of media files, optionally filtered by virtual folder. */
    public function index(Request $request)
    {
        $folder     = $request->input('folder');
        $folderMeta = (isset($folder, self::FOLDERS[$folder])) ? self::FOLDERS[$folder] : null;
        $readOnly   = $folderMeta['readonly'] ?? false;

        // ── Virtual folders (residents / members) served from model queries ──
        if ($folderMeta && in_array($folderMeta['source'], ['residents', 'members'])) {
            [$files, $folderCounts] = $this->virtualFolderData($request, $folder, $folderMeta);
            return view('media', compact('files', 'folder', 'folderCounts', 'readOnly'));
        }

        // ── Standard media_files table query ─────────────────────────────────
        $query = MediaFile::with('uploader')->latest();

        if ($folderMeta) {
            $query->where(function ($q) use ($folderMeta) {
                foreach ($folderMeta['prefixes'] as $i => $prefix) {
                    $method = $i === 0 ? 'where' : 'orWhere';
                    $q->{$method}('path', 'like', $prefix . '%');
                }
            });
        }

        if ($search = $request->input('search')) {
            $query->where('original_name', 'like', '%' . $search . '%');
        }

        if ($type = $request->input('type')) {
            if ($type === 'image') {
                $query->where('mime_type', 'like', 'image/%');
            } elseif ($type === 'document') {
                $query->where('mime_type', 'not like', 'image/%');
            }
        }

        $files        = $query->paginate(config('civicore.pagination.media', 24))->withQueryString();
        $folderCounts = $this->buildFolderCounts();

        return view('media', compact('files', 'folder', 'folderCounts', 'readOnly'));
    }

    /**
     * Build a paginator from Resident or FamilyMember photo_path records.
     * Returns [$paginator, $folderCounts].
     */
    private function virtualFolderData(Request $request, string $folder, array $folderMeta): array
    {
        $perPage = config('civicore.pagination.media', 24);
        $search  = $request->input('search');

        if ($folderMeta['source'] === 'residents') {
            $query = Resident::whereNotNull('photo_path');
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('fullname', 'like', "%{$search}%")
                      ->orWhere('photo_path', 'like', "%{$search}%");
                });
            }
            $rows = $query->orderByDesc('updated_at')->get();

            $items = $rows->map(fn($r) => new VirtualMediaFile([
                'id'            => 'resident:' . $r->id,
                'disk'          => 'local',
                'path'          => $r->photo_path,
                'original_name' => $r->fullname ?: basename($r->photo_path),
                'mime_type'     => 'image/jpeg',
                'size'          => Storage::disk('local')->exists($r->photo_path)
                                    ? Storage::disk('local')->size($r->photo_path) : 0,
                'created_at'    => $r->updated_at,
            ]));
        } else {
            // members
            $query = FamilyMember::whereNotNull('photo_path');
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('fullname', 'like', "%{$search}%")
                      ->orWhere('photo_path', 'like', "%{$search}%");
                });
            }
            $rows = $query->orderByDesc('updated_at')->get();

            $items = $rows->map(fn($m) => new VirtualMediaFile([
                'id'            => 'member:' . $m->id,
                'disk'          => 'local',
                'path'          => $m->photo_path,
                'original_name' => $m->fullname ?: basename($m->photo_path),
                'mime_type'     => 'image/jpeg',
                'size'          => Storage::disk('local')->exists($m->photo_path)
                                    ? Storage::disk('local')->size($m->photo_path) : 0,
                'created_at'    => $m->updated_at,
            ]));
        }

        $page      = $request->input('page', 1);
        $paginator = new LengthAwarePaginator(
            $items->forPage($page, $perPage),
            $items->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return [$paginator, $this->buildFolderCounts()];
    }

    /**
     * Count files per virtual folder for the sidebar.
     * Returns [ 'users' => 3, 'payments' => 12, ..., '_all' => 22 ]
     */
    private function buildFolderCounts(): array
    {
        $counts = ['_all' => MediaFile::count()
            + Resident::whereNotNull('photo_path')->count()
            + FamilyMember::whereNotNull('photo_path')->count()];

        foreach (self::FOLDERS as $key => $meta) {
            if ($meta['source'] === 'residents') {
                $counts[$key] = Resident::whereNotNull('photo_path')->count();
            } elseif ($meta['source'] === 'members') {
                $counts[$key] = FamilyMember::whereNotNull('photo_path')->count();
            } else {
                $q = MediaFile::query()->where(function ($inner) use ($meta) {
                    foreach ($meta['prefixes'] as $i => $prefix) {
                        $method = $i === 0 ? 'where' : 'orWhere';
                        $inner->{$method}('path', 'like', $prefix . '%');
                    }
                });
                $counts[$key] = $q->count();
            }
        }

        return $counts;
    }

    /** Returns the FOLDERS definition for use in views. */
    public static function folders(): array
    {
        return self::FOLDERS;
    }

    /** Delete a single media file (only for media_files-backed entries). */
    public function destroy(MediaFile $mediaFile)
    {
        Storage::disk($mediaFile->disk)->delete($mediaFile->path);
        $mediaFile->delete();

        return redirect()->route('media.index')
            ->with('success', __('app.flash_file_deleted'));
    }

    /** Bulk delete multiple media files. */
    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'ids'   => 'required|array|min:1',
            'ids.*' => 'string|uuid|exists:media_files,id',
        ]);

        $files = MediaFile::whereIn('id', $request->ids)->get();
        $count = $files->count();

        foreach ($files as $file) {
            Storage::disk($file->disk)->delete($file->path);
        }

        MediaFile::whereIn('id', $request->ids)->delete();

        return redirect()->route('media.index')
            ->with('success', __('app.flash_files_deleted', ['count' => $count]));
    }
}
