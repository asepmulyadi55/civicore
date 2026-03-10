<?php

namespace Database\Seeders;

use App\Models\Block;
use Illuminate\Database\Seeder;

class BlockSeeder extends Seeder
{
  public function run(): void
  {
    $blocks = [
      ['name' => 'Block A', 'description' => 'Terrace Houses — North Wing', 'is_active' => true],
      ['name' => 'Block B', 'description' => 'Apartments — East Wing', 'is_active' => true],
      ['name' => 'Block C', 'description' => 'Villas — South Wing', 'is_active' => true],
    ];

    foreach ($blocks as $block) {
      Block::firstOrCreate(['name' => $block['name']], $block);
    }
  }
}
