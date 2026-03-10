<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
  public function showLoginForm()
  {
    if (Auth::check()) {
      return redirect(Auth::user()->homeUrl());
    }
    return view('login');
  }

  public function login(Request $request)
  {
    $request->validate([
      'username' => ['required', 'string'],
      'password' => ['required', 'string'],
    ], [
      'username.required' => 'Please enter your username or email.',
      'password.required' => 'Please enter your password.',
    ]);

    $field = filter_var($request->username, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
    $user = User::where($field, $request->username)->first();

    if (!$user || !Hash::check($request->password, $user->password)) {
      return back()->with('error', 'Invalid username or password. Please try again.')
        ->withInput($request->only('username', 'remember'));
    }

    if (!$user->is_active) {
      // Distinguish pending (never logged in / never approved) from deactivated
      if ($user->last_login_at) {
        $msg = 'Your account has been deactivated. Please contact the administrator.';
      } else {
        $msg = 'Your account is pending admin approval.';
      }
      return back()->with('error', $msg)
        ->withInput($request->only('username', 'remember'));
    }

    // ── Single-session check ──────────────────────────────────────────────
    // If another active session exists for this user, redirect to conflict page
    // so they can choose to cancel or kick the other device.
    if ($user->session_token && $user->session_token !== $request->session()->getId()) {
      // Store user_id in session temporarily (guest session) so the conflict
      // page can offer the "Use this device" action.
      session(['conflict_user_id' => $user->id]);
      return redirect()->route('session.conflict');
    }

    // ── Successful login ──────────────────────────────────────────────────
    Auth::login($user, $request->boolean('remember'));
    $request->session()->regenerate();

    // Track session and activity timestamps
    $user->session_token = $request->session()->getId();
    $user->last_login_at = now();
    $user->last_active_at = now();
    $user->save();

    return redirect()->intended($user->homeUrl())
      ->with('success', 'Welcome back, ' . $user->name . '!');
  }

  public function logout(Request $request)
  {
    $user = Auth::user();
    if ($user) {
      /** @var \App\Models\User $user */
      $user->session_token = null;
      $user->last_active_at = null;   // mark as offline immediately
      $user->save();
    }

    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect('/');
  }
}
