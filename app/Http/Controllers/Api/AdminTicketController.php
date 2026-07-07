<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use Illuminate\Http\Request;

class AdminTicketController extends Controller
{
    public function index(Request $request)
    {
        if (!$request->user()->isSuperAdmin()) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $tickets = Ticket::with(['team', 'user'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json(['data' => $tickets]);
    }

    public function show(Request $request, Ticket $ticket)
    {
        if (!$request->user()->isSuperAdmin()) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        return response()->json(['data' => $ticket->load(['team', 'user'])]);
    }

    public function updateStatus(Request $request, Ticket $ticket)
    {
        if (!$request->user()->isSuperAdmin()) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $validated = $request->validate([
            'status' => 'required|in:open,in_progress,resolved,closed',
        ]);

        $ticket->update([
            'status' => $validated['status'],
        ]);

        return response()->json(['data' => $ticket, 'message' => 'Status tiket diperbarui.']);
    }
}
