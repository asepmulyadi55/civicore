<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
  private array $settings = [
    // Community
    ['key' => 'community_address', 'value' => '', 'label' => 'Community Address', 'group' => 'community'],
    ['key' => 'community_phone', 'value' => '', 'label' => 'Community Phone', 'group' => 'community'],

    // Locale
    ['key' => 'app_language', 'value' => 'en', 'label' => 'Application Language', 'group' => 'locale'],
    ['key' => 'date_format', 'value' => 'DD/MM/YYYY', 'label' => 'Date Format', 'group' => 'locale'],

    // Financial
    ['key' => 'default_fee_amount', 'value' => '0', 'label' => 'Default Monthly Fee Amount', 'group' => 'financial'],
    ['key' => 'late_payment_grace_days', 'value' => '7', 'label' => 'Late Payment Grace Period (days)', 'group' => 'financial'],

    // Notifications (1 = enabled, 0 = disabled)
    ['key' => 'notify_payment_approved', 'value' => '1', 'label' => 'Email on Payment Approved', 'group' => 'notifications'],
    ['key' => 'notify_payment_rejected', 'value' => '1', 'label' => 'Email on Payment Rejected', 'group' => 'notifications'],
    ['key' => 'notify_new_resident', 'value' => '1', 'label' => 'Email on New Resident Registration', 'group' => 'notifications'],

    // Security
    ['key' => 'session_timeout_minutes', 'value' => '120', 'label' => 'Session Timeout (minutes)', 'group' => 'security'],
    ['key' => 'require_2fa_admin', 'value' => '0', 'label' => 'Require 2FA for Admin/Treasurer', 'group' => 'security'],
  ];

  public function up(): void
  {
    foreach ($this->settings as $setting) {
      Setting::firstOrCreate(
        ['key' => $setting['key']],
        $setting
      );
    }
  }

  public function down(): void
  {
    $keys = array_column($this->settings, 'key');
    Setting::whereIn('key', $keys)->delete();
  }
};
