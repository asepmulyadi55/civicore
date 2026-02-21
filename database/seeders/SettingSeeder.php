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
      ['key' => 'community_name', 'value' => 'CiviCore Residential Community', 'label' => 'Community Name', 'group' => 'general'],

      // Payment
      ['key' => 'default_due_day', 'value' => '5', 'label' => 'Default Payment Due Day (1–28)', 'group' => 'payment'],
      ['key' => 'currency_symbol', 'value' => 'Rp', 'label' => 'Currency Symbol', 'group' => 'payment'],
      ['key' => 'currency_code', 'value' => 'IDR', 'label' => 'Currency Code', 'group' => 'payment'],
    ];

    foreach ($settings as $setting) {
      Setting::firstOrCreate(['key' => $setting['key']], $setting);
    }
  }
}
