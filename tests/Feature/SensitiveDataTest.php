<?php

namespace Tests\Feature;

use App\Models\Block;
use App\Models\Householder;
use App\Models\Householder;
use App\Models\Role;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SensitiveDataTest extends TestCase
{
    use RefreshDatabase;

    private const SPOUSE_NAME = self::SPOUSE_NAME;
    private const SPOUSE_NIK  = self::SPOUSE_NIK;

    // ── Helpers ──────────────────────────────────────────────────────────────────

    private function createAdmin(): User
    {
        $role = Role::create(['name' => 'admin', 'label' => 'Admin', 'permissions' => []]);

        return User::create([
            'name'      => 'Admin User',
            'username'  => 'admin',
            'email'     => 'admin@test.com',
            'password'  => bcrypt('password'),
            'is_active' => true,
            'role_id'   => $role->id,
        ]);
    }

    private function createResidentUser(): User
    {
        $role = Role::create(['name' => 'resident', 'label' => 'Resident', 'permissions' => []]);

        return User::create([
            'name'      => 'Regular Resident',
            'username'  => 'resident',
            'email'     => 'resident@test.com',
            'password'  => bcrypt('password'),
            'is_active' => true,
            'role_id'   => $role->id,
        ]);
    }

    private function createResident(string $blockName = 'Block A', string $unitNumber = 'A-01'): Resident
    {
        $block = Block::create(['name' => $blockName, 'is_active' => true]);
        $unit  = Unit::create(['block_id' => $block->id, 'unit_number' => $unitNumber, 'is_active' => true]);

        return Householder::create([
            'unit_id'            => $unit->id,
            'fullname'           => 'Ahmad Fauzi',
            'phone'              => '081234567890',
            'family_card_number' => '1234567890123456',
            'is_active'          => true,
        ]);
    }

    // ── Family Card Number (FCN) reveal ──────────────────────────────────────────

    /** @test */
    public function guest_cannot_reveal_fcn()
    {
        $resident = $this->createResident();

        $this->get(route('residents.reveal-fcn', $resident))
            ->assertRedirect(route('login'));
    }

    /** @test */
    public function non_admin_user_cannot_reveal_fcn()
    {
        $user     = $this->createResidentUser();
        $resident = $this->createResident();

        $this->actingAs($user)
            ->get(route('residents.reveal-fcn', $resident))
            ->assertForbidden();
    }

    /** @test */
    public function admin_can_reveal_fcn()
    {
        $admin    = $this->createAdmin();
        $resident = $this->createResident();

        $this->actingAs($admin)
            ->get(route('residents.reveal-fcn', $resident))
            ->assertOk()
            ->assertJsonStructure(['value'])
            ->assertJson(['value' => '1234567890123456']);
    }

    /** @test */
    public function reveal_fcn_returns_empty_string_when_not_set()
    {
        $admin    = $this->createAdmin();
        $block    = Block::create(['name' => 'Block B', 'is_active' => true]);
        $unit     = Unit::create(['block_id' => $block->id, 'unit_number' => 'B-01', 'is_active' => true]);
        $resident = Householder::create([
            'unit_id'   => $unit->id,
            'fullname'  => 'No KK Resident',
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->get(route('residents.reveal-fcn', $resident))
            ->assertOk()
            ->assertJson(['value' => '']);
    }

    // ── NIK reveal ───────────────────────────────────────────────────────────────

    /** @test */
    public function guest_cannot_reveal_nik()
    {
        $resident = $this->createResident();
        $member   = Householder::create([
            'resident_id'  => $resident->id,
            'fullname'     => self::SPOUSE_NAME,
            'relationship' => 'spouse',
            'nik'          => self::SPOUSE_NIK,
        ]);

        $this->get(route('residents.residents.reveal-nik', [$resident, $member]))
            ->assertRedirect(route('login'));
    }

    /** @test */
    public function non_admin_user_cannot_reveal_nik()
    {
        $user     = $this->createResidentUser();
        $resident = $this->createResident();
        $member   = Householder::create([
            'resident_id'  => $resident->id,
            'fullname'     => self::SPOUSE_NAME,
            'relationship' => 'spouse',
            'nik'          => self::SPOUSE_NIK,
        ]);

        $this->actingAs($user)
            ->get(route('residents.residents.reveal-nik', [$resident, $member]))
            ->assertForbidden();
    }

    /** @test */
    public function admin_can_reveal_nik()
    {
        $admin    = $this->createAdmin();
        $resident = $this->createResident();
        $member   = Householder::create([
            'resident_id'  => $resident->id,
            'fullname'     => self::SPOUSE_NAME,
            'relationship' => 'spouse',
            'nik'          => self::SPOUSE_NIK,
        ]);

        $this->actingAs($admin)
            ->get(route('residents.residents.reveal-nik', [$resident, $member]))
            ->assertOk()
            ->assertJsonStructure(['value'])
            ->assertJson(['value' => self::SPOUSE_NIK]);
    }

    /** @test */
    public function reveal_nik_returns_404_when_resident_belongs_to_different_resident()
    {
        $admin      = $this->createAdmin();
        $resident   = $this->createResident('Block A', 'A-01');
        $otherBlock = Block::create(['name' => 'Block B', 'is_active' => true]);
        $otherUnit  = Unit::create(['block_id' => $otherBlock->id, 'unit_number' => 'B-01', 'is_active' => true]);
        $other      = Householder::create([
            'unit_id'   => $otherUnit->id,
            'fullname'  => 'Other Resident',
            'is_active' => true,
        ]);
        $member = Householder::create([
            'resident_id'  => $other->id, // belongs to OTHER resident
            'fullname'     => 'Budi Santoso',
            'relationship' => 'child',
            'nik'          => '3201010101010099',
        ]);

        // Passing $resident but $member belongs to $other → 404
        $this->actingAs($admin)
            ->get(route('residents.residents.reveal-nik', [$resident, $member]))
            ->assertNotFound();
    }
}

