<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UpdateLastActive
{
  /** Only write to DB at most once per minute per session to avoid hammering the DB. */
  private const THROTTLE_SECONDS = 60;

  public function handle(Request $request, Closure $next)
  {
    if (Auth::check()) {
      $user = Auth::user();
      $lastActive = $user->last_active_at;

      $shouldUpdate = !$lastActive
        || now()->diffInSeconds($lastActive) >= self::THROTTLE_SECONDS;

      if ($shouldUpdate) {
        /** @var \App\Models\User $user */
        $user->timestamps = false; // don't touch updated_at
        $user->last_active_at = now();
        $user->save();
        $user->timestamps = true;
      }
    }

    return $next($request);
  }
}
