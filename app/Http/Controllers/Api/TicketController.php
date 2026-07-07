<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TicketController extends Controller
{
    public function index(Request $request)
    {
        $team = $request->user()->team;
        if (!$team || !$request->user()->isTeamLeader()) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $tickets = Ticket::where('team_id', $team->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json(['data' => $tickets]);
    }

    public function store(Request $request)
    {
        $team = $request->user()->team;
        if (!$team || !$request->user()->isTeamLeader()) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'category' => 'required|in:bug,feature,billing,other',
            'priority' => 'required|in:low,medium,high',
            'attachment' => 'nullable|file|max:10240', // 10MB max
        ]);

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $filename = Str::random(40) . '.' . $file->getClientOriginalExtension();
            $path = "teams/{$team->id}/tickets/{$filename}";
            Storage::disk('r2')->put($path, file_get_contents($file));
            
            $cdnBase = rtrim(config('filesystems.disks.r2.url', 'https://' . env('R2_CUSTOM_DOMAIN')), '/');
            $attachmentPath = $cdnBase . '/' . ltrim($path, '/');
        }

        $ticket = Ticket::create([
            'team_id' => $team->id,
            'user_id' => $request->user()->id,
            'title' => $validated['title'],
            'description' => $validated['description'],
            'category' => $validated['category'],
            'priority' => $validated['priority'],
            'status' => 'open',
            'attachment_path' => $attachmentPath,
        ]);

        return response()->json(['data' => $ticket, 'message' => 'Tiket berhasil dibuat.'], 201);
    }

    public function show(Request $request, Ticket $ticket)
    {
        $team = $request->user()->team;
        if (!$team || !$request->user()->isTeamLeader() || $ticket->team_id !== $team->id) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        return response()->json(['data' => $ticket]);
    }
}
