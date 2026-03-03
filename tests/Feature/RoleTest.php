<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleTest extends TestCase
{
  use RefreshDatabase;

  // ── Helpers ─────────────────────────────────────────────────────────────────

  private function createAdminUser(): User
  {
    $adminRole = Role::create(['name' => 'admin', 'label' => 'Admin', 'permissions' => []]);
    return User::create([
      'name' => 'Admin',
      'username' => 'admin',
      'email' => 'admin@test.com',
      'password' => bcrypt('password'),
      'is_active' => true,
      'role_id' => $adminRole->id,
    ]);
  }

  private function createTreasurerUser(): User
  {
    $role = Role::create([
      'name' => 'treasurer',
      'label' => 'Treasurer',
      'permissions' => [
        'roles.view' => true,
      ]
    ]);
    return User::create([
      'name' => 'Treasurer',
      'username' => 'treasurer',
      'email' => 'treasurer@test.com',
      'password' => bcrypt('password'),
      'is_active' => true,
      'role_id' => $role->id,
    ]);
  }

  // ── Authorization ────────────────────────────────────────────────────────────

  /** @test */
  public function guests_are_redirected_from_roles_page()
  {
    $this->get(route('roles.index'))->assertRedirect(route('login'));
  }

  /** @test */
  public function authenticated_user_can_view_roles_page()
  {
    $admin = $this->createAdminUser();
    $this->actingAs($admin)->get(route('roles.index'))->assertOk();
  }

  // ── Index ─────────────────────────────────────────────────────────────────────

  /** @test */
  public function roles_page_shows_all_roles()
  {
    $admin = $this->createAdminUser();
    Role::create(['name' => 'treasurer', 'label' => 'Treasurer', 'permissions' => []]);

    $response = $this->actingAs($admin)->get(route('roles.index'));
    $response->assertOk()->assertSee('Admin')->assertSee('Treasurer');
  }

  // ── Store ─────────────────────────────────────────────────────────────────────

  /** @test */
  public function admin_can_create_a_new_role()
  {
    $admin = $this->createAdminUser();

    $response = $this->actingAs($admin)->post(route('roles.store'), [
      'name' => 'block_coordinator',
      'label' => 'Block Coordinator',
      'description' => 'Manages a specific block.',
    ]);

    $response->assertRedirect()->assertSessionHas('success');
    $this->assertDatabaseHas('roles', [
      'name' => 'block_coordinator',
      'label' => 'Block Coordinator',
    ]);
  }

  /** @test */
  public function store_requires_name()
  {
    $admin = $this->createAdminUser();

    $this->actingAs($admin)->post(route('roles.store'), [
      'name' => '',
      'label' => 'Test Role',
    ])->assertSessionHasErrors('name');
  }

  /** @test */
  public function store_requires_label()
  {
    $admin = $this->createAdminUser();

    $this->actingAs($admin)->post(route('roles.store'), [
      'name' => 'test_role',
      'label' => '',
    ])->assertSessionHasErrors('label');
  }

  /** @test */
  public function store_requires_unique_name()
  {
    $admin = $this->createAdminUser();
    Role::create(['name' => 'existing_role', 'label' => 'Existing', 'permissions' => []]);

    $this->actingAs($admin)->post(route('roles.store'), [
      'name' => 'existing_role',
      'label' => 'Duplicate',
    ])->assertSessionHasErrors('name');
  }

  /** @test */
  public function store_name_must_be_alpha_dash()
  {
    $admin = $this->createAdminUser();

    $this->actingAs($admin)->post(route('roles.store'), [
      'name' => 'invalid name with spaces',
      'label' => 'Test',
    ])->assertSessionHasErrors('name');
  }

  // ── Update ────────────────────────────────────────────────────────────────────

  /** @test */
  public function admin_can_update_a_non_admin_role()
  {
    $admin = $this->createAdminUser();
    $role = Role::create(['name' => 'treasurer', 'label' => 'Treasurer', 'permissions' => []]);

    $response = $this->actingAs($admin)->patch(route('roles.update', $role), [
      'label' => 'Treasurer Updated',
      'description' => 'Updated description.',
    ]);

    $response->assertRedirect()->assertSessionHas('success');
    $this->assertDatabaseHas('roles', ['id' => $role->id, 'label' => 'Treasurer Updated']);
  }

  /** @test */
  public function admin_role_cannot_be_updated()
  {
    $admin = $this->createAdminUser();
    $adminRole = $admin->role;

    $this->actingAs($admin)->patch(route('roles.update', $adminRole), [
      'label' => 'Hacked Admin',
    ])->assertSessionHas('error');

    $this->assertDatabaseHas('roles', ['id' => $adminRole->id, 'label' => 'Admin']);
  }

  // ── Update Permissions ────────────────────────────────────────────────────────

  /** @test */
  public function admin_can_update_permissions_for_a_role()
  {
    $admin = $this->createAdminUser();
    $role = Role::create(['name' => 'treasurer', 'label' => 'Treasurer', 'permissions' => []]);

    // Route is PATCH — dots in form keys become underscores in PHP
    $response = $this->actingAs($admin)->patch(route('roles.permissions', $role), [
      'dashboard_view' => '1',
      'payments_view' => '1',
      'payments_approve' => '1',
    ]);

    $response->assertRedirect()->assertSessionHas('success');

    $role->refresh();
    $this->assertTrue($role->permissions['dashboard.view'] ?? false);
    $this->assertTrue($role->permissions['payments.view'] ?? false);
    $this->assertTrue($role->permissions['payments.approve'] ?? false);
    // Keys not submitted should be false
    $this->assertFalse($role->permissions['residents.view'] ?? false);
  }

  /** @test */
  public function admin_role_permissions_cannot_be_updated()
  {
    $admin = $this->createAdminUser();
    $adminRole = $admin->role;

    $this->actingAs($admin)->patch(route('roles.permissions', $adminRole), [
      'dashboard_view' => '1',
    ])->assertSessionHas('error');
  }

  // ── Destroy ───────────────────────────────────────────────────────────────────

  /** @test */
  public function admin_can_delete_a_role_with_no_users()
  {
    $admin = $this->createAdminUser();
    $role = Role::create(['name' => 'empty_role', 'label' => 'Empty Role', 'permissions' => []]);

    $this->actingAs($admin)->delete(route('roles.destroy', $role))
      ->assertRedirect()->assertSessionHas('success');

    $this->assertDatabaseMissing('roles', ['id' => $role->id]);
  }

  /** @test */
  public function admin_role_cannot_be_deleted()
  {
    $admin = $this->createAdminUser();
    $adminRole = $admin->role;

    $this->actingAs($admin)->delete(route('roles.destroy', $adminRole))
      ->assertSessionHas('error');

    $this->assertDatabaseHas('roles', ['id' => $adminRole->id]);
  }

  /** @test */
  public function role_with_assigned_users_cannot_be_deleted()
  {
    $admin = $this->createAdminUser();
    $otherRole = Role::create(['name' => 'secretary', 'label' => 'Secretary', 'permissions' => []]);

    // Assign a user to this role
    User::create([
      'name' => 'Some User',
      'username' => 'someuser',
      'email' => 'some@test.com',
      'password' => bcrypt('password'),
      'is_active' => true,
      'role_id' => $otherRole->id,
    ]);

    $this->actingAs($admin)->delete(route('roles.destroy', $otherRole))
      ->assertSessionHas('error');

    $this->assertDatabaseHas('roles', ['id' => $otherRole->id]);
  }
}
