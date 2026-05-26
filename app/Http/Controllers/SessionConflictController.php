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
    $userId   = $request->input('user_id');
    $expected = session('conflict_user_id');

    // Validate that the submitted user_id matches what the server stored.
    // hash_equals prevents timing attacks on the comparison.
    if (!$expected || !hash_equals((string) $expected, (string) $userId)) {
      return redirect()->route('login')
        ->with('error', 'Session expired. Please log in again.');
    }

    $user = User::find($userId);

    if (!$user) {
      return redirect()->route('login')
        ->with('error', 'Account not found. Please log in again.');
    }

    // Consume the stored value — one-time use only.
    session()->forget('conflict_user_id');

    // Log in first — Auth::login() regenerates the session ID internally.
    // Capture the new session ID AFTER login to avoid an instant mismatch loop.
    Auth::login($user);

    $user->session_token  = $request->session()->getId();
    $user->last_login_at  = now();
    $user->last_active_at = now();
    $user->save();

    return redirect($user->homeUrl())
      ->with('success', 'You are now using this device. Other sessions have been logged out.');
  }
}
