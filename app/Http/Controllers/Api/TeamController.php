<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TeamResource;
use App\Http\Resources\UserResource;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TeamController extends Controller
{
    public function index(Request $request)
    {
        if (! $request->user()->isSuperAdmin()) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $teams = Team::withCount('members')
            ->with('leader:id,name,email')
            ->orderBy('created_at', 'desc')
            ->get();

        return TeamResource::collection($teams);
    }

    public function store(Request $request)
    {
        if (! $request->user()->isSuperAdmin()) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'leader_id' => 'required|exists:users,id',
        ]);

        $team = Team::create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'description' => $validated['description'] ?? '',
            'leader_id' => $validated['leader_id'],
        ]);

        // Assign leader to team
        User::where('id', $validated['leader_id'])->update([
            'team_id' => $team->id,
            'role' => 'team_leader',
        ]);

        return new TeamResource($team->load('leader:id,name,email'));
    }

    public function show(Team $team)
    {
        $team->loadCount('members');
        $team->load('leader:id,name,email');

        return new TeamResource($team);
    }

    public function update(Request $request, Team $team)
    {
        if (! $request->user()->isSuperAdmin()) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'leader_id' => 'sometimes|exists:users,id',
        ]);

        if (isset($validated['name'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        // If changing leader, update old leader role and new leader role
        if (isset($validated['leader_id']) && $validated['leader_id'] !== $team->leader_id) {
            // Demote old leader
            if ($team->leader_id) {
                User::where('id', $team->leader_id)->update(['role' => 'member']);
            }
            // Promote new leader
            User::where('id', $validated['leader_id'])->update([
                'team_id' => $team->id,
                'role' => 'team_leader',
            ]);
        }

        $team->update($validated);

        return new TeamResource($team->fresh()->load('leader:id,name,email'));
    }

    public function destroy(Request $request, Team $team)
    {
        if (! $request->user()->isSuperAdmin()) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        // Remove all members from team
        User::where('team_id', $team->id)->update(['team_id' => null, 'role' => 'member']);

        $team->delete();

        return response()->json(['message' => 'Tim berhasil dihapus.']);
    }

    public function members(Request $request, Team $team)
    {
        if (! $request->user()->isSuperAdmin() && $request->user()->team_id !== $team->id) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $members = User::where('team_id', $team->id)
            ->with('roles')
            ->get();

        return UserResource::collection($members);
    }

    public function invite(Request $request, Team $team)
    {
        if (! $request->user()->isSuperAdmin() && ! $request->user()->isTeamLeader()) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $user = User::find($request->user_id);

        if ($user->team_id && $user->team_id !== $team->id) {
            return response()->json(['message' => 'User sudah ada di tim lain.'], 422);
        }

        $user->update([
            'team_id' => $team->id,
            'role' => 'member',
        ]);

        return new UserResource($user->fresh()->load('roles'));
    }

    public function removeMember(Request $request, Team $team, User $user)
    {
        if (! $request->user()->isSuperAdmin() && ! $request->user()->isTeamLeader()) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        if ($user->id === $team->leader_id) {
            return response()->json(['message' => 'Tidak bisa menghapus leader dari tim.'], 422);
        }

        $user->update(['team_id' => null, 'role' => 'member']);

        return response()->json(['message' => 'Anggota berhasil dihapus dari tim.']);
    }

    public function switchTeam(Request $request)
    {
        if (! $request->user()->isSuperAdmin()) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $request->validate([
            'team_id' => 'nullable|exists:teams,id',
        ]);

        // Super Admin can view any team or all teams
        $teamId = $request->team_id ?? null;

        return response()->json([
            'message' => 'Team switched.',
            'team_id' => $teamId,
        ]);
    }

    public function updateMember(Request $request, Team $team, User $user)
    {
        if (! $request->user()->isSuperAdmin() && ! $request->user()->isTeamLeader()) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        if ($user->team_id !== $team->id) {
            return response()->json(['message' => 'User bukan anggota tim ini.'], 422);
        }

        // Cannot edit yourself
        if ($user->id === $request->user()->id) {
            return response()->json(['message' => 'Tidak bisa mengedit diri sendiri.'], 422);
        }

        // Only super_admin can change leader role
        if ($request->has('role') && $request->role === 'team_leader' && ! $request->user()->isSuperAdmin()) {
            return response()->json(['message' => 'Hanya super admin yang bisa mengubah role leader.'], 403);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'role' => 'sometimes|in:member,team_leader',
        ]);

        // If changing role
        if (isset($validated['role']) && $validated['role'] !== $user->role) {
            if ($validated['role'] === 'team_leader') {
                // Demote current leader if exists
                $currentLeader = User::where('team_id', $team->id)
                    ->where('role', 'team_leader')
                    ->where('id', '!=', $user->id)
                    ->first();
                if ($currentLeader) {
                    $currentLeader->update(['role' => 'member']);
                }
            }
        }

        $user->update($validated);

        return new UserResource($user->fresh()->load('roles'));
    }

    public function updateSettings(Request $request)
    {
        if (! $request->user()->isTeamLeader() && ! $request->user()->isSuperAdmin()) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $team = $request->user()->team;
        if (! $team) {
            return response()->json(['message' => 'Team not found.'], 404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'logo_url' => 'nullable|string|url',
        ]);

        $data = $request->only(['name', 'description']);
        if ($request->has('logo_url')) {
            $data['logo_url'] = $request->logo_url;
        }

        $team->update($data);

        return new TeamResource($team);
    }

    public function uploadLogo(Request $request)
    {
        if (! $request->user()->isTeamLeader() && ! $request->user()->isSuperAdmin()) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $team = $request->user()->team;
        if (! $team) {
            return response()->json(['message' => 'Team not found.'], 404);
        }

        $request->validate([
            'logo' => 'required|image|mimes:jpg,jpeg,png,svg,webp|max:5120',
        ]);

        $file = $request->file('logo');
        $path = $file->storeAs(
            $team->slug . '/logo',
            time() . '_' . $file->getClientOriginalName(),
            'r2'
        );

        /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
        $disk = \Illuminate\Support\Facades\Storage::disk('r2');

        $team->update([
            'logo_url' => $disk->url($path),
        ]);

        return new TeamResource($team);
    }
}
