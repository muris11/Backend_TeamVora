<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\DailyLogResource;
use App\Models\DailyLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DailyLogController extends Controller
{
    public function index(Request $request)
    {
        $query = DailyLog::where('user_id', $request->user()->id);

        if (! $request->user()->isSuperAdmin()) {
            $query->where('team_id', $request->user()->team_id);
        }

        $logs = $query->orderBy('log_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return DailyLogResource::collection($logs);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'log_date' => 'required|date|before_or_equal:today',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        $path = null;
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $path = $file->storeAs('dailylogs/' . date('Y/m'), time() . '_' . $file->getClientOriginalName(), 'public');
        }

        $log = DailyLog::create([
            'user_id' => $request->user()->id,
            'team_id' => $request->user()->team_id,
            'log_date' => $validated['log_date'],
            'title' => $validated['title'],
            'content' => $validated['content'],
            'attachment_path' => $path ? Storage::disk('public')->url($path) : null,
        ]);

        return new DailyLogResource($log);
    }

    public function show(DailyLog $dailyLog)
    {
        return new DailyLogResource($dailyLog->load('user'));
    }

    public function update(Request $request, DailyLog $dailyLog)
    {
        if ($request->user()->id !== $dailyLog->user_id) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        $dailyLog->update($validated);
        return new DailyLogResource($dailyLog->fresh());
    }

    public function destroy(Request $request, DailyLog $dailyLog)
    {
        if ($request->user()->id !== $dailyLog->user_id) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        if ($dailyLog->created_at->diffInDays(now()) > 3) {
            return response()->json(['message' => 'Log > 3 hari tidak bisa dihapus.'], 422);
        }

        $dailyLog->delete();
        return response()->json(['message' => 'Log dihapus.']);
    }

    public function exportData(Request $request)
    {
        $logs = DailyLog::where('user_id', $request->user()->id)
            ->orderBy('log_date', 'asc')
            ->get();

        return response()->json([
            'user' => ['name' => $request->user()->name, 'email' => $request->user()->email],
            'logs' => DailyLogResource::collection($logs),
        ]);
    }
}
