<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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
                    return redirect('/')->with('error', 'Your account is pending admin approval.');
                }

                Auth::login($user, true);
                return redirect('/dashboard')->with('success', 'Welcome back!');
            }

            // Check if user exists by email
            $user = User::where('email', $googleUser->email)->first();

            if ($user) {
                // User exists but no Google ID linked
                if ($intent === 'login') {
                    // LOGIN flow: Don't auto-link, ask them to register
                    return redirect('/')
                        ->with('error', 'Account exists. Please use the regular login or register with Google first.');
                }

                // REGISTER flow: Link Google to existing account
                if (!$user->is_active) {
                    return redirect('/')->with('error', 'Your account is pending admin approval.');
                }

                $user->update(['google_id' => $googleUser->id]);
                Auth::login($user, true);
                return redirect('/dashboard')->with('success', 'Google account linked successfully!');
            }

            // No existing user
            if ($intent === 'login') {
                // LOGIN flow: Don't create account
                return redirect('/')
                    ->with('error', 'No account found. Please register first.');
            }

            // REGISTER flow: Create new user
            $user = User::create([
                'name' => $googleUser->name,
                'username' => $this->generateUsername($googleUser->email),
                'email' => $googleUser->email,
                'google_id' => $googleUser->id,
                'password' => Hash::make(uniqid()), // Random password
                'is_active' => false, // Require admin approval
                'email_verified_at' => now(),
            ]);

            // Don't login yet - redirect to login with message
            return redirect('/')
                ->with('success', 'Account created successfully! Please wait for admin approval before logging in.');

        } catch (\Exception $e) {
            return redirect('/')->with('error', 'Failed to authenticate with Google. Please try again.');
        }
    }

    /**
     * Generate unique username from email
     */
    private function generateUsername($email)
    {
        $username = explode('@', $email)[0];
        $username = preg_replace('/[^a-zA-Z0-9_]/', '_', $username);

        // Check if username exists
        $originalUsername = $username;
        $counter = 1;

        while (User::where('username', $username)->exists()) {
            $username = $originalUsername . '_' . $counter;
            $counter++;
        }

        return $username;
    }
}
