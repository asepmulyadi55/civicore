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

  // ── Footer section ────────────────────────────────────────────────────────────

  /** @test */
  public function admin_can_save_footer_section()
  {
    $admin = $this->createAdminUser();

    $this->actingAs($admin)->post(route('homepage.footer'), [
      'brand_name'    => 'Dwipapuri',
      'tagline'       => 'A great place to live.',
      'contact_email' => 'hello@dwipapuri.com',
      'contact_phone' => '+62 123 4567 890',
      'facebook_url'  => 'https://facebook.com/dwipapuri',
      'instagram_url' => 'https://instagram.com/dwipapuri',
      'copyright'     => '© 2025 Dwipapuri. All rights reserved.',
      'bottom_note'   => 'Built for a better community experience.',
      'links'         => [
        ['label' => 'Resident Portal', 'url' => 'https://dwipapuri.com/portal'],
        ['label' => 'Privacy Policy',  'url' => 'https://dwipapuri.com/privacy'],
      ],
    ])
      ->assertRedirect(route('homepage.index'))
      ->assertSessionHas('success');

    $saved = json_decode(\App\Models\Setting::get('homepage_footer', '{}'), true);
    $this->assertEquals('Dwipapuri', $saved['brand_name']);
    $this->assertCount(2, $saved['links']);
  }

  /** @test */
  public function footer_rejects_invalid_email()
  {
    $admin = $this->createAdminUser();

    $this->actingAs($admin)->post(route('homepage.footer'), [
      'contact_email' => 'not-an-email',
    ])->assertSessionHasErrors('contact_email');
  }

  /** @test */
  public function footer_rejects_invalid_social_urls()
  {
    $admin = $this->createAdminUser();

    $this->actingAs($admin)->post(route('homepage.footer'), [
      'facebook_url' => 'not-a-url',
    ])->assertSessionHasErrors('facebook_url');
  }

  /** @test */
  public function footer_strips_empty_links()
  {
    $admin = $this->createAdminUser();

    $this->actingAs($admin)->post(route('homepage.footer'), [
      'links' => [
        ['label' => '', 'url' => ''],
        ['label' => 'Portal', 'url' => 'https://dwipapuri.com'],
      ],
    ])
      ->assertRedirect(route('homepage.index'));

    $saved = json_decode(\App\Models\Setting::get('homepage_footer', '{}'), true);
    $this->assertCount(1, $saved['links']);
    $this->assertEquals('Portal', $saved['links'][0]['label']);
  }

  /** @test */
  public function guest_cannot_save_footer()
  {
    $this->post(route('homepage.footer'), ['brand_name' => 'Test'])
      ->assertRedirect(route('login'));
  }
}
