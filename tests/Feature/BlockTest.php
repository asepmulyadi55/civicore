<?php

namespace Tests\Feature;

use App\Models\Block;
use App\Models\Resident;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BlockTest extends TestCase
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

  // ── Authorization ────────────────────────────────────────────────────────────

  /** @test */
  public function guests_are_redirected_from_blocks_page()
  {
    $this->get(route('blocks.index'))->assertRedirect(route('login'));
  }

  /** @test */
  public function authenticated_user_can_view_blocks_page()
  {
    $admin = $this->createAdminUser();
    $this->actingAs($admin)->get(route('blocks.index'))->assertOk()->assertViewIs('blocks');
  }

  // ── Index ─────────────────────────────────────────────────────────────────────

  /** @test */
  public function blocks_page_shows_existing_blocks()
  {
    $admin = $this->createAdminUser();
    Block::create(['name' => 'Block A', 'is_active' => true]);
    Block::create(['name' => 'Block B', 'is_active' => true]);

    $this->actingAs($admin)->get(route('blocks.index'))
      ->assertSee('Block A')->assertSee('Block B');
  }

  // ── Store ─────────────────────────────────────────────────────────────────────

  /** @test */
  public function admin_can_create_a_new_block()
  {
    $admin = $this->createAdminUser();

    $response = $this->actingAs($admin)->post(route('blocks.store'), [
      'name' => 'Block C',
      'description' => 'The third block.',
    ]);

    $response->assertRedirect(route('blocks.index'))->assertSessionHas('success');
    $this->assertDatabaseHas('blocks', ['name' => 'Block C', 'is_active' => true]);
  }

  /** @test */
  public function store_requires_a_name()
  {
    $admin = $this->createAdminUser();

    $this->actingAs($admin)->post(route('blocks.store'), ['name' => ''])
      ->assertSessionHasErrors('name');
  }

  /** @test */
  public function store_requires_unique_block_name()
  {
    $admin = $this->createAdminUser();
    Block::create(['name' => 'Block A', 'is_active' => true]);

    $this->actingAs($admin)->post(route('blocks.store'), ['name' => 'Block A'])
      ->assertSessionHasErrors('name');
  }

  // ── Update ────────────────────────────────────────────────────────────────────

  /** @test */
  public function admin_can_update_a_block()
  {
    $admin = $this->createAdminUser();
    $block = Block::create(['name' => 'Old Block', 'is_active' => true]);

    $response = $this->actingAs($admin)->patch(route('blocks.update', $block), [
      'name' => 'Updated Block',
      'is_active' => true,
    ]);

    $response->assertRedirect(route('blocks.index'))->assertSessionHas('success');
    $this->assertDatabaseHas('blocks', ['id' => $block->id, 'name' => 'Updated Block']);
  }

  /** @test */
  public function update_requires_unique_name_ignoring_own()
  {
    $admin = $this->createAdminUser();
    $block1 = Block::create(['name' => 'Block A', 'is_active' => true]);
    $block2 = Block::create(['name' => 'Block B', 'is_active' => true]);

    // Updating block2 to use its own name is allowed
    $this->actingAs($admin)->patch(route('blocks.update', $block2), [
      'name' => 'Block B',
      'is_active' => true,
    ])->assertSessionHas('success');

    // Updating block2 to use block1's name fails
    $this->actingAs($admin)->patch(route('blocks.update', $block2), [
      'name' => 'Block A',
      'is_active' => true,
    ])->assertSessionHasErrors('name');
  }

  /** @test */
  public function admin_can_deactivate_a_block()
  {
    $admin = $this->createAdminUser();
    $block = Block::create(['name' => 'Block A', 'is_active' => true]);

    $this->actingAs($admin)->patch(route('blocks.update', $block), [
      'name' => 'Block A',
      'is_active' => false,
    ]);

    $this->assertDatabaseHas('blocks', ['id' => $block->id, 'is_active' => false]);
  }

  // ── Destroy ───────────────────────────────────────────────────────────────────

  /** @test */
  public function admin_can_delete_a_block_with_no_residents()
  {
    $admin = $this->createAdminUser();
    $block = Block::create(['name' => 'Empty Block', 'is_active' => true]);

    $this->actingAs($admin)->delete(route('blocks.destroy', $block))
      ->assertRedirect(route('blocks.index'))->assertSessionHas('success');

    $this->assertDatabaseMissing('blocks', ['id' => $block->id]);
  }

  /** @test */
  public function block_with_residents_cannot_be_deleted()
  {
    $admin = $this->createAdminUser();
    $block = Block::create(['name' => 'Block A', 'is_active' => true]);
    $resident = Resident::create([
      'block_id' => $block->id,
      'unit_number' => 'A-101',
      'fullname' => 'Ahmad',
      'is_active' => true,
    ]);

    $this->actingAs($admin)->delete(route('blocks.destroy', $block))
      ->assertRedirect(route('blocks.index'))->assertSessionHas('error');

    $this->assertDatabaseHas('blocks', ['id' => $block->id]);
  }
}
