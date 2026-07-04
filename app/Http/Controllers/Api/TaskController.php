<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use App\Models\User;
use App\Notifications\TaskAssignedNotification;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $query = Task::with(['creator:id,name', 'assignee:id,name,avatar_path']);

        if (! $request->user()->isSuperAdmin()) {
            $query->where('team_id', $request->user()->team_id);
        }

        $tasks = $query->orderBy('created_at', 'desc')
            ->get();

        return TaskResource::collection($tasks)->additional([
            'users' => User::select('id', 'name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'assignee_id' => 'nullable|exists:users,id',
            'priority' => 'required|in:low,medium,high',
            'due_date' => 'nullable|date',
        ]);

        $task = Task::create([
            'creator_id' => $request->user()->id,
            'team_id' => $request->user()->team_id,
            'assignee_id' => $validated['assignee_id'],
            'title' => $validated['title'],
            'description' => $validated['description'] ?? '',
            'priority' => $validated['priority'],
            'status' => 'todo',
            'due_date' => $validated['due_date'],
        ]);

        if ($task->assignee_id && $task->assignee_id !== $request->user()->id) {
            $task->assignee->notify(new TaskAssignedNotification($task));
        }

        return new TaskResource($task->load(['creator:id,name', 'assignee:id,name']));
    }

    public function show(Task $task)
    {
        return new TaskResource($task->load(['creator:id,name', 'assignee:id,name']));
    }

    public function update(Request $request, Task $task)
    {
        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'assignee_id' => 'nullable|exists:users,id',
            'priority' => 'sometimes|in:low,medium,high',
            'due_date' => 'nullable|date',
        ]);

        $task->update($validated);
        return new TaskResource($task->fresh()->load(['creator:id,name', 'assignee:id,name']));
    }

    public function updateStatus(Request $request, Task $task)
    {
        $validated = $request->validate([
            'status' => 'required|in:todo,in_progress,done',
        ]);

        $task->update(['status' => $validated['status']]);
        return new TaskResource($task->fresh());
    }

    public function destroy(Request $request, Task $task)
    {
        if ($request->user()->id !== $task->creator_id && ! $request->user()->hasRole('Admin')) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $task->delete();
        return response()->json(['message' => 'Task dihapus.']);
    }
}
