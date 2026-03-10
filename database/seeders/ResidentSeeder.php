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

    // Data: [block_name => [[unit, fullname, phone, fee]]]
    $data = [
      'Block A' => [
        ['A-101', 'Ahmad Fauzi', '081234567890', 500000],
        ['A-102', 'Siti Nurhaliza', '081234567891', 500000],
        ['A-103', 'Budi Hartono', '081234567892', 500000],
        ['A-104', 'Rina Kusuma', '081234567893', 500000],
        ['A-105', 'Joko Widodo', '081234567894', 500000],
        ['A-106', 'Maya Sari', '081234567895', 500000],
        ['A-107', 'Hendra Gunawan', '081234567896', 500000],
        ['A-108', 'Julian Rivera', '081234567897', 500000], // linked to resident user
      ],
      'Block B' => [
        ['B-201', 'Rudi Setiawan', '082234567890', 450000],
        ['B-202', 'Ani Wahyuni', '082234567891', 450000],
        ['B-203', 'Dedi Kurniawan', '082234567892', 450000],
        ['B-204', 'Lestari Putri', '082234567893', 450000],
        ['B-205', 'Agus Salim', '082234567894', 450000],
        ['B-206', 'Fitri Handayani', '082234567895', 450000],
        ['B-207', 'Wahyu Santoso', '082234567896', 450000],
        ['B-208', 'Sari Dewi', '082234567897', 450000],
      ],
      'Block C' => [
        ['C-301', 'Bambang Supriyadi', '083234567890', 600000],
        ['C-302', 'Nur Aini', '083234567891', 600000],
        ['C-303', 'Teguh Prasetyo', '083234567892', 600000],
        ['C-304', 'Wulandari', '083234567893', 600000],
        ['C-305', 'Fajar Nugroho', '083234567894', 600000],
        ['C-306', 'Indah Permata', '083234567895', 600000],
        ['C-307', 'Arief Rahman', '083234567896', 600000],
        ['C-308', 'Dina Pratiwi', '083234567897', 600000],
      ],
    ];

    $residentUser = User::where('username', 'resident')->first();

    foreach ($data as $blockName => $residents) {
      $block = $blocks->firstWhere('name', $blockName);
      if (!$block)
        continue;

      foreach ($residents as [$unit, $fullname, $phone, $fee]) {
        // Link the last resident of Block A to the resident user account
        $userId = ($blockName === 'Block A' && $unit === 'A-108') ? $residentUser?->id : null;

        $resident = Resident::firstOrCreate(
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
