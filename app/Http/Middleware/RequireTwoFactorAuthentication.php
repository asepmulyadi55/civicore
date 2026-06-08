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
            // If user doesn't have a 2FA secret, force them to set it up
            if (empty($user->two_factor_secret)) {
                // Prevent redirect loop if already on setup page or logging out
                if ($request->routeIs('settings.2fa', 'settings.2fa.enable', 'settings.2fa.confirm', 'logout')) {
                    return $next($request);
                }
                
                return redirect()->route('settings.2fa')
                    ->with('error', 'Mandatory Security: You must enable Two-Factor Authentication to access this application.');
            }
        }

        return $next($request);
    }
}
