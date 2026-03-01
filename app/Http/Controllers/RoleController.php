<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::withCount('users')->orderBy('id')->get();
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

        return redirect()->back()->with('success', "Role '{$data['label']}' created successfully.");
    }

    public function update(Request $request, Role $role)
    {
        if ($role->name === 'admin') {
            return redirect()->back()->with('error', 'The Admin role cannot be modified.');
        }

        $data = $request->validate([
            'label' => 'required|string|max:100',
            'description' => 'nullable|string|max:255',
            'icon' => 'nullable|string|max:50',
        ]);

        $role->update($data);

        return redirect()->back()->with('success', "Role '{$role->label}' updated.");
    }

    public function updatePermissions(Request $request, Role $role)
    {
        if ($role->name === 'admin') {
            return redirect()->back()->with('error', 'Admin permissions cannot be modified.');
        }

        // Build permissions array from submitted checkboxes.
        // PHP converts dots to underscores in $_POST keys (language-level behavior),
        // so <input name="dashboard.view"> arrives as $_POST['dashboard_view'].
        // We store as dot-notation keys ('dashboard.view') but look up with underscores.
        $raw = $request->post(); // flat array from $_POST
        $permissions = [];
        foreach (Role::$availablePermissions as $module => $actions) {
            foreach ($actions as $action) {
                $storageKey = "{$module}.{$action}"; // how we store in JSON
                $postKey = "{$module}_{$action}"; // how PHP delivers it from form
                $permissions[$storageKey] = isset($raw[$postKey]) && $raw[$postKey] === '1';
            }
        }

        $role->update(['permissions' => $permissions]);

        return redirect()->back()->with('success', "Permissions for '{$role->label}' updated.");
    }

    public function destroy(Role $role)
    {
        if ($role->name === 'admin') {
            return redirect()->back()->with('error', 'The Admin role cannot be deleted.');
        }
        if ($role->users()->count() > 0) {
            return redirect()->back()->with('error', "Cannot delete '{$role->label}' — it has users assigned.");
        }

        $label = $role->label;
        $role->delete();

        return redirect()->back()->with('success', "Role '{$label}' deleted.");
    }
}
