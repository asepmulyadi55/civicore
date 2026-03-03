<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
  /**
   * Show the login form.
   */
  public function showLoginForm()
  {
    if (Auth::check()) {
      return redirect(Auth::user()->homeUrl());
    }
    return view('login');
  }

  /**
   * Handle a login request to the application.
   */
  public function login(Request $request)
  {
    $request->validate([
      'username' => ['required', 'string'],
      'password' => ['required', 'string'],
    ], [
      'username.required' => 'Please enter your username or email.',
      'password.required' => 'Please enter your password.',
    ]);

    // Determine credential field (email or username)
    $field = filter_var($request->username, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
    $user = \App\Models\User::where($field, $request->username)->first();

    // Verify credentials BEFORE creating a session
    if (!$user || !Hash::check($request->password, $user->password)) {
      return back()->with('error', 'Invalid username or password. Please try again.')
        ->withInput($request->only('username', 'remember'));
    }

    // Check activation status BEFORE logging in
    if (!$user->is_active) {
      return back()->with('error', 'Your account is pending admin approval.')
        ->withInput($request->only('username', 'remember'));
    }

    Auth::login($user, $request->boolean('remember'));
    $request->session()->regenerate();

    return redirect()->intended($user->homeUrl())
      ->with('success', 'Welcome back, ' . $user->name . '!');
  }

  /**
   * Log the user out of the application.
   */
  public function logout(Request $request)
  {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect('/');
  }
}
