<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\UploadMediaRequest;
use App\Http\Resources\API\MediaFileResource;
use App\Models\MediaFile;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediaController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        if (!$request->user()->can('media.view')) {
            return $this->forbidden();
        }

        $query = MediaFile::with('uploader:id,name');

        if ($search = $request->input('search')) {
            $query->where('original_name', 'like', "%{$search}%");
        }

        if ($mimeType = $request->input('mime_type')) {
            $query->where('mime_type', 'like', "%{$mimeType}%");
        }

        $query->orderByDesc('created_at');

        $paginator = $query->paginate($request->input('per_page', 20));

        return $this->paginated($paginator, MediaFileResource::collection($paginator), 'Media files fetched successfully');
    }

    public function upload(UploadMediaRequest $request): JsonResponse
    {
        $file        = $request->file('file');
        $disk        = $request->input('disk', 'public');
        $folder      = trim($request->input('folder', 'uploads'), '/');
        $extension   = $file->getClientOriginalExtension();
        $filename    = Str::uuid() . '.' . $extension;
        $storagePath = $folder . '/' . $filename;

        $file->storeAs($folder, $filename, $disk);

        $media = MediaFile::create([
            'disk'          => $disk,
            'path'          => $storagePath,
            'original_name' => $file->getClientOriginalName(),
            'mime_type'     => $file->getMimeType(),
            'size'          => $file->getSize(),
            'uploaded_by'   => $request->user()->id,
        ]);

        $media->load('uploader:id,name');

        return $this->created(new MediaFileResource($media), 'File uploaded successfully');
    }

    public function destroy(Request $request, MediaFile $media): JsonResponse
    {
        if (!$request->user()->can('media.delete')) {
            return $this->forbidden();
        }

        Storage::disk($media->disk)->delete($media->path);
        $media->delete();

        return $this->noContent('File deleted successfully');
    }

    /**
     * Serve a private media file (authenticated).
     */
    public function file(Request $request, MediaFile $media): mixed
    {
        if (!$request->user()->can('media.view')) {
            return $this->forbidden();
        }

        if ($media->disk !== 'local' || !Storage::disk('local')->exists($media->path)) {
            return $this->notFound('File not found');
        }

        return Storage::disk('local')->response($media->path, $media->original_name);
    }
}
