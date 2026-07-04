<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TeamInvitationResource;
use App\Models\Team;
use App\Models\TeamInvitation;
use App\Models\User;
use App\Notifications\TeamInvitationNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TeamInvitationController extends Controller
{
    public function send(Request $request, Team $team)
    {
        if (! $request->user()->isSuperAdmin() && ! $request->user()->isTeamLeader()) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $validated = $request->validate([
            'email' => 'required|email',
        ]);

        $invitation = TeamInvitation::create([
            'team_id' => $team->id,
            'invited_by' => $request->user()->id,
            'email' => $validated['email'],
            'token' => Str::random(64),
            'status' => 'pending',
            'expires_at' => now()->addDays(7),
        ]);

        $invitation->load('team', 'inviter');

        $recipient = User::where('email', $validated['email'])->first();
        if ($recipient) {
            $recipient->notify(new TeamInvitationNotification($invitation));
        } else {
            \Illuminate\Support\Facades\Notification::route('mail', $validated['email'])
                ->notify(new TeamInvitationNotification($invitation));
        }

        return new TeamInvitationResource($invitation);
    }

    public function accept(string $token)
    {
        $invitation = TeamInvitation::where('token', $token)
            ->with('team')
            ->firstOrFail();

        if ($invitation->isExpired()) {
            $invitation->update(['status' => 'expired']);
            return response()->json(['message' => 'Undangan sudah kadaluarsa.'], 410);
        }

        if ($invitation->status !== 'pending') {
            return response()->json(['message' => 'Undangan sudah tidak valid.'], 422);
        }

        $user = User::where('email', $invitation->email)->first();

        if (! $user) {
            return response()->json([
                'message' => 'User belum terdaftar. Silakan register terlebih dahulu.',
                'email' => $invitation->email,
                'team_id' => $invitation->team_id,
                'token' => $token,
            ], 200);
        }

        $user->update([
            'team_id' => $invitation->team_id,
            'role' => 'member',
        ]);

        $user->assignRole('Member');

        $invitation->update(['status' => 'accepted']);

        return response()->json([
            'message' => 'Berhasil bergabung dengan tim.',
            'team' => $invitation->team,
        ]);
    }

    public function list(Team $team)
    {
        $invitations = TeamInvitation::where('team_id', $team->id)
            ->where('status', 'pending')
            ->where('expires_at', '>', now())
            ->with('inviter:id,name,email')
            ->orderBy('created_at', 'desc')
            ->get();

        return TeamInvitationResource::collection($invitations);
    }
}
