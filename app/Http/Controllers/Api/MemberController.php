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
        if (! $request->user()->hasAnyRole(['Admin', 'Lead'])) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        return response()->json([
            'users' => UserResource::collection(User::with(['roles', 'permissions', 'team'])->get()),
            'roles' => Role::with('permissions')->get(),
            'permissions' => Permission::all()->pluck('name'),
        ]);
    }

    public function updateRole(Request $request, User $user)
    {
        if (! $request->user()->hasAnyRole(['Admin', 'Lead'])) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        if ($request->user()->id === $user->id) {
            return response()->json(['message' => 'Cannot change own role.'], 422);
        }

        $request->validate(['role' => 'required|exists:roles,name']);
        $user->syncRoles([$request->role]);

        return new UserResource($user->fresh()->load('roles'));
    }

    public function updatePermissions(Request $request, User $user)
    {
        if (! $request->user()->hasAnyRole(['Admin', 'Lead'])) {
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
        if (! $request->user()->hasAnyRole(['Admin', 'Lead'])) {
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
