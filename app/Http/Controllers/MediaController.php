<?php

namespace App\Http\Controllers;

use App\Models\MediaFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class MediaController extends Controller
{
    /**
     * The virtual folder definitions.
     * Each key is the URL `?folder=` value.
     * `prefixes` lists the path prefixes that belong to this folder.
     */
    private const FOLDERS = [
        'users'    => ['label' => 'Users',    'icon' => 'person',        'prefixes' => ['avatars/']],
        'payments' => ['label' => 'Payments', 'icon' => 'receipt_long',  'prefixes' => ['proofs/']],
        'homepage' => ['label' => 'Homepage', 'icon' => 'home',          'prefixes' => ['homepage/']],
    ];

    /** Display paginated list of all uploaded media files, optionally filtered by virtual folder. */
    public function index(Request $request)
    {
        $folder      = $request->input('folder'); // e.g. 'users', 'payments', 'homepage'
        $folderMeta  = isset($folder, self::FOLDERS[$folder]) ? self::FOLDERS[$folder] : null;

        $query = MediaFile::with('uploader')->latest();

        // ── Folder filter ────────────────────────────────────────────────────
        if ($folderMeta) {
            $query->where(function ($q) use ($folderMeta) {
                foreach ($folderMeta['prefixes'] as $i => $prefix) {
                    $method = $i === 0 ? 'where' : 'orWhere';
                    $q->{$method}('path', 'like', $prefix . '%');
                }
            });
        }

        // ── Search filter ────────────────────────────────────────────────────
        if ($search = $request->input('search')) {
            $query->where('original_name', 'like', '%' . $search . '%');
        }

        // ── Type filter ──────────────────────────────────────────────────────
        if ($type = $request->input('type')) {
            if ($type === 'image') {
                $query->where('mime_type', 'like', 'image/%');
            } elseif ($type === 'document') {
                $query->where('mime_type', 'not like', 'image/%');
            }
        }

        $files = $query->paginate(config('civicore.pagination.media', 24))->withQueryString();

        // ── Build per-folder file counts for the sidebar ─────────────────────
        $folderCounts = $this->buildFolderCounts();

        return view('media', compact('files', 'folder', 'folderCounts'));
    }

    /**
     * Count files per virtual folder using a single query.
     * Returns [ 'users' => 3, 'payments' => 12, 'homepage' => 7, '_all' => 22 ]
     */
    private function buildFolderCounts(): array
    {
        $counts = ['_all' => MediaFile::count()];

        foreach (self::FOLDERS as $key => $meta) {
            $q = MediaFile::query();
            $q->where(function ($inner) use ($meta) {
                foreach ($meta['prefixes'] as $i => $prefix) {
                    $method = $i === 0 ? 'where' : 'orWhere';
                    $inner->{$method}('path', 'like', $prefix . '%');
                }
            });
            $counts[$key] = $q->count();
        }

        return $counts;
    }

    /** Returns the FOLDERS definition for use in views. */
    public static function folders(): array
    {
        return self::FOLDERS;
    }

    /** Delete a single media file. */
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
