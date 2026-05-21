<?php

namespace Database\Seeders;

use App\Models\Block;
use App\Models\FamilyMember;
use App\Models\FeeHistory;
use App\Models\Resident;
use App\Models\Unit;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ResidentSeeder extends Seeder
{
  public function run(): void
  {
    $admin = User::where('username', 'admin')->first();
    $blocks = Block::all();

    $data = [
      'Block A' => [
        ['A-101', 'Ahmad Fauzi', '081234567890', 165000],
      ],
      'Block B' => [
        ['B-201', 'Rudi Setiawan', '082234567890', 165000],
      ],
      'Block C' => [
        ['C-301', 'Bambang Supriyadi', '083234567890', 165000],
      ],
    ];

    $wantedUnitNumbers = array_merge(...array_values(array_map(fn($r) => array_column($r, 0), $data)));
    $wantedUnitIds = Unit::whereIn('unit_number', $wantedUnitNumbers)->pluck('id');
    Resident::whereNotIn('unit_id', $wantedUnitIds)->delete();

    $residentUser = User::where('username', 'resident')->first();

    foreach ($data as $blockName => $residents) {
      $block = $blocks->firstWhere('name', $blockName);
      if (!$block) continue;

      foreach ($residents as [$unitNum, $fullname, $phone, $fee]) {
        $unit = Unit::updateOrCreate(
          ['block_id' => $block->id, 'unit_number' => $unitNum],
          ['house_status' => 'owner_occupied', 'is_active' => true]
        );

        $userId = ($blockName === 'Block A' && $unitNum === 'A-101') ? $residentUser?->id : null;

        $resident = Resident::updateOrCreate(
          ['unit_id' => $unit->id],
          [
            'block_id' => $block->id,
            'fullname'  => $fullname,
            'phone'     => $phone,
            'is_active' => true,
            'user_id'   => $userId,
          ]
        );

        FeeHistory::firstOrCreate(
          ['resident_id' => $resident->id, 'effective_from' => '2023-01-01'],
          [
            'amount'     => $fee,
            'created_by' => $admin?->id,
            'notes'      => 'Initial fee assignment',
          ]
        );
      }
    }

    // ── Family members: covers all Posyandu age categories (Block A) ────────
    $unitA101  = Unit::where('unit_number', 'A-101')->first();
    $residentA = $unitA101 ? Resident::where('unit_id', $unitA101->id)->first() : null;

    if ($residentA) {
      $members = [
        ['fullname' => 'Ahmad Fauzi',    'relationship' => 'head',   'gender' => 'male',   'birth_date' => Carbon::now()->subYears(40)->startOfMonth(),  'is_head' => true,  'occupation' => 'Civil Servant', 'education' => 'bachelor'],
        ['fullname' => 'Sari Dewi Fauzi','relationship' => 'spouse', 'gender' => 'female', 'birth_date' => Carbon::now()->subYears(38)->startOfMonth(),  'is_head' => false, 'occupation' => 'Homemaker',    'education' => 'senior_high'],
        ['fullname' => 'Rizky Fauzi',    'relationship' => 'child',  'gender' => 'male',   'birth_date' => Carbon::now()->subYears(8)->startOfMonth(),   'is_head' => false, 'occupation' => null,           'education' => 'elementary'],
        ['fullname' => 'Nayla Fauzi',    'relationship' => 'child',  'gender' => 'female', 'birth_date' => Carbon::now()->subMonths(30)->startOfMonth(), 'is_head' => false, 'occupation' => null,           'education' => 'none'],
        ['fullname' => 'Bayi Fauzi',     'relationship' => 'child',  'gender' => 'male',   'birth_date' => Carbon::now()->subMonths(4)->startOfMonth(),  'is_head' => false, 'occupation' => null,           'education' => 'none'],
        ['fullname' => 'Haji Fauzi Tua', 'relationship' => 'parent', 'gender' => 'male',   'birth_date' => Carbon::now()->subYears(68)->startOfMonth(),  'is_head' => false, 'occupation' => 'Retired',      'education' => 'junior_high'],
      ];

      foreach ($members as $m) {
        FamilyMember::updateOrCreate(
          ['resident_id' => $residentA->id, 'fullname' => $m['fullname']],
          $m
        );
      }
    }

    // ── Block B: teen (Remaja) + adult head ─────────────────────────────────
    $unitB201  = Unit::where('unit_number', 'B-201')->first();
    $residentB = $unitB201 ? Resident::where('unit_id', $unitB201->id)->first() : null;

    if ($residentB) {
      $bMembers = [
        ['fullname' => 'Rudi Setiawan',  'relationship' => 'head',  'gender' => 'male',   'birth_date' => Carbon::now()->subYears(45)->startOfMonth(), 'is_head' => true,  'occupation' => 'Entrepreneur', 'education' => 'bachelor'],
        ['fullname' => 'Putri Setiawan', 'relationship' => 'child', 'gender' => 'female', 'birth_date' => Carbon::now()->subYears(14)->startOfMonth(), 'is_head' => false, 'occupation' => null,           'education' => 'junior_high'],
      ];

      foreach ($bMembers as $m) {
        FamilyMember::updateOrCreate(
          ['resident_id' => $residentB->id, 'fullname' => $m['fullname']],
          $m
        );
      }
    }
  }
}
