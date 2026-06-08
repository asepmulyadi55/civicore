<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class RequireTwoFactorAuthentication
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if ($user) {
            // 1. If user doesn't have a 2FA secret, force them to set it up
            if (empty($user->two_factor_secret)) {
                // Prevent redirect loop if already on setup page
                if ($request->routeIs('2fa.setup', '2fa.activate')) {
                    return $next($request);
                }
                return redirect()->route('2fa.setup');
            }

            // 2. If user has secret but hasn't completed OTP challenge for this session
            if (!$request->session()->has('2fa_verified')) {
                // Prevent redirect loop
                if ($request->routeIs('2fa.challenge', '2fa.verify')) {
                    return $next($request);
                }
                return redirect()->route('2fa.challenge');
            }
        }

        return $next($request);
    }
}
