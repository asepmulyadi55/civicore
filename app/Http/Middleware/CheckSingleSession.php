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
    // EXCEPTION: if the stored session has been inactive for ≥ 8 hours we treat
    // it as expired and silently clear it (avoids conflict popup after overnight).
    $sessionTimeoutHours = (int) config('session.timeout_hours', 8);
    $oldSessionExpired = !$user->last_active_at
      || $user->last_active_at->diffInHours(now()) >= $sessionTimeoutHours;

    if ($user->session_token && $user->session_token !== $request->session()->getId()) {
      if ($oldSessionExpired) {
        // Silently take over — old session has died
        $user->session_token = $request->session()->getId();
        $user->last_active_at = \Illuminate\Support\Carbon::now();
        $user->timestamps = false;
        $user->save();
        $user->timestamps = true;
      } else {
        Auth::logout();
        $request->session()->invalidate();
        return redirect()->route('session.conflict')
          ->with('conflict_user_id', $user->id);
      }
    }

    return $next($request);
  }
}
