<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;

class User extends Authenticatable
{
  /** @use HasFactory<\Database\Factories\UserFactory> */
  use HasFactory, Notifiable, HasUuids;

  protected $fillable = [
    'name',
    'username',
    'email',
    'password',
    'is_active',
    'google_id',
    'role_id',
    'unit_number',
    'avatar',
    'language',
    'session_token',
    'last_login_at',
    'last_active_at',
  ];

  protected $hidden = [
    'password',
    'remember_token',
  ];

  protected function casts(): array
  {
    return [
      'email_verified_at' => 'datetime',
      'password' => 'hashed',
      'is_active' => 'boolean',
      'last_login_at' => 'datetime',
      'last_active_at' => 'datetime',
    ];
  }

  // ── Relationships ───────────────────────────────────────────

  public function role(): BelongsTo
  {
    return $this->belongsTo(Role::class);
  }

  // The block this user coordinates (only for block_coordinator role)
  // Legacy single-block accessor — prefer blocks() for multi-block support
  public function block(): BelongsTo
  {
    return $this->belongsTo(Block::class);
  }

  // All blocks this coordinator is assigned to (many-to-many)
  public function blocks(): BelongsToMany
  {
    return $this->belongsToMany(Block::class, 'block_user');
  }

  /**
   * Returns all block IDs this coordinator is responsible for.
   * Used by PaymentController and ReportController for scoping.
   */
  public function coordinatedBlockIds(): array
  {
    return $this->blocks()->pluck('blocks.id')->all();
  }

  // If this user is also a householder (linked account)
  public function householder(): HasOne
  {
    return $this->hasOne(Householder::class);
  }

  /**
   * Resolve the linked householder with a three-level fallback:
   * 1. By user_id (fast path, already linked)
   * 2. By email (if emails match)
   * 3. By block_id + unit_number (if neither user_id nor email matched)
   * Auto-repairs householders.user_id on match so future lookups use the fast path.
   */
  public function resolveHouseholder(): ?Householder
  {
    $householder = $this->hasOne(Householder::class)->first();

    if (!$householder && $this->email) {
      $householder = Householder::where('email', $this->email)->first();
    }
    if ($householder && !$householder->user_id) {
      $householder->update(['user_id' => $this->id]);
    }

    return $householder;
  }

  // ── Role Helpers ────────────────────────────────────────────

  public function isAdmin(): bool
  {
    return $this->role?->name === 'admin';
  }

  public function isTreasurer(): bool
  {
    return $this->role?->name === 'treasurer';
  }

  public function isBlockCoordinator(): bool
  {
    return $this->role?->name === 'block_coordinator';
  }

  public function isHouseholder(): bool
  {
    return $this->role?->name === 'householder';
  }

  public function isPosyandu(): bool
  {
    return $this->role?->name === 'posyandu';
  }

  /**
   * The URL to redirect this user to after login.
   */
  public function homeUrl(): string
  {
    if ($this->can('overview.view') && !$this->can('dashboard.view')) {
      return '/overview';
    }
    return '/dashboard';
  }

  /**
   * Returns the user's avatar URL.
   * Served through the authenticated /private/* route — not publicly accessible.
   * Falls back to a DiceBear initials avatar for users without an uploaded photo.
   */
  public function avatarUrl(): string
  {
    if ($this->avatar && Storage::disk('local')->exists($this->avatar)) {
      return route('private.file', ['path' => $this->avatar]);
    }
    $initials = urlencode(mb_substr($this->name, 0, 2, 'UTF-8'));
    return "https://api.dicebear.com/8.x/initials/svg?seed={$initials}&backgroundColor=4f46e5&fontFamily=Arial";
  }

  /**
   * Returns true if the user has been active within the last 5 minutes.
   */
  public function isOnline(): bool
  {
    return $this->last_active_at !== null
      && $this->last_active_at->diffInMinutes(now()) < 5;
  }

  // ── Permission Checking ──────────────────────────────────────

  /**
   * Check if this user has a given permission (e.g. 'payments.approve').
   * Admins always have all permissions.
   * Overrides Authenticatable::can() for simple string permissions.
   */
  public function can($ability, $arguments = []): bool
  {
    // If $ability is a string permission key (our custom system)
    if (is_string($ability) && str_contains($ability, '.')) {
      if ($this->isAdmin()) {
        return true;
      }
      return $this->role?->hasPermission($ability) ?? false;
    }
    // Fall back to standard Laravel Gate / policy check
    return parent::can($ability, $arguments);
  }


}
