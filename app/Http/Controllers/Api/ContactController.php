<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name'  => 'required|string|max:255',
            'email'      => 'required|email|max:255',
            'company'    => 'nullable|string|max:255',
            'message'    => 'required|string|min:10',
        ]);

        $message = ContactMessage::create($validated);

        return response()->json([
            'message' => 'Pesan berhasil dikirim.',
            'data'    => $message,
        ], 201);
    }

    public function index(Request $request)
    {
        $query = ContactMessage::query();

        if ($request->has('unread')) {
            $query->where('is_read', false);
        }

        $messages = $query->orderByDesc('created_at')->paginate(20);

        return response()->json(['data' => $messages]);
    }

    public function markRead($id)
    {
        $message = ContactMessage::findOrFail($id);
        $message->update(['is_read' => true]);

        return response()->json(['message' => 'Pesan ditandai sudah dibaca.']);
    }
}
