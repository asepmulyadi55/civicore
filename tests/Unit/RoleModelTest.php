<?php

namespace Tests\Unit;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Unit tests for Role model logic and User permission/role checks.
 * These test the model methods directly without HTTP layer.
 */
class RoleModelTest extends TestCase
{
  use RefreshDatabase;

  // ── Role::hasPermission ───────────────────────────────────────────────────────

  /** @test */
  public function admin_role_always_has_every_permission()
  {
    $adminRole = Role::create(['name' => 'admin', 'label' => 'Admin', 'permissions' => []]);

    foreach (Role::$availablePermissions as $module => $actions) {
      foreach ($actions as $action) {
        $this->assertTrue(
          $adminRole->hasPermission("{$module}.{$action}"),
          "Admin should have {$module}.{$action}"
        );
      }
    }
  }

  /** @test */
  public function non_admin_role_returns_true_for_granted_permission()
  {
    $role = Role::create([
      'name' => 'treasurer',
      'label' => 'Treasurer',
      'permissions' => ['payments.approve' => true, 'payments.view' => true],
    ]);

    $this->assertTrue($role->hasPermission('payments.approve'));
    $this->assertTrue($role->hasPermission('payments.view'));
  }

  /** @test */
  public function non_admin_role_returns_false_for_ungranted_permission()
  {
    $role = Role::create([
      'name' => 'treasurer',
      'label' => 'Treasurer',
      'permissions' => ['payments.view' => true],
    ]);

    $this->assertFalse($role->hasPermission('residents.delete'));
    $this->assertFalse($role->hasPermission('roles.create'));
  }

  /** @test */
  public function non_admin_role_returns_false_for_explicitly_false_permission()
  {
    $role = Role::create([
      'name' => 'treasurer',
      'label' => 'Treasurer',
      'permissions' => ['payments.view' => false],
    ]);

    $this->assertFalse($role->hasPermission('payments.view'));
  }

  /** @test */
  public function non_admin_role_returns_false_for_missing_permission_key()
  {
    $role = Role::create([
      'name' => 'treasurer',
      'label' => 'Treasurer',
      'permissions' => [],
    ]);

    $this->assertFalse($role->hasPermission('payments.view'));
  }

  // ── User::can (permission delegation) ────────────────────────────────────────

  /** @test */
  public function admin_user_has_all_permissions_via_can()
  {
    $adminRole = Role::create(['name' => 'admin', 'label' => 'Admin', 'permissions' => []]);
    $user = User::create([
      'name' => 'Admin',
      'username' => 'admin',
      'email' => 'admin@test.com',
      'password' => bcrypt('password'),
      'is_active' => true,
      'role_id' => $adminRole->id,
    ]);

    $this->assertTrue($user->can('payments.approve'));
    $this->assertTrue($user->can('residents.delete'));
    $this->assertTrue($user->can('roles.create'));
  }

  /** @test */
  public function user_can_returns_true_for_granted_permission()
  {
    $role = Role::create([
      'name' => 'treasurer',
      'label' => 'Treasurer',
      'permissions' => ['payments.approve' => true, 'payments.view' => true],
    ]);
    $user = User::create([
      'name' => 'Treasurer',
      'username' => 'treasurer',
      'email' => 'treasurer@test.com',
      'password' => bcrypt('password'),
      'is_active' => true,
      'role_id' => $role->id,
    ]);

    $this->assertTrue($user->can('payments.approve'));
    $this->assertTrue($user->can('payments.view'));
  }

  /** @test */
  public function user_can_returns_false_for_ungranted_permission()
  {
    $role = Role::create([
      'name' => 'treasurer',
      'label' => 'Treasurer',
      'permissions' => ['payments.view' => true],
    ]);
    $user = User::create([
      'name' => 'Treasurer',
      'username' => 'treasurer',
      'email' => 'treasurer@test.com',
      'password' => bcrypt('password'),
      'is_active' => true,
      'role_id' => $role->id,
    ]);

    $this->assertFalse($user->can('residents.delete'));
    $this->assertFalse($user->can('roles.create'));
  }

  /** @test */
  public function user_with_no_role_has_no_permissions()
  {
    $user = User::create([
      'name' => 'No Role',
      'username' => 'norole',
      'email' => 'norole@test.com',
      'password' => bcrypt('password'),
      'is_active' => true,
      'role_id' => null,
    ]);

    $this->assertFalse($user->can('payments.view'));
    $this->assertFalse($user->can('dashboard.view'));
  }

  // ── User role helper methods ───────────────────────────────────────────────────

  /** @test */
  public function is_admin_returns_true_for_admin_user()
  {
    $role = Role::create(['name' => 'admin', 'label' => 'Admin', 'permissions' => []]);
    $user = User::create([
      'name' => 'Admin',
      'username' => 'admin',
      'email' => 'admin@test.com',
      'password' => bcrypt('password'),
      'is_active' => true,
      'role_id' => $role->id,
    ]);

    $this->assertTrue($user->isAdmin());
  }

  /** @test */
  public function is_admin_returns_false_for_non_admin_user()
  {
    $role = Role::create(['name' => 'treasurer', 'label' => 'Treasurer', 'permissions' => []]);
    $user = User::create([
      'name' => 'Treasurer',
      'username' => 'treasurer',
      'email' => 'treasurer@test.com',
      'password' => bcrypt('password'),
      'is_active' => true,
      'role_id' => $role->id,
    ]);

    $this->assertFalse($user->isAdmin());
  }

  /** @test */
  public function is_resident_returns_true_for_resident_role()
  {
    $role = Role::create(['name' => 'resident', 'label' => 'Resident', 'permissions' => []]);
    $user = User::create([
      'name' => 'Resident',
      'username' => 'resuser',
      'email' => 'res@test.com',
      'password' => bcrypt('password'),
      'is_active' => true,
      'role_id' => $role->id,
    ]);

    $this->assertTrue($user->isHouseholder());
    $this->assertFalse($user->isAdmin());
  }

  /** @test */
  public function user_can_approve_payments_when_role_has_permission()
  {
    $role = Role::create([
      'name' => 'treasurer',
      'label' => 'Treasurer',
      'permissions' => ['payments.approve' => true],
    ]);
    $user = User::create([
      'name' => 'Treasurer',
      'username' => 'treasurer',
      'email' => 'treasurer@test.com',
      'password' => bcrypt('password'),
      'is_active' => true,
      'role_id' => $role->id,
    ]);

    $this->assertTrue($user->can('payments.approve'));
  }

  /** @test */
  public function user_cannot_approve_payments_when_role_lacks_permission()
  {
    $role = Role::create([
      'name' => 'resident',
      'label' => 'Resident',
      'permissions' => ['payments.approve' => false],
    ]);
    $user = User::create([
      'name' => 'Resident',
      'username' => 'resuser',
      'email' => 'res@test.com',
      'password' => bcrypt('password'),
      'is_active' => true,
      'role_id' => $role->id,
    ]);

    $this->assertFalse($user->can('payments.approve'));
  }
}

