<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PrivateFileController extends Controller
{
  /**
   * Serve a private file (proof image or avatar).
   * Requires authentication — enforced by the route middleware.
   *
   * Only paths under proofs/ or avatars/ are permitted (whitelist
   * to prevent directory traversal into other private directories).
   */
  public function serve(string $path): Response
  {
    // Whitelist: only proofs/ and avatars/ are allowed
    if (!str_starts_with($path, 'proofs/') && !str_starts_with($path, 'avatars/')) {
      abort(403, 'Access denied.');
    }

    // Guard against path traversal tricks (e.g. ../../etc/passwd)
    if (str_contains($path, '..')) {
      abort(403, 'Access denied.');
    }

    $disk = Storage::disk('local');

    if (!$disk->exists($path)) {
      abort(404, 'File not found.');
    }

    // Resolve the real filesystem path and detect MIME type via PHP's finfo
    $realPath = storage_path('app/private/' . $path);
    $mimeType = mime_content_type($realPath) ?: 'application/octet-stream';
    $contents = $disk->get($path);

    return response($contents, 200)
      ->header('Content-Type', $mimeType)
      ->header('Cache-Control', 'private, no-store, no-cache');
  }
}
