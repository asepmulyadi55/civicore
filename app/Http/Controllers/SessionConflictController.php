<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SessionConflictController extends Controller
{
  /**
   * Show the conflict page when a session mismatch is detected.
   * The user_id is passed via GET query string (set by CheckSingleSession).
   */
  public function show(Request $request)
  {
    return view('session-conflict');
  }

  /**
   * "Use this device" — log the user in on this browser and kick the other session.
   * IMPORTANT: We must save the session_token AFTER Auth::login() because login()
   * regenerates the session ID internally. Saving before would store the old
   * guest session ID, which instantly triggers a mismatch → redirect loop.
   */
  public function useThisDevice(Request $request)
  {
    $userId = $request->input('user_id');
    $user = User::find($userId);

    if ($user) {
      // Log in first — this regenerates the session ID internally
      Auth::login($user);

      // NOW capture the new session ID (post-login) and store it
      $user->session_token = $request->session()->getId();
      $user->last_login_at = now();
      $user->last_active_at = now();
      $user->save();

      return redirect($user->homeUrl())
        ->with('success', 'You are now using this device. Other sessions have been logged out.');
    }

    return redirect('/')->with('error', 'Session expired. Please log in again.');
  }
}
