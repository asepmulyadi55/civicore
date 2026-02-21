<?php

namespace Tests\Feature;

use App\Models\Block;
use App\Models\FeeHistory;
use App\Models\PaymentRecord;
use App\Models\Resident;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResidentTest extends TestCase
{
  use RefreshDatabase;

  // ── Helpers ─────────────────────────────────────────────────────────────────

  /**
   * Create roles and blocks needed for most tests, return an admin user.
   */
  private function createTestData(): array
  {
    $adminRole = Role::create(['name' => 'admin', 'label' => 'Admin']);
    Role::create(['name' => 'resident', 'label' => 'Resident']);

    $blockA = Block::create(['name' => 'Block A', 'is_active' => true]);
    $blockB = Block::create(['name' => 'Block B', 'is_active' => true]);

    $admin = User::create([
      'name' => 'Test Admin',
      'username' => 'testadmin',
      'email' => 'admin@test.com',
      'password' => bcrypt('password'),
      'is_active' => true,
      'role_id' => $adminRole->id,
    ]);

    return compact('admin', 'blockA', 'blockB');
  }

  // ── Authorization ────────────────────────────────────────────────────────────

  /** @test */
  public function guests_are_redirected_from_residents_page()
  {
    $response = $this->get(route('residents.index'));
    $response->assertRedirect(route('login'));
  }

  /** @test */
  public function authenticated_user_can_view_residents_page()
  {
    ['admin' => $admin] = $this->createTestData();

    $response = $this->actingAs($admin)->get(route('residents.index'));
    $response->assertOk();
    $response->assertViewIs('residents');
  }

  // ── Index / Listing ──────────────────────────────────────────────────────────

  /** @test */
  public function residents_page_shows_residents_from_database()
  {
    ['admin' => $admin, 'blockA' => $blockA] = $this->createTestData();

    $resident = Resident::create([
      'block_id' => $blockA->id,
      'unit_number' => 'A-101',
      'fullname' => 'Ahmad Fauzi',
      'phone' => '081234567890',
      'is_active' => true,
    ]);

    $response = $this->actingAs($admin)->get(route('residents.index'));
    $response->assertOk();
    $response->assertSee('Ahmad Fauzi');
  }

  /** @test */
  public function residents_page_can_be_searched_by_name()
  {
    ['admin' => $admin, 'blockA' => $blockA] = $this->createTestData();

    Resident::create(['block_id' => $blockA->id, 'unit_number' => 'A-101', 'fullname' => 'Ahmad Fauzi', 'is_active' => true]);
    Resident::create(['block_id' => $blockA->id, 'unit_number' => 'A-102', 'fullname' => 'Siti Nurhaliza', 'is_active' => true]);

    $response = $this->actingAs($admin)->get(route('residents.index', ['search' => 'Ahmad']));
    $response->assertOk();
    $response->assertSee('Ahmad Fauzi');
    $response->assertDontSee('Siti Nurhaliza');
  }

  /** @test */
  public function residents_page_can_be_filtered_by_block()
  {
    ['admin' => $admin, 'blockA' => $blockA, 'blockB' => $blockB] = $this->createTestData();

    Resident::create(['block_id' => $blockA->id, 'unit_number' => 'A-101', 'fullname' => 'Block A Resident', 'is_active' => true]);
    Resident::create(['block_id' => $blockB->id, 'unit_number' => 'B-101', 'fullname' => 'Block B Resident', 'is_active' => true]);

    $response = $this->actingAs($admin)->get(route('residents.index', ['block_id' => $blockA->id]));
    $response->assertOk();
    $response->assertSee('Block A Resident');
    $response->assertDontSee('Block B Resident');
  }

  /** @test */
  public function residents_page_can_be_filtered_by_active_status()
  {
    ['admin' => $admin, 'blockA' => $blockA] = $this->createTestData();

    Resident::create(['block_id' => $blockA->id, 'unit_number' => 'A-101', 'fullname' => 'Active Resident', 'is_active' => true]);
    Resident::create(['block_id' => $blockA->id, 'unit_number' => 'A-102', 'fullname' => 'Inactive Resident', 'is_active' => false]);

    $response = $this->actingAs($admin)->get(route('residents.index', ['status' => 'active']));
    $response->assertOk();
    $response->assertSee('Active Resident');
    $response->assertDontSee('Inactive Resident');
  }

  // ── Store (Create) ────────────────────────────────────────────────────────────

  /** @test */
  public function admin_can_create_a_resident_with_initial_fee()
  {
    ['admin' => $admin, 'blockA' => $blockA] = $this->createTestData();

    $response = $this->actingAs($admin)->post(route('residents.store'), [
      'fullname' => 'Budi Santoso',
      'phone' => '081999888777',
      'block_id' => $blockA->id,
      'unit_number' => 'A-201',
      'monthly_fee' => 500000,
      'fee_start' => '2024-01',
    ]);

    $response->assertRedirect(route('residents.index'));
    $response->assertSessionHas('success');

    // Resident is in the database
    $this->assertDatabaseHas('residents', [
      'fullname' => 'Budi Santoso',
      'block_id' => $blockA->id,
      'unit_number' => 'A-201',
      'is_active' => true,
    ]);

    // Fee history record is also created
    $resident = Resident::where('unit_number', 'A-201')->first();
    $feeHistory = FeeHistory::where('resident_id', $resident->id)->first();
    $this->assertNotNull($feeHistory, 'Fee history record was not created.');
    $this->assertEquals(500000, $feeHistory->amount);
    $this->assertEquals('2024-01-01', $feeHistory->effective_from->format('Y-m-d'));
  }

  /** @test */
  public function store_requires_fullname()
  {
    ['admin' => $admin, 'blockA' => $blockA] = $this->createTestData();

    $response = $this->actingAs($admin)->post(route('residents.store'), [
      'fullname' => '',
      'block_id' => $blockA->id,
      'unit_number' => 'A-201',
      'monthly_fee' => 500000,
      'fee_start' => '2024-01',
    ]);

    $response->assertSessionHasErrors('fullname');
    $this->assertStringContainsString(
      "Please enter the resident's full name.",
      session('errors')->first('fullname')
    );
  }

  /** @test */
  public function store_requires_block()
  {
    ['admin' => $admin] = $this->createTestData();

    $response = $this->actingAs($admin)->post(route('residents.store'), [
      'fullname' => 'Test Resident',
      'block_id' => '',
      'unit_number' => 'A-201',
      'monthly_fee' => 500000,
      'fee_start' => '2024-01',
    ]);

    $response->assertSessionHasErrors('block_id');
    $this->assertStringContainsString(
      'Please select a block for this resident.',
      session('errors')->first('block_id')
    );
  }

  /** @test */
  public function store_requires_unit_number()
  {
    ['admin' => $admin, 'blockA' => $blockA] = $this->createTestData();

    $response = $this->actingAs($admin)->post(route('residents.store'), [
      'fullname' => 'Test Resident',
      'block_id' => $blockA->id,
      'unit_number' => '',
      'monthly_fee' => 500000,
      'fee_start' => '2024-01',
    ]);

    $response->assertSessionHasErrors('unit_number');
  }

  /** @test */
  public function store_requires_positive_monthly_fee()
  {
    ['admin' => $admin, 'blockA' => $blockA] = $this->createTestData();

    $response = $this->actingAs($admin)->post(route('residents.store'), [
      'fullname' => 'Test Resident',
      'block_id' => $blockA->id,
      'unit_number' => 'A-201',
      'monthly_fee' => -100,
      'fee_start' => '2024-01',
    ]);

    $response->assertSessionHasErrors('monthly_fee');
  }

  /** @test */
  public function store_requires_valid_fee_start_month()
  {
    ['admin' => $admin, 'blockA' => $blockA] = $this->createTestData();

    $response = $this->actingAs($admin)->post(route('residents.store'), [
      'fullname' => 'Test Resident',
      'block_id' => $blockA->id,
      'unit_number' => 'A-201',
      'monthly_fee' => 500000,
      'fee_start' => 'not-a-month',
    ]);

    $response->assertSessionHasErrors('fee_start');
  }

  // ── Update ────────────────────────────────────────────────────────────────────

  /** @test */
  public function admin_can_update_resident_details()
  {
    ['admin' => $admin, 'blockA' => $blockA, 'blockB' => $blockB] = $this->createTestData();

    $resident = Resident::create([
      'block_id' => $blockA->id,
      'unit_number' => 'A-101',
      'fullname' => 'Old Name',
      'phone' => '081111111111',
      'is_active' => true,
    ]);

    $response = $this->actingAs($admin)->put(route('residents.update', $resident), [
      'fullname' => 'New Name',
      'phone' => '082222222222',
      'block_id' => $blockB->id,
      'unit_number' => 'B-101',
      'is_active' => true,
    ]);

    $response->assertRedirect(route('residents.index'));
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('residents', [
      'id' => $resident->id,
      'fullname' => 'New Name',
      'phone' => '082222222222',
      'block_id' => $blockB->id,
      'unit_number' => 'B-101',
    ]);
  }

  /** @test */
  public function update_prevents_duplicate_unit_number_in_same_block()
  {
    ['admin' => $admin, 'blockA' => $blockA] = $this->createTestData();

    // Two residents in the same block
    $resident1 = Resident::create(['block_id' => $blockA->id, 'unit_number' => 'A-101', 'fullname' => 'Resident One', 'is_active' => true]);
    $resident2 = Resident::create(['block_id' => $blockA->id, 'unit_number' => 'A-102', 'fullname' => 'Resident Two', 'is_active' => true]);

    // Try to change resident2's unit to A-101 (already taken)
    $response = $this->actingAs($admin)->put(route('residents.update', $resident2), [
      'fullname' => 'Resident Two',
      'block_id' => $blockA->id,
      'unit_number' => 'A-101',
      'is_active' => true,
    ]);

    $response->assertSessionHasErrors('unit_number');
    $this->assertStringContainsString(
      'This unit number is already taken in the selected block.',
      session('errors')->first('unit_number')
    );
  }

  /** @test */
  public function update_allows_keeping_own_unit_number()
  {
    ['admin' => $admin, 'blockA' => $blockA] = $this->createTestData();

    $resident = Resident::create(['block_id' => $blockA->id, 'unit_number' => 'A-101', 'fullname' => 'Ahmad', 'is_active' => true]);

    // Update only the name, keep the unit — should pass
    $response = $this->actingAs($admin)->put(route('residents.update', $resident), [
      'fullname' => 'Ahmad Fauzi',
      'block_id' => $blockA->id,
      'unit_number' => 'A-101',
      'is_active' => true,
    ]);

    $response->assertRedirect(route('residents.index'));
    $response->assertSessionHas('success');
  }

  // ── Destroy (Deactivate) ──────────────────────────────────────────────────────

  /** @test */
  public function destroy_deactivates_resident_instead_of_deleting()
  {
    ['admin' => $admin, 'blockA' => $blockA] = $this->createTestData();

    $resident = Resident::create([
      'block_id' => $blockA->id,
      'unit_number' => 'A-101',
      'fullname' => 'To Be Deactivated',
      'is_active' => true,
    ]);

    $response = $this->actingAs($admin)->delete(route('residents.destroy', $resident));

    $response->assertRedirect(route('residents.index'));

    // Row still exists in DB (payment history preserved)
    $this->assertDatabaseHas('residents', ['id' => $resident->id]);

    // But is now inactive
    $this->assertDatabaseHas('residents', ['id' => $resident->id, 'is_active' => false]);
  }

  /** @test */
  public function deactivating_resident_does_not_delete_payment_records()
  {
    ['admin' => $admin, 'blockA' => $blockA] = $this->createTestData();

    $resident = Resident::create([
      'block_id' => $blockA->id,
      'unit_number' => 'A-101',
      'fullname' => 'Has Payments',
      'is_active' => true,
    ]);

    // Create a payment record
    PaymentRecord::create([
      'resident_id' => $resident->id,
      'payment_month' => '2024-01-01',
      'amount' => 500000,
      'status' => 'approved',
    ]);

    $this->actingAs($admin)->delete(route('residents.destroy', $resident));

    // Payment record still exists
    $this->assertDatabaseHas('payment_records', [
      'resident_id' => $resident->id,
    ]);
  }
}
