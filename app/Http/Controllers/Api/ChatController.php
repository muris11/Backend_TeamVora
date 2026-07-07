<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Team;
use App\Models\User;
use App\Events\MessageSent;
use App\Models\TeamMedia;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function getConversations(Request $request, Team $team)
    {
        $currentUser = $request->user();

        // Pastikan user adalah anggota tim
        $isMember = $team->members()->where('users.id', $currentUser->id)->exists();
        $isLeader = $team->leader_id === $currentUser->id;

        if (!$isMember && !$isLeader) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // ===== 1. Buat / pastikan Grup "General" ada =====
        $mainGroup = Conversation::where('team_id', $team->id)
            ->where('type', 'group')
            ->first();

        if (!$mainGroup) {
            $mainGroup = Conversation::create([
                'team_id' => $team->id,
                'type' => 'group',
                'name' => 'General',
            ]);
        }

        // Pastikan user adalah participant grup
        $mainGroup->participants()->firstOrCreate([
            'user_id' => $currentUser->id,
        ]);

        // ===== 2. Buat DM dengan semua anggota tim lainnya =====
        $allMembers = $team->members()->get();

        // Tambahkan leader jika belum ada di members
        if ($team->leader_id && !$allMembers->contains('id', $team->leader_id)) {
            $leader = User::find($team->leader_id);
            if ($leader) {
                $allMembers->push($leader);
            }
        }

        foreach ($allMembers as $member) {
            if ($member->id === $currentUser->id) continue;

            // Cek apakah DM sudah ada antara currentUser dan member ini
            $existingDm = Conversation::where('team_id', $team->id)
                ->where('type', 'dm')
                ->whereHas('participants', function ($q) use ($currentUser) {
                    $q->where('user_id', $currentUser->id);
                })
                ->whereHas('participants', function ($q) use ($member) {
                    $q->where('user_id', $member->id);
                })
                ->first();

            if (!$existingDm) {
                $dm = Conversation::create([
                    'team_id' => $team->id,
                    'type' => 'dm',
                ]);

                $dm->participants()->createMany([
                    ['user_id' => $currentUser->id],
                    ['user_id' => $member->id],
                ]);
            }
        }

        // ===== 3. Ambil semua conversations =====
        $conversations = Conversation::where('team_id', $team->id)
            ->whereHas('participants', function ($q) use ($currentUser) {
                $q->where('user_id', $currentUser->id);
            })
            ->with(['participants.user:id,name,avatar_path'])
            ->with(['messages' => function ($q) {
                $q->latest()->take(1);
            }])
            ->get()
            ->sortBy(function ($conv) {
                // Sort: ada pesan terbaru di atas, belum ada pesan di bawah
                $lastMsg = $conv->messages->first();
                return $lastMsg ? $lastMsg->created_at->timestamp : 0;
            }, SORT_REGULAR, true)
            ->values();

        return response()->json($conversations);
    }

    public function getMessages(Request $request, Conversation $conversation)
    {
        if (!$conversation->participants()->where('user_id', $request->user()->id)->exists()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $messages = $conversation->messages()
            ->with(['sender:id,name,avatar_path', 'media'])
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json($messages);
    }

    public function sendMessage(Request $request, Conversation $conversation)
    {
        if (!$conversation->participants()->where('user_id', $request->user()->id)->exists()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'content' => 'required_without:file|string|nullable',
            'file' => 'nullable|file|max:10240',
        ]);

        $mediaId = null;
        $attachmentPath = null;

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $path = $file->store('team_media/' . $conversation->team_id, 'r2');

            $media = TeamMedia::create([
                'team_id' => $conversation->team_id,
                'user_id' => $request->user()->id,
                'type' => 'document',
                'name' => $file->getClientOriginalName(),
                'file_path' => $path,
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
            ]);

            $mediaId = $media->id;
            $attachmentPath = $path;
        }

        $message = $conversation->messages()->create([
            'sender_id' => $request->user()->id,
            'content' => $request->input('content'),
            'attachment_path' => $attachmentPath,
            'media_id' => $mediaId,
        ]);

        $message->load('sender:id,name,avatar_path', 'media');

        broadcast(new MessageSent($message))->toOthers();

        return response()->json($message, 201);
    }

    public function startDm(Request $request, Team $team)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id'
        ]);

        $currentUserId = $request->user()->id;
        $targetUserId = $request->input('user_id');

        if ($currentUserId == $targetUserId) {
            return response()->json(['message' => 'Tidak bisa chat dengan diri sendiri'], 400);
        }

        $conversation = Conversation::where('team_id', $team->id)
            ->where('type', 'dm')
            ->whereHas('participants', function ($q) use ($currentUserId) {
                $q->where('user_id', $currentUserId);
            })
            ->whereHas('participants', function ($q) use ($targetUserId) {
                $q->where('user_id', $targetUserId);
            })
            ->first();

        if (!$conversation) {
            $conversation = Conversation::create([
                'team_id' => $team->id,
                'type' => 'dm',
            ]);

            $conversation->participants()->createMany([
                ['user_id' => $currentUserId],
                ['user_id' => $targetUserId],
            ]);
        }

        return response()->json($conversation);
    }
}
