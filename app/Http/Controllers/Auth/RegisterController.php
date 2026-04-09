<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class RegisterController extends Controller
{
  /**
   * Show the registration form.
   */
  public function showRegistrationForm()
  {
    return view('auth.register');
  }

  /**
   * Handle a registration request.
   * Anyone can register — Admin will assign block/unit and approve from User Management.
   */
  public function register(Request $request)
  {
    $request->validate([
      'fullname' => ['required', 'string', 'max:255'],
      'username' => ['required', 'string', 'max:255', 'unique:users'],
      'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
      'password' => ['required', 'string', 'confirmed', Password::min(8)->mixedCase()->numbers()->symbols()],
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

    User::create([
      'name' => $request->fullname,
      'username' => $request->username,
      'email' => $request->email,
      'password' => Hash::make($request->password),
      'is_active' => false,
    ]);

    return redirect()->route('login')->with('success', 'Registration successful! Please wait for admin approval before logging in.');
  }
}
