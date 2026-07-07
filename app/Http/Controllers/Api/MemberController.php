<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class MemberController extends Controller
{
    public function index(Request $request)
    {
        if (! $request->user()->isSuperAdmin() && ! $request->user()->hasPermissionTo('manage_members')) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        return response()->json([
            'users' => UserResource::collection(User::with(['roles', 'permissions', 'team'])->get()),
            'roles' => Role::with('permissions')->get(),
            'permissions' => Permission::all()->pluck('name'),
        ]);
    }

    public function store(Request $request)
    {
        if (! $request->user()->isSuperAdmin() && ! $request->user()->hasPermissionTo('manage_members')) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'role' => 'required|exists:roles,name',
            'team_id' => 'nullable|exists:teams,id',
            'phone' => 'nullable|string|max:20|unique:users,phone',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'role' => $request->role,
            'team_id' => $request->team_id,
            'phone' => $request->phone,
        ]);

        $user->syncRoles([$request->role]);

        return response()->json([
            'message' => 'User created successfully.',
            'user' => new UserResource($user->load(['roles', 'team'])),
        ], 201);
    }

    public function update(Request $request, User $user)
    {
        if (! $request->user()->isSuperAdmin() && ! $request->user()->hasPermissionTo('manage_members')) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8',
            'role' => 'sometimes|required|exists:roles,name',
            'team_id' => 'nullable|exists:teams,id',
            'phone' => 'nullable|string|max:20|unique:users,phone,' . $user->id,
        ]);

        $data = $request->only(['name', 'email', 'team_id', 'phone']);
        if ($request->filled('password')) {
            $data['password'] = bcrypt($request->password);
        }
        if ($request->has('role')) {
            $data['role'] = $request->role;
            if ($user->id !== $request->user()->id) {
                $user->syncRoles([$request->role]);
            }
        }

        $user->update($data);

        return response()->json([
            'message' => 'User updated successfully.',
            'user' => new UserResource($user->fresh()->load(['roles', 'team'])),
        ]);
    }

    public function destroy(Request $request, User $user)
    {
        if (! $request->user()->isSuperAdmin() && ! $request->user()->hasPermissionTo('manage_members')) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        if ($request->user()->id === $user->id) {
            return response()->json(['message' => 'Cannot delete yourself.'], 422);
        }

        // Delete user's avatar if exists
        if ($user->avatar_path) {
            \Illuminate\Support\Facades\Storage::disk('r2')->delete($user->avatar_path);
        }

        $user->delete();

        return response()->json(['message' => 'User deleted successfully.']);
    }

    public function updateRole(Request $request, User $user)
    {
        if (! $request->user()->hasPermissionTo('manage_members')) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        if ($request->user()->id === $user->id) {
            return response()->json(['message' => 'Cannot change own role.'], 422);
        }

        $request->validate(['role' => 'required|exists:roles,name']);
        $user->syncRoles([$request->role]);
        $user->update(['role' => $request->role]);

        return new UserResource($user->fresh()->load('roles'));
    }

    public function updatePermissions(Request $request, User $user)
    {
        if (! $request->user()->hasPermissionTo('manage_permissions')) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $request->validate([
            'permissions' => 'present|array',
            'permissions.*' => 'string|exists:permissions,name',
        ]);

        $user->syncPermissions($request->permissions);
        return new UserResource($user->fresh()->load(['roles', 'permissions']));
    }

    public function updateRolePermissions(Request $request, Role $role)
    {
        if (! $request->user()->hasPermissionTo('manage_permissions')) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $request->validate([
            'permissions' => 'present|array',
            'permissions.*' => 'string|exists:permissions,name',
        ]);

        $role->syncPermissions($request->permissions);
        return response()->json(['message' => 'Permissions updated.', 'role' => $role->fresh()->load('permissions')]);
    }
}
