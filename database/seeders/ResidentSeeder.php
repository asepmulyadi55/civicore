<?php

namespace Database\Seeders;

use App\Models\Block;
use App\Models\FeeHistory;
use App\Models\Resident;
use App\Models\Unit;
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
    $wantedUnitNumbers = array_merge(...array_values(array_map(fn($r) => array_column($r, 0), $data)));
    // Find Unit IDs corresponding to wanted unit numbers, then delete residents not linked to them
    $wantedUnitIds = Unit::whereIn('unit_number', $wantedUnitNumbers)->pluck('id');
    Resident::whereNotIn('unit_id', $wantedUnitIds)->delete();

    $residentUser = User::where('username', 'resident')->first();

    foreach ($data as $blockName => $residents) {
      $block = $blocks->firstWhere('name', $blockName);
      if (!$block)
        continue;

      foreach ($residents as [$unitNum, $fullname, $phone, $fee]) {
        // Ensure the Unit record exists
        $unit = Unit::updateOrCreate(
          ['block_id' => $block->id, 'unit_number' => $unitNum],
          ['house_status' => 'owner_occupied', 'is_active' => true]
        );

        // Link the first resident of Block A to the resident user account
        $userId = ($blockName === 'Block A' && $unitNum === 'A-101') ? $residentUser?->id : null;

        $resident = Resident::updateOrCreate(
          ['unit_id' => $unit->id],
          [
            'block_id' => $block->id,
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
