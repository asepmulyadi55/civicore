<?php

namespace App\Policies;

use App\Models\Resident;
use App\Models\User;

class ResidentPolicy
{
    /**
     * SECURITY CHECK: The 'Roles & Permissions' configuration module itself 
     * is strictly locked to Super Admin only to prevent Privilege Escalation.
     * 
     * Intercept all checks for super admins.
     */
    public function before(User $user, string $ability): bool|null
    {
        if ($user->isAdmin()) {
            return true;
        }
        return null; // Fallthrough to specific checks
    }

    /**
     * Dynamically checks if the user has the 'residents.view' permission.
     * Assumes $user->can() or $user->hasPermission() checks the database matrix.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('residents.view');
    }

    public function view(User $user, Resident $resident): bool
    {
        return $user->can('residents.view');
    }

    public function create(User $user): bool
    {
        return $user->can('residents.create');
    }

    public function update(User $user, Resident $resident): bool
    {
        return $user->can('residents.edit');
    }

    public function delete(User $user, Resident $resident): bool
    {
        return $user->can('residents.delete');
    }

    public function approve(User $user): bool
    {
        return $user->can('residents.approve');
    }
}
