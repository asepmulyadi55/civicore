<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\StoreUserRequest;
use App\Http\Requests\API\UpdateUserRequest;
use App\Http\Resources\API\UserResource;
use App\Models\Role;
use App\Models\User;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        if (!$request->user()->can('users.view')) {
            return $this->forbidden();
        }

        $query = User::with(['role', 'block']);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%");
            });
        }

        if ($roleId = $request->input('role_id')) {
            $query->where('role_id', $roleId);
        }

        if ($request->has('is_active')) {
            $query->where('is_active', filter_var($request->input('is_active'), FILTER_VALIDATE_BOOLEAN));
        }

        $sortBy    = in_array($request->input('sort_by'), ['name', 'email', 'created_at']) ? $request->input('sort_by') : 'name';
        $sortOrder = $request->input('sort_order', 'asc') === 'desc' ? 'desc' : 'asc';
        $query->orderBy($sortBy, $sortOrder);

        $paginator = $query->paginate($request->input('per_page', 15));

        return $this->paginated($paginator, UserResource::collection($paginator), 'Users fetched successfully');
    }

    public function show(Request $request, User $user): JsonResponse
    {
        if (!$request->user()->can('users.view')) {
            return $this->forbidden();
        }

        $user->load(['role', 'block']);

        return $this->success(new UserResource($user), 'User fetched successfully');
    }

    public function store(StoreUserRequest $request): JsonResponse
    {
        $data             = $request->validated();
        $data['password'] = Hash::make($data['password']);

        $user = User::create($data);
        $user->load(['role', 'block']);

        return $this->created(new UserResource($user), 'User created successfully');
    }

    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        $data = $request->validated();

        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $user->update($data);
        $user->load(['role', 'block']);

        return $this->success(new UserResource($user), 'User updated successfully');
    }

    public function destroy(Request $request, User $user): JsonResponse
    {
        if (!$request->user()->can('users.delete')) {
            return $this->forbidden();
        }

        if ($user->id === $request->user()->id) {
            return $this->error('You cannot delete your own account.', 422);
        }

        $user->tokens()->delete();
        $user->delete();

        return $this->noContent('User deleted successfully');
    }

    public function assignRole(Request $request, User $user): JsonResponse
    {
        if (!$request->user()->can('users.edit')) {
            return $this->forbidden();
        }

        $data = $request->validate([
            'role_id' => ['required', 'uuid', 'exists:roles,id'],
        ]);

        $user->update(['role_id' => $data['role_id']]);
        $user->load(['role', 'block']);

        return $this->success(new UserResource($user), 'Role assigned successfully');
    }

    public function roles(Request $request): JsonResponse
    {
        if (!$request->user()->can('roles.view')) {
            return $this->forbidden();
        }

        $roles = Role::orderBy('name')->get(['id', 'name', 'label', 'description']);

        return $this->success($roles, 'Roles fetched successfully');
    }
}
