<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    protected $fillable = [
        'name',
        'label',
        'description',
        'icon',
        'bg_class',
        'text_class',
        'permissions',
    ];

    protected $casts = [
        'permissions' => 'array',
    ];

    // ── Available permissions — single source of truth ──────────────
    public static array $availablePermissions = [
        'dashboard' => ['view'],
        'residents' => ['view', 'create', 'edit', 'delete'],
        'blocks' => ['view', 'create', 'edit', 'delete'],
        'payments' => ['view', 'create', 'edit', 'delete', 'approve'],
        'reports' => ['view'],
        'users' => ['view', 'create', 'edit', 'delete', 'approve'],
        'roles' => ['view', 'create', 'edit', 'delete'],
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Check if this role has a given permission key (e.g. 'payments.approve').
     * Admin roles always return true.
     */
    public function hasPermission(string $key): bool
    {
        if ($this->name === 'admin') {
            return true;
        }
        return (bool) ($this->permissions[$key] ?? false);
    }
}
