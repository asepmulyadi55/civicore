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

    if ($search = $request->get('search')) {
      $query->where(function ($q) use ($search) {
        $q->where('name', 'like', "%{$search}%")
          ->orWhere('email', 'like', "%{$search}%")
          ->orWhere('username', 'like', "%{$search}%");
      });
    }

    if ($roleId = $request->get('role_id')) {
      $query->where('role_id', $roleId);
    }

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
    $totalUsers = User::count();
    $activeUsers = User::where('is_active', true)->count();
    $pendingUsers = User::where('is_active', false)->whereNotNull('email')->count();

    return view('users', compact('users', 'roles', 'totalUsers', 'activeUsers', 'pendingUsers'));
  }

  /**
   * Create a new user from the admin form.
   */
  public function store(Request $request)
  {
    $validated = $request->validate([
      'name' => ['required', 'string', 'max:100'],
      'username' => ['required', 'string', 'max:50', 'unique:users,username'],
      'email' => ['required', 'email', 'max:255', 'unique:users,email'],
      'password' => ['required', 'string', 'min:8'],
      'role_id' => ['nullable', 'exists:roles,id'],
      'is_active' => ['nullable', 'boolean'],
    ], [
      'name.required' => 'Please enter the user\'s full name.',
      'name.max' => 'Full name must not exceed 100 characters.',
      'username.required' => 'Please choose a username.',
      'username.max' => 'Username must not exceed 50 characters.',
      'username.unique' => 'This username is already taken. Please choose another.',
      'email.required' => 'Please enter an email address.',
      'email.email' => 'Please enter a valid email address (e.g. user@example.com).',
      'email.unique' => 'This email is already registered to another user.',
      'password.required' => 'Please set a password for this user.',
      'password.min' => 'Password must be at least 8 characters long.',
    ]);

    $user = User::create([
      'name' => $validated['name'],
      'username' => $validated['username'],
      'email' => $validated['email'],
      'password' => Hash::make($validated['password']),
      'role_id' => $validated['role_id'] ?? null,
      'is_active' => $request->boolean('is_active', true),
    ]);

    // Auto-link to resident if email matches
    if ($user->email) {
      Resident::where('email', $user->email)
        ->whereNull('user_id')
        ->update(['user_id' => $user->id]);
    }

    return redirect()->route('users.index')
      ->with('success', "\"{$user->name}\" has been created successfully.");
  }

  /**
   * Update a user's profile (name, username, email, role, block, optional password).
   */
  public function update(Request $request, User $user)
  {
    $validated = $request->validate([
      'name' => ['required', 'string', 'max:100'],
      'username' => ['required', 'string', 'max:50', Rule::unique('users', 'username')->ignore($user->id)],
      'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
      'role_id' => ['nullable', 'exists:roles,id'],
      'password' => ['nullable', 'string', 'min:8'],
    ], [
      'name.required' => 'Please enter the user\'s full name.',
      'name.max' => 'Full name must not exceed 100 characters.',
      'username.required' => 'Please choose a username.',
      'username.max' => 'Username must not exceed 50 characters.',
      'username.unique' => 'This username is already taken. Please choose another.',
      'email.required' => 'Please enter an email address.',
      'email.email' => 'Please enter a valid email address (e.g. user@example.com).',
      'email.unique' => 'This email is already registered to another user.',
      'password.min' => 'New password must be at least 8 characters long.',
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
    Resident::where('user_id', $user->id)->update(['user_id' => null]);
    $user->delete();

    return redirect()->route('users.index')
      ->with('success', "\"{$name}\" has been deleted.");
  }
}
