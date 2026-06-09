<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Support\Google2FA;

class TwoFactorChallengeController extends Controller
{
    public function show(Request $request)
    {
        // Only allow if there's a pending 2FA login
        if (!$request->session()->has('2fa:user:id')) {
            return redirect()->route('login');
        }

        return view('auth.two-factor-challenge');
    }

    public function verify(Request $request)
    {
        $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ]);

        $userId = $request->session()->get('2fa:user:id');
        $user = User::find($userId);

        if (!$user || !$user->two_factor_secret) {
            $request->session()->forget(['2fa:user:id', '2fa:remember']);
            return redirect()->route('login')->with('error', 'Authentication failed. Please login again.');
        }

        $valid = Google2FA::verifyKey($user->two_factor_secret, $request->code);

        if ($valid) {
            $remember = $request->session()->get('2fa:remember', false);
            
            // Log the user in
            Auth::login($user, $remember);
            $request->session()->regenerate();

            // Clear 2FA session data
            $request->session()->forget(['2fa:user:id', '2fa:remember']);

            // Update session tracking
            $user->session_token = $request->session()->getId();
            $user->last_login_at = now();
            $user->last_active_at = now();
            $user->save();

            return redirect()->intended($user->homeUrl())
                ->with('success', 'Welcome back, ' . $user->name . '!');
        }

        return back()->withErrors(['code' => 'The provided two-factor authentication code was invalid.']);
    }
}
