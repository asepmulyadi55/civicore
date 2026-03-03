<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Resident;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
  /**
   * Show the registration form.
   */
  public function showRegistrationForm()
  {
    return view('register');
  }

  /**
   * Handle a registration request for the application.
   */
  public function register(Request $request)
  {
    $request->validate([
      'fullname' => ['required', 'string', 'max:255'],
      'username' => ['required', 'string', 'max:255', 'unique:users'],
      'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
      'password' => ['required', 'string', 'min:8', 'confirmed'],
      'password_confirmation' => ['required'],
    ], [
      'fullname.required' => 'Please enter your full name.',
      'username.required' => 'Please choose a username.',
      'username.unique' => 'This username is already taken. Please choose another.',
      'email.required' => 'Please enter your email address.',
      'email.email' => 'Please enter a valid email address.',
      'email.unique' => 'This email is already registered. Please login instead.',
      'password.required' => 'Please enter a password.',
      'password.min' => 'Password must be at least 8 characters.',
      'password.confirmed' => 'Passwords do not match. Please try again.',
      'password_confirmation.required' => 'Please confirm your password.',
    ]);

    // Email must exist in the residents table before registration is allowed.
    // This ensures every user account is linked to a resident record.
    if (!Resident::where('email', $request->email)->exists()) {
      return back()
        ->withErrors([
          'email' => 'Your email is not registered as a resident. Please contact your Block Coordinator or the administrator to have your email added to your resident profile first.',
        ])
        ->withInput();
    }

    User::create([
      'name' => $request->fullname,
      'username' => $request->username,
      'email' => $request->email,
      'password' => Hash::make($request->password),
      'is_active' => false, // Requires admin approval
    ]);

    return redirect('/')->with('success', 'Registration successful! Please wait for admin approval before logging in.');
  }
}
