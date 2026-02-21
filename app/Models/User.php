<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
  /** @use HasFactory<\Database\Factories\UserFactory> */
  use HasFactory, Notifiable;

  protected $fillable = [
    'name',
    'username',
    'email',
    'password',
    'is_active',
    'google_id',
    'role_id',
    'block_id',
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
    ];
  }

  // ── Relationships ───────────────────────────────────────────

  public function role(): BelongsTo
  {
    return $this->belongsTo(Role::class);
  }

  // The block this user coordinates (only for block_coordinator role)
  public function block(): BelongsTo
  {
    return $this->belongsTo(Block::class);
  }

  // If this user is also a resident (linked account)
  public function resident(): HasMany
  {
    return $this->hasMany(Resident::class);
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

  public function isResident(): bool
  {
    return $this->role?->name === 'resident';
  }

  public function canApprovePayments(): bool
  {
    return $this->isAdmin() || $this->isTreasurer();
  }
}
