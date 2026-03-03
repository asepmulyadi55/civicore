<?php

namespace Tests\Feature;

use App\Models\Block;
use App\Models\Resident;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegisterTest extends TestCase
{
  use RefreshDatabase;

  /** @test */
  public function registration_page_can_be_displayed()
  {
    $response = $this->get('/register');

    $response->assertStatus(200);
    $response->assertViewIs('register');
  }

  /** @test */
  public function user_can_register_with_valid_data()
  {
    // RegisterController requires the email to exist as a resident first
    $block = Block::create(['name' => 'Block A', 'is_active' => true]);
    Resident::create([
      'block_id' => $block->id,
      'unit_number' => 'A-101',
      'fullname' => 'John Doe',
      'email' => 'john@example.com',
      'is_active' => true
    ]);

    $response = $this->post('/register', [
      'fullname' => 'John Doe',
      'username' => 'johndoe',
      'email' => 'john@example.com',
      'password' => 'password123',
    ]);

    $response->assertRedirect('/');
    $response->assertSessionHas('success', 'Registration successful! Please wait for admin approval before logging in.');

    $this->assertDatabaseHas('users', [
      'name' => 'John Doe',
      'username' => 'johndoe',
      'email' => 'john@example.com',
      'is_active' => false,
    ]);
  }

  /** @test */
  public function new_user_is_inactive_by_default()
  {
    $block = Block::create(['name' => 'Block A', 'is_active' => true]);
    Resident::create([
      'block_id' => $block->id,
      'unit_number' => 'A-101',
      'fullname' => 'John Doe',
      'email' => 'john@example.com',
      'is_active' => true
    ]);

    $this->post('/register', [
      'fullname' => 'John Doe',
      'username' => 'johndoe',
      'email' => 'john@example.com',
      'password' => 'password123',
    ]);

    $user = User::where('email', 'john@example.com')->first();
    $this->assertEquals(0, $user->is_active);
  }

  /** @test */
  public function registration_validates_required_fullname()
  {
    $response = $this->post('/register', [
      'fullname' => '',
      'username' => 'johndoe',
      'email' => 'john@example.com',
      'password' => 'password123',
    ]);

    $response->assertSessionHasErrors(['fullname']);
    $this->assertDatabaseCount('users', 0);
  }

  /** @test */
  public function registration_validates_required_username()
  {
    $response = $this->post('/register', [
      'fullname' => 'John Doe',
      'username' => '',
      'email' => 'john@example.com',
      'password' => 'password123',
    ]);

    $response->assertSessionHasErrors(['username']);
    $this->assertDatabaseCount('users', 0);
  }

  /** @test */
  public function registration_validates_unique_username()
  {
    User::factory()->create(['username' => 'johndoe']);

    $response = $this->post('/register', [
      'fullname' => 'John Doe',
      'username' => 'johndoe',
      'email' => 'john@example.com',
      'password' => 'password123',
    ]);

    $response->assertSessionHasErrors(['username']);
    $this->assertDatabaseCount('users', 1);
  }

  /** @test */
  public function registration_validates_required_email()
  {
    $response = $this->post('/register', [
      'fullname' => 'John Doe',
      'username' => 'johndoe',
      'email' => '',
      'password' => 'password123',
    ]);

    $response->assertSessionHasErrors(['email']);
    $this->assertDatabaseCount('users', 0);
  }

  /** @test */
  public function registration_validates_email_format()
  {
    $response = $this->post('/register', [
      'fullname' => 'John Doe',
      'username' => 'johndoe',
      'email' => 'invalid-email',
      'password' => 'password123',
    ]);

    $response->assertSessionHasErrors(['email']);
    $this->assertDatabaseCount('users', 0);
  }

  /** @test */
  public function registration_validates_unique_email()
  {
    User::factory()->create(['email' => 'john@example.com']);

    $response = $this->post('/register', [
      'fullname' => 'John Doe',
      'username' => 'johndoe',
      'email' => 'john@example.com',
      'password' => 'password123',
    ]);

    $response->assertSessionHasErrors(['email']);
    $this->assertDatabaseCount('users', 1);
  }

  /** @test */
  public function registration_validates_password_minimum_length()
  {
    $response = $this->post('/register', [
      'fullname' => 'John Doe',
      'username' => 'johndoe',
      'email' => 'john@example.com',
      'password' => '1234',
    ]);

    $response->assertSessionHasErrors(['password']);
    $this->assertDatabaseCount('users', 0);
  }

  /** @test */
  public function password_is_hashed_when_stored()
  {
    $block = Block::create(['name' => 'Block A', 'is_active' => true]);
    Resident::create([
      'block_id' => $block->id,
      'unit_number' => 'A-101',
      'fullname' => 'John Doe',
      'email' => 'john@example.com',
      'is_active' => true
    ]);

    $this->post('/register', [
      'fullname' => 'John Doe',
      'username' => 'johndoe',
      'email' => 'john@example.com',
      'password' => 'password123',
    ]);

    $user = User::where('email', 'john@example.com')->first();

    $this->assertNotEquals('password123', $user->password);
    $this->assertTrue(\Hash::check('password123', $user->password));
  }
}
