<?php

namespace Database\Seeders;

use App\Models\Block;
use App\Models\FeeHistory;
use App\Models\Resident;
use App\Models\User;
use Illuminate\Database\Seeder;

class ResidentSeeder extends Seeder
{
  public function run(): void
  {
    $admin = User::where('username', 'admin')->first();

    $blocks = Block::all();

    // One resident per block — enough for quick testing.
    // Re-run safely: extra units from larger old seeds are deleted first (cascades to payments + fee_histories).
    $data = [
      'Block A' => [
        ['A-101', 'Ahmad Fauzi', '081234567890', 165000], // linked to resident user
      ],
      'Block B' => [
        ['B-201', 'Rudi Setiawan', '082234567890', 165000],
      ],
      'Block C' => [
        ['C-301', 'Bambang Supriyadi', '083234567890', 165000],
      ],
    ];

    // Remove any residents NOT in the seed list (cleans up old larger seeds).
    $wantedUnits = array_merge(...array_values(array_map(fn($r) => array_column($r, 0), $data)));
    Resident::whereNotIn('unit_number', $wantedUnits)->delete();

    $residentUser = User::where('username', 'resident')->first();

    foreach ($data as $blockName => $residents) {
      $block = $blocks->firstWhere('name', $blockName);
      if (!$block)
        continue;

      foreach ($residents as [$unit, $fullname, $phone, $fee]) {
        // Link the first resident of Block A to the resident user account
        $userId = ($blockName === 'Block A' && $unit === 'A-101') ? $residentUser?->id : null;

        $resident = Resident::updateOrCreate(
          ['block_id' => $block->id, 'unit_number' => $unit],
          [
            'fullname' => $fullname,
            'phone' => $phone,
            'is_active' => true,
            'user_id' => $userId,
          ]
        );

        // Create initial fee history entry (effective from Jan 2023)
        FeeHistory::firstOrCreate(
          ['resident_id' => $resident->id, 'effective_from' => '2023-01-01'],
          [
            'amount' => $fee,
            'created_by' => $admin?->id,
            'notes' => 'Initial fee assignment',
          ]
        );
      }
    }
  }
}
