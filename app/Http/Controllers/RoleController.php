<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RoleController extends Controller
{
    public function index(Request $request)
    {
        $query = Role::withCount('users');

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('label', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $sortMap = ['label' => 'label', 'users_count' => 'users_count'];
        $sort = $request->get('sort');
        $dir  = $request->get('direction', 'asc') === 'desc' ? 'desc' : 'asc';
        $query->orderBy(isset($sortMap[$sort]) ? $sort : 'id', $dir);

        $roles = $query->get();
        return view('roles', compact('roles'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|alpha_dash|max:50|unique:roles,name',
            'label' => 'required|string|max:100',
            'description' => 'nullable|string|max:255',
            'icon' => 'nullable|string|max:50',
        ]);

        Role::create(array_merge($data, [
            'bg_class' => 'bg-slate-100 dark:bg-slate-800',
            'text_class' => 'text-slate-600 dark:text-slate-400',
            'permissions' => [],
        ]));

        return redirect()->back()->with('success', __('app.flash_role_created', ['name' => $data['label']]));
    }

    public function update(Request $request, Role $role)
    {
        if ($role->name === 'admin') {
            return redirect()->back()->with('error', __('app.flash_role_admin_no_modify'));
        }

        $data = $request->validate([
            'label' => 'required|string|max:100',
            'description' => 'nullable|string|max:255',
            'icon' => 'nullable|string|max:50',
        ]);

        $role->update($data);

        return redirect()->back()->with('success', __('app.flash_role_updated', ['name' => $role->label]));
    }

    public function updatePermissions(Request $request, Role $role)
    {
        if ($role->name === 'admin') {
            return redirect()->back()->with('error', __('app.flash_role_admin_perms_no_modify'));
        }

        // Build permissions array from submitted checkboxes.
        // PHP converts dots to underscores in $_POST keys (language-level behavior),
        // so <input name="dashboard.view"> arrives as $_POST['dashboard_view'].
        // We store as dot-notation keys ('dashboard.view') but look up with underscores.
        $raw = $request->all(); // works for all HTTP methods including PATCH
        $permissions = [];
        foreach (Role::$availablePermissions as $module => $actions) {
            foreach ($actions as $action) {
                $storageKey = "{$module}.{$action}"; // how we store in JSON
                $postKey = "{$module}_{$action}"; // how PHP delivers it from form
                $permissions[$storageKey] = isset($raw[$postKey]) && $raw[$postKey] === '1';
            }
        }

        $oldPermissions = $role->permissions;
        $role->update(['permissions' => $permissions]);

        Log::info('Role permissions updated', [
            'role_id' => $role->id,
            'role' => $role->name,
            'old' => $oldPermissions,
            'new' => $permissions,
            'by' => auth()->id(),
        ]);

        return redirect()->route('roles.index')
            ->with('success', __('app.flash_role_permissions_updated', ['name' => $role->label]));
    }

    public function destroy(Role $role)
    {
        if ($role->name === 'admin') {
            return redirect()->back()->with('error', __('app.flash_role_admin_no_delete'));
        }
        if ($role->users()->count() > 0) {
            return redirect()->back()->with('error', __('app.flash_role_has_users', ['name' => $role->label]));
        }

        $label = $role->label;
        $role->delete();

        return redirect()->back()->with('success', __('app.flash_role_deleted', ['name' => $label]));
    }
}
