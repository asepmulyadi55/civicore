<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class PrivateFileController extends Controller
{
  /**
   * Serve a private file (proof image or avatar).
   * Requires authentication — enforced by the route middleware.
   *
   * Only paths under proofs/ or avatars/ are permitted (whitelist
   * to prevent directory traversal into other private directories).
   * Homepage files are stored on the PUBLIC disk and must never be
   * routed through here.
   */
  public function serve(string $path): Response
  {
    // Whitelist: only private module directories are allowed
    $allowed = ['proofs/', 'avatars/', 'members/', 'residents/'];
    $allowed_match = false;
    foreach ($allowed as $prefix) {
      if (str_starts_with($path, $prefix)) {
        $allowed_match = true;
        break;
      }
    }

    if (!$allowed_match) {
      abort(403, 'Access denied.');
    }

    // Guard against path traversal (e.g. ../../, %2e%2e, null bytes)
    if (str_contains($path, '..') || str_contains($path, "\0")) {
      abort(403, 'Access denied.');
    }

    $disk = Storage::disk('local');

    if (!$disk->exists($path)) {
      abort(404, 'File not found.');
    }

    // Resolve the real filesystem path
    $storagePath = storage_path('app/private/' . $path);
    $realPath    = realpath($storagePath);
    $storageBase = realpath(storage_path('app/private'));

    // Double-check the resolved path is still within the private storage root
    if ($realPath === false || !str_starts_with($realPath, $storageBase . DIRECTORY_SEPARATOR)) {
      abort(403, 'Access denied.');
    }

    $mimeType = mime_content_type($realPath) ?: 'application/octet-stream';

    // Whitelist of allowed MIME types for private files
    $allowedMimes = [
      'image/jpeg', 'image/png', 'image/webp', 'image/gif',
      'application/pdf',
    ];
    if (!in_array($mimeType, $allowedMimes, true)) {
      abort(403, 'File type not allowed.');
    }

    $contents = $disk->get($path);

    return response($contents, 200)
      ->header('Content-Type', $mimeType)
      ->header('Content-Disposition', 'inline; filename="' . basename($path) . '"')
      ->header('Cache-Control', 'private, no-store, no-cache, must-revalidate')
      ->header('X-Content-Type-Options', 'nosniff')
      ->header('X-Frame-Options', 'DENY');
  }
}
