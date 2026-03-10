<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LoginTest extends TestCase
{
  use RefreshDatabase;

  /** @test */
  public function login_page_can_be_displayed()
  {
    $response = $this->get('/');

    $response->assertStatus(200);
    $response->assertViewIs('login');
  }

  /** @test */
  public function user_can_login_with_username()
  {
    $user = User::factory()->create([
      'username' => 'testuser',
      'password' => Hash::make('password123'),
      'is_active' => true,
    ]);

    $response = $this->post('/login', [
      'username' => 'testuser',
      'password' => 'password123',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect('/dashboard');
    $response->assertSessionHas('success', fn($v) => str_starts_with($v, 'Welcome back,'));
  }

  /** @test */
  public function user_can_login_with_email()
  {
    $user = User::factory()->create([
      'email' => 'test@example.com',
      'password' => Hash::make('password123'),
      'is_active' => true,
    ]);

    $response = $this->post('/login', [
      'username' => 'test@example.com', // Using email in username field
      'password' => 'password123',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect('/dashboard');
  }

  /** @test */
  public function user_cannot_login_with_incorrect_password()
  {
    $user = User::factory()->create([
      'username' => 'testuser',
      'password' => Hash::make('password123'),
      'is_active' => true,
    ]);

    $response = $this->post('/login', [
      'username' => 'testuser',
      'password' => 'wrongpassword',
    ]);

    $this->assertGuest();
    $response->assertRedirect('/');
    $response->assertSessionHas('error', 'Invalid username or password. Please try again.');
  }

  /** @test */
  public function inactive_user_cannot_login()
  {
    $user = User::factory()->create([
      'username' => 'testuser',
      'password' => Hash::make('password123'),
      'is_active' => false, // Inactive account
    ]);

    $response = $this->post('/login', [
      'username' => 'testuser',
      'password' => 'password123',
    ]);

    $this->assertGuest();
    $response->assertRedirect('/');
    $response->assertSessionHas('error', 'Your account is pending admin approval.');
  }

  /** @test */
  public function login_validates_required_fields()
  {
    $response = $this->post('/login', [
      'username' => '',
      'password' => '',
    ]);

    $response->assertSessionHasErrors(['username', 'password']);
  }

  /** @test */
  public function remember_me_functionality_works()
  {
    $user = User::factory()->create([
      'username' => 'testuser',
      'password' => Hash::make('password123'),
      'is_active' => true,
    ]);

    $response = $this->post('/login', [
      'username' => 'testuser',
      'password' => 'password123',
      'remember' => 'on',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect('/dashboard');

    // Check remember token is set
    $this->assertNotNull(auth()->user()->remember_token);
  }

  /** @test */
  public function user_can_logout()
  {
    $user = User::factory()->create([
      'is_active' => true,
    ]);

    $this->actingAs($user);

    $response = $this->post('/logout');

    $this->assertGuest();
    $response->assertRedirect('/');
  }
}
