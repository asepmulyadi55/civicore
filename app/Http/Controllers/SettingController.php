<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SettingController extends Controller
{
  /** Keys that store boolean toggles (0/1). */
  private const BOOLEAN_KEYS = [
    'notify_payment_approved',
    'notify_payment_rejected',
    'notify_new_resident',
    'require_2fa_admin',
  ];

  /** Keys and their validation rules. */
  private const RULES = [
    'app_name' => ['required', 'string', 'max:100'],
    'support_email' => ['required', 'email', 'max:150'],
    'community_name' => ['required', 'string', 'max:150'],
    'community_address' => ['nullable', 'string', 'max:500'],
    'community_phone' => ['nullable', 'string', 'max:30'],
    'app_language' => ['required', 'in:en,id'],
    'date_format' => ['required', 'in:DD/MM/YYYY,MM/DD/YYYY,YYYY-MM-DD'],
    'currency_symbol' => ['required', 'string', 'max:10'],
    'currency_code' => ['required', 'string', 'max:10'],
    'default_fee_amount' => ['required', 'numeric', 'min:0'],
    'late_payment_grace_days' => ['required', 'integer', 'min:0', 'max:60'],
    'default_due_day' => ['required', 'integer', 'min:1', 'max:28'],
    'notify_payment_approved' => ['nullable', 'boolean'],
    'notify_payment_rejected' => ['nullable', 'boolean'],
    'notify_new_resident' => ['nullable', 'boolean'],
    'session_timeout_minutes' => ['required', 'integer', 'min:15', 'max:1440'],
    'require_2fa_admin' => ['nullable', 'boolean'],
  ];

  public function index()
  {
    $all = Setting::all()->keyBy('key');

    return view('settings', compact('all'));
  }

  public function update(Request $request)
  {
    $data = $request->only(array_keys(self::RULES));

    // Unchecked checkboxes are absent from POST — normalise booleans to 0/1
    foreach (self::BOOLEAN_KEYS as $boolKey) {
      $data[$boolKey] = $request->has($boolKey) ? '1' : '0';
    }

    $request->merge($data);
    $request->validate(self::RULES);

    foreach ($data as $key => $value) {
      Setting::set($key, $value ?? '');
    }

    // Flush entire settings cache so stale values don't linger
    Cache::flush();

    return redirect()->route('settings.index')
      ->with('success', 'Settings saved successfully.');
  }
}
