<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
  /**
   * Redirect to Google OAuth for LOGIN
   */
  public function redirectToGoogleLogin()
  {
    session(['google_oauth_intent' => 'login']);
    return Socialite::driver('google')->redirect();
  }

  /**
   * Redirect to Google OAuth for REGISTER
   */
  public function redirectToGoogleRegister()
  {
    session(['google_oauth_intent' => 'register']);
    return Socialite::driver('google')->redirect();
  }

  /**
   * Handle Google OAuth callback
   */
  public function handleGoogleCallback()
  {
    try {
      $googleUser = Socialite::driver('google')->stateless()->user();
      $intent = session('google_oauth_intent', 'login'); // Default to login
      session()->forget('google_oauth_intent'); // Clean up

      // Check if user exists by Google ID
      $user = User::where('google_id', $googleUser->id)->first();

      if ($user) {
        // Existing user with Google ID - check if active
        if (!$user->is_active) {
          return redirect()->route('login')->with('error', 'Your account is pending admin approval.');
        }

        Auth::login($user, true);
        request()->session()->regenerate();
        $user->session_token  = request()->session()->getId();
        $user->last_login_at  = now();
        $user->last_active_at = now();
        $user->save();
        return redirect($user->homeUrl())->with('success', 'Welcome back, ' . $user->name . '!');
      }

      // Check if user exists by email
      $user = User::where('email', $googleUser->email)->first();

      if ($user) {
        // User exists but no Google ID linked
        if ($intent === 'login') {
          // LOGIN flow: Don't auto-link, show clear instructions
          return redirect()->route('login')
            ->with('error', 'This email is already registered without Google. Please log in with your password, then link Google from your profile. Or use "Register with Google" to connect your account.');
        }

        // REGISTER flow: Link Google to existing account
        if (!$user->is_active) {
          return redirect()->route('login')->with('error', 'Your account is pending admin approval.');
        }

        $user->update(['google_id' => $googleUser->id]);
        Auth::login($user, true);
        request()->session()->regenerate();
        $user->session_token  = request()->session()->getId();
        $user->last_login_at  = now();
        $user->last_active_at = now();
        $user->save();
        return redirect($user->homeUrl())->with('success', 'Google account linked successfully!');
      }

      // No existing user
      if ($intent === 'login') {
        // LOGIN flow: Don't create account
        return redirect()->route('login')
          ->with('error', 'No account found. Please register first.');
      }

      // REGISTER flow: Create new user with default 'resident' role
      $residentRole = Role::where('name', 'resident')->first();

      try {
        $user = User::create([
          'name'              => $googleUser->name,
          'username'          => $this->generateUsername($googleUser->email),
          'email'             => $googleUser->email,
          'google_id'         => $googleUser->id,
          'password'          => Hash::make(Str::random(32)), // Secure random password — Google users sign in via OAuth
          'role_id'           => $residentRole?->id,   // Default role so views don't break
          'is_active'         => false,                // Require admin approval
          'email_verified_at' => now(),
        ]);
      } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
        // Race condition: two simultaneous registrations with the same email.
        // The second one loses — redirect as if registration succeeded so the
        // user is not confused, but they will need admin approval regardless.
        Log::warning('Google OAuth registration race condition', ['email' => $googleUser->email]);

        return redirect()->route('login')
          ->with('success', 'Account created! Please wait for admin approval before logging in.');
      }

      // Don't login yet — redirect to login page with message
      return redirect()->route('login')
        ->with('success', 'Account created! Please wait for admin approval before logging in.');

    } catch (\Exception $e) {
      Log::error('Google OAuth failed', [
        'error' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
      ]);
      return redirect()->route('login')->with('error', 'Failed to authenticate with Google. Please try again.');
    }
  }

  /**
   * Generate unique username from email.
   * Uses a retry loop with a max cap; the DB unique constraint is the true
   * source of truth — the loop is just a best-effort pre-check.
   */
  private function generateUsername(string $email): string
  {
    $base     = preg_replace('/[^a-zA-Z0-9_]/', '_', explode('@', $email)[0]);
    $username = $base;
    $counter  = 1;
    $maxTries = 50;

    while ($counter <= $maxTries && User::where('username', $username)->exists()) {
      $username = $base . '_' . $counter;
      $counter++;
    }

    // Final fallback: append random suffix to guarantee uniqueness
    if ($counter > $maxTries) {
      $username = $base . '_' . substr(bin2hex(random_bytes(4)), 0, 8);
    }

    return $username;
  }
}
