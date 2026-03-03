<?php

namespace Tests\Feature;

use App\Models\Block;
use App\Models\PaymentRecord;
use App\Models\Resident;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentTest extends TestCase
{
  use RefreshDatabase;

  // ── Test Fixture Constants ────────────────────────────────────────────────────

  private const BLOCK_NAME = 'Block A';
  private const RESIDENT_NAME = 'Ahmad Fauzi';
  private const PAYMENT_MONTH = '2024-01-01';
  private const PAYMENT_MONTH_2 = '2024-02-01';
  private const MONTH_INPUT = '2024-01';      // YYYY-MM format used in form inputs
  private const PAYMENT_AMOUNT = 500000;

  // ── Helpers ─────────────────────────────────────────────────────────────────

  private function createRoles(): array
  {
    $admin = Role::create(['name' => 'admin', 'label' => 'Admin', 'permissions' => []]);
    $treasurer = Role::create([
      'name' => 'treasurer',
      'label' => 'Treasurer',
      'permissions' => [
        'payments.view' => true,
        'payments.create' => true,
        'payments.edit' => true,
        'payments.approve' => true,
      ],
    ]);
    $resident = Role::create(['name' => 'resident', 'label' => 'Resident', 'permissions' => []]);

    return compact('admin', 'treasurer', 'resident');
  }

  private function makeAdmin(array $roles): User
  {
    return User::create([
      'name' => 'Admin',
      'username' => 'admin',
      'email' => 'admin@test.com',
      'password' => bcrypt('password'),
      'is_active' => true,
      'role_id' => $roles['admin']->id,
    ]);
  }

  private function makeTreasurer(array $roles): User
  {
    return User::create([
      'name' => 'Treasurer',
      'username' => 'treasurer',
      'email' => 'treasurer@test.com',
      'password' => bcrypt('password'),
      'is_active' => true,
      'role_id' => $roles['treasurer']->id,
    ]);
  }

  /** Shared helper: create a Block + Resident for tests that need one. */
  private function makeResidentInBlock(string $fullname = 'Budi'): array
  {
    $block = Block::create(['name' => self::BLOCK_NAME, 'is_active' => true]);
    $resident = Resident::create([
      'block_id' => $block->id,
      'unit_number' => 'A-101',
      'fullname' => $fullname,
      'is_active' => true,
    ]);

    return compact('block', 'resident');
  }

  // ── Authorization ────────────────────────────────────────────────────────────

  /** @test */
  public function guests_are_redirected_from_payments_page()
  {
    $this->get(route('payments.index'))->assertRedirect(route('login'));
  }

  /** @test */
  public function authenticated_user_can_view_payments_page()
  {
    $roles = $this->createRoles();
    $admin = $this->makeAdmin($roles);

    $this->actingAs($admin)->get(route('payments.index'))->assertOk();
  }

  // ── Index / Listing ──────────────────────────────────────────────────────────

  /** @test */
  public function payments_page_shows_existing_records()
  {
    $roles = $this->createRoles();
    $admin = $this->makeAdmin($roles);
    $block = Block::create(['name' => self::BLOCK_NAME, 'is_active' => true]);
    $resident = Resident::create([
      'block_id' => $block->id,
      'unit_number' => 'A-101',
      'fullname' => self::RESIDENT_NAME,
      'is_active' => true,
    ]);

    PaymentRecord::create([
      'resident_id' => $resident->id,
      'payment_month' => self::PAYMENT_MONTH,
      'amount' => self::PAYMENT_AMOUNT,
      'status' => 'approved',
    ]);

    $response = $this->actingAs($admin)->get(route('payments.index'));
    $response->assertOk()->assertSee(self::RESIDENT_NAME);
  }

  /** @test */
  public function payments_can_be_searched_by_resident_name()
  {
    $roles = $this->createRoles();
    $admin = $this->makeAdmin($roles);
    $block = Block::create(['name' => self::BLOCK_NAME, 'is_active' => true]);

    $r1 = Resident::create(['block_id' => $block->id, 'unit_number' => 'A-101', 'fullname' => self::RESIDENT_NAME, 'is_active' => true]);
    $r2 = Resident::create(['block_id' => $block->id, 'unit_number' => 'A-102', 'fullname' => 'Siti Rahayu', 'is_active' => true]);

    PaymentRecord::create(['resident_id' => $r1->id, 'payment_month' => self::PAYMENT_MONTH, 'amount' => self::PAYMENT_AMOUNT, 'status' => 'pending']);
    PaymentRecord::create(['resident_id' => $r2->id, 'payment_month' => self::PAYMENT_MONTH, 'amount' => self::PAYMENT_AMOUNT, 'status' => 'pending']);

    $response = $this->actingAs($admin)->get(route('payments.index', ['search' => 'Ahmad']));
    $response->assertOk();

    // Check view data: only Ahmad's payment should be in the paginator, not Siti's
    $payments = $response->viewData('payments');
    $names = collect(iterator_to_array($payments->getIterator()))->pluck('resident.fullname');
    $this->assertContains(self::RESIDENT_NAME, $names);
    $this->assertNotContains('Siti Rahayu', $names);
  }

  /** @test */
  public function payments_can_be_filtered_by_status()
  {
    $roles = $this->createRoles();
    $admin = $this->makeAdmin($roles);
    $block = Block::create(['name' => self::BLOCK_NAME, 'is_active' => true]);
    $resident = Resident::create(['block_id' => $block->id, 'unit_number' => 'A-101', 'fullname' => 'Ahmad', 'is_active' => true]);

    PaymentRecord::create(['resident_id' => $resident->id, 'payment_month' => self::PAYMENT_MONTH, 'amount' => self::PAYMENT_AMOUNT, 'status' => 'approved']);
    PaymentRecord::create(['resident_id' => $resident->id, 'payment_month' => self::PAYMENT_MONTH_2, 'amount' => self::PAYMENT_AMOUNT, 'status' => 'pending']);

    $response = $this->actingAs($admin)->get(route('payments.index', ['status' => 'approved']));
    $response->assertOk();
    $this->assertCount(1, $response->viewData('payments'));
  }

  // ── Store (Create) ────────────────────────────────────────────────────────────

  /** @test */
  public function admin_can_create_a_single_month_payment()
  {
    $roles = $this->createRoles();
    $admin = $this->makeAdmin($roles);
    ['resident' => $resident] = $this->makeResidentInBlock();

    $this->actingAs($admin)->post(route('payments.store'), [
      'resident_id' => $resident->id,
      'months' => [self::MONTH_INPUT],
      'amount' => self::PAYMENT_AMOUNT,
      'status' => 'pending',
    ]);

    $record = PaymentRecord::where('resident_id', $resident->id)->first();
    $this->assertNotNull($record, 'Payment record was not created.');
    $this->assertEquals(self::PAYMENT_MONTH, \Carbon\Carbon::parse($record->payment_month)->format('Y-m-d'));
    $this->assertEquals('pending', $record->status);
    $this->assertEquals(self::PAYMENT_AMOUNT, (int) $record->amount);
  }

  /** @test */
  public function admin_can_create_multi_month_payment_with_shared_batch_id()
  {
    $roles = $this->createRoles();
    $admin = $this->makeAdmin($roles);
    ['resident' => $resident] = $this->makeResidentInBlock();

    $this->actingAs($admin)->post(route('payments.store'), [
      'resident_id' => $resident->id,
      'months' => ['2024-01', '2024-02', '2024-03'],
      'amount' => self::PAYMENT_AMOUNT,
      'status' => 'pending',
    ]);

    $records = PaymentRecord::where('resident_id', $resident->id)->get();
    $this->assertCount(3, $records);
    // All records share the same batch_id
    $this->assertEquals(1, $records->pluck('batch_id')->unique()->count());
  }

  /** @test */
  public function store_skips_already_existing_months()
  {
    $roles = $this->createRoles();
    $admin = $this->makeAdmin($roles);
    ['resident' => $resident] = $this->makeResidentInBlock();

    // Pre-create payment for January
    PaymentRecord::create(['resident_id' => $resident->id, 'payment_month' => self::PAYMENT_MONTH, 'amount' => self::PAYMENT_AMOUNT, 'status' => 'pending']);

    // Try submitting the same month again
    $this->actingAs($admin)->post(route('payments.store'), [
      'resident_id' => $resident->id,
      'months' => [self::MONTH_INPUT],
      'amount' => self::PAYMENT_AMOUNT,
      'status' => 'pending',
    ]);

    // No duplicate should have been created
    $this->assertCount(1, PaymentRecord::where('resident_id', $resident->id)->get());
  }

  /** @test */
  public function store_requires_resident_id()
  {
    $roles = $this->createRoles();
    $admin = $this->makeAdmin($roles);

    $response = $this->actingAs($admin)->post(route('payments.store'), [
      'resident_id' => '',
      'months' => [self::MONTH_INPUT],
      'amount' => self::PAYMENT_AMOUNT,
      'status' => 'pending',
    ]);

    $response->assertSessionHasErrors('resident_id');
  }

  /** @test */
  public function store_requires_at_least_one_month()
  {
    $roles = $this->createRoles();
    $admin = $this->makeAdmin($roles);
    ['resident' => $resident] = $this->makeResidentInBlock();

    $response = $this->actingAs($admin)->post(route('payments.store'), [
      'resident_id' => $resident->id,
      'months' => [],
      'amount' => self::PAYMENT_AMOUNT,
      'status' => 'pending',
    ]);

    $response->assertSessionHasErrors('months');
  }

  /** @test */
  public function store_requires_valid_status()
  {
    $roles = $this->createRoles();
    $admin = $this->makeAdmin($roles);
    ['resident' => $resident] = $this->makeResidentInBlock();

    $response = $this->actingAs($admin)->post(route('payments.store'), [
      'resident_id' => $resident->id,
      'months' => [self::MONTH_INPUT],
      'amount' => self::PAYMENT_AMOUNT,
      'status' => 'invalid_status',
    ]);

    $response->assertSessionHasErrors('status');
  }

  // ── Approve ───────────────────────────────────────────────────────────────────

  /** @test */
  public function treasurer_can_approve_a_payment()
  {
    $roles = $this->createRoles();
    $treasurer = $this->makeTreasurer($roles);
    ['resident' => $resident] = $this->makeResidentInBlock();

    $payment = PaymentRecord::create([
      'resident_id' => $resident->id,
      'payment_month' => self::PAYMENT_MONTH,
      'amount' => self::PAYMENT_AMOUNT,
      'status' => 'pending',
    ]);

    $response = $this->actingAs($treasurer)->patch(route('payments.approve', $payment));

    $response->assertRedirect(route('payments.index'))->assertSessionHas('success');
    $this->assertDatabaseHas('payment_records', [
      'id' => $payment->id,
      'status' => 'approved',
    ]);
  }

  /** @test */
  public function approve_sets_approved_by_and_approved_at()
  {
    $roles = $this->createRoles();
    $treasurer = $this->makeTreasurer($roles);
    ['resident' => $resident] = $this->makeResidentInBlock();

    $payment = PaymentRecord::create([
      'resident_id' => $resident->id,
      'payment_month' => self::PAYMENT_MONTH,
      'amount' => self::PAYMENT_AMOUNT,
      'status' => 'pending',
    ]);

    $this->actingAs($treasurer)->patch(route('payments.approve', $payment));

    $payment->refresh();
    $this->assertEquals('approved', $payment->status);
    $this->assertEquals($treasurer->id, $payment->approved_by);
    $this->assertNotNull($payment->approved_at);
  }

  // ── Reject ────────────────────────────────────────────────────────────────────

  /** @test */
  public function treasurer_can_reject_a_payment_with_reason()
  {
    $roles = $this->createRoles();
    $treasurer = $this->makeTreasurer($roles);
    ['resident' => $resident] = $this->makeResidentInBlock();

    $payment = PaymentRecord::create([
      'resident_id' => $resident->id,
      'payment_month' => self::PAYMENT_MONTH,
      'amount' => self::PAYMENT_AMOUNT,
      'status' => 'pending',
    ]);

    $response = $this->actingAs($treasurer)->patch(route('payments.reject', $payment), [
      'rejection_reason' => 'Bukti pembayaran tidak jelas.',
    ]);

    $response->assertRedirect(route('payments.index'))->assertSessionHas('success');
    $this->assertDatabaseHas('payment_records', [
      'id' => $payment->id,
      'status' => 'rejected',
    ]);
  }

  /** @test */
  public function reject_requires_reason_of_at_least_10_characters()
  {
    $roles = $this->createRoles();
    $treasurer = $this->makeTreasurer($roles);
    ['resident' => $resident] = $this->makeResidentInBlock();

    $payment = PaymentRecord::create([
      'resident_id' => $resident->id,
      'payment_month' => self::PAYMENT_MONTH,
      'amount' => self::PAYMENT_AMOUNT,
      'status' => 'pending',
    ]);

    $response = $this->actingAs($treasurer)->patch(route('payments.reject', $payment), [
      'rejection_reason' => 'Short',
    ]);

    $response->assertSessionHasErrors('rejection_reason');
  }

  /** @test */
  public function reject_requires_a_reason()
  {
    $roles = $this->createRoles();
    $treasurer = $this->makeTreasurer($roles);
    ['resident' => $resident] = $this->makeResidentInBlock();

    $payment = PaymentRecord::create([
      'resident_id' => $resident->id,
      'payment_month' => self::PAYMENT_MONTH,
      'amount' => self::PAYMENT_AMOUNT,
      'status' => 'pending',
    ]);

    $response = $this->actingAs($treasurer)->patch(route('payments.reject', $payment), [
      'rejection_reason' => '',
    ]);

    $response->assertSessionHasErrors('rejection_reason');
  }

  // ── Destroy ───────────────────────────────────────────────────────────────────

  /** @test */
  public function admin_can_delete_a_pending_payment()
  {
    $roles = $this->createRoles();
    $admin = $this->makeAdmin($roles);
    ['resident' => $resident] = $this->makeResidentInBlock();

    $payment = PaymentRecord::create([
      'resident_id' => $resident->id,
      'payment_month' => self::PAYMENT_MONTH,
      'amount' => self::PAYMENT_AMOUNT,
      'status' => 'pending',
    ]);

    $response = $this->actingAs($admin)->delete(route('payments.destroy', $payment));

    $response->assertRedirect(route('payments.index'))->assertSessionHas('success');
    $this->assertDatabaseMissing('payment_records', ['id' => $payment->id]);
  }

  /** @test */
  public function admin_cannot_delete_an_approved_payment()
  {
    $roles = $this->createRoles();
    $admin = $this->makeAdmin($roles);
    ['resident' => $resident] = $this->makeResidentInBlock();

    $payment = PaymentRecord::create([
      'resident_id' => $resident->id,
      'payment_month' => self::PAYMENT_MONTH,
      'amount' => self::PAYMENT_AMOUNT,
      'status' => 'approved',
    ]);

    $this->actingAs($admin)->delete(route('payments.destroy', $payment))
      ->assertRedirect(route('payments.index'))
      ->assertSessionHas('error');

    $this->assertDatabaseHas('payment_records', ['id' => $payment->id]);
  }

  /** @test */
  public function non_admin_cannot_delete_payments()
  {
    $roles = $this->createRoles();
    $treasurer = $this->makeTreasurer($roles);
    ['resident' => $resident] = $this->makeResidentInBlock();

    $payment = PaymentRecord::create([
      'resident_id' => $resident->id,
      'payment_month' => self::PAYMENT_MONTH,
      'amount' => self::PAYMENT_AMOUNT,
      'status' => 'pending',
    ]);

    // Treasurer lacks 'payments.delete' permission — middleware returns 403
    $this->actingAs($treasurer)->delete(route('payments.destroy', $payment))
      ->assertForbidden();

    $this->assertDatabaseHas('payment_records', ['id' => $payment->id]);
  }

  /** @test */
  public function deleting_one_record_in_a_batch_deletes_entire_batch()
  {
    $roles = $this->createRoles();
    $admin = $this->makeAdmin($roles);
    ['resident' => $resident] = $this->makeResidentInBlock();

    $batchId = 'test-batch-uuid-001';
    $p1 = PaymentRecord::create(['resident_id' => $resident->id, 'payment_month' => self::PAYMENT_MONTH, 'amount' => self::PAYMENT_AMOUNT, 'status' => 'pending', 'batch_id' => $batchId]);
    $p2 = PaymentRecord::create(['resident_id' => $resident->id, 'payment_month' => self::PAYMENT_MONTH_2, 'amount' => self::PAYMENT_AMOUNT, 'status' => 'pending', 'batch_id' => $batchId]);

    $this->actingAs($admin)->delete(route('payments.destroy', $p1));

    $this->assertDatabaseMissing('payment_records', ['id' => $p1->id]);
    $this->assertDatabaseMissing('payment_records', ['id' => $p2->id]);
  }

  // ── Batch Approve/Reject ──────────────────────────────────────────────────────

  /** @test */
  public function treasurer_can_approve_a_batch_of_payments()
  {
    $roles = $this->createRoles();
    $treasurer = $this->makeTreasurer($roles);
    ['resident' => $resident] = $this->makeResidentInBlock();

    $batchId = 'batch-approve-test';
    PaymentRecord::create(['resident_id' => $resident->id, 'payment_month' => self::PAYMENT_MONTH, 'amount' => self::PAYMENT_AMOUNT, 'status' => 'pending', 'batch_id' => $batchId]);
    PaymentRecord::create(['resident_id' => $resident->id, 'payment_month' => self::PAYMENT_MONTH_2, 'amount' => self::PAYMENT_AMOUNT, 'status' => 'pending', 'batch_id' => $batchId]);

    // Route: POST /payments/batch/{batchId}/approve  →  name: payments.batch.approve
    $this->actingAs($treasurer)
      ->post(route('payments.batch.approve', $batchId))
      ->assertRedirect(route('payments.index'))
      ->assertSessionHas('success');

    $this->assertEquals(2, PaymentRecord::where('batch_id', $batchId)->where('status', 'approved')->count());
  }
}
