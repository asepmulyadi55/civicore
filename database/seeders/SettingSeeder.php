<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
  public function run(): void
  {
    $settings = [
      // General
      ['key' => 'app_name', 'value' => 'CiviCore', 'label' => 'Application Name', 'group' => 'general'],
      ['key' => 'support_email', 'value' => 'support@civicore-community.com', 'label' => 'Support Email', 'group' => 'general'],

      // Community
      ['key' => 'community_name', 'value' => 'CiviCore Residential Community', 'label' => 'Community Name', 'group' => 'community'],
      ['key' => 'community_address', 'value' => '', 'label' => 'Community Address', 'group' => 'community'],
      ['key' => 'community_phone', 'value' => '', 'label' => 'Community Phone', 'group' => 'community'],

      // Locale
      ['key' => 'app_language', 'value' => 'en', 'label' => 'Application Language', 'group' => 'locale'],
      ['key' => 'date_format', 'value' => 'DD/MM/YYYY', 'label' => 'Date Format', 'group' => 'locale'],
      ['key' => 'currency_symbol', 'value' => 'Rp', 'label' => 'Currency Symbol', 'group' => 'locale'],
      ['key' => 'currency_code', 'value' => 'IDR', 'label' => 'Currency Code', 'group' => 'locale'],

      // Financial
      ['key' => 'default_fee_amount', 'value' => '0', 'label' => 'Default Monthly Fee Amount', 'group' => 'financial'],
      ['key' => 'late_payment_grace_days', 'value' => '7', 'label' => 'Late Payment Grace Period (days)', 'group' => 'financial'],
      ['key' => 'default_due_day', 'value' => '5', 'label' => 'Payment Due Day (1–28)', 'group' => 'financial'],

      // Notifications
      ['key' => 'notify_payment_approved', 'value' => '1', 'label' => 'Email on Payment Approved', 'group' => 'notifications'],
      ['key' => 'notify_payment_rejected', 'value' => '1', 'label' => 'Email on Payment Rejected', 'group' => 'notifications'],
      ['key' => 'notify_new_resident', 'value' => '1', 'label' => 'Email on New Resident Registration', 'group' => 'notifications'],

      // Security
      ['key' => 'session_timeout_minutes', 'value' => '120', 'label' => 'Session Timeout (minutes)', 'group' => 'security'],
      ['key' => 'require_2fa_admin', 'value' => '0', 'label' => 'Require 2FA for Admin/Treasurer', 'group' => 'security'],
    ];

    foreach ($settings as $setting) {
      Setting::firstOrCreate(['key' => $setting['key']], $setting);
    }
  }
}
