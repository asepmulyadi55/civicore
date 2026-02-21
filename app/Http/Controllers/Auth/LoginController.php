<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class LoginController extends Controller
{
  /**
   * Show the login form.
   */
  public function showLoginForm()
  {
    return view('login');
  }

  /**
   * Handle a login request to the application.
   */
  public function login(Request $request)
  {
    // Validate the request
    $validator = Validator::make($request->all(), [
      'username' => ['required', 'string'],
      'password' => ['required', 'string'],
    ], [
      'username.required' => 'Please enter your username or email.',
      'password.required' => 'Please enter your password.',
    ]);

    if ($validator->fails()) {
      return redirect()->back()
        ->with('error', $validator->errors()->first())
        ->withInput($request->only('username', 'remember'));
    }

    // Attempt to log the user in using either username or email
    $credentials = [
      'password' => $request->password,
    ];

    // Check if input is email or username
    if (filter_var($request->username, FILTER_VALIDATE_EMAIL)) {
      $credentials['email'] = $request->username;
    } else {
      $credentials['username'] = $request->username;
    }

    // Attempt authentication
    if (Auth::attempt($credentials, $request->filled('remember'))) {
      $request->session()->regenerate();

      // Check if user is active
      if (!Auth::user()->is_active) {
        Auth::logout();
        return redirect()->back()->with('error', 'Your account is pending admin approval.');
      }

      // Role-based redirect
      $user = Auth::user();
      $redirectTo = $user->isResident() ? '/my-overview' : '/dashboard';

      return redirect()->intended($redirectTo)->with('success', 'Welcome back, ' . $user->name . '!');
    }

    // Authentication failed
    return redirect()->back()->with('error', 'Invalid username or password. Please try again.');
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
