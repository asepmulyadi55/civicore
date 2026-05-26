<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Validates that internal API endpoints are only called from the server's
 * own SPA. The SPA receives the key via a Blade-injected meta tag on the
 * same origin, so it is never exposed directly to the public.
 *
 * Usage in routes: ->middleware('api.key')
 */
class VerifyApiKey
{
    public function handle(Request $request, Closure $next): Response
    {
        $expected = config('civicore.api_key');

        // If no key is configured (e.g. empty .env), block all access.
        if (empty($expected)) {
            abort(403, 'API key not configured.');
        }

        $provided = $request->header('X-Api-Key');

        // Constant-time comparison to prevent timing attacks
        if (!hash_equals($expected, (string) $provided)) {
            return response()->json(['error' => 'Unauthorized.'], 401);
        }

        return $next($request);
    }
}
