<?php

namespace App\Http\Controllers;

use App\Models\MediaFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MediaController extends Controller
{
    /** Display paginated list of all uploaded media files. */
    public function index(Request $request)
    {
        $query = MediaFile::with('uploader')->latest();

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

        $files = $query->paginate(24)->withQueryString();

        return view('media', compact('files'));
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
