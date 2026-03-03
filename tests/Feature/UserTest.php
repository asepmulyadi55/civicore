<?php

namespace Tests\Feature;

use App\Models\Block;
use App\Models\Resident;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserTest extends TestCase
{
  use RefreshDatabase;

  // ── Helpers ─────────────────────────────────────────────────────────────────

  private function createAdminUser(): User
  {
    $role = Role::create(['name' => 'admin', 'label' => 'Admin', 'permissions' => []]);
    return User::create([
      'name' => 'Admin',
      'username' => 'admin',
      'email' => 'admin@test.com',
      'password' => bcrypt('password'),
      'is_active' => true,
      'role_id' => $role->id,
    ]);
  }

  private function createResidentRole(): Role
  {
    return Role::create(['name' => 'resident', 'label' => 'Resident', 'permissions' => []]);
  }

  // ── Authorization ────────────────────────────────────────────────────────────

  /** @test */
  public function guests_are_redirected_from_users_page()
  {
    $this->get(route('users.index'))->assertRedirect(route('login'));
  }

  /** @test */
  public function admin_can_view_users_page()
  {
    $admin = $this->createAdminUser();
    $this->actingAs($admin)->get(route('users.index'))->assertOk()->assertViewIs('users');
  }

  // ── Index / Search / Filter ───────────────────────────────────────────────────

  /** @test */
  public function users_page_shows_all_users()
  {
    $admin = $this->createAdminUser();
    $role = $this->createResidentRole();
    User::create([
      'name' => 'Siti Nurhaliza',
      'username' => 'siti',
      'email' => 'siti@test.com',
      'password' => bcrypt('password'),
      'is_active' => true,
      'role_id' => $role->id,
    ]);

    $this->actingAs($admin)->get(route('users.index'))
      ->assertSee('Admin')->assertSee('Siti Nurhaliza');
  }

  /** @test */
  public function users_page_can_be_searched_by_name()
  {
    $admin = $this->createAdminUser();
    $role = $this->createResidentRole();
    User::create([
      'name' => 'Budi Santoso',
      'username' => 'budi',
      'email' => 'budi@test.com',
      'password' => bcrypt('password'),
      'is_active' => true,
      'role_id' => $role->id,
    ]);

    $response = $this->actingAs($admin)->get(route('users.index', ['search' => 'Admin']));
    $response->assertSee('Admin')->assertDontSee('Budi Santoso');
  }

  /** @test */
  public function users_page_can_be_filtered_by_role()
  {
    $admin = $this->createAdminUser();
    $role = $this->createResidentRole();
    User::create([
      'name' => 'Resident User',
      'username' => 'resuser',
      'email' => 'res@test.com',
      'password' => bcrypt('password'),
      'is_active' => true,
      'role_id' => $role->id,
    ]);

    $response = $this->actingAs($admin)->get(route('users.index', ['role_id' => $role->id]));
    $response->assertOk();

    // Check view data: only residents should appear, not the admin
    $users = $response->viewData('users');
    $names = $users->pluck('name')->all();
    $this->assertContains('Resident User', $names);
    $this->assertNotContains('Admin', $names);
  }

  // ── Store (Create User) ───────────────────────────────────────────────────────

  /** @test */
  public function admin_can_create_a_new_user()
  {
    $admin = $this->createAdminUser();
    $role = $this->createResidentRole();

    $response = $this->actingAs($admin)->post(route('users.store'), [
      'name' => 'New User',
      'username' => 'newuser',
      'email' => 'newuser@test.com',
      'password' => 'password123',
      'role_id' => $role->id,
      'is_active' => true,
    ]);

    $response->assertRedirect()->assertSessionHas('success');
    $this->assertDatabaseHas('users', ['email' => 'newuser@test.com', 'username' => 'newuser']);
  }

  /** @test */
  public function store_password_is_hashed()
  {
    $admin = $this->createAdminUser();
    $role = $this->createResidentRole();

    $this->actingAs($admin)->post(route('users.store'), [
      'name' => 'New User',
      'username' => 'newuser',
      'email' => 'newuser@test.com',
      'password' => 'password123',
      'role_id' => $role->id,
    ]);

    $user = User::where('email', 'newuser@test.com')->first();
    $this->assertNotNull($user);
    $this->assertTrue(Hash::check('password123', $user->password));
  }

  /** @test */
  public function store_requires_name()
  {
    $admin = $this->createAdminUser();
    $role = $this->createResidentRole();

    $this->actingAs($admin)->post(route('users.store'), [
      'name' => '',
      'username' => 'newuser',
      'email' => 'newuser@test.com',
      'password' => 'password123',
      'role_id' => $role->id,
    ])->assertSessionHasErrors('name');
  }

  /** @test */
  public function store_requires_unique_username()
  {
    $admin = $this->createAdminUser();
    $role = $this->createResidentRole();

    $this->actingAs($admin)->post(route('users.store'), [
      'name' => 'Duplicate',
      'username' => 'admin',  // 'admin' already exists
      'email' => 'unique@test.com',
      'password' => 'password123',
      'role_id' => $role->id,
    ])->assertSessionHasErrors('username');
  }

  /** @test */
  public function store_requires_unique_email()
  {
    $admin = $this->createAdminUser();
    $role = $this->createResidentRole();

    $this->actingAs($admin)->post(route('users.store'), [
      'name' => 'Duplicate',
      'username' => 'uniqueuser',
      'email' => 'admin@test.com',  // already exists
      'password' => 'password123',
      'role_id' => $role->id,
    ])->assertSessionHasErrors('email');
  }

  /** @test */
  public function store_requires_password_of_at_least_8_characters()
  {
    $admin = $this->createAdminUser();
    $role = $this->createResidentRole();

    $this->actingAs($admin)->post(route('users.store'), [
      'name' => 'Test',
      'username' => 'testuser',
      'email' => 'test@test.com',
      'password' => 'short',
      'role_id' => $role->id,
    ])->assertSessionHasErrors('password');
  }

  // ── Update ────────────────────────────────────────────────────────────────────

  /** @test */
  public function admin_can_update_user_details()
  {
    $admin = $this->createAdminUser();
    $role = $this->createResidentRole();
    $userToUpdate = User::create([
      'name' => 'Old Name',
      'username' => 'olduser',
      'email' => 'old@test.com',
      'password' => bcrypt('password'),
      'is_active' => true,
      'role_id' => $role->id,
    ]);

    $response = $this->actingAs($admin)->patch(route('users.update', $userToUpdate), [
      'name' => 'New Name',
      'username' => 'newusername',
      'email' => 'new@test.com',
      'role_id' => $role->id,
    ]);

    $response->assertRedirect()->assertSessionHas('success');
    $this->assertDatabaseHas('users', [
      'id' => $userToUpdate->id,
      'name' => 'New Name',
      'email' => 'new@test.com',
    ]);
  }

  // ── Approve / Deactivate / Delete ─────────────────────────────────────────────

  /** @test */
  public function admin_can_approve_a_pending_user()
  {
    $admin = $this->createAdminUser();
    $role = $this->createResidentRole();
    $pendingUser = User::create([
      'name' => 'Pending',
      'username' => 'pending',
      'email' => 'pending@test.com',
      'password' => bcrypt('password'),
      'is_active' => false,
      'role_id' => $role->id,
    ]);

    $this->actingAs($admin)->patch(route('users.approve', $pendingUser))
      ->assertRedirect()->assertSessionHas('success');

    $this->assertDatabaseHas('users', ['id' => $pendingUser->id, 'is_active' => true]);
  }

  /** @test */
  public function admin_can_deactivate_an_active_user()
  {
    $admin = $this->createAdminUser();
    $role = $this->createResidentRole();
    $activeUser = User::create([
      'name' => 'Active',
      'username' => 'activeuser',
      'email' => 'active@test.com',
      'password' => bcrypt('password'),
      'is_active' => true,
      'role_id' => $role->id,
    ]);

    $this->actingAs($admin)->patch(route('users.deactivate', $activeUser))
      ->assertRedirect()->assertSessionHas('success');

    $this->assertDatabaseHas('users', ['id' => $activeUser->id, 'is_active' => false]);
  }

  /** @test */
  public function admin_can_delete_an_inactive_user()
  {
    $admin = $this->createAdminUser();
    $role = $this->createResidentRole();
    $toDelete = User::create([
      'name' => 'Delete Me',
      'username' => 'deleteme',
      'email' => 'delete@test.com',
      'password' => bcrypt('password'),
      'is_active' => false,
      'role_id' => $role->id,
    ]);

    $this->actingAs($admin)->delete(route('users.destroy', $toDelete))
      ->assertRedirect()->assertSessionHas('success');

    $this->assertDatabaseMissing('users', ['id' => $toDelete->id]);
  }

  /** @test */
  public function admin_cannot_delete_themselves()
  {
    $admin = $this->createAdminUser();

    $this->actingAs($admin)->delete(route('users.destroy', $admin))
      ->assertSessionHas('error');

    $this->assertDatabaseHas('users', ['id' => $admin->id]);
  }
}
