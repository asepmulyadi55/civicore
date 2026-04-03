<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomepageTest extends TestCase
{
  use RefreshDatabase;

  // ── Helpers ─────────────────────────────────────────────────────────────────

  private function createAdminUser(): User
  {
    $role = Role::create(['name' => 'admin', 'label' => 'Admin', 'permissions' => []]);
    return User::create([
      'name'      => 'Admin',
      'username'  => 'admin',
      'email'     => 'admin@test.com',
      'password'  => bcrypt('password'),
      'is_active' => true,
      'role_id'   => $role->id,
    ]);
  }

  // ── Authorization ────────────────────────────────────────────────────────────

  /** @test */
  public function guests_are_redirected_from_homepage_cms()
  {
    $this->get(route('homepage.index'))->assertRedirect(route('login'));
  }

  /** @test */
  public function authenticated_user_can_view_homepage_cms()
  {
    $admin = $this->createAdminUser();
    $this->actingAs($admin)
      ->get(route('homepage.index'))
      ->assertOk()
      ->assertViewIs('homepage');
  }

  // ── Old events route is removed ───────────────────────────────────────────────

  /** @test */
  public function old_events_route_no_longer_exists()
  {
    $admin = $this->createAdminUser();
    $this->actingAs($admin)
      ->get('/events')
      ->assertNotFound();
  }

  // ── Hero section ──────────────────────────────────────────────────────────────

  /** @test */
  public function admin_can_save_hero_section()
  {
    $admin = $this->createAdminUser();

    $this->actingAs($admin)->post(route('homepage.hero'), [
      'title'    => 'Welcome to Dwipapuri',
      'subtitle' => 'A vibrant community',
      'cta_text' => 'Explore Events',
      'cta_url'  => '/events',
      'bg_image' => 'https://cdn.example.com/hero.jpg',
    ])
      ->assertRedirect(route('homepage.index'))
      ->assertSessionHas('success');
  }

  /** @test */
  public function hero_section_requires_title()
  {
    $admin = $this->createAdminUser();

    $this->actingAs($admin)->post(route('homepage.hero'), [
      'title' => '',
    ])->assertSessionHasErrors('title');
  }

  // ── Featured event ────────────────────────────────────────────────────────────

  /** @test */
  public function admin_can_save_featured_event()
  {
    $admin = $this->createAdminUser();

    $this->actingAs($admin)->post(route('homepage.featured-event'), [
      'title'       => 'Annual Gala',
      'description' => 'Our biggest night',
      'youtube_id'  => 'dQw4w9WgXcQ',
      'date'        => '2025-06-15',
      'status'      => 'upcoming',
    ])
      ->assertRedirect(route('homepage.index'))
      ->assertSessionHas('success');
  }

  // ── Events list ───────────────────────────────────────────────────────────────

  /** @test */
  public function admin_can_add_an_upcoming_event()
  {
    $admin = $this->createAdminUser();

    $this->actingAs($admin)->post(route('homepage.events.store'), [
      'title'  => 'Community Picnic',
      'date'   => '2025-08-10',
      'status' => 'upcoming',
    ])
      ->assertRedirect(route('homepage.index'))
      ->assertSessionHas('success');
  }

  /** @test */
  public function add_event_requires_title()
  {
    $admin = $this->createAdminUser();

    $this->actingAs($admin)->post(route('homepage.events.store'), [
      'title'  => '',
      'status' => 'upcoming',
    ])->assertSessionHasErrors('title');
  }

  /** @test */
  public function admin_can_delete_an_event()
  {
    $admin = $this->createAdminUser();

    // Add an event first
    $this->actingAs($admin)->post(route('homepage.events.store'), [
      'title'  => 'Event To Delete',
      'status' => 'past',
    ]);

    // Retrieve the stored event's id
    $events = json_decode(\App\Models\Setting::get('homepage_events', '[]'), true);
    $this->assertCount(1, $events);

    $this->actingAs($admin)
      ->delete(route('homepage.events.destroy', $events[0]['id']))
      ->assertRedirect(route('homepage.index'))
      ->assertSessionHas('success');

    // Confirm it's gone
    $events = json_decode(\App\Models\Setting::get('homepage_events', '[]'), true);
    $this->assertCount(0, $events);
  }

  // ── About section ─────────────────────────────────────────────────────────────

  /** @test */
  public function admin_can_save_about_section()
  {
    $admin = $this->createAdminUser();

    $this->actingAs($admin)->post(route('homepage.about'), [
      'content' => 'Dwipapuri is a modern residential community established in 2005.',
    ])
      ->assertRedirect(route('homepage.index'))
      ->assertSessionHas('success');
  }

  /** @test */
  public function about_section_requires_content()
  {
    $admin = $this->createAdminUser();

    $this->actingAs($admin)->post(route('homepage.about'), [
      'content' => '',
    ])->assertSessionHasErrors('content');
  }
}
