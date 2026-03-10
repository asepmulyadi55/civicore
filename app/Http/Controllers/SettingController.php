<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password as PasswordRule;

class SettingController extends Controller
{
  public function index()
  {
    return view('settings', ['user' => Auth::user()]);
  }

  /** Update name, language and optionally avatar. */
  public function updateProfile(Request $request)
  {
    /** @var \App\Models\User $user */
    $user = Auth::user();

    $data = $request->validate([
      'name' => ['required', 'string', 'max:100'],
      'language' => ['required', 'in:en,id'],
      'avatar' => ['nullable', 'image', 'max:2048'], // max 2 MB
    ]);

    if ($request->hasFile('avatar')) {
      // Delete old avatar if exists
      if ($user->avatar) {
        Storage::disk('local')->delete($user->avatar);
      }
      $data['avatar'] = $request->file('avatar')->store('avatars', 'local');
    } else {
      unset($data['avatar']);
    }

    $user->update($data);

    // Apply locale immediately for the current session
    session(['app_locale' => $data['language']]);

    return redirect()->route('settings.index')
      ->with('success', __('app.flash_profile_updated'));
  }

  /** Change password — requires current password verification. */
  public function updatePassword(Request $request)
  {
    /** @var \App\Models\User $user */
    $user = Auth::user();

    $request->validate([
      'current_password' => ['required', 'string'],
      'password' => ['required', 'confirmed', PasswordRule::min(8)->letters()->numbers()],
    ]);

    if (!Hash::check($request->current_password, $user->password)) {
      return back()
        ->withErrors(['current_password' => 'The current password is incorrect.'])
        ->withInput();
    }

    $user->update(['password' => Hash::make($request->password)]);

    return redirect()->route('settings.index')
      ->with('success', __('app.flash_password_changed'));
  }

  /** Send a password reset link to the authenticated user's email. */
  public function sendResetLink(Request $request)
  {
    /** @var \App\Models\User $user */
    $user = Auth::user();

    Password::sendResetLink(['email' => $user->email]);

    return redirect()->route('settings.index')
      ->with('success', __('app.flash_reset_sent', ['email' => $user->email]));
  }

  /** Save session timeout — admin only (permission:settings.view). */
  public function updateSecurity(Request $request)
  {
    $request->validate([
      'session_timeout_minutes' => ['required', 'integer', 'min:5', 'max:120'],
    ]);

    Setting::set('session_timeout_minutes', (string) $request->session_timeout_minutes);
    Cache::flush();

    return redirect()->route('settings.index')
      ->with('success', __('app.flash_security_saved'));
  }
}
