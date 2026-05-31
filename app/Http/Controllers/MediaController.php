<?php

namespace App\Http\Controllers;

use App\Models\Householder;
use App\Models\MediaFile;
use App\Models\PaymentRecord;
use App\Models\Resident;
use App\Models\Setting;
use App\Models\User;
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
     * readonly=true        → hide bulk-select checkboxes for regular flow;
     *                        virtual folders have their own bulk route.
     */
    private const FOLDERS = [
        'users'        => ['label' => 'Users',        'icon' => 'person',           'prefixes' => ['avatars/'],      'source' => 'media_files', 'readonly' => false],
        'payments'     => ['label' => 'Payments',     'icon' => 'receipt_long',     'prefixes' => ['proofs/'],       'source' => 'media_files', 'readonly' => false],
        'homepage'     => ['label' => 'Homepage',     'icon' => 'home',             'prefixes' => ['homepage/'],     'source' => 'media_files', 'readonly' => false],
        'householders' => ['label' => 'Householders', 'icon' => 'people',           'prefixes' => ['householders/'], 'source' => 'householders','readonly' => true],
        'residents'    => ['label' => 'Residents',    'icon' => 'family_restroom',  'prefixes' => ['residents/'],    'source' => 'residents',   'readonly' => true],
    ];

    /** Display paginated list of media files, optionally filtered by virtual folder. */
    public function index(Request $request)
    {
        $folder          = $request->input('folder');
        $folderMeta      = (isset($folder, self::FOLDERS[$folder])) ? self::FOLDERS[$folder] : null;
        $readOnly        = $folderMeta['readonly'] ?? false;
        $isVirtualFolder = $folderMeta && in_array($folderMeta['source'], ['householders', 'residents']);

        // ── Virtual folders (residents / members) served from model queries ──
        if ($isVirtualFolder) {
            [$files, $folderCounts] = $this->virtualFolderData($request, $folder, $folderMeta);
            return view('media', compact('files', 'folder', 'folderCounts', 'readOnly', 'isVirtualFolder'));
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
        $isVirtualFolder = false;

        return view('media', compact('files', 'folder', 'folderCounts', 'readOnly', 'isVirtualFolder'));
    }

    /**
     * Build a paginator from Resident or FamilyMember photo_path records.
     * Returns [$paginator, $folderCounts].
     */
    private function virtualFolderData(Request $request, string $folder, array $folderMeta): array
    {
        $perPage = config('civicore.pagination.media', 24);
        $search  = $request->input('search');

        if ($folderMeta['source'] === 'householders') {
            $query = Householder::whereNotNull('photo_path');
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('fullname', 'like', "%{$search}%")
                      ->orWhere('photo_path', 'like', "%{$search}%");
                });
            }
            $rows = $query->orderByDesc('updated_at')->get();

            $items = $rows->map(fn($r) => new VirtualMediaFile([
                'id'            => 'householder:' . $r->id,
                'disk'          => 'local',
                'path'          => $r->photo_path,
                'original_name' => $r->fullname ?: basename($r->photo_path),
                'mime_type'     => 'image/jpeg',
                'size'          => Storage::disk('local')->exists($r->photo_path)
                                    ? Storage::disk('local')->size($r->photo_path) : 0,
                'created_at'    => $r->updated_at,
            ]));
        } else {
            // residents
            $query = Resident::whereNotNull('photo_path');
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('fullname', 'like', "%{$search}%")
                      ->orWhere('photo_path', 'like', "%{$search}%");
                });
            }
            $rows = $query->orderByDesc('updated_at')->get();

            $items = $rows->map(fn($m) => new VirtualMediaFile([
                'id'            => 'resident:' . $m->id,
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
            + Householder::whereNotNull('photo_path')->count()
            + Resident::whereNotNull('photo_path')->count()];

        foreach (self::FOLDERS as $key => $meta) {
            if ($meta['source'] === 'householders') {
                $counts[$key] = Householder::whereNotNull('photo_path')->count();
            } elseif ($meta['source'] === 'residents') {
                $counts[$key] = Resident::whereNotNull('photo_path')->count();
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

    // ── Virtual file deletions (residents / members) ──────────────────────────

    /**
     * Remove a resident's profile photo.
     * Blocked if the path is still referenced by a Resident record.
     * (Manage the photo from the Resident profile page to remove it properly.)
     */
    public function destroyHouseholderPhoto(Householder $householder)
    {
        if (!$householder->photo_path) {
            return redirect()
                ->route('media.index', ['folder' => 'householders'])
                ->with('error', 'This householder has no photo to delete.');
        }

        if (Householder::where('photo_path', $householder->photo_path)->exists()) {
            return redirect()
                ->route('media.index', ['folder' => 'householders'])
                ->with('error', __('app.flash_file_in_use', [
                    'reason' => "the householder profile of {$householder->fullname}. Remove it from their profile page instead.",
                ]));
        }

        Storage::disk('local')->delete($householder->photo_path);
        $householder->update(['photo_path' => null]);

        return redirect()
            ->route('media.index', ['folder' => 'householders'])
            ->with('success', __('app.flash_photo_removed', ['name' => $householder->fullname]));
    }

    public function destroyResidentPhoto(Resident $resident)
    {
        if (!$resident->photo_path) {
            return redirect()
                ->route('media.index', ['folder' => 'residents'])
                ->with('error', 'This resident has no photo to delete.');
        }

        if (Resident::where('photo_path', $resident->photo_path)->exists()) {
            return redirect()
                ->route('media.index', ['folder' => 'residents'])
                ->with('error', __('app.flash_file_in_use', [
                    'reason' => "the resident profile of {$resident->fullname}. Remove it from their profile page instead.",
                ]));
        }

        Storage::disk('local')->delete($resident->photo_path);
        $resident->update(['photo_path' => null]);

        return redirect()
            ->route('media.index', ['folder' => 'residents'])
            ->with('success', __('app.flash_photo_removed', ['name' => $resident->fullname]));
    }

    /**
     * Bulk delete virtual photos (residents and/or members).
     * Accepts IDs in "resident:uuid" or "member:uuid" format.
     * Files still referenced by a profile record are skipped.
     */
    public function virtualBulkDestroy(Request $request)
    {
        $request->validate([
            'ids'   => 'required|array|min:1',
            'ids.*' => ['string', 'regex:/^(householder|resident):[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i'],
        ]);

        $deleted        = 0;
        $skipped        = 0;
        $redirectFolder = 'householders';

        foreach ($request->ids as $compositeId) {
            [$type, $uuid] = explode(':', $compositeId, 2);

            if ($type === 'householder') {
                $redirectFolder = 'householders';
                $model = Householder::find($uuid);
                if ($model && $model->photo_path) {
                    if (Householder::where('photo_path', $model->photo_path)->exists()) {
                        $skipped++;
                        continue;
                    }
                    Storage::disk('local')->delete($model->photo_path);
                    $model->update(['photo_path' => null]);
                    $deleted++;
                }
            } elseif ($type === 'resident') {
                $redirectFolder = 'residents';
                $model = Resident::find($uuid);
                if ($model && $model->photo_path) {
                    if (Resident::where('photo_path', $model->photo_path)->exists()) {
                        $skipped++;
                        continue;
                    }
                    Storage::disk('local')->delete($model->photo_path);
                    $model->update(['photo_path' => null]);
                    $deleted++;
                }
            }
        }

        if ($skipped > 0 && $deleted > 0) {
            $message = __('app.flash_bulk_partial', ['deleted' => $deleted, 'skipped' => $skipped]);
            $type    = 'success';
        } elseif ($skipped > 0) {
            $message = __('app.flash_file_in_use', ['reason' => 'active householder/resident profiles. Remove photos from their profile pages instead.']);
            $type    = 'error';
        } else {
            $message = __('app.flash_files_deleted', ['count' => $deleted]);
            $type    = 'success';
        }

        return redirect()
            ->route('media.index', ['folder' => $redirectFolder])
            ->with($type, $message);
    }

    // ── Standard media_files deletions ────────────────────────────────────────

    /**
     * Delete a single MediaFile entry.
     * Blocked if the file is still actively referenced (user avatar, payment proof, homepage image).
     */
    public function destroy(MediaFile $mediaFile)
    {
        $inUse = $this->checkInUse($mediaFile->path);
        if ($inUse) {
            return redirect()->back()
                ->with('error', __('app.flash_file_in_use', ['reason' => $inUse]));
        }

        Storage::disk($mediaFile->disk)->delete($mediaFile->path);
        $mediaFile->delete();

        return redirect()->route('media.index')
            ->with('success', __('app.flash_file_deleted'));
    }

    /**
     * Bulk delete multiple media files.
     * In-use files are skipped; the flash message reports both deleted and skipped counts.
     */
    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'ids'   => 'required|array|min:1',
            'ids.*' => 'string|uuid|exists:media_files,id',
        ]);

        $files   = MediaFile::whereIn('id', $request->ids)->get();
        $deleted = 0;
        $skipped = 0;

        foreach ($files as $file) {
            if ($this->checkInUse($file->path)) {
                $skipped++;
                continue;
            }
            Storage::disk($file->disk)->delete($file->path);
            $file->delete();
            $deleted++;
        }

        if ($skipped > 0 && $deleted > 0) {
            $message = __('app.flash_bulk_partial', ['deleted' => $deleted, 'skipped' => $skipped]);
            $type    = 'success';
        } elseif ($skipped > 0) {
            $message = __('app.flash_bulk_all_skipped', ['skipped' => $skipped]);
            $type    = 'error';
        } else {
            $message = __('app.flash_files_deleted', ['count' => $deleted]);
            $type    = 'success';
        }

        return redirect()->route('media.index')->with($type, $message);
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    /**
     * Check whether a file path is still actively referenced by any record.
     * Returns a human-readable reason string if in use, or null if safe to delete.
     *
     * NOTE: PHP's json_encode() escapes forward slashes by default (/ → \/),
     * so we decode the JSON before searching rather than doing a raw string search.
     */
    private function checkInUse(string $path): ?string
    {
        // User avatar (direct column match)
        if (User::where('avatar', $path)->exists()) {
            return 'a user avatar';
        }

        // Payment proof (direct column match)
        if (PaymentRecord::where('proof_path', $path)->exists()) {
            return 'a payment record';
        }

        // Resident profile photo
        if (Resident::where('photo_path', $path)->exists()) {
            return 'a resident profile photo';
        }

        // Family member profile photo
        if (FamilyMember::where('photo_path', $path)->exists()) {
            return 'a family member profile photo';
        }

        // Homepage settings — decode JSON first to avoid json_encode slash-escaping issues
        $homepageKeys = [
            'homepage_hero',
            'homepage_about',
            'homepage_events',
            'homepage_memorable_moments',
        ];
        foreach ($homepageKeys as $key) {
            $json = Setting::get($key, '');
            if (!$json) continue;
            $data = json_decode($json, true);
            if (is_array($data) && $this->arrayContainsValue($data, $path)) {
                return 'the homepage';
            }
        }

        return null;
    }

    /**
     * Recursively walk a decoded JSON array looking for any string value
     * that contains the given $needle (the file path).
     */
    private function arrayContainsValue(array $data, string $needle): bool
    {
        foreach ($data as $value) {
            if (is_string($value) && str_contains($value, $needle)) {
                return true;
            }
            if (is_array($value) && $this->arrayContainsValue($value, $needle)) {
                return true;
            }
        }
        return false;
    }
}
