<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Tests\TestCase;

class SocialAuthTest extends TestCase
{
    use RefreshDatabase;

    private const CALLBACK_URL = '/auth/google/callback';

    // ── Helpers ──────────────────────────────────────────────────────────────────

    private function makeSocialiteUser(string $id, string $name, string $email): SocialiteUser
    {
        $user        = new SocialiteUser;
        $user->id    = $id;
        $user->name  = $name;
        $user->email = $email;

        return $user;
    }

    private function mockSocialite(SocialiteUser $user): void
    {
        Socialite::shouldReceive('driver->stateless->user')->andReturn($user);
    }

    private function ensureRoles(): void
    {
        Role::firstOrCreate(['name' => 'admin'],    ['label' => 'Admin',    'permissions' => []]);
        Role::firstOrCreate(['name' => 'resident'], ['label' => 'Resident', 'permissions' => []]);
    }

    // ── Login flow ───────────────────────────────────────────────────────────────

    /** @test */
    public function existing_google_user_can_log_in()
    {
        $this->ensureRoles();
        $role = Role::where('name', 'resident')->first();

        $user = User::create([
            'name'      => 'John Doe',
            'username'  => 'johndoe',
            'email'     => 'john@example.com',
            'password'  => Hash::make('random'),
            'google_id' => 'google-id-123',
            'is_active' => true,
            'role_id'   => $role->id,
        ]);

        $this->mockSocialite($this->makeSocialiteUser('google-id-123', 'John Doe', 'john@example.com'));
        $this->withSession(['google_oauth_intent' => 'login']);

        $this->get(self::CALLBACK_URL)->assertRedirect();

        $this->assertAuthenticatedAs($user);
    }

    /** @test */
    public function inactive_google_user_cannot_log_in()
    {
        $this->ensureRoles();
        $role = Role::where('name', 'resident')->first();

        User::create([
            'name'      => 'Inactive User',
            'username'  => 'inactive',
            'email'     => 'inactive@example.com',
            'password'  => Hash::make('random'),
            'google_id' => 'google-id-inactive',
            'is_active' => false,
            'role_id'   => $role->id,
        ]);

        $this->mockSocialite($this->makeSocialiteUser('google-id-inactive', 'Inactive User', 'inactive@example.com'));

        $this->get(self::CALLBACK_URL)
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    /** @test */
    public function login_flow_rejects_unregistered_google_account()
    {
        $this->ensureRoles();

        $this->mockSocialite($this->makeSocialiteUser('new-google-id', 'New Person', 'new@example.com'));
        $this->withSession(['google_oauth_intent' => 'login']);

        $this->get(self::CALLBACK_URL)
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    /** @test */
    public function login_flow_rejects_email_only_account_and_shows_instructions()
    {
        $this->ensureRoles();
        $role = Role::where('name', 'resident')->first();

        // User registered with email/password — no google_id
        User::create([
            'name'      => 'Email User',
            'username'  => 'emailuser',
            'email'     => 'email@example.com',
            'password'  => Hash::make('password'),
            'is_active' => true,
            'role_id'   => $role->id,
        ]);

        $this->mockSocialite($this->makeSocialiteUser('some-google-id', 'Email User', 'email@example.com'));
        $this->withSession(['google_oauth_intent' => 'login']);

        $this->get(self::CALLBACK_URL)
            ->assertRedirect(route('login'))
            ->assertSessionHas('error');

        $this->assertGuest();
    }

    // ── Register flow ─────────────────────────────────────────────────────────────

    /** @test */
    public function register_flow_creates_inactive_account_pending_approval()
    {
        $this->ensureRoles();

        $this->mockSocialite($this->makeSocialiteUser('brand-new-id', 'Brand New', 'brandnew@example.com'));
        $this->withSession(['google_oauth_intent' => 'register']);

        $this->get(self::CALLBACK_URL)
            ->assertRedirect(route('login'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'email'     => 'brandnew@example.com',
            'is_active' => false,
            'google_id' => 'brand-new-id',
        ]);

        // Should NOT be logged in — needs admin approval first
        $this->assertGuest();
    }

    /** @test */
    public function register_flow_links_google_to_existing_email_account()
    {
        $this->ensureRoles();
        $role  = Role::where('name', 'resident')->first();
        $email = 'existing@example.com';

        $user = User::create([
            'name'      => 'Existing Email',
            'username'  => 'existingemail',
            'email'     => $email,
            'password'  => Hash::make('password'),
            'is_active' => true,
            'role_id'   => $role->id,
        ]);

        $this->mockSocialite($this->makeSocialiteUser('link-google-id', 'Existing Email', $email));
        $this->withSession(['google_oauth_intent' => 'register']);

        $this->get(self::CALLBACK_URL)->assertRedirect();

        $this->assertAuthenticatedAs($user);
        $this->assertDatabaseHas('users', [
            'email'     => $email,
            'google_id' => 'link-google-id',
        ]);
    }

    /** @test */
    public function register_flow_rejects_inactive_existing_email_account()
    {
        $this->ensureRoles();
        $role = Role::where('name', 'resident')->first();

        User::create([
            'name'      => 'Inactive',
            'username'  => 'inactiveregister',
            'email'     => 'inactiveregister@example.com',
            'password'  => Hash::make('password'),
            'is_active' => false,
            'role_id'   => $role->id,
        ]);

        $this->mockSocialite($this->makeSocialiteUser('inactive-register-id', 'Inactive', 'inactiveregister@example.com'));
        $this->withSession(['google_oauth_intent' => 'register']);

        $this->get(self::CALLBACK_URL)
            ->assertRedirect(route('login'))
            ->assertSessionHas('error');

        $this->assertGuest();
    }

    // ── Session tracking ──────────────────────────────────────────────────────────

    /** @test */
    public function successful_google_login_updates_session_tracking_fields()
    {
        $this->ensureRoles();
        $role = Role::where('name', 'resident')->first();

        $user = User::create([
            'name'      => 'Tracked User',
            'username'  => 'tracked',
            'email'     => 'tracked@example.com',
            'password'  => Hash::make('random'),
            'google_id' => 'tracked-google-id',
            'is_active' => true,
            'role_id'   => $role->id,
        ]);

        $this->mockSocialite($this->makeSocialiteUser('tracked-google-id', 'Tracked User', 'tracked@example.com'));
        $this->withSession(['google_oauth_intent' => 'login']);

        $this->get(self::CALLBACK_URL);

        $user->refresh();

        $this->assertNotNull($user->session_token);
        $this->assertNotNull($user->last_login_at);
        $this->assertNotNull($user->last_active_at);
    }
}
