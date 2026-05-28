<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Ensures only users with a specific role name can access the route.
 * Usage: ->middleware('api.role:admin') or ->middleware('api.role:admin,treasurer')
 */
class CheckApiRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated.'], 401);
        }

        $userRole = $user->role?->name;

        if (!in_array($userRole, $roles)) {
            return response()->json(['success' => false, 'message' => 'Insufficient role privileges.'], 403);
        }

        return $next($request);
    }
}
