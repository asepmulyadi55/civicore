<?php

namespace App\Http\Controllers;

use App\Models\Resident;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
  public function index(Request $request)
  {
    $query = User::with('role')->orderBy('created_at', 'desc');

    // Search by name, email, or username
    if ($search = $request->get('search')) {
      $query->where(function ($q) use ($search) {
        $q->where('name', 'like', "%{$search}%")
          ->orWhere('email', 'like', "%{$search}%")
          ->orWhere('username', 'like', "%{$search}%");
      });
    }

    // Filter by role
    if ($roleId = $request->get('role_id')) {
      $query->where('role_id', $roleId);
    }

    // Filter by status
    if ($status = $request->get('status')) {
      if ($status === 'pending') {
        $query->where('is_active', false)->whereNotNull('email');
      } elseif ($status === 'active') {
        $query->where('is_active', true);
      } elseif ($status === 'inactive') {
        $query->where('is_active', false)->whereNull('email');
      }
    }

    $users = $query->paginate(20)->withQueryString();
    $roles = Role::orderBy('name')->get();

    return view('users', compact('users', 'roles'));
  }

  /**
   * Update a user's profile (name, username, email, role, optional password).
   */
  public function update(Request $request, User $user)
  {
    $validated = $request->validate([
      'name' => ['required', 'string', 'max:100'],
      'username' => [
        'required',
        'string',
        'max:50',
        Rule::unique('users', 'username')->ignore($user->id)
      ],
      'email' => [
        'required',
        'email',
        'max:255',
        Rule::unique('users', 'email')->ignore($user->id)
      ],
      'role_id' => ['nullable', 'exists:roles,id'],
      'password' => ['nullable', 'string', 'min:8'],
    ]);

    $user->name = $validated['name'];
    $user->username = $validated['username'];
    $user->email = $validated['email'];
    $user->role_id = $validated['role_id'] ?? null;

    if (!empty($validated['password'])) {
      $user->password = Hash::make($validated['password']);
    }

    $user->save();

    return redirect()->route('users.index')
      ->with('success', "\"{$user->name}\" has been updated successfully.");
  }

  public function approve(User $user)
  {
    $user->update(['is_active' => true]);

    // Auto-link to matching resident by email
    if ($user->email) {
      Resident::where('email', $user->email)
        ->whereNull('user_id')
        ->update(['user_id' => $user->id]);
    }

    return redirect()->route('users.index')
      ->with('success', "\"{$user->name}\" has been approved and can now log in.");
  }

  public function deactivate(User $user)
  {
    if ($user->id === auth()->id()) {
      return redirect()->route('users.index')
        ->with('error', 'You cannot deactivate your own account.');
    }

    $user->update(['is_active' => false]);

    return redirect()->route('users.index')
      ->with('success', "\"{$user->name}\" has been deactivated.");
  }

  public function destroy(User $user)
  {
    if ($user->id === auth()->id()) {
      return redirect()->route('users.index')
        ->with('error', 'You cannot delete your own account.');
    }

    $name = $user->name;
    // Unlink any linked residents before deleting the user
    Resident::where('user_id', $user->id)->update(['user_id' => null]);
    $user->delete();

    return redirect()->route('users.index')
      ->with('success', "\"{$name}\" has been deleted.");
  }
}
