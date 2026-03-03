<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckSingleSession
{
  public function handle(Request $request, Closure $next)
  {
    if (!Auth::check()) {
      return $next($request);
    }

    $user = Auth::user();

    // Skip check for the conflict-resolution routes themselves to avoid redirect loops
    if ($request->routeIs('session.conflict', 'session.use-this')) {
      return $next($request);
    }

    // If user has a stored session token that does NOT match the current session,
    // another device/browser has claimed the session — redirect to conflict page.
    if ($user->session_token && $user->session_token !== $request->session()->getId()) {
      Auth::logout();
      $request->session()->invalidate();

      return redirect()->route('session.conflict')
        ->with('conflict_user_id', $user->id);
    }

    return $next($request);
  }
}
