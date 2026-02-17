<?php

namespace Tests\Feature;

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
        $response = $this->post('/register', [
            'fullname' => 'John Doe',
            'username' => 'johndoe',
            'email' => 'john@example.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/');
        $response->assertSessionHas('success', 'Registration successful! Please wait for admin approval before logging in.');

        // Check user was created
        $this->assertDatabaseHas('users', [
            'name' => 'John Doe',
            'username' => 'johndoe',
            'email' => 'john@example.com',
            'is_active' => false, // Should be inactive by default
        ]);
    }

    /** @test */
    public function new_user_is_inactive_by_default()
    {
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

        $response->assertSessionHas('error', 'Please enter your full name.');
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

        $response->assertSessionHas('error', 'Please choose a username.');
        $this->assertDatabaseCount('users', 0);
    }

    /** @test */
    public function registration_validates_unique_username()
    {
        User::factory()->create([
            'username' => 'johndoe',
        ]);

        $response = $this->post('/register', [
            'fullname' => 'John Doe',
            'username' => 'johndoe', // Duplicate
            'email' => 'john@example.com',
            'password' => 'password123',
        ]);

        $response->assertSessionHas('error', 'This username is already taken. Please choose another.');
        $this->assertDatabaseCount('users', 1); // Only the first user
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

        $response->assertSessionHas('error', 'Please enter your email address.');
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

        $response->assertSessionHas('error', 'Please enter a valid email address.');
        $this->assertDatabaseCount('users', 0);
    }

    /** @test */
    public function registration_validates_unique_email()
    {
        User::factory()->create([
            'email' => 'john@example.com',
        ]);

        $response = $this->post('/register', [
            'fullname' => 'John Doe',
            'username' => 'johndoe',
            'email' => 'john@example.com', // Duplicate
            'password' => 'password123',
        ]);

        $response->assertSessionHas('error', 'This email is already registered. Please login instead.');
        $this->assertDatabaseCount('users', 1); // Only the first user
    }

    /** @test */
    public function registration_validates_password_minimum_length()
    {
        $response = $this->post('/register', [
            'fullname' => 'John Doe',
            'username' => 'johndoe',
            'email' => 'john@example.com',
            'password' => '1234', // Too short
        ]);

        $response->assertSessionHas('error', 'Password must be at least 8 characters.');
        $this->assertDatabaseCount('users', 0);
    }

    /** @test */
    public function password_is_hashed_when_stored()
    {
        $this->post('/register', [
            'fullname' => 'John Doe',
            'username' => 'johndoe',
            'email' => 'john@example.com',
            'password' => 'password123',
        ]);

        $user = User::where('email', 'john@example.com')->first();

        // Password should be hashed, not plain text
        $this->assertNotEquals('password123', $user->password);
        $this->assertTrue(\Hash::check('password123', $user->password));
    }
}
