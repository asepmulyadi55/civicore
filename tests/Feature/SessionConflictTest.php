<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SessionConflictTest extends TestCase
{
    use RefreshDatabase;

    // ── Helper ───────────────────────────────────────────────────────────────────

    private function createUser(): User
    {
        $role = Role::create(['name' => 'resident', 'label' => 'Resident', 'permissions' => []]);

        return User::create([
            'name'      => 'Jane Doe',
            'username'  => 'janedoe',
            'email'     => 'jane@test.com',
            'password'  => bcrypt('password'),
            'is_active' => true,
            'role_id'   => $role->id,
        ]);
    }

    // ── Conflict page ─────────────────────────────────────────────────────────────

    /** @test */
    public function conflict_page_is_publicly_accessible()
    {
        $this->get(route('session.conflict'))
            ->assertOk()
            ->assertViewIs('session-conflict');
    }

    // ── useThisDevice — rejection cases ──────────────────────────────────────────

    /** @test */
    public function use_this_device_rejected_with_no_session_key()
    {
        $user = $this->createUser();

        // No conflict_user_id in session → rejected
        $this->post(route('session.use-this'), ['user_id' => $user->id])
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    /** @test */
    public function use_this_device_rejected_when_user_id_mismatches_session()
    {
        $user    = $this->createUser();
        $wrongId = 'completely-wrong-id';

        $this->withSession(['conflict_user_id' => $user->id])
            ->post(route('session.use-this'), ['user_id' => $wrongId])
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    /** @test */
    public function use_this_device_rejected_for_nonexistent_user()
    {
        $this->withSession(['conflict_user_id' => 'nonexistent-uuid'])
            ->post(route('session.use-this'), ['user_id' => 'nonexistent-uuid'])
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    // ── useThisDevice — success path ─────────────────────────────────────────────

    /** @test */
    public function use_this_device_logs_in_user_with_valid_session_key()
    {
        $user = $this->createUser();

        $this->withSession(['conflict_user_id' => $user->id])
            ->post(route('session.use-this'), ['user_id' => $user->id])
            ->assertRedirect();

        $this->assertAuthenticatedAs($user);
    }

    /** @test */
    public function use_this_device_updates_session_tracking_fields()
    {
        $user = $this->createUser();

        $this->withSession(['conflict_user_id' => $user->id])
            ->post(route('session.use-this'), ['user_id' => $user->id]);

        $user->refresh();

        $this->assertNotNull($user->session_token);
        $this->assertNotNull($user->last_login_at);
        $this->assertNotNull($user->last_active_at);
    }

    // ── useThisDevice — one-time use ─────────────────────────────────────────────

    /** @test */
    public function conflict_user_id_is_consumed_after_successful_login()
    {
        $user = $this->createUser();

        $this->withSession(['conflict_user_id' => $user->id])
            ->post(route('session.use-this'), ['user_id' => $user->id]);

        // The session key must be gone after use
        $this->assertFalse(session()->has('conflict_user_id'));
    }

    /** @test */
    public function replaying_the_same_request_after_login_is_rejected()
    {
        $user = $this->createUser();

        // First call — succeeds and consumes the session key
        $this->withSession(['conflict_user_id' => $user->id])
            ->post(route('session.use-this'), ['user_id' => $user->id]);

        // Second call — no conflict_user_id left in session, must be rejected
        $this->post(route('session.use-this'), ['user_id' => $user->id])
            ->assertRedirect(route('login'));
    }
}
