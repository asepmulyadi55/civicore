<?php

namespace App\Http\Controllers;

use App\Models\MediaFile;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password as PasswordRule;
use App\Support\Google2FA;

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
        MediaFile::where('path', $user->avatar)->delete();
      }
      $file = $request->file('avatar');
      $data['avatar'] = $file->store('avatars', 'local');
      MediaFile::create([
        'disk'          => 'local',
        'path'          => $data['avatar'],
        'original_name' => $file->getClientOriginalName(),
        'mime_type'     => $file->getMimeType(),
        'size'          => $file->getSize(),
        'uploaded_by'   => $user->id,
      ]);
    } else {
      unset($data['avatar']);
    }

    $user->update($data);

    // Apply locale immediately for the current session
    session(['app_locale' => $data['language']]);

    return redirect()->route('settings.index')
      ->with('success', __('app.flash_profile_updated'));
  }

  /** Change password — requires current password only if one is already set. */
  public function updatePassword(Request $request)
  {
    /** @var \App\Models\User $user */
    $user = Auth::user();
    // Google OAuth users always have a random password they don't know.
    // The correct signal is google_id: if set, skip the current-password requirement.
    $hasPassword = is_null($user->google_id);

    // Validation rules differ depending on whether the user already has a password
    $rules = [
      'password' => ['required', 'confirmed', PasswordRule::min(8)->mixedCase()->numbers()->symbols()],
    ];
    if ($hasPassword) {
      $rules['current_password'] = ['required', 'string'];
    }

    $request->validate($rules);

    // For users with an existing password, verify it before allowing the change
    if ($hasPassword && !Hash::check($request->current_password, $user->password)) {
      return back()
        ->withErrors(['current_password' => 'The current password is incorrect.'])
        ->withInput();
    }

    $user->update(['password' => $request->password]); // Cast 'hashed' applies bcrypt automatically

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

  /** Save session timeout and GA ID — admin only (permission:settings.edit). */
  public function updateSecurity(Request $request)
  {
    $request->validate([
      'session_timeout_minutes' => ['required', 'integer', 'min:5', 'max:120'],
      'ga_measurement_id'       => ['nullable', 'string', 'max:20', 'regex:/^G-[A-Z0-9]+$/i'],
    ]);

    Setting::set('session_timeout_minutes', (string) $request->session_timeout_minutes);
    Setting::set('ga_measurement_id', trim((string) $request->input('ga_measurement_id', '')));
    Cache::flush();

    return redirect()->route('settings.index')
      ->with('success', __('app.flash_security_saved'));
  }

  /** Save admin memo — admin only. */
  public function updateMemo(Request $request)
  {
    $request->validate([
      'admin_memo' => ['nullable', 'string', 'max:1000'],
    ]);

    Setting::set('admin_memo', $request->input('admin_memo', ''));

    return redirect()->route('settings.index')
      ->with('success', __('app.flash_memo_updated'));
  }

  /** Save Posyandu age-category range limits — admin only. */
  public function updatePosyandu(Request $request)
  {
    $request->validate([
      'posyandu_baby_max_months'    => ['required', 'integer', 'min:1', 'max:24'],
      'posyandu_toddler_max_months' => ['required', 'integer', 'min:2', 'max:60'],
      'posyandu_child_max_months'   => ['required', 'integer', 'min:12', 'max:144'],
      'posyandu_teen_max_months'    => ['required', 'integer', 'min:48', 'max:216'],
      'posyandu_adult_max_months'   => ['required', 'integer', 'min:120', 'max:840'],
    ]);

    $keys = [
      'posyandu_baby_max_months',
      'posyandu_toddler_max_months',
      'posyandu_child_max_months',
      'posyandu_teen_max_months',
      'posyandu_adult_max_months',
    ];

    foreach ($keys as $key) {
      Setting::set($key, (string) $request->integer($key));
    }

    Cache::flush();

    return redirect()->route('settings.index')
      ->with('success', __('app.flash_posyandu_saved'));
  }

  // ── Two-Factor Authentication ───────────────────────────────────────────────

  public function showTwoFactor(Request $request)
  {
      $user = Auth::user();
      
      // If already enabled, redirect to settings index
      if ($user->two_factor_secret) {
          return redirect()->route('settings.index')->with('success', '2FA is already enabled.');
      }

      // Automatically generate a new secret for setup if they haven't started yet
      $secret = $request->session()->get('2fa_setup_secret');
      if (!$secret) {
          $secret = Google2FA::generateSecretKey();
          $request->session()->put('2fa_setup_secret', $secret);
      }

      $appName = config('app.name');
      $qrCodeUrl = Google2FA::getQRCodeUrl(
          $appName,
          $user->email ?? $user->username,
          $secret
      );

      $qrCodeImg = 'https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=' . urlencode($qrCodeUrl);

      return view('settings.two-factor', [
          'user' => $user,
          'secret' => $secret,
          'qrCodeImg' => $qrCodeImg
      ]);
  }

  // enableTwoFactor POST route is no longer needed since showTwoFactor automatically starts the setup.

  public function confirmTwoFactor(Request $request)
  {
      $request->validate(['code' => 'required|string|size:6']);
      
      $secret = $request->session()->get('2fa_setup_secret');
      if (!$secret) {
          return redirect()->route('settings.2fa')->with('error', 'Session expired. Please try again.');
      }

      $valid = Google2FA::verifyKey($secret, $request->code);

      if ($valid) {
          $user = Auth::user();
          $user->two_factor_secret = $secret;
          $user->save();

          $request->session()->forget('2fa_setup_secret');

          return redirect()->route('settings.index')->with('success', 'Two-Factor Authentication has been enabled successfully.');
      }

      return back()->with('error', 'Invalid authentication code. Please try again.');
  }

}

