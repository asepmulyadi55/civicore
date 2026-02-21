<?php

namespace Database\Seeders;

use App\Models\PaymentMethod;
use Illuminate\Database\Seeder;

class PaymentMethodSeeder extends Seeder
{
  public function run(): void
  {
    $methods = [
      ['name' => 'cash', 'label' => 'Cash', 'is_active' => true],
      ['name' => 'bank_transfer', 'label' => 'Bank Transfer', 'is_active' => true],
      ['name' => 'e_wallet', 'label' => 'E-Wallet', 'is_active' => true],
    ];

    foreach ($methods as $method) {
      PaymentMethod::firstOrCreate(['name' => $method['name']], $method);
    }
  }
}
