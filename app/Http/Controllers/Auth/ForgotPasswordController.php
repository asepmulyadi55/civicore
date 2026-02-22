<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

class ForgotPasswordController extends Controller
{
    public function showLinkRequestForm()
    {
        return view('auth.forgot-password');
    }

    public function sendResetLinkEmail(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ], [
            'email.required' => 'Please enter your email address.',
            'email.email' => 'Please enter a valid email address.',
        ]);

        // Send the password reset link using Laravel's built-in broker
        $status = Password::sendResetLink(
            $request->only('email')
        );

        // Always return the same vague message regardless of whether the email exists.
        // This prevents email enumeration attacks (someone scanning which emails are registered).
        return back()->with('success', 'If that email address is registered, a reset link has been sent. Please check your inbox.');
    }
}
